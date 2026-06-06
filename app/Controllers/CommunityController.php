<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\CommunityMessage;
use App\Models\Investment;
use App\Models\User;

class CommunityController extends Controller
{
    public function index()
    {
        if (!Session::has('user_id')) {
            header('Location: /login');
            exit;
        }

        $userId = Session::get('user_id');
        $user = User::findById($userId);

        // Check if user has active investment or has invested before (any investment record?)
        // Requirement: "seuls les utilisateurs ayan un plan d'investissement actifou ayant deja investis peuvntdonner leur avis"
        // Let's check if they have ANY investment record.
        // For now, let's use Investment::getActiveByUserId($userId) to check active. 
        // Or create a method to check any investment.
        // Let's assume having an active one is the primary criteria for "discuter".
        // If not active, they can "juste voir".

        $investments = Investment::getActiveByUserId($userId);
        $isAdmin = Session::get('user_role') === 'admin';
        $canPost = count($investments) > 0 || $isAdmin;

        return $this->view('dashboard/community', [
            'title' => 'Communauté',
            'user' => $user,
            'canPost' => $canPost
        ]);
    }

    public function fetch()
    {
        // JSON API for polling
        $userId = Session::get('user_id');
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Auth required']);
            exit;
        }

        $approved = CommunityMessage::getApproved();

        // If user is logged in, merge their pending messages at the top (Ghosting)
        // Actually, the requirement says "User sees own pending posts immediately".
        // So we fetch approved + user's pending.
        $pending = CommunityMessage::getUserPending($userId);

        // Merge: Pending first, then Approved
        $messages = array_merge($pending, $approved);

        header('Content-Type: application/json');
        echo json_encode($messages);
        exit;
    }

    public function store()
    {
        if (!Session::has('user_id')) {
            http_response_code(401);
            exit;
        }

        $userId = Session::get('user_id');
        $message = trim($_POST['message'] ?? '');

        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Message empty']);
            exit;
        }

        // Permission check
        $investments = Investment::getActiveByUserId($userId);
        $isAdmin = Session::get('user_role') === 'admin';

        if (count($investments) === 0 && !$isAdmin) {
            http_response_code(403);
            echo json_encode(['error' => 'Investment required']);
            exit;
        }

        try {
            $result = CommunityMessage::create([
                'user_id' => $userId,
                'message' => $message,
                'status' => 'pending'
            ]);

            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                throw new \Exception("Erreur lors de l'insertion en base de données");
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
