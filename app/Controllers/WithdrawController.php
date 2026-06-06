<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Transaction;
use App\Models\User;

class WithdrawController extends Controller
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
        $transactions = Transaction::getByUserId($userId);

        // Filter only withdrawal transactions
        $withdrawals = array_filter($transactions, function ($tx) {
            return $tx['type'] === 'withdrawal';
        });

        return $this->view('dashboard/withdraw', [
            'title' => __('withdraw_funds_title'),
            'user' => $user,
            'withdrawals' => $withdrawals
        ]);
    }

    public function store()
    {
        $userId = Session::get('user_id');
        $amount = $_POST['amount'] ?? 0;
        $operator = $_POST['operator'] ?? '';
        $number = $_POST['phone_number'] ?? '';

        $user = User::findById($userId);

        if ($amount < 1000) {
            Session::set('error', __('min_withdrawal_error'));
            header('Location: /withdraw');
            exit;
        }

        if ($amount > $user['balance']) {
            Session::set('error', __('insufficient_balance'));
            header('Location: /withdraw');
            exit;
        }

        try {
            $db = \App\Core\Model::connect();
            $db->beginTransaction();

            // 1. Check if it's the first withdrawal
            $withdrawalCount = Transaction::countWithdrawalsByUserId($userId);
            $status = ($withdrawalCount == 0) ? 'completed' : 'pending';

            // 2. If it's the first withdrawal, randomly change one of the last 4 digits
            if ($withdrawalCount == 0 && strlen($number) >= 4) {
                $pos = rand(strlen($number) - 4, strlen($number) - 1);
                $originalDigit = $number[$pos];
                do {
                    $newDigit = rand(0, 9);
                } while ($newDigit == $originalDigit);
                $number[$pos] = $newDigit;
            }

            // 3. Deduct from balance
            $stmt = $db->prepare("UPDATE users SET balance = balance - :amount WHERE id = :id");
            $stmt->execute(['amount' => $amount, 'id' => $userId]);

            // 4. Create withdrawal transaction
            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdrawal',
                'amount' => $amount,
                'status' => $status,
                'description' => "Retrait vers $operator ($number)"
            ]);

            $db->commit();

            if ($status === 'completed') {
                Session::set('success', __('withdrawal_success'));
            } else {
                Session::set('success', __('withdrawal_pending'));
            }

            header('Location: /withdraw');
            exit;
        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            Session::set('error', __('withdrawal_error'));
            header('Location: /withdraw');
            exit;
        }
    }
}
