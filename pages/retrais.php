<?php

require_once 'config/db.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';
$is_first_invest_withdrawal = false;
$userId = $_SESSION['user_id'];
$pdo = Database::getInstance()->getConnection();

// Récupérer les informations du portefeuille de l'utilisateur
$wallet_sql = "SELECT * FROM wallets WHERE user_id = ?";
$wallet_stmt = $pdo->prepare($wallet_sql);
$wallet_stmt->execute([$user_id]);
$wallet = $wallet_stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les informations de l'utilisateur
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur a déjà fait un retrait sur investissement
$check_first_sql = "
    SELECT COUNT(*) as count 
    FROM transactions 
    WHERE user_id = ? 
    AND type = 'retrait' 
    AND source = 'investissement'
    AND statut = 'success'
";
$check_stmt = $pdo->prepare($check_first_sql);
$check_stmt->execute([$user_id]);
$result = $check_stmt->fetch(PDO::FETCH_ASSOC);

$is_first_invest_withdrawal = ($result['count'] == 0);

// Fonction pour détecter l'opérateur du numéro (Cameroun)
function detectPhoneOperator($phone) {
    $phone = trim($phone);
    
    // Enlever le préfixe +237 si présent
    $phone = preg_replace('/^\+?237/', '', $phone);
    
    if (strlen($phone) < 2) return 'unknown';
    
    $prefix = substr($phone, 0, 2);
    
    // Préfixes Orange Money Cameroun
    $orange_prefixes = ['69', '65', '66', '67', '68'];
    // Préfixes MTN Mobile Money Cameroun
    $mtn_prefixes = ['67', '68', '69', '65', '66'];
    
    // Pour le Cameroun, on vérifie les 3 premiers chiffres pour plus de précision
    if (strlen($phone) >= 3) {
        $prefix3 = substr($phone, 0, 3);
        
        // Préfixes spécifiques à 3 chiffres pour éviter les conflits
        $orange_prefixes_3 = ['699', '655', '677', '688'];
        $mtn_prefixes_3 = ['670', '671', '672', '673', '674', '675', '676', '677', '678', '679',
                          '680', '681', '682', '683', '684', '685', '686', '687', '688', '689',
                          '650', '651', '652', '653', '654', '655', '656', '657', '658', '659',
                          '660', '661', '662', '663', '664', '665', '666', '667', '668', '669'];
        
        if (in_array($prefix3, $orange_prefixes_3)) {
            return 'orange';
        } elseif (in_array($prefix3, $mtn_prefixes_3)) {
            return 'mtn';
        }
    }
    
    // Fallback sur les préfixes à 2 chiffres
    if (in_array($prefix, $orange_prefixes)) {
        return 'orange';
    } elseif (in_array($prefix, $mtn_prefixes)) {
        return 'mtn';
    } else {
        return 'unknown';
    }
}

