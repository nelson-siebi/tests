<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Investment extends Model
{
    public static function getActive()
    {
        $db = self::connect();
        $sql = "SELECT i.*, p.daily_profit_percent, p.daily_profit_amount, p.ads_per_day 
                FROM investments i 
                JOIN investment_plans p ON i.plan_id = p.id 
                WHERE i.status = 'active'";
        return $db->query($sql)->fetchAll();
    }

    public static function getActiveByUserId($userId)
    {
        $db = self::connect();
        $sql = "SELECT i.*, p.name as plan_name, p.daily_profit_percent, p.daily_profit_amount, p.ads_per_day 
                FROM investments i 
                JOIN investment_plans p ON i.plan_id = p.id 
                WHERE i.user_id = :user_id AND i.status = 'active'";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function processPayouts()
    {
        $activeInvestments = self::getActive();
        $db = self::connect();
        $count = 0;

        foreach ($activeInvestments as $invest) {
            // Check if payout was already done today (simple check: last_payout_at is today)
            if ($invest['last_payout_at'] && date('Y-m-d', strtotime($invest['last_payout_at'])) === date('Y-m-d')) {
                continue;
            }

            // Check if user has watched required ads today
            $watchedAds = Ad::getWatchedCountToday($invest['user_id']);
            if ($watchedAds < $invest['ads_per_day']) {
                continue; // Skip payout if ads not watched
            }

            $profit = $invest['daily_profit_amount'] ?? (($invest['amount'] * $invest['daily_profit_percent']) / 100);

            try {
                $db->beginTransaction();

                // 1. Add profit to user balance
                $stmtUser = $db->prepare("UPDATE users SET balance = balance + :profit WHERE id = :id");
                $stmtUser->execute(['profit' => $profit, 'id' => $invest['user_id']]);

                // 2. Update investment totals
                $stmtInvest = $db->prepare("UPDATE investments SET total_profit = total_profit + :profit, last_payout_at = NOW() WHERE id = :id");
                $stmtInvest->execute(['profit' => $profit, 'id' => $invest['id']]);

                // 3. Log transaction
                Transaction::create([
                    'user_id' => $invest['user_id'],
                    'type' => 'payout',
                    'amount' => $profit,
                    'status' => 'completed',
                    'description' => "Gain journalier pour l'investissement #{$invest['id']}"
                ]);

                $db->commit();
                $count++;
            } catch (\Exception $e) {
                $db->rollBack();
            }
        }

        return $count;
    }
}
