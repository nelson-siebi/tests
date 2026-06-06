<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Ad extends Model
{
    public static function all()
    {
        $db = self::connect();
        return $db->query("SELECT * FROM ads ORDER BY created_at DESC")->fetchAll();
    }

    public static function find($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT * FROM ads WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function hasWatchedToday($userId, $adId)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT id FROM watched_ads WHERE user_id = :user_id AND ad_id = :ad_id AND watched_at = CURDATE()");
        $stmt->execute(['user_id' => $userId, 'ad_id' => $adId]);
        return $stmt->fetch() ? true : false;
    }

    public static function markAsWatched($userId, $adId)
    {
        $db = self::connect();
        $stmt = $db->prepare("INSERT INTO watched_ads (user_id, ad_id, watched_at) VALUES (:user_id, :ad_id, CURDATE())");
        return $stmt->execute(['user_id' => $userId, 'ad_id' => $adId]);
    }

    public static function getWatchedCountToday($userId)
    {
        $db = self::connect();
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM watched_ads WHERE user_id = :user_id AND watched_at = CURDATE()");
        $stmt->execute(['user_id' => $userId]);
        $res = $stmt->fetch();
        return $res['count'] ?? 0;
    }
}