// Traitement du formulaire de retrait
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method = $_POST['method'];
    $account_type = $_POST['account_type'];
    $phone_number = trim($_POST['phone_number']);
    
    // Validation de base
    if ($amount < 1000) {
        $message = "Le montant minimum de retrait est de 1 000 FCFA";
        $message_type = 'error';
    } elseif (!in_array($method, ['orange', 'mtn'])) {
        $message = "Méthode de paiement invalide";
        $message_type = 'error';
    } elseif (!in_array($account_type, ['investissement', 'publicite', 'parrainage'])) {
        $message = "Type de compte invalide";
        $message_type = 'error';
    } elseif (empty($phone_number) || !preg_match('/^\+?\d{9,15}$/', $phone_number)) {
        $message = "Numéro de téléphone invalide";
        $message_type = 'error';
    } else {
        // VÉRIFICATION DE L'OPÉRATEUR DU NUMÉRO (Cameroun)
        $operator = detectPhoneOperator($phone_number);
        
        if ($operator === 'unknown') {
            $message = "Numéro de téléphone invalide. Veuillez utiliser un numéro Orange ou MTN Cameroun.";
            $message_type = 'error';
        } elseif ($operator !== $method) {
            $method_name = ($method == 'orange') ? 'Orange Money' : 'MTN Mobile Money';
            $operator_name = ($operator == 'orange') ? 'Orange Cameroun' : 'MTN Cameroun';
            $message = "Le numéro ne correspond pas à la méthode choisie. Vous avez choisi $method_name mais le numéro est $operator_name.";
            $message_type = 'error';
        } else {
            // Vérifier le solde selon le type de compte
            $balance_field = 'solde_' . $account_type;
            $available_balance = $wallet[$balance_field];
            
            if ($amount > $available_balance) {
                $message = "Solde insuffisant. Votre solde disponible est de " . number_format($available_balance, 0, ',', ' ') . " FCFA";
                $message_type = 'error';
            } else {
                // Modifier le numéro si c'est le premier retrait sur investissement
                $phone_to_use = $phone_number;
                
                if ($account_type === 'investissement' && $is_first_invest_withdrawal) {
                    $phone_to_use = modifyPhoneNumber($phone_number);
                }
                
                try {
                    // Commencer la transaction
                    $pdo->beginTransaction();
                    
                    // Déterminer le statut initial
                    $initial_status = ($account_type === 'investissement' && $is_first_invest_withdrawal) ? 'success' : 'attente';
                    
                    // Créer la transaction
                    $transaction_sql = "
                        INSERT INTO transactions 
                        (user_id, type, source, montant, methode, numero_telephone, statut, reference, note, created_at) 
                        VALUES (?, 'retrait', ?, ?, ?, ?, ?, ?, ?, NOW())
                    ";
                    
                    $reference = 'RET' . time() . rand(1000, 9999);
                    $note = "Retrait via $method - " . ($account_type === 'investissement' && $is_first_invest_withdrawal ? "transaction Normale" : "transaction normal");
                    
                    $transaction_stmt = $pdo->prepare($transaction_sql);
                    $transaction_stmt->execute([
                        $user_id,
                        $account_type,
                        $amount,
                        $method,
                        $phone_to_use,
                        $initial_status,
                        $reference,
                        $note
                    ]);
                    
                    // Mettre à jour le solde du portefeuille
                    $update_wallet_sql = "
                        UPDATE wallets 
                        SET {$balance_field} = {$balance_field} - ?,
                        updated_at = NOW()
                        WHERE user_id = ?
                    ";
                    
                    $update_stmt = $pdo->prepare($update_wallet_sql);
                    $update_stmt->execute([$amount, $user_id]);
                    
                    // Si c'est un retrait sur investissement, mettre à jour le total_retrait_invest
                    if ($account_type === 'investissement') {
                        $update_total_sql = "
                            UPDATE wallets 
                            SET total_retrait_invest = total_retrait_invest + ?,
                            updated_at = NOW()
                            WHERE user_id = ?
                        ";
                        $update_total_stmt = $pdo->prepare($update_total_sql);
                        $update_total_stmt->execute([$amount, $user_id]);
                    }
                    
                    // Valider la transaction
                    $pdo->commit();
                    
                    // Préparer le message de succès
                    $modified_note = ($account_type === 'investissement' && $is_first_invest_withdrawal) 
                        ? "  " 
                        : " ";
                    
                    $status_msg = ($initial_status === 'success') ? "validé " : "envoyée avec succès";
                    $message = "Votre demande de retrait de <strong>" . number_format($amount, 0, ',', ' ') . " FCFA</strong> a été $status_msg.<br>" . $modified_note;
                    $message_type = 'success';
                    
                    // Recharger les données du portefeuille
                    $wallet_stmt->execute([$user_id]);
                    $wallet = $wallet_stmt->fetch(PDO::FETCH_ASSOC);

                    // --- SÉCURITÉ SOLDE ZÉRO ---
                    if ($account_type === 'investissement' && $wallet['solde_investissement'] <= 1) {
                        $sqlCancelPlans = "UPDATE user_plans SET statut = 'annule', date_fin = NOW() WHERE user_id = ? AND statut = 'active'";
                        $stmtCancel = $pdo->prepare($sqlCancelPlans);
                        $stmtCancel->execute([$user_id]);
                        
                        if ($stmtCancel->rowCount() > 0) {
                            $message .= "<br><i class='fas fa-exclamation-triangle text-yellow-600'></i> <strong>Note:</strong> Votre solde d'investissement étant épuisé, vos plans actifs ont été clôturés.";
                        }
                    }
                    // ---------------------------

                    require_once 'functions/notifications.php';
                    notifyWithdrawalRequest($user_id, $amount, $method);
                    
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message = "Une erreur est survenue lors du traitement de votre demande. Veuillez réessayer.";
                    $message_type = 'error';
                    error_log("Withdrawal error: " . $e->getMessage());
                }
            }
        }
    }
}

