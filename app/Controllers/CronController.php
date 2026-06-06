<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Env;
use App\Models\Investment;

class CronController extends Controller
{
    public function processPayouts()
    {
        // Set content type to JSON
        header('Content-Type: application/json');

        // Check if token matches
        $token = $_GET['token'] ?? '';
        $expectedToken = Env::get('CRON_TOKEN', 'investian_cron_secret_key_2026');

        if (empty($token) || $token !== $expectedToken) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid or missing security token'
            ]);
            exit;
        }

        try {
            $count = Investment::processPayouts();
            echo json_encode([
                'success' => true,
                'message' => 'Daily payouts processed successfully',
                'payouts_count' => $count,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
