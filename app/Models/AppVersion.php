<?php

namespace App\Models;

use App\Core\Model;

class AppVersion extends Model
{
    public static function getActiveVersion()
    {
        $db = self::connect();
        $stmt = $db->prepare("
            SELECT * FROM app_versions 
            WHERE is_active = 1 
            AND (expiry_date IS NULL OR expiry_date > NOW())
            ORDER BY upload_date DESC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function all()
    {
        $db = self::connect();
        $stmt = $db->query("
            SELECT av.*, u.name as uploader_name 
            FROM app_versions av
            LEFT JOIN users u ON av.uploaded_by = u.id
            ORDER BY upload_date DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create($data)
    {
        $db = self::connect();

        // Calculate expiry date (14 days from upload)
        $expiryDate = date('Y-m-d H:i:s', strtotime('+14 days'));

        // Deactivate all previous versions
        $db->exec("UPDATE app_versions SET is_active = 0");

        $stmt = $db->prepare("
            INSERT INTO app_versions 
            (version_name, version_code, apk_file_path, file_size, expiry_date, uploaded_by, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $data['version_name'],
            $data['version_code'],
            $data['apk_file_path'],
            $data['file_size'] ?? null,
            $expiryDate,
            $data['uploaded_by'],
            $data['notes'] ?? null
        ]);
    }

    public static function incrementDownloadCount($id)
    {
        $db = self::connect();
        $stmt = $db->prepare("UPDATE app_versions SET download_count = download_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function delete($id)
    {
        $db = self::connect();

        // Get file path before deleting
        $stmt = $db->prepare("SELECT apk_file_path FROM app_versions WHERE id = ?");
        $stmt->execute([$id]);
        $version = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($version && file_exists(public_path($version['apk_file_path']))) {
            unlink(public_path($version['apk_file_path']));
        }

        $stmt = $db->prepare("DELETE FROM app_versions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getDaysUntilExpiry($uploadDate, $expiryDate = null)
    {
        if ($expiryDate) {
            $expiry = new \DateTime($expiryDate);
        } else {
            $upload = new \DateTime($uploadDate);
            $expiry = $upload->modify('+14 days');
        }

        $now = new \DateTime();
        $diff = $now->diff($expiry);

        return $diff->invert ? 0 : $diff->days;
    }
}
