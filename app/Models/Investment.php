<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class Investment extends Model
{
    public static function getActive()
    {
        $db = self::connect();
        $sql = "SELECT i.*, p.daily_profit_percent, p.daily_profit_amount, p.ads_per_day, u.created_at as user_created_at 
                FROM investments i 
                JOIN investment_plans p ON i.plan_id = p.id 
                JOIN users u ON i.user_id = u.id
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
            // 1. Check if user account is at least 24 hours old
            $userCreatedAt = strtotime($invest['user_created_at']);
            if (time() - $userCreatedAt < 86400) {
                continue; // Skip payout if account created less than 24 hours ago
            }

            // 2. Check if investment itself is at least 24 hours old
            $investmentStartDate = strtotime($invest['start_date']);
            if (time() - $investmentStartDate < 86400) {
                continue; // Skip payout if investment created less than 24 hours ago
            }

            // 3. Check if payout was already done today (simple check: last_payout_at is today)
            if ($invest['last_payout_at'] && date('Y-m-d', strtotime($invest['last_payout_at'])) === date('Y-m-d')) {
                continue;
            }

            // 4. Check if user has watched required ads today
            $watchedAds = Ad::getWatchedCountToday($invest['user_id']);
            if ($watchedAds < $invest['ads_per_day']) {
                continue; // Skip payout if ads not watched
            }

            $profit = $invest['daily_profit_amount'] ?? (($invest['amount'] * $invest['daily_profit_percent']) / 100);

            try {
                $db->beginTransaction();

                // Double check prevention inside sql where clause (atomic check)
                // 1. Update investment totals only if it wasn't already updated today
                $stmtInvest = $db->prepare("
                    UPDATE investments 
                    SET total_profit = total_profit + :profit, last_payout_at = NOW() 
                    WHERE id = :id AND (last_payout_at IS NULL OR DATE(last_payout_at) < CURDATE())
                ");
                $stmtInvest->execute(['profit' => $profit, 'id' => $invest['id']]);

                // If rowCount is 0, it means it was already updated by another process/concurrency
                if ($stmtInvest->rowCount() === 0) {
                    throw new \Exception("Payout already processed for investment #{$invest['id']}");
                }

                // 2. Add profit to user balance
                $stmtUser = $db->prepare("UPDATE users SET balance = balance + :profit WHERE id = :id");
                $stmtUser->execute(['profit' => $profit, 'id' => $invest['user_id']]);

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