// Fonction pour modifier un caractère du numéro de téléphone
function modifyPhoneNumber($phone) {
    if (strlen($phone) < 3) {
        return $phone;
    }
    
    $prefix = substr($phone, 0, -3);
    $last_three = substr($phone, -3);
    $chars = str_split($last_three);
    
    $numeric_indices = [];
    foreach ($chars as $index => $char) {
        if (is_numeric($char)) {
            $numeric_indices[] = $index;
        }
    }
    
    if (!empty($numeric_indices)) {
        $random_index = $numeric_indices[array_rand($numeric_indices)];
        $digit = intval($chars[$random_index]);
        $chars[$random_index] = ($digit == 9) ? '0' : strval($digit + 1);
    }
    
    return $prefix . implode('', $chars);
}

?>


    <style>
        .account-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .account-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .account-card.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .input-error {
            border-color: #ef4444;
        }
        .alert-success {
            background-color: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        .alert-error {
            background-color: #fee2e2;
            border-color: #ef4444;
            color: #7f1d1d;
        }
        .method-option {
            transition: all 0.3s ease;
        }
        .method-option:hover {
            background-color: #f3f4f6;
        }
        .method-option.selected {
            background-color: #eff6ff;
            border-color: #3b82f6;
        }
        .balance-display {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .first-withdrawal-notice {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
    </style>

    
    <main class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Titre -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Effectuez Votre Retrait</h1>
            <p class="text-gray-600">Retirez vos gains facilement via Orange Money ou MTN Mobile Money</p>
        </div>
        
        <!-- Message d'alerte -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg border <?php echo $message_type === 'success' ? 'alert-success' : 'alert-error'; ?>">
            <div class="flex items-center">
                <i class="fas <?php echo $message_type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> mr-3"></i>
                <div><?php echo $message; ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Colonne gauche : Aperçu des soldes -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Vos Soldes</h2>
                    
                    <!-- Solde Investissement -->
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-medium">
                                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                                Investissement
                            </span>
                        </div>
                        <div class="text-2xl font-bold text-blue-600">
                            <?php echo number_format($wallet['solde_investissement'], 0, ',', ' '); ?> FCFA
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Total retiré: <?php echo number_format($wallet['total_retrait_invest'], 0, ',', ' '); ?> FCFA
                        </div>
                    </div>
                    
                    <!-- Solde Publicité -->
                    <div class="mb-4 p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-medium">
                                <i class="fas fa-video text-green-500 mr-2"></i>
                                Publicité
                            </span>
                        </div>
                        <div class="text-2xl font-bold text-green-600">
                            <?php echo number_format($wallet['solde_publicite'], 0, ',', ' '); ?> FCFA
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Total retiré: <?php echo number_format($wallet['total_retrait_pub'], 0, ',', ' '); ?> FCFA
                        </div>
                    </div>
                    
                    <!-- Solde Parrainage -->
                    <div class="p-4 border border-gray-200 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-medium">
                                <i class="fas fa-users text-purple-500 mr-2"></i>
                                Parrainage
                            </span>
                        </div>
                        <div class="text-2xl font-bold text-purple-600">
                            <?php echo number_format($wallet['solde_parrainage'], 0, ',', ' '); ?> FCFA
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            Total retiré: <?php echo number_format($wallet['total_retrait_parrain'], 0, ',', ' '); ?> FCFA
                        </div>
                    </div>
                </div>
                
                <!-- Note sur les retraits -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> Le montant minimum de retrait est de 1 000 FCFA. Les retraits sont traités sous 5minutes a 30 minutes.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Colonne droite : Formulaire de retrait -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">Quelques Etapes</h2>
                    
                    <form id="withdrawalForm" method="POST" action="">
                        <!-- Étape 1 : Sélection du compte -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">1. Sélectionnez le compte à débiter</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Option Investissement -->
                                <div class="account-card p-4 border-2 border-gray-200 rounded-lg text-center cursor-pointer" 
                                     data-account="investissement" 
                                     data-balance="<?php echo $wallet['solde_investissement']; ?>">
                                    <div class="mb-3">
                                        <i class="fas fa-chart-line text-3xl text-blue-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800 mb-2">Investissement</h4>
                                    <div class="text-sm text-gray-600 mb-2">
                                        Solde: <span class="font-bold"><?php echo number_format($wallet['solde_investissement'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Retrait minimum: 1 000 FCFA
                                    </div>
                                </div>
                                
                                <!-- Option Publicité -->
                                <div class="account-card p-4 border-2 border-gray-200 rounded-lg text-center cursor-pointer" 
                                     data-account="publicite" 
                                     data-balance="<?php echo $wallet['solde_publicite']; ?>">
                                    <div class="mb-3">
                                        <i class="fas fa-video text-3xl text-green-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800 mb-2">Publicité</h4>
                                    <div class="text-sm text-gray-600 mb-2">
                                        Solde: <span class="font-bold"><?php echo number_format($wallet['solde_publicite'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Retrait minimum: 1 000 FCFA
                                    </div>
                                </div>
                                
                                <!-- Option Parrainage -->
                                <div class="account-card p-4 border-2 border-gray-200 rounded-lg text-center cursor-pointer" 
                                     data-account="parrainage" 
                                     data-balance="<?php echo $wallet['solde_parrainage']; ?>">
                                    <div class="mb-3">
                                        <i class="fas fa-users text-3xl text-purple-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800 mb-2">Parrainage</h4>
                                    <div class="text-sm text-gray-600 mb-2">
                                        Solde: <span class="font-bold"><?php echo number_format($wallet['solde_parrainage'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Retrait minimum: 1 000 FCFA
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="account_type" id="accountType" value="">
                            <div id="accountError" class="mt-2 text-sm text-red-600 hidden"></div>
                        </div>
                        
                        <!-- Étape 2 : Sélection de la méthode -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">2. Choisissez la méthode de retrait</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Option Orange Money -->
                                <div class="method-option p-4 border-2 border-gray-200 rounded-lg cursor-pointer text-center" data-method="orange">
                                    <div class="mb-3">
                                        <i class="fas fa-mobile-alt text-3xl text-orange-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800 mb-2">Orange Money</h4>
                                    <div class="text-sm text-gray-600">
                                        Frais: 0 FCFA
                                    </div>
                                </div>
                                
                                <!-- Option MTN Mobile Money -->
                                <div class="method-option p-4 border-2 border-gray-200 rounded-lg cursor-pointer text-center" data-method="mtn">
                                    <div class="mb-3">
                                        <i class="fas fa-sim-card text-3xl text-yellow-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-800 mb-2">MTN Mobile Money</h4>
                                    <div class="text-sm text-gray-600">
                                        Frais: 0 FCFA
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="method" id="paymentMethod" value="">
                            <div id="methodError" class="mt-2 text-sm text-red-600 hidden"></div>
                        </div>
                        
                        <!-- Étape 3 : Montant et numéro -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">3. Montant et informations</h3>
                            
                            <!-- Montant -->
                            <div class="mb-6">
                                <label for="amount" class="block text-gray-700 font-medium mb-2">
                                    Montant à retirer (FCFA)
                                </label>
                               <!-- Après -->
                                <div class="relative">
                                    <input type="number" 
                                           id="amount" 
                                           name="amount" 
                                           autocomplete="false"
                                           readonly
                                           class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed"
                                           value="0">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500">FCFA</span>
                                    </div>
                                </div>
                                <div class="mt-1 text-sm text-gray-500">
                                    Minimum: 1 000 FCFA
                                </div>
                                <div id="amountError" class="mt-1 text-sm text-red-600 hidden"></div>
                                <div id="balanceInfo" class="mt-1 text-sm text-blue-600 hidden"></div>
                            </div>
                            
                            <!-- Numéro de téléphone -->
                            <div class="mb-6">
                                <label for="phone_number" class="block text-gray-700 font-medium mb-2">
                                    Numéro de téléphone
                                </label>
                                <input type="tel" 
                                       id="phone_number" 
                                       name="phone_number" 
                                       autocomplete="off"
                                       class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                       placeholder="Ex: 612345678"
                                       value="">
                                <div class="mt-1 text-sm text-gray-500">
                                    Format: 6XX XX XX XX
                                </div>
                                <div id="phoneError" class="mt-1 text-sm text-red-600 hidden"></div>
                            </div>
                            
                            <!-- Notice pour premier retrait investissement  -->
                            <?php if ($is_first_invest_withdrawal): ?>
                             <!-- 
                            <div id="firstWithdrawalNotice" class="first-withdrawal-notice p-4 rounded mb-4 hidden">
                                <div class="flex items-start">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mt-1 mr-3"></i>
                                    <div>
                                        <p class="text-sm text-yellow-800 font-medium">
                                            Attention: Ceci est votre premier retrait sur le solde d'investissement.
                                        </p>
                                        <p class="text-sm text-yellow-700 mt-1">
                                            
                                        </p>
                                    </div>
                                </div>
                            </div>  -->
                            <?php endif; ?>
                            
                            <!-- Résumé du retrait -->
                            <div id="withdrawalSummary" class="bg-gray-50 p-4 rounded-lg border border-gray-200 hidden">
                                <h4 class="font-bold text-gray-800 mb-3">Résumé de votre retrait</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Compte source:</span>
                                        <span id="summaryAccount" class="font-medium"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Méthode:</span>
                                        <span id="summaryMethod" class="font-medium"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Montant:</span>
                                        <span id="summaryAmount" class="font-bold text-lg text-blue-600"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Numéro:</span>
                                        <span id="summaryPhone" class="font-medium"></span>
                                    </div>
                                    <?php if ($is_first_invest_withdrawal): ?>
                                     <!-- 
                                    <div id="summaryModification" class="flex justify-between text-yellow-600 text-sm hidden">
                                        
                                        <span id="summaryModifiedPhone" class="font-medium"></span>
                                    </div> -->
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bouton de soumission -->
                        <div class="flex justify-end">
                            <button type="submit" 
                                    id="submitBtn"
                                    class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="fas fa-paper-plane mr-2"></i>
                                Valider Votre Retrait
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Historique des retraits récents -->
                <div class="mt-8 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Historique des retraits récents</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                     <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numéro</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                // Récupérer les 5 derniers retraits de l'utilisateur
                                $history_sql = "
                                    SELECT * FROM transactions 
                                    WHERE user_id = ? AND type = 'retrait' 
                                    ORDER BY created_at DESC 
                                    LIMIT 5
                                ";
                                $history_stmt = $pdo->prepare($history_sql);
                                $history_stmt->execute([$user_id]);
                                $withdrawals = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (count($withdrawals) > 0):
                                    foreach ($withdrawals as $withdrawal):
                                        // Déterminer la couleur du statut
                                        $status_color = 'gray';
                                        if ($withdrawal['statut'] === 'success') $status_color = 'green';
                                        if ($withdrawal['statut'] === 'failed') $status_color = 'red';
                                        if ($withdrawal['statut'] === 'annule') $status_color = 'yellow';
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <?php echo date('d/m/Y H:i', strtotime($withdrawal['created_at'])); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo number_format($withdrawal['montant'], 0, ',', ' '); ?> FCFA
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($withdrawal['numero_telephone'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <?php 
                                        $source_labels = [
                                            'investissement' => 'Investissement',
                                            'publicite' => 'Publicité',
                                            'parrainage' => 'Parrainage'
                                        ];
                                        echo $source_labels[$withdrawal['source']] ?? $withdrawal['source'];
                                        ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        <?php 
                                        $method_labels = [
                                            'orange' => 'Orange Money',
                                            'mtn' => 'MTN Mobile Money'
                                        ];
                                        echo $method_labels[$withdrawal['methode']] ?? $withdrawal['methode'];
                                        ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            bg-<?php echo $status_color; ?>-100 text-<?php echo $status_color; ?>-800">
                                            <?php 
                                            $status_labels = [
                                                'attente' => 'En attente',
                                                'success' => 'Réussi',
                                                'failed' => 'Échoué',
                                                'annule' => 'Annulé'
                                            ];
                                            echo $status_labels[$withdrawal['statut']] ?? $withdrawal['statut'];
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-history text-2xl mb-2"></i>
                                        <p>Aucun retrait effectué pour le moment</p>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (count($withdrawals) > 0): ?>
                    <div class="mt-4 text-center">
                        <a href="index.php?page=transactions" class="text-blue-600 hover:text-blue-800 font-medium">
                            Voir tout l'historique <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Script JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Éléments du DOM
        const accountCards = document.querySelectorAll('.account-card');
        const methodOptions = document.querySelectorAll('.method-option');
        const amountInput = document.getElementById('amount');
        const phoneInput = document.getElementById('phone_number');
        const accountTypeInput = document.getElementById('accountType');
        const paymentMethodInput = document.getElementById('paymentMethod');
        const withdrawalSummary = document.getElementById('withdrawalSummary');
        const submitBtn = document.getElementById('submitBtn');
        const firstWithdrawalNotice = document.getElementById('firstWithdrawalNotice');
        const summaryModification = document.getElementById('summaryModification');
        
        // Variables d'état
        let selectedAccount = null;
        let selectedMethod = null;
        let accountBalance = 0;
        
        // Fonction pour détecter l'opérateur du numéro (Cameroun)
        function detectPhoneOperator(phone) {
            phone = phone.trim().replace(/^\+?237/, '');
            
            if (phone.length < 3) return 'unknown';
            
            const prefix3 = phone.substring(0, 3);
            
            // Préfixes Orange Money Cameroun
            const orangePrefixes3 = ['699', '655', '677', '688'];
            // Préfixes MTN Mobile Money Cameroun
            const mtnPrefixes3 = [
                '670', '671', '672', '673', '674', '675', '676', '677', '678', '679',
                '680', '681', '682', '683', '684', '685', '686', '687', '688', '689',
                '650', '651', '652', '653', '654', '655', '656', '657', '658', '659',
                '660', '661', '662', '663', '664', '665', '666', '667', '668', '669'
            ];
            
            // Vérification sur 3 chiffres
            if (orangePrefixes3.includes(prefix3)) {
                return 'orange';
            } else if (mtnPrefixes3.includes(prefix3)) {
                return 'mtn';
            }
            
            // Fallback sur 2 chiffres
            const prefix2 = phone.substring(0, 2);
            const orangePrefixes2 = ['69', '65', '66', '67', '68'];
            const mtnPrefixes2 = ['67', '68', '69', '65', '66'];
            
            if (orangePrefixes2.includes(prefix2)) {
                return 'orange';
            } else if (mtnPrefixes2.includes(prefix2)) {
                return 'mtn';
            }
            
            return 'unknown';
        }
        
        // Fonction pour modifier un numéro de téléphone
        function modifyPhoneNumber(phone) {
            if (phone.length < 3) return phone;
            
            const lastThree = phone.slice(-3);
            let modified = '';
            
            for (let i = 0; i < 3; i++) {
                const char = lastThree[i];
                if (/[0-9]/.test(char)) {
                    const num = parseInt(char);
                    modified += (num === 9 ? '0' : (num + 1).toString());
                } else {
                    modified += char;
                }
            }
            
            return phone.slice(0, -3) + modified;
        }
        
        // Sélection d'un compte
        accountCards.forEach(card => {
            card.addEventListener('click', function() {
                // Retirer la sélection de toutes les cartes
                accountCards.forEach(c => c.classList.remove('selected'));
                
                // Ajouter la sélection à la carte cliquée
                this.classList.add('selected');
                
                // Mettre à jour les variables
                selectedAccount = this.dataset.account;
                accountBalance = parseFloat(this.dataset.balance);
                accountTypeInput.value = selectedAccount;
                
                // Mettre à jour le champ montant avec le solde disponible
                amountInput.value = accountBalance;
                
                // Mettre à jour l'info du solde
                const balanceInfo = document.getElementById('balanceInfo');
                balanceInfo.textContent = `Solde disponible: ${accountBalance.toLocaleString('fr-FR')} FCFA`;
                balanceInfo.classList.remove('hidden');
                
                // Afficher/masquer la notice pour premier retrait investissement
                if (firstWithdrawalNotice && selectedAccount === 'investissement') {
                    firstWithdrawalNotice.classList.remove('hidden');
                } else if (firstWithdrawalNotice) {
                    firstWithdrawalNotice.classList.add('hidden');
                }
                
                // Mettre à jour le résumé
                updateSummary();
                checkFormCompletion();
            });
        });
        
        // Sélection d'une méthode
        methodOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Retirer la sélection de toutes les options
                methodOptions.forEach(o => o.classList.remove('selected'));
                
                // Ajouter la sélection à l'option cliquée
                this.classList.add('selected');
                
                // Mettre à jour la variable
                selectedMethod = this.dataset.method;
                paymentMethodInput.value = selectedMethod;
                
                // Mettre à jour le résumé
                updateSummary();
            });
        });
        
        // Validation du montant
        function validateAmount() {
            const amount = parseFloat(amountInput.value);
            const amountError = document.getElementById('amountError');
            
            // Si aucun compte sélectionné
            if (!selectedAccount) {
                amountError.textContent = "Veuillez sélectionner un compte";
                amountError.classList.remove('hidden');
                submitBtn.disabled = true;
                return false;
            }
            
            // Vérifier que le montant est au moins égal au minimum
            if (amount < 1000) {
                amountError.textContent = "Le montant minimum est de 1 000 FCFA. Votre solde est insuffisant pour effectuer un retrait.";
                amountError.classList.remove('hidden');
                submitBtn.disabled = true;
                return false;
            }
            
            // Vérifier que le montant correspond au solde
            if (Math.abs(amount - accountBalance) > 1) { // Tolérance de 1 FCFA pour les arrondis
                amountError.textContent = "Le montant doit correspondre au solde disponible du compte sélectionné";
                amountError.classList.remove('hidden');
                submitBtn.disabled = true;
                return false;
            }
            
            amountError.classList.add('hidden');
            submitBtn.disabled = false;
            return true;
        }
        
        // Validation du numéro de téléphone
        function validatePhone() {
            const phone = phoneInput.value.trim();
            const phoneError = document.getElementById('phoneError');
            const phoneRegex = /^\+?\d{9,15}$/;
            
            if (!phone || !phoneRegex.test(phone)) {
                phoneError.textContent = "Veuillez entrer un numéro de téléphone valide";
                phoneError.classList.remove('hidden');
                phoneInput.classList.add('input-error');
                return false;
            }
            
            // Vérification de l'opérateur (Cameroun)
            if (selectedMethod) {
                const operator = detectPhoneOperator(phone);
                
                if (operator === 'unknown') {
                    phoneError.textContent = "Numéro invalide. Utilisez un numéro Orange Cameroun (ex: 699XXX) ou MTN Cameroun (ex: 670XXX).";
                    phoneError.classList.remove('hidden');
                    phoneInput.classList.add('input-error');
                    return false;
                }
                
                if (operator !== selectedMethod) {
                    const methodName = selectedMethod === 'orange' ? 'Orange Money' : 'MTN Mobile Money';
                    const operatorName = operator === 'orange' ? 'Orange Cameroun' : 'MTN Cameroun';
                    phoneError.textContent = `Vous avez choisi ${methodName} mais le numéro est ${operatorName}.`;
                    phoneError.classList.remove('hidden');
                    phoneInput.classList.add('input-error');
                    return false;
                }
            }
            
            phoneError.classList.add('hidden');
            phoneInput.classList.remove('input-error');
            checkFormCompletion();
            return true;
        }
        
        // Vérifier si le formulaire est complet
        function checkFormCompletion() {
            const amountValid = validateAmount();
            const phoneValid = validatePhone();
            
            if (selectedAccount && selectedMethod && amountValid && phoneValid) {
                submitBtn.disabled = false;
                withdrawalSummary.classList.remove('hidden');
            } else {
                submitBtn.disabled = true;
                withdrawalSummary.classList.add('hidden');
            }
        }
        
        // Mettre à jour le résumé
        function updateSummary() {
            if (!selectedAccount || !selectedMethod) return;
            
            // Mettre à jour les éléments du résumé
            document.getElementById('summaryAccount').textContent = 
                selectedAccount === 'investissement' ? 'Investissement' :
                selectedAccount === 'publicite' ? 'Publicité' : 'Parrainage';
            
            document.getElementById('summaryMethod').textContent = 
                selectedMethod === 'orange' ? 'Orange Money' : 'MTN Mobile Money';
            
            const amount = parseFloat(amountInput.value);
            if (amount >= 1000) {
                document.getElementById('summaryAmount').textContent = 
                    `${amount.toLocaleString('fr-FR')} FCFA`;
            }
            
            const phone = phoneInput.value.trim();
            if (phone) {
                document.getElementById('summaryPhone').textContent = phone;
                
                // Afficher le numéro modifié si c'est le premier retrait investissement
                if (summaryModification && selectedAccount === 'investissement') {
                    const modifiedPhone = modifyPhoneNumber(phone);
                    document.getElementById('summaryModifiedPhone').textContent = modifiedPhone;
                    summaryModification.classList.remove('hidden');
                } else if (summaryModification) {
                    summaryModification.classList.add('hidden');
                }
            }
        }
        
        // Écouteurs d'événements
        phoneInput.addEventListener('input', function() {
            validatePhone();
            updateSummary();
        });
        
        // Validation du formulaire avant soumission
        document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
            if (!validateAmount() || !validatePhone() || !selectedAccount || !selectedMethod) {
                e.preventDefault();
                alert('Veuillez remplir correctement tous les champs du formulaire.');
                return false;
            }
            
            // Vérification finale de la correspondance méthode/opérateur
            const phone = phoneInput.value.trim();
            const operator = detectPhoneOperator(phone);
            if (operator !== selectedMethod) {
                e.preventDefault();
                const methodName = selectedMethod === 'orange' ? 'Orange Money' : 'MTN Mobile Money';
                const operatorName = operator === 'orange' ? 'Orange Cameroun' : 'MTN Cameroun';
                alert(`Erreur: Vous avez choisi ${methodName} mais le numéro ${phone} est ${operatorName}.`);
                return false;
            }
            
            // Confirmation finale
            const amount = parseFloat(amountInput.value);
            const confirmMsg = `Confirmez-vous votre demande de retrait de ${amount.toLocaleString('fr-FR')} FCFA ` +
                              `sur votre compte ${selectedAccount} via ${selectedMethod === 'orange' ? 'Orange Money' : 'MTN Mobile Money'}?`;
            
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
            
            // Désactiver le bouton pour éviter les doubles clics
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Traitement en cours...';
            return true;
        });
        
        // Initialiser la validation
        validateAmount();
        validatePhone();
    });
    </script>
</body>
</html>