<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Plan;
use App\Models\User;
use App\Models\Transaction;
use App\Core\Model;

class PlanController extends Controller
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
        $plans = Plan::all();
        return $this->view('dashboard/plans', [
            'title' => 'Plans d\'Investissement',
            'plans' => $plans
        ]);
    }

    public function invest()
    {
        $planId = $_POST['plan_id'] ?? 0;
        $userId = Session::get('user_id');

        $user = User::findById($userId);
        $plan = Plan::find($planId);

        if (!$plan) {
            return $this->redirectWithPlanError($planId, 'Plan inexistant.');
        }

        $amount = $plan['price']; // Fixed amount from plan

        if ($user['balance'] < $amount) {
            $missing = $amount - $user['balance'];
            Session::set('missing_amount', $missing);
            Session::set('target_plan_id', $planId);
            return $this->redirectWithPlanError($planId, 'insufficient_balance');
        }

        // Process Investment
        $db = Model::connect();
        try {
            $db->beginTransaction();

            // Deduct balance
            $stmt = $db->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
            $stmt->execute(['amount' => $amount, 'id' => $userId]);

            // Create investment record
            $sqlInvest = "INSERT INTO investments (user_id, plan_id, amount, end_date, daily_profit, ads_per_day) 
                          VALUES (:user_id, :plan_id, :amount, DATE_ADD(NOW(), INTERVAL :days DAY), :daily_profit, :ads_per_day)";
            $stmtInvest = $db->prepare($sqlInvest);
            $stmtInvest->execute([
                'user_id' => $userId,
                'plan_id' => $planId,
                'amount' => $amount,
                'days' => $plan['duration_days'],
                'daily_profit' => $plan['daily_profit_amount'],
                'ads_per_day' => $plan['ads_per_day']
            ]);

            // Log transaction
            Transaction::create([
                'user_id' => $userId,
                'type' => 'investment',
                'amount' => $amount,
                'status' => 'completed',
                'description' => "Investissement dans le plan : " . $plan['name']
            ]);

            $db->commit();

            // Handle Referral Commission (20%)
            if ($user['referred_by']) {
                $commission = $amount * 0.20;
                $referrer = User::findById($user['referred_by']);

                if ($referrer) {
                    $db->beginTransaction();
                    try {
                        // Add to referrer balance
                        $stmtRef = $db->prepare("UPDATE users SET balance = balance + :commission WHERE id = :id");
                        $stmtRef->execute(['commission' => $commission, 'id' => $referrer['id']]);

                        // Log transaction
                        Transaction::create([
                            'user_id' => $referrer['id'],
                            'type' => 'commission',
                            'amount' => $commission,
                            'status' => 'completed',
                            'description' => "Commission de parrainage (20%) sur l'investissement de " . $user['name']
                        ]);
                        $db->commit();
                    } catch (\Exception $e) {
                        $db->rollBack();
                        // Log error but don't stop main flow
                        error_log("Commission Error: " . $e->getMessage());
                    }
                }
            }

            header('Location: /plans?success=1');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            return $this->redirectWithPlanError($planId, 'Une erreur est survenue lors de l\'investissement.');
        }
    }

    private function redirectWithPlanError($planId, $error)
    {
        Session::set('plan_error', $error);
        Session::set('target_plan_id', $planId);
        header('Location: /plans');
        exit;
    }
}
