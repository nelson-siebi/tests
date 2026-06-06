<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class GuideStep extends Model
{
    public static function forGuide($guide_id)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM guide_steps WHERE guide_id = :guide_id ORDER BY order_index ASC");
        $stmt->execute(['guide_id' => $guide_id]);
        return $stmt->fetchAll();
    }

    public static function create($data)
    {
        $db = self::connect();
        $sql = "INSERT INTO guide_steps (guide_id, title, content, media_url, media_type, order_index) 
                VALUES (:guide_id, :title, :content, :media_url, :media_type, :order_index)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'guide_id' => $data['guide_id'],
            'title' => $data['title'] ?? null,
            'content' => $data['content'],
            'media_url' => $data['media_url'] ?? null,
            'media_type' => $data['media_type'] ?? 'none',
            'order_index' => $data['order_index'] ?? 0
        ]);
    }

    public static function update($id, $data)
    {
        $db = self::connect();
        $fields = [];
        $params = ['id' => $id];

        foreach (['title', 'content', 'media_url', 'media_type', 'order_index'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields))
            return true;

        $sql = "UPDATE guide_steps SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function delete($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("DELETE FROM guide_steps WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function deleteForGuide($guide_id)
    {
        $db = self::connect();
        $stmt = $db->prepare("DELETE FROM guide_steps WHERE guide_id = :guide_id");
        return $stmt->execute(['guide_id' => $guide_id]);
    }
}
