<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO('mysql:host=localhost;dbname=invest;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Création de données de test</h2>";
    
    // 1. Créer un utilisateur test
    $password = password_hash('test123', PASSWORD_DEFAULT);
    $referral_code = 'TEST' . rand(1000, 9999);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (nom, prenom, email, phone, password, referral_code, statut, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    $stmt->execute(['Test', 'User', 'test@example.com', '+237123456789', $password, $referral_code]);
    $user_id = $pdo->lastInsertId();
    echo "<div>✓ Utilisateur créé (ID: $user_id)</div>";
    
    // 2. Créer un wallet pour l'utilisateur
    $stmt = $pdo->prepare("
        INSERT INTO wallets (user_id, solde_investissement, solde_publicite, solde_parrainage, created_at) 
        VALUES (?, 0, 0, 0, NOW())
    ");
    $stmt->execute([$user_id]);
    echo "<div>✓ Wallet créé</div>";
    
    // 3. Vérifier qu'un plan existe
    $stmt = $pdo->query("SELECT id FROM plans WHERE actif = 1 LIMIT 1");
    $plan = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$plan) {
        // Créer un plan test
        $stmt = $pdo->prepare("
            INSERT INTO plans (nom, prix, roi_journalier, duree_jours, videos_par_jour, gain_par_video, actif, description) 
            VALUES ('Plan Test', 5000.00, 500.00, 30, 5, 10.00, 1, 'Plan de test')
        ");
        $stmt->execute();
        $plan_id = $pdo->lastInsertId();
        echo "<div>✓ Plan test créé (ID: $plan_id)</div>";
    } else {
        $plan_id = $plan['id'];
        echo "<div>✓ Plan existant utilisé (ID: $plan_id)</div>";
    }
    
    // 4. Créer une transaction en attente
    $transaction_code = 'TEST' . time() . rand(100, 999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    $stmt = $pdo->prepare("
        INSERT INTO pending_transactions (user_id, plan_id, montant, methode, numero_telephone, transaction_code, status, expires_at, created_at) 
        VALUES (?, ?, ?, 'orange', '237123456789', ?, 'pending', ?, NOW())
    ");
    $stmt->execute([$user_id, $plan_id, 5000.00, $transaction_code, $expires_at]);
    $transaction_id = $pdo->lastInsertId();
    
    echo "<div>✓ Transaction en attente créée :</div>";
    echo "<ul>";
    echo "<li>ID Transaction: $transaction_id</li>";
    echo "<li>Code: $transaction_code</li>";
    echo "<li>Montant: 5000 FCFA</li>";
    echo "<li>Expire à: $expires_at</li>";
    echo "<li>Lien test: <a href='test_validation.php?action=validate&id=$transaction_id'>Valider cette transaction</a></li>";
    echo "</ul>";
    
    echo "<h3>Données créées avec succès !</h3>";
    echo "<p><a href='test_validation.php'>Tester maintenant</a></p>";
    
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>