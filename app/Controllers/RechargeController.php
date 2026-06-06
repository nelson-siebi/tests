<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Transaction;

class RechargeController extends Controller
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
        $transactions = Transaction::getByUserId(Session::get('user_id'));
        return $this->view('dashboard/recharge', [
            'title' => 'Recharger mon compte',
            'transactions' => $transactions
        ]);
    }

    public function store()
    {
        $amount = $_POST['amount'] ?? 0;
        $reference = $_POST['reference'] ?? '';

        if ($amount < 4000) {
            return $this->view('dashboard/recharge', [
                'title' => 'Recharger mon compte',
                'error' => 'Le montant minimum de recharge est de 4000 XAF.',
                'transactions' => Transaction::getByUserId(Session::get('user_id'))
            ]);
        }

        Transaction::create([
            'user_id' => Session::get('user_id'),
            'type' => 'deposit',
            'amount' => $amount,
            'status' => 'pending',
            'reference' => $reference,
            'description' => 'Demande de recharge via le site.'
        ]);

        header('Location: /recharge?success=1');
        exit;
    }
}
