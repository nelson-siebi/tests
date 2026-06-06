<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Transaction extends Model
{
    public static function create($data)
    {
        $db = self::connect();
        $sql = "INSERT INTO transactions (user_id, type, amount, status, reference, description) 
                VALUES (:user_id, :type, :amount, :status, :reference, :description)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'amount' => $data['amount'],
            'status' => $data['status'] ?? 'pending',
            'reference' => $data['reference'] ?? null,
            'description' => $data['description'] ?? null
        ]);
    }

    public static function getByUserId($userId)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
    public static function countWithdrawalsByUserId($userId)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = :user_id AND type = 'withdrawal'");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchColumn();
    }

    public static function getLatest($limit = 10)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT t.*, u.phone, u.name 
                              FROM transactions t 
                              JOIN users u ON t.user_id = u.id 
                              ORDER BY t.created_at DESC 
                              LIMIT :limit");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
