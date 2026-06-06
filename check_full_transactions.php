<?php
require_once 'config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== DEPOSITS (Type='depot') ===\n";
    $stmt = $db->query("SELECT id, type, source, montant, statut, created_at FROM transactions WHERE type = 'depot'");
    $deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($deposits as $d) {
        printf("ID: %d | Source: %s | Montant: %s | Statut: %s | Date: %s\n", 
            $d['id'], $d['source'], number_format($d['montant'], 2), $d['statut'], $d['created_at']);
    }

    echo "\n=== WITHDRAWALS (Type='retrait') ===\n";
    $stmt = $db->query("SELECT id, type, source, montant, statut, created_at FROM transactions WHERE type = 'retrait'");
    $withdrawals = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($withdrawals as $w) {
        printf("ID: %d | Source: %s | Montant: %s | Statut: %s | Date: %s\n", 
            $w['id'], $w['source'], number_format($w['montant'], 2), $w['statut'], $w['created_at']);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
