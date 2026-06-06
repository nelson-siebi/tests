<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Plan extends Model
{
    public static function all()
    {
        $db = self::connect();
        $stmt = $db->query("SELECT * FROM investment_plans WHERE status = 'active' ORDER BY price ASC");
        return $stmt->fetchAll();
    }

    public static function findById($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM investment_plans WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function find($id)
    {
        return self::findById($id);
    }

    public static function update($id, $data)
    {
        $db = self::connect();
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
        }
        $sql = "UPDATE investment_plans SET " . implode(', ', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        $stmt = $db->prepare($sql);
        return $stmt->execute($data);
    }
}
