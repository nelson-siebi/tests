<?php

namespace App\Core;

use PDO;
use PDOException;

class Model
{
    protected static $db;

    public static function connect()
    {
        if (!self::$db) {
            try {
                $config = require_once __DIR__ . '/../Config/database.php';
                $dsn = "mysql:host={$config['host']};dbname={$config['db_name']};charset=utf8mb4";
                self::$db = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$db;
    }
}
