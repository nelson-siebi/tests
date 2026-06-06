<?php
// test_requetes.php
$pdo = new PDO('mysql:host=localhost;dbname=invest;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Testez chaque requête du fichier
$queries = [
    "SELECT role FROM admin_users WHERE id = 1",
    "SELECT COUNT(*) FROM pending_transactions WHERE status = 'pending'",
    "DESCRIBE pending_transactions",
    "DESCRIBE user_plans",
    "DESCRIBE wallets"
];

foreach ($queries as $query) {
    echo "<h3>Test : $query</h3>";
    try {
        $stmt = $pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>" . print_r($result, true) . "</pre>";
    } catch(Exception $e) {
        echo "<div style='color:red'>Erreur : " . $e->getMessage() . "</div>";
    }
}
?>