<?php
require_once '../config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "--- CHECKING INVESTMENT VS DEPOSITS ---\n";
    
    // Get all users with wallets
    $sql = "SELECT DISTINCT w.user_id, w.total_depots FROM wallets w";
    $stmt = $db->query($sql);
    $wallets = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // user_id => total_depots
    
    foreach ($wallets as $userId => $totalDepots) {
        // Calculate actual investment from plans
        $sqlPlans = "SELECT SUM(montant_investi) FROM user_plans WHERE user_id = ?";
        $stmtPlans = $db->prepare($sqlPlans);
        $stmtPlans->execute([$userId]);
        $totalInvested = $stmtPlans->fetchColumn() ?: 0;
        
        echo "User ID: $userId\n";
        echo "  - Total Deposits (Wallets): " . number_format($totalDepots) . "\n";
        echo "  - Sum of Plans (Actual Invested): " . number_format($totalInvested) . "\n";
        
        if ($totalDepots != $totalInvested) {
             echo "  [!] DISCREPANCY FOUND\n";
        } else {
             echo "  [OK] MATCH\n";
        }
        echo "-----------------------------------\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
