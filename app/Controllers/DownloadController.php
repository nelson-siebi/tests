<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class DownloadController extends Controller
{
    public function index()
    {
        // Check if user is logged in to personalize experience
        $userId = Session::get('user_id');
        $user = null;

        if ($userId) {
            $user = \App\Models\User::findById($userId);
        }

        // Get active APK version
        $activeVersion = \App\Models\AppVersion::getActiveVersion();
        $daysLeft = 0;

        if ($activeVersion) {
            $daysLeft = \App\Models\AppVersion::getDaysUntilExpiry(
                $activeVersion['upload_date'],
                $activeVersion['expiry_date']
            );
        }

        return $this->view('download/index', [
            'title' => 'Télécharger l\'Application',
            'user' => $user,
            'activeVersion' => $activeVersion,
            'daysLeft' => $daysLeft
        ]);
    }

    public function downloadApk()
    {
        $activeVersion = \App\Models\AppVersion::getActiveVersion();

        if (!$activeVersion) {
            header('Location: /download');
            exit;
        }

        // Increment download count
        \App\Models\AppVersion::incrementDownloadCount($activeVersion['id']);

        // Serve the file
        $filePath = public_path($activeVersion['apk_file_path']);

        if (file_exists($filePath)) {
            header('Content-Type: application/vnd.android.package-archive');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }

        header('Location: /download');
        exit;
    }
}
