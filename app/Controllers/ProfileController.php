<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class ProfileController extends Controller
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

        return $this->view('dashboard/profile', [
            'title' => \App\Core\Language::get('profile'),
            'user' => $user,
            'investments' => $investments
        ]);
    }

    public function update()
    {
        $userId = Session::get('user_id');
        $name = $_POST['name'] ?? null;
        $password = $_POST['password'] ?? null;
        $confirmPassword = $_POST['confirm_password'] ?? null;

        $data = [];
        if ($name) {
            $data['name'] = $name;
        }

        if (!empty($password)) {
            if ($password !== $confirmPassword) {
                Session::set('error', \App\Core\Language::get('passwords_dont_match'));
                header('Location: /profile');
                exit;
            }
            $data['password'] = $password;
        }

        if (User::update($userId, $data)) {
            Session::set('success', \App\Core\Language::get('profile_updated'));
        } else {
            Session::set('error', \App\Core\Language::get('error_updating_profile'));
        }

        header('Location: /profile');
        exit;
    }
}
