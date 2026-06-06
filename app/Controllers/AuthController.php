<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::has('user_id')) {
            // Redirection basée sur le rôle si déjà connecté
            $role = Session::get('user_role');
            if ($role === 'admin') {
                header('Location: /admin');
            } else {
                header('Location: /dashboard');
            }
            exit;
        }
        return $this->view('auth/login', ['title' => 'Connexion']);
    }

    public function login()
    {
        $identifier = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Allow any identifier (email or phone) without strict email validation
        $user = User::findByEmailOrPhone($identifier);

        if ($user && password_verify($password, $user['password'])) {
            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);

            if ($user['role'] === 'admin') {
                header('Location: /admin');
            } else {
                header('Location: /dashboard');
            }
            exit;
        }

        return $this->view('auth/login', [
            'title' => 'Connexion',
            'error' => 'Identifiant ou mot de passe incorrect.'
        ]);
    }

    public function showRegister()
    {
        // Capture referral code from URL if present
        if (isset($_GET['ref'])) {
            setcookie('ref', $_GET['ref'], time() + (86400 * 30), "/"); // 30 days
        }
        return $this->view('auth/register', ['title' => 'Inscription']);
    }

    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $password = $_POST['password'] ?? '';

        if (User::findByEmail($email)) {
            return $this->view('auth/register', [
                'title' => 'Inscription',
                'error' => 'Cet email est déjà utilisé.'
            ]);
        }

        if (User::findByEmailOrPhone($phone)) {
            return $this->view('auth/register', [
                'title' => 'Inscription',
                'error' => 'Ce numéro de téléphone est déjà utilisé.'
            ]);
        }

        // Handle Referral
        $referredBy = null;
        if (isset($_COOKIE['ref'])) {
            $referrer = User::findByReferralCode($_COOKIE['ref']);
            if ($referrer) {
                $referredBy = $referrer['id'];
            }
        }

        // Generate Unique Referral Code
        do {
            $referralCode = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (User::findByReferralCode($referralCode));

        User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'referral_code' => $referralCode,
            'referred_by' => $referredBy
        ]);

        header('Location: /login');
        exit;
    }

    public function logout()
    {
        Session::destroy();
        header('Location: /login');
        exit;
    }
}
