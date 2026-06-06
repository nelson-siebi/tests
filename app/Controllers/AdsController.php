<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Ad;
use App\Models\User;
use App\Models\Investment;
use App\Core\Model;

class AdsController extends Controller
{
    public function __construct()
    {
        if (!Session::has('user_id')) {
            header('Location: /login');
            exit;
        }
    }

    public function index()
    {
        $userId = Session::get('user_id');

        // Check if user has active investments
        $activeInvestments = Investment::getActiveByUserId($userId);

        $hasActiveInvestment = !empty($activeInvestments);

        // Get only active ads
        $db = Model::connect();
        $stmt = $db->query("SELECT * FROM ads WHERE status = 'active' ORDER BY created_at DESC");
        $ads = $stmt->fetchAll();

        $watchedToday = Ad::getWatchedCountToday($userId);

        // Find required ads count
        $requiredAds = 0;
        foreach ($activeInvestments as $inv) {
            if ($inv['ads_per_day'] > $requiredAds) {
                $requiredAds = $inv['ads_per_day'];
            }
        }

        return $this->view('dashboard/ads', [
            'title' => \App\Core\Language::get('ads'),
            'ads' => $ads,
            'watchedToday' => $watchedToday,
            'requiredAds' => $requiredAds,
            'hasActiveInvestment' => $hasActiveInvestment
        ]);
    }

    public function watch()
    {
        $adId = $_POST['ad_id'] ?? 0;
        $userId = Session::get('user_id');

        if ($adId && !Ad::hasWatchedToday($userId, $adId)) {
            // Get ad details for reward
            $db = Model::connect();
            $stmt = $db->prepare("SELECT reward FROM ads WHERE id = ? AND status = 'active'");
            $stmt->execute([$adId]);
            $ad = $stmt->fetch();

            if ($ad) {
                try {
                    $db->beginTransaction();

                    // Mark as watched
                    Ad::markAsWatched($userId, $adId);

                    // Increment view count
                    $stmt = $db->prepare("UPDATE ads SET view_count = view_count + 1 WHERE id = ?");
                    $stmt->execute([$adId]);

                    // Credit reward to user balance
                    $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                    $stmt->execute([$ad['reward'], $userId]);

                    // Create transaction record
                    $stmt = $db->prepare("INSERT INTO transactions (user_id, type, amount, status, description) VALUES (?, 'payout', ?, 'completed', ?)");
                    $stmt->execute([$userId, $ad['reward'], 'Récompense publicité']);

                    $db->commit();

                    header('Location: /ads?success=1&reward=' . $ad['reward']);
                    exit;
                } catch (\Exception $e) {
                    $db->rollBack();
                    header('Location: /ads?error=1');
                    exit;
                }
            }
        }

        header('Location: /ads');
        exit;
    }
}
