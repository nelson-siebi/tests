<?php
// api/create_pending_transaction.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gérer les requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Démarrer la session

// Inclure la configuration de la base de données
require_once __DIR__ . '/../config/db.php';
$pdo = Database::getInstance()->getConnection();


// Exporter une instance globale si nécessaire
// $db = Database::getInstance()->getConnection();



// Fonction pour générer un code de transaction unique
function generateTransactionCode() {
    return 'TXN' . date('YmdHis') . strtoupper(bin2hex(random_bytes(4)));
}

// Fonction pour générer un code USSD
function generateUSSDCode($method, $amount, $phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if ($method === 'orange') {
        // Format: *126*1*1*numero*montant#
        return "*126*1*1*{$phone}*{$amount}#";
    } elseif ($method === 'mtn') {
        // Format: *126*1*1*numero*montant#
        return "*126*1*1*{$phone}*{$amount}#";
    } else {
        return null;
    }
}

// Vérifier la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit();
}

// Vérifier si l'utilisateur est connecté
// if (!isset($_SESSION['userId'])) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'message' => 'Non authentifié']);
//     exit();
// }

// Récupérer et valider les données
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

// Validation des champs requis
$required_fields = ['plan_id', 'montant', 'methode', 'phone'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Le champ $field est requis"]);
        exit();
    }
}

// Validation supplémentaire
$plan_id = intval($data['plan_id']);
$montant = floatval($data['montant']);
$methode = in_array($data['methode'], ['orange', 'mtn']) ? $data['methode'] : 'orange';
$phone = trim($data['phone']);
$user_id = intval($data['user_id']);


// Vérifier si le plan existe et est actif
try {
    // $pdo = Database::getInstance()->getConnection();
    
    // Vérifier le plan
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ? AND actif = 1");
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Plan invalide ou inactif']);
        exit();
    }
    
    // Vérifier le montant minimum
    if ($montant < $plan['prix']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Montant inférieur au minimum requis']);
        exit();
    }
    
    // Vérifier si l'utilisateur a déjà une transaction en attente pour ce plan
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM pending_transactions 
        WHERE user_id = ? AND plan_id = ? AND status IN ('pending', 'processing')
        AND expires_at > NOW()
    ");
    $stmt->execute([$user_id, $plan_id]);
    $existing_pending = $stmt->fetch();
    
    if ($existing_pending['count'] > 10000) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Vous avez déjà une transaction en attente pour ce plan'
        ]);
        exit();
    }
    
    // Générer un code de transaction unique
    $transaction_code = generateTransactionCode();
    
    // Générer le code USSD
    $ussd_code = generateUSSDCode($methode, $montant, $phone);
    
    if (!$ussd_code) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur de génération du code USSD']);
        exit();
    }
    
    // Calculer la date d'expiration (15 minutes)
    $expires_at = date('Y-m-d H:i:s', strtotime('+150000 minutes'));
    
    // Stocker les données de session
    $session_data = [
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'referrer' => $_SERVER['HTTP_REFERER'] ?? null,
        'timestamp' => time()
    ];
    
    // Insérer la transaction en attente
    $stmt = $pdo->prepare("
        INSERT INTO pending_transactions 
        (user_id, plan_id, montant, methode, numero_telephone, transaction_code, status, expires_at, session_data) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)
    ");
    
    $stmt->execute([
        $user_id,
        $plan_id,
        $montant,
        $methode,
        $phone,
        $transaction_code,
        $expires_at,
        json_encode($session_data)
    ]);
    
    $transaction_id = $pdo->lastInsertId();
    
    // Enregistrer le log de sécurité
    // $stmt = $pdo->prepare("
    //     INSERT INTO security_logs (user_id, action, ip_address, user_agent, details) 
    //     VALUES (?, 'pending_transaction_created', ?, ?, ?)
    // ");
    
    // $log_details = json_encode([
    //     'transaction_id' => $transaction_id,
    //     'plan_id' => $plan_id,
    //     'montant' => $montant,
    //     'methode' => $methode
    // ]);
    
    // $stmt->execute([
    //     $user_id,
    //     $_SERVER['REMOTE_ADDR'] ?? null,
    //     $_SERVER['HTTP_USER_AGENT'] ?? null,
    //     $log_details
    // ]);
    
    // Réponse JSON
    echo json_encode([
        'success' => true,
        'message' => 'Transaction créée avec succès',
        'data' => [
            'transaction_id' => $transaction_id,
            'transaction_code' => $transaction_code,
            'ussd_code' => $ussd_code,
            'expires_at' => $expires_at,
            'countdown' => 900, // 15 minutes en secondes
            'instructions' => [
                'orange' => 'Composez le code sur votre téléphone et suivez les instructions',
                'mtn' => 'Composez le code sur votre téléphone et suivez les instructions'
            ]
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Transaction pending error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur serveur',
        'debug' => (DEBUG_MODE ? $e->getMessage() : null)
    ]);
}
?>