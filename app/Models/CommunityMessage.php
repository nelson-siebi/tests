<?php

namespace App\Models;

use App\Core\Model;

class CommunityMessage extends Model
{
    public static function create($data)
    {
        $db = self::connect();
        
        // Vérifier si la colonne fake_name existe
        try {
            $db->query("SELECT fake_name FROM community_messages LIMIT 1");
            $hasFakeName = true;
        } catch (\PDOException $e) {
            $hasFakeName = false;
        }
        
        if ($hasFakeName && !empty($data['fake_name'])) {
            $stmt = $db->prepare("INSERT INTO community_messages (user_id, message, status, fake_name) VALUES (:user_id, :message, :status, :fake_name)");
            return $stmt->execute([
                'user_id' => $data['user_id'],
                'message' => $data['message'],
                'status' => $data['status'] ?? 'pending',
                'fake_name' => $data['fake_name']
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO community_messages (user_id, message, status) VALUES (:user_id, :message, :status)");
            return $stmt->execute([
                'user_id' => $data['user_id'],
                'message' => $data['message'],
                'status' => $data['status'] ?? 'pending'
            ]);
        }
    }

    public static function getApproved()
    {
        $db = self::connect();
        
        // Vérifier si la colonne fake_name existe
        try {
            $db->query("SELECT fake_name FROM community_messages LIMIT 1");
            $hasFakeName = true;
        } catch (\PDOException $e) {
            $hasFakeName = false;
        }
        
        if ($hasFakeName) {
            $sql = "SELECT m.*, 
                    CASE WHEN m.fake_name IS NOT NULL AND m.fake_name != '' 
                         THEN m.fake_name 
                         ELSE u.name 
                    END as user_name 
                    FROM community_messages m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.status = 'approved' 
                    ORDER BY m.created_at DESC LIMIT 50";
        } else {
            $sql = "SELECT m.*, u.name as user_name 
                    FROM community_messages m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.status = 'approved' 
                    ORDER BY m.created_at DESC LIMIT 50";
        }
        return $db->query($sql)->fetchAll();
    }

    public static function getPending()
    {
        $db = self::connect();
        
        // Vérifier si la colonne fake_name existe
        try {
            $db->query("SELECT fake_name FROM community_messages LIMIT 1");
            $hasFakeName = true;
        } catch (\PDOException $e) {
            $hasFakeName = false;
        }
        
        if ($hasFakeName) {
            $sql = "SELECT m.*, 
                    CASE WHEN m.fake_name IS NOT NULL AND m.fake_name != '' 
                         THEN m.fake_name 
                         ELSE u.name 
                    END as user_name,
                    u.name as real_user_name
                    FROM community_messages m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.status = 'pending' 
                    ORDER BY m.created_at ASC";
        } else {
            $sql = "SELECT m.*, u.name as user_name 
                    FROM community_messages m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.status = 'pending' 
                    ORDER BY m.created_at ASC";
        }
        return $db->query($sql)->fetchAll();
    }

    public static function getUserPending($userId)
    {
        $db = self::connect();
        
        // Vérifier si la colonne fake_name existe
        try {
            $db->query("SELECT fake_name FROM community_messages LIMIT 1");
            $hasFakeName = true;
        } catch (\PDOException $e) {
            $hasFakeName = false;
        }
        
        if ($hasFakeName) {
            $stmt = $db->prepare("SELECT m.*, 
                                  CASE WHEN m.fake_name IS NOT NULL AND m.fake_name != '' 
                                       THEN m.fake_name 
                                       ELSE u.name 
                                  END as user_name 
                                  FROM community_messages m 
                                  JOIN users u ON m.user_id = u.id 
                                  WHERE m.user_id = :user_id AND m.status = 'pending' 
                                  ORDER BY m.created_at DESC");
        } else {
            $stmt = $db->prepare("SELECT m.*, u.name as user_name 
                                  FROM community_messages m 
                                  JOIN users u ON m.user_id = u.id 
                                  WHERE m.user_id = :user_id AND m.status = 'pending' 
                                  ORDER BY m.created_at DESC");
        }
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function updateStatus($id, $status)
    {
        $db = self::connect();
        $stmt = $db->prepare("UPDATE community_messages SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }
}
