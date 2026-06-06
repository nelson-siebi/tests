<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public static function create($data)
    {
        $db = self::connect();
        $sql = "INSERT INTO users (name, email, phone, password, referral_code) VALUES (:name, :email, :phone, :password, :referral_code)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'referral_code' => substr(md5(uniqid()), 0, 8)
        ]);
    }

    public static function findByEmail($email)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public static function findByEmailOrPhone($identifier)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :identifier OR phone = :identifier");
        $stmt->execute(['identifier' => $identifier]);
        return $stmt->fetch();
    }

    public static function findById($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function update($id, $data)
    {
        $db = self::connect();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['balance'])) {
            $fields[] = "balance = :balance";
            $params['balance'] = $data['balance'];
        }

        if (empty($fields))
            return true;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function findByReferralCode($code)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE referral_code = :code");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch();
    }
}
