<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Guide extends Model
{
    public static function all($withSteps = false)
    {
        $db = self::connect();
        $stmt = $db->query("SELECT * FROM guides WHERE status = 'active' ORDER BY order_index ASC, created_at DESC");
        $guides = $stmt->fetchAll();

        if ($withSteps) {
            foreach ($guides as &$guide) {
                $guide['steps'] = GuideStep::forGuide($guide['id']);
            }
        }

        return $guides;
    }

    public static function findById($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM guides WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $db = self::connect();
        $sql = "INSERT INTO guides (title, description, content, image_url, order_index, status) 
                VALUES (:title, :description, :content, :image_url, :order_index, :status)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'],
            'image_url' => $data['image_url'] ?? null,
            'order_index' => $data['order_index'] ?? 0,
            'status' => $data['status'] ?? 'active'
        ]);
        return $db->lastInsertId();
    }

    public static function update($id, $data)
    {
        $db = self::connect();
        $fields = [];
        $params = ['id' => $id];

        foreach (['title', 'description', 'content', 'image_url', 'order_index', 'status'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields))
            return true;

        $sql = "UPDATE guides SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("DELETE FROM guides WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
