<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class DashboardController extends Controller
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
        $user = User::findById($userId);
        $investments = \App\Models\Investment::getActiveByUserId($userId);

        $watchedToday = \App\Models\Ad::getWatchedCountToday($userId);
        $requiredAds = 0;
        $totalProfits = 0;

        foreach ($investments as $inv) {
            $totalProfits += $inv['total_profit'] ?? 0;
            if ($inv['ads_per_day'] > $requiredAds) {
                $requiredAds = $inv['ads_per_day'];
            }
        }

        $plans = \App\Models\Plan::all(); // This is already fetching active plans ordered by min_amount
        $topPlans = array_slice($plans, 0, 3);

        $recentUserTransactions = \App\Models\Transaction::getByUserId($userId);
        $recentUserTransactions = array_slice($recentUserTransactions, 0, 3);

        $globalTransactions = \App\Models\Transaction::getLatest(10);

        // Mask phone numbers for public display
        $globalTransactions = array_map(function ($tx) {
            if (!empty($tx['phone'])) {
                $tx['phone_masked'] = substr($tx['phone'], 0, 2) . '****' . substr($tx['phone'], -3);
            } else {
                $tx['phone_masked'] = 'User';
            }
            return $tx;
        }, $globalTransactions);

        return $this->view('dashboard/index', [
            'title' => \App\Core\Language::get('dashboard'),
            'user' => $user,
            'investments' => $investments,
            'watchedToday' => $watchedToday,
            'requiredAds' => $requiredAds,
            'totalProfits' => $totalProfits,
            'availablePlans' => $topPlans,
            'recentUserTransactions' => $recentUserTransactions,
            'globalTransactions' => $globalTransactions
        ]);
    }

    public function guide()
    {
        $userId = Session::get('user_id');
        $user = User::findById($userId);
        $guides = \App\Models\Guide::all(true);

        return $this->view('dashboard/guide', [
            'title' => \App\Core\Language::get('guide_title'),
            'user' => $user,
            'guides' => $guides
        ]);
    }

    public function support()
    {
        // No authentication required as per request "facilement voyable" but usually dashboard routes are protected.
        // The user said "cree une page assistance facilement voyable, la ou l'utilisateur peux egalemnt voir...".
        // It resides in dashboard views folder, and I put it under DashboardController which is protected.
        // It's accessible via /support.
        return $this->view('dashboard/support', [
            'title' => 'Assistance'
        ]);
    }
}
