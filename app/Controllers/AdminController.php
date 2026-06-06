<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Plan;
use App\Core\Model;

class AdminController extends Controller
{
    public function __construct()
    {
        if (!Session::has('user_id')) {
            header('Location: /login');
            exit;
        }

        $userId = Session::get('user_id');
        $user = User::findById($userId);

        if (!$user || $user['role'] !== 'admin') {
            header('Location: /dashboard');
            exit;
        }

        // Ensure session is synchronized
        Session::set('user_role', 'admin');
    }

    public function index()
    {
        $db = Model::connect();
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalDeposits = $db->query("SELECT SUM(amount) FROM transactions WHERE type='deposit' AND status='completed'")->fetchColumn() ?: 0;
        $pendingRecharges = $db->query("SELECT COUNT(*) FROM transactions WHERE type='deposit' AND status='pending'")->fetchColumn();
        $pendingWithdrawals = $db->query("SELECT COUNT(*) FROM transactions WHERE type='withdrawal' AND status='pending'")->fetchColumn();

        // Vérifier s'il y a des migrations en attente
        $pendingMigrations = 0;
        try {
            require_once __DIR__ . '/../../database/migrations/run_migrations.php';
            $runner = new \Database\Migrations\MigrationRunner($db);
            $status = $runner->getStatus();
            $pendingMigrations = count($status['pending']);
        } catch (\Exception $e) {
            // Ignorer si le système de migration n'est pas disponible
        }

        return $this->view('admin/index', [
            'title' => 'Admin Dashboard',
            'userCount' => $userCount,
            'totalDeposits' => $totalDeposits,
            'pendingRecharges' => $pendingRecharges,
            'pendingWithdrawals' => $pendingWithdrawals,
            'pendingMigrations' => $pendingMigrations
        ]);
    }

    /**
     * Exécuter les migrations de la base de données
     */
    public function runMigrations()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin');
            exit;
        }

        $db = Model::connect();
        
        try {
            require_once __DIR__ . '/../../database/migrations/run_migrations.php';
            $runner = new \Database\Migrations\MigrationRunner($db);
            $result = $runner->run();

            if ($result['success']) {
                Session::set('success', $result['message']);
            } else {
                $errorMsg = 'Erreurs: ' . implode(', ', array_column($result['errors'], 'error'));
                Session::set('error', $errorMsg);
            }
        } catch (\Exception $e) {
            Session::set('error', 'Erreur: ' . $e->getMessage());
        }

        header('Location: /admin');
        exit;
    }

    public function recharges()
    {
        $db = Model::connect();
        $stmt = $db->prepare("SELECT t.*, u.name as user_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type='deposit' AND t.status='pending' ORDER BY t.created_at DESC");
        $stmt->execute();
        $pending = $stmt->fetchAll();

        return $this->view('admin/recharges', [
            'title' => 'Gérer les Recharges',
            'pending' => $pending
        ]);
    }

    public function approveRecharge()
    {
        $id = $_POST['id'] ?? 0;
        $db = Model::connect();

        try {
            $db->beginTransaction();

            // Get transaction details
            $stmt = $db->prepare("SELECT * FROM transactions WHERE id = :id AND status='pending'");
            $stmt->execute(['id' => $id]);
            $tx = $stmt->fetch();

            if ($tx) {
                // Update user balance
                $stmtUpdate = $db->prepare("UPDATE users SET balance = balance + :amount WHERE id = :user_id");
                $stmtUpdate->execute(['amount' => $tx['amount'], 'user_id' => $tx['user_id']]);

                // Mark transaction as completed
                $stmtTx = $db->prepare("UPDATE transactions SET status='completed' WHERE id = :id");
                $stmtTx->execute(['id' => $id]);
            }

            $db->commit();
            header('Location: /admin/recharges?success=1');
            exit;
        } catch (\Exception $e) {
            $db->rollBack();
            header('Location: /admin/recharges?error=1');
            exit;
        }
    }

    public function plans()
    {
        $plans = Plan::all();
        return $this->view('admin/plans', [
            'title' => 'Gérer les Plans',
            'plans' => $plans
        ]);
    }

    public function showCreatePlan()
    {
        return $this->view('admin/create_plan', ['title' => 'Créer un Plan']);
    }

    public function storePlan()
    {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $profitAmount = $_POST['daily_profit_amount'] ?? 0;
        $ads = $_POST['ads_per_day'] ?? 5;
        $duration = 30; // Fixed to 30 days as requested

        // Handle Image Upload
        $image_url = $this->uploadMedia($_FILES['image'] ?? null, 'uploads/plans/')
            ?? 'https://images.unsplash.com/photo-1611974717424-3684a0006145?auto=format&fit=crop&q=80&w=400';

        // Calculate percentage for backward compatibility
        $profitPercent = ($price > 0) ? ($profitAmount / $price) * 100 : 0;

        $db = Model::connect();
        $sql = "INSERT INTO investment_plans (name, description, image_url, price, daily_profit_amount, daily_profit_percent, duration_days, ads_per_day) 
                VALUES (:name, :description, :image_url, :price, :profit_amount, :profit_percent, :duration, :ads)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'image_url' => $image_url,
            'price' => $price,
            'profit_amount' => $profitAmount,
            'profit_percent' => $profitPercent,
            'duration' => $duration,
            'ads' => $ads
        ]);

        header('Location: /admin/plans?success=1');
        exit;
    }

    public function editPlan()
    {
        $id = $_GET['id'] ?? 0;
        $plan = Plan::findById($id);

        if (!$plan) {
            header('Location: /admin/plans');
            exit;
        }

        return $this->view('admin/edit_plan', [
            'title' => 'Modifier le Plan',
            'plan' => $plan
        ]);
    }

    public function updatePlan()
    {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $profitAmount = $_POST['daily_profit_amount'] ?? 0;
        $ads = $_POST['ads_per_day'] ?? 5;

        $db = Model::connect();

        // Handle Image Upload (Optional replacement)
        $new_image = $this->uploadMedia($_FILES['image'] ?? null, 'uploads/plans/');

        if ($new_image) {
            $image_url = $new_image;
        } else {
            // Keep existing image
            $stmt = $db->prepare("SELECT image_url FROM investment_plans WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $image_url = $stmt->fetchColumn();
        }

        $profitPercent = ($price > 0) ? ($profitAmount / $price) * 100 : 0;

        $sql = "UPDATE investment_plans SET 
                name = :name, 
                description = :description, 
                image_url = :image_url, 
                price = :price, 
                daily_profit_amount = :profit_amount, 
                daily_profit_percent = :profit_percent, 
                ads_per_day = :ads 
                WHERE id = :id";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'image_url' => $image_url,
            'price' => $price,
            'profit_amount' => $profitAmount,
            'profit_percent' => $profitPercent,
            'ads' => $ads,
            'id' => $id
        ]);

        header('Location: /admin/plans?success=updated');
        exit;
    }

    public function deletePlan()
    {
        $id = $_POST['id'] ?? 0;
        $db = Model::connect();
        $stmt = $db->prepare("UPDATE investment_plans SET status = 'deleted' WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header('Location: /admin/plans?success=deleted');
        exit;
    }

    public function ads()
    {
        $ads = \App\Models\Ad::all();
        return $this->view('admin/ads', [
            'title' => 'Gérer les Publicités',
            'ads' => $ads
        ]);
    }

    public function createAd()
    {
        $title = $_POST['title'] ?? '';
        $link = $_POST['link'] ?? '';
        $duration = $_POST['duration'] ?? 30;
        $reward = $_POST['reward'] ?? 50;
        $status = $_POST['status'] ?? 'active';

        if (empty($title) || empty($link)) {
            header('Location: /admin/ads?error=missing_fields');
            exit;
        }

        $db = Model::connect();
        $stmt = $db->prepare("INSERT INTO ads (title, link, duration, reward, status, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $link, $duration, $reward, $status, Session::get('user_id')]);

        header('Location: /admin/ads?success=created');
        exit;
    }

    public function updateAd()
    {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $link = $_POST['link'] ?? '';
        $duration = $_POST['duration'] ?? 30;
        $reward = $_POST['reward'] ?? 50;
        $status = $_POST['status'] ?? 'active';

        if (!$id || empty($title) || empty($link)) {
            header('Location: /admin/ads?error=missing_fields');
            exit;
        }

        $db = Model::connect();
        $stmt = $db->prepare("UPDATE ads SET title = ?, link = ?, duration = ?, reward = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $link, $duration, $reward, $status, $id]);

        header('Location: /admin/ads?success=updated');
        exit;
    }

    public function deleteAd()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: /admin/ads?error=missing_id');
            exit;
        }

        $db = Model::connect();
        $stmt = $db->prepare("DELETE FROM ads WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: /admin/ads?success=deleted');
        exit;
    }

    public function toggleAdStatus()
    {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            header('Location: /admin/ads?error=missing_id');
            exit;
        }

        $db = Model::connect();
        $stmt = $db->prepare("UPDATE ads SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?");
        $stmt->execute([$id]);

        header('Location: /admin/ads?success=toggled');
        exit;
    }

    public function processPayouts()
    {
        $count = \App\Models\Investment::processPayouts();
        header("Location: /admin?payouts=$count");
        exit;
    }

    // Admin Guide Management
    public function guides()
    {
        $guides = \App\Models\Guide::all();
        return $this->view('admin/guides/index', [
            'title' => 'Gérer les Guides',
            'guides' => $guides
        ]);
    }

    public function showCreateGuide()
    {
        return $this->view('admin/guides/create', ['title' => 'Créer un Guide']);
    }

    public function storeGuide()
    {
        // Handle Thumbnail Upload
        $image_url = $this->uploadMedia($_FILES['image'] ?? null, 'uploads/guides/')
            ?? 'https://images.unsplash.com/photo-1579389083078-4e7018379f7e?auto=format&fit=crop&q=80&w=400';

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'content' => $_POST['content'] ?? '',
            'image_url' => $image_url,
            'order_index' => $_POST['order_index'] ?? 0,
            'status' => $_POST['status'] ?? 'active'
        ];

        $guideId = \App\Models\Guide::create($data);

        // Handle Steps
        if (isset($_POST['steps']) && is_array($_POST['steps'])) {
            foreach ($_POST['steps'] as $index => $stepData) {
                if (!empty($stepData['content'])) {
                    $media_url = $stepData['media_url'] ?? null;
                    $media_type = $stepData['media_type'] ?? 'none';

                    // Handle file upload for this step
                    if (isset($_FILES['steps']['name'][$index]['media_file']) && $_FILES['steps']['error'][$index]['media_file'] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['steps']['name'][$index]['media_file'],
                            'type' => $_FILES['steps']['type'][$index]['media_file'],
                            'tmp_name' => $_FILES['steps']['tmp_name'][$index]['media_file'],
                            'error' => $_FILES['steps']['error'][$index]['media_file'],
                            'size' => $_FILES['steps']['size'][$index]['media_file']
                        ];
                        $uploaded = $this->uploadMedia($file, 'uploads/guides/steps/');
                        if ($uploaded) {
                            $media_url = $uploaded;
                            $media_type = (strpos($file['type'], 'video') !== false) ? 'video' : 'image';
                        }
                    }

                    \App\Models\GuideStep::create([
                        'guide_id' => $guideId,
                        'title' => $stepData['title'] ?? null,
                        'content' => $stepData['content'],
                        'media_url' => $media_url,
                        'media_type' => $media_type,
                        'order_index' => $index
                    ]);
                }
            }
        }

        header('Location: /admin/guides?success=1');
        exit;
    }

    public function showEditGuide()
    {
        $id = $_GET['id'] ?? 0;
        $guide = \App\Models\Guide::findById($id);
        if (!$guide) {
            header('Location: /admin/guides');
            exit;
        }
        $steps = \App\Models\GuideStep::forGuide($id);

        return $this->view('admin/guides/edit', [
            'title' => 'Modifier le Guide',
            'guide' => $guide,
            'steps' => $steps
        ]);
    }

    public function updateGuide()
    {
        $id = $_POST['id'] ?? 0;

        // Handle Image Upload (Optional replacement)
        $new_image = $this->uploadMedia($_FILES['image'] ?? null, 'uploads/guides/');

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'content' => $_POST['content'] ?? '',
            'order_index' => $_POST['order_index'] ?? 0,
            'status' => $_POST['status'] ?? 'active'
        ];

        if ($new_image) {
            $data['image_url'] = $new_image;
        }

        \App\Models\Guide::update($id, $data);

        // Handle Steps
        \App\Models\GuideStep::deleteForGuide($id);
        if (isset($_POST['steps']) && is_array($_POST['steps'])) {
            foreach ($_POST['steps'] as $index => $stepData) {
                if (!empty($stepData['content'])) {
                    $media_url = $stepData['media_url'] ?? null;
                    $media_type = $stepData['media_type'] ?? 'none';

                    // Handle file upload for this step
                    if (isset($_FILES['steps']['name'][$index]['media_file']) && $_FILES['steps']['error'][$index]['media_file'] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['steps']['name'][$index]['media_file'],
                            'type' => $_FILES['steps']['type'][$index]['media_file'],
                            'tmp_name' => $_FILES['steps']['tmp_name'][$index]['media_file'],
                            'error' => $_FILES['steps']['error'][$index]['media_file'],
                            'size' => $_FILES['steps']['size'][$index]['media_file']
                        ];
                        $uploaded = $this->uploadMedia($file, 'uploads/guides/steps/');
                        if ($uploaded) {
                            $media_url = $uploaded;
                            $media_type = (strpos($file['type'], 'video') !== false) ? 'video' : 'image';
                        }
                    }

                    \App\Models\GuideStep::create([
                        'guide_id' => $id,
                        'title' => $stepData['title'] ?? null,
                        'content' => $stepData['content'],
                        'media_url' => $media_url,
                        'media_type' => $media_type,
                        'order_index' => $index
                    ]);
                }
            }
        }

        header('Location: /admin/guides?success=updated');
        exit;
    }

    public function deleteGuide()
    {
        $id = $_POST['id'] ?? 0;
        \App\Models\Guide::delete($id);
        header('Location: /admin/guides?success=deleted');
        exit;
    }

    // Withdrawal Management
    public function withdrawals()
    {
        $db = Model::connect();

        // Get all withdrawal requests with user information
        // Essayer d'abord avec 'withdrawal' (structure standard)
        try {
            $stmt = $db->query("
                SELECT t.*, u.name as user_name, u.email as user_email, u.balance 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.type = 'withdrawal' 
                ORDER BY 
                    CASE t.status 
                        WHEN 'pending' THEN 1 
                        WHEN 'completed' THEN 2 
                        WHEN 'rejected' THEN 3 
                    END,
                    t.created_at DESC
            ");
            $withdrawals = $stmt->fetchAll();

            // Count pending withdrawals
            $stmt = $db->query("SELECT COUNT(*) as count FROM transactions WHERE type = 'withdrawal' AND status = 'pending'");
            $pendingCount = $stmt->fetch()['count'];
        } catch (\PDOException $e) {
            // Si ça échoue, essayer avec 'retrait' (structure alternative)
            $stmt = $db->query("
                SELECT t.*, u.name as user_name, u.email as user_email, u.balance,
                    CASE t.statut 
                        WHEN 'attente' THEN 'pending'
                        WHEN 'success' THEN 'completed'
                        WHEN 'annule' THEN 'rejected'
                        ELSE t.statut 
                    END as status
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.type = 'retrait' 
                ORDER BY t.created_at DESC
            ");
            $withdrawals = $stmt->fetchAll();

            // Count pending withdrawals
            $stmt = $db->query("SELECT COUNT(*) as count FROM transactions WHERE type = 'retrait' AND statut = 'attente'");
            $pendingCount = $stmt->fetch()['count'];
        }

        // Get all users for the manual withdrawal form
        $stmt = $db->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name");
        $users = $stmt->fetchAll();

        return $this->view('admin/withdrawals', [
            'title' => 'Gestion des Retraits',
            'withdrawals' => $withdrawals,
            'pendingCount' => $pendingCount,
            'users' => $users
        ]);
    }

    public function approveWithdrawal()
    {
        $id = $_POST['id'] ?? null;
        $action = $_POST['action'] ?? null; // 'approve' or 'reject'

        if (!$id || !$action) {
            header('Location: /admin/withdrawals?error=missing_data');
            exit;
        }

        $db = Model::connect();

        // Get withdrawal details
        $stmt = $db->prepare("SELECT * FROM transactions WHERE id = ? AND type = 'withdrawal'");
        $stmt->execute([$id]);
        $withdrawal = $stmt->fetch();

        if (!$withdrawal) {
            header('Location: /admin/withdrawals?error=not_found');
            exit;
        }

        if ($withdrawal['status'] !== 'pending') {
            header('Location: /admin/withdrawals?error=already_processed');
            exit;
        }

        try {
            $db->beginTransaction();

            if ($action === 'approve') {
                // Mark as completed
                $stmt = $db->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
                $stmt->execute([$id]);

                $message = 'approved';
            } else {
                // Reject and refund the amount to user balance
                $stmt = $db->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ?");
                $stmt->execute([$id]);

                // Refund to user balance
                $stmt = $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $stmt->execute([$withdrawal['amount'], $withdrawal['user_id']]);

                $message = 'rejected';
            }

            $db->commit();
            header("Location: /admin/withdrawals?success=$message");
        } catch (\Exception $e) {
            $db->rollBack();
            header('Location: /admin/withdrawals?error=database');
        }

        exit;
    }

    /**
     * Ajouter un retrait manuellement depuis l'admin
     * Pour l'admin lui-même ou un utilisateur spécifique
     */
    public function addManualWithdrawal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/withdrawals');
            exit;
        }

        $userId = $_POST['user_id'] ?? Session::get('user_id');
        $amount = floatval($_POST['amount'] ?? 0);
        $method = $_POST['method'] ?? 'orange';
        $source = $_POST['source'] ?? 'investissement';
        $phoneNumber = trim($_POST['phone_number'] ?? '');
        $status = $_POST['status'] ?? 'attente';
        $note = $_POST['note'] ?? '';

        // Validation
        if ($amount <= 0) {
            header('Location: /admin/withdrawals?error=invalid_amount');
            exit;
        }

        if (empty($phoneNumber)) {
            header('Location: /admin/withdrawals?error=missing_phone');
            exit;
        }

        $db = Model::connect();

        try {
            $db->beginTransaction();

            // Générer une référence unique
            $reference = 'RET' . time() . rand(1000, 9999);

            // Vérifier si la colonne numero_telephone existe
            $columnExists = false;
            try {
                $db->query("SELECT numero_telephone FROM transactions LIMIT 1");
                $columnExists = true;
            } catch (\PDOException $e) {
                $columnExists = false;
            }

            if ($columnExists) {
                // Structure avec numero_telephone (utilisée par pages/retrais.php)
                $sql = "INSERT INTO transactions 
                        (user_id, type, source, montant, methode, numero_telephone, statut, reference, note, created_at) 
                        VALUES (:user_id, 'retrait', :source, :montant, :methode, :phone, :statut, :reference, :note, NOW())";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':source' => $source,
                    ':montant' => $amount,
                    ':methode' => $method,
                    ':phone' => $phoneNumber,
                    ':statut' => $status,
                    ':reference' => $reference,
                    ':note' => $note ?: "Retrait créé manuellement par l'admin"
                ]);
            } else {
                // Structure standard sans numero_telephone
                $sql = "INSERT INTO transactions 
                        (user_id, type, amount, status, reference, description) 
                        VALUES (:user_id, 'withdrawal', :amount, :status, :reference, :description)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':amount' => $amount,
                    ':status' => $status === 'success' ? 'completed' : 'pending',
                    ':reference' => $reference,
                    ':description' => "Retrait via $method - Tél: $phoneNumber | " . ($note ?: "Créé manuellement par l'admin")
                ]);
            }

            // Mettre à jour le solde du wallet si le statut est 'success'
            if ($status === 'success') {
                // Vérifier si la table wallets existe
                try {
                    $db->query("SELECT 1 FROM wallets LIMIT 1");
                    $walletExists = true;
                } catch (\PDOException $e) {
                    $walletExists = false;
                }

                if ($walletExists) {
                    $balanceField = 'solde_' . $source;
                    $updateSql = "UPDATE wallets SET {$balanceField} = {$balanceField} - :amount WHERE user_id = :user_id";
                    $updateStmt = $db->prepare($updateSql);
                    $updateStmt->execute([':amount' => $amount, ':user_id' => $userId]);
                }
            }

            $db->commit();
            header('Location: /admin/withdrawals?success=manual_withdrawal_created');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Erreur création retrait manuel: " . $e->getMessage());
            header('Location: /admin/withdrawals?error=creation_failed');
            exit;
        }
    }

    // User Management
    public function users()
    {
        $db = Model::connect();

        // Handle search
        $search = $_GET['search'] ?? '';
        $sql = "SELECT * FROM users";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE name LIKE ? OR email LIKE ?";
            $params = ["%$search%", "%$search%"];
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        // Get all plans for the add plan modal
        $plans = Plan::all();

        return $this->view('admin/users', [
            'title' => 'Gestion des Utilisateurs',
            'users' => $users,
            'plans' => $plans,
            'search' => $search
        ]);
    }

    public function updateUser()
    {
        $id = $_POST['id'] ?? null;
        $balance = $_POST['balance'] ?? 0;
        $role = $_POST['role'] ?? 'user';
        $status = $_POST['status'] ?? 'active'; // active, suspended

        if (!$id) {
            header('Location: /admin/users?error=missing_id');
            exit;
        }

        $db = Model::connect();
        $stmt = $db->prepare("UPDATE users SET balance = ?, role = ?, status = ? WHERE id = ?");
        $stmt->execute([$balance, $role, $status, $id]);

        header('Location: /admin/users?success=updated');
        exit;
    }

    public function addPlanToUser()
    {
        $userId = $_POST['user_id'] ?? null;
        $planId = $_POST['plan_id'] ?? null;

        if (!$userId || !$planId) {
            header('Location: /admin/users?error=missing_data');
            exit;
        }

        $plan = Plan::findById($planId);
        if (!$plan) {
            header('Location: /admin/users?error=plan_not_found');
            exit;
        }

        // Activate plan for user (Admin override, no balance check usually, or optional)
        // Here we assume admin gift/manual activation, so we don't deduct balance or check it
        // We just verify valid inputs.

        $db = Model::connect();
        $stmt = $db->prepare("
            INSERT INTO investments (user_id, plan_id, amount, daily_profit, total_profit, start_date, end_date, next_payout, status, ads_per_day)
            VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), DATE_ADD(NOW(), INTERVAL 1 DAY), 'active', ?)
        ");

        $dailyProfitAmount = $plan['daily_profit_amount'] ?: ($plan['price'] * ($plan['daily_profit_percent'] / 100));
        $totalProfit = $dailyProfitAmount * $plan['duration_days'];

        $stmt->execute([
            $userId,
            $planId,
            $plan['price'],
            $dailyProfitAmount,
            $totalProfit,
            $plan['duration_days'],
            $plan['ads_per_day']
        ]);

        header('Location: /admin/users?success=plan_added');
        exit;
    }

    // Moderation
    public function moderation()
    {
        $db = Model::connect();
        
        $pending = \App\Models\CommunityMessage::getPending();
        
        // Get all users for the fake name dropdown
        $stmt = $db->query("SELECT id, name, email FROM users ORDER BY name");
        $users = $stmt->fetchAll();
        
        return $this->view('admin/moderation', [
            'title' => 'Modération Communauté',
            'messages' => $pending,
            'users' => $users
        ]);
    }

    public function approveMessage()
    {
        $id = $_POST['id'] ?? null;
        if ($id) {
            \App\Models\CommunityMessage::updateStatus($id, 'approved');
        }
        header('Location: /admin/moderation?success=approved');
        exit;
    }

    public function rejectMessage()
    {
        $id = $_POST['id'] ?? null;
        if ($id) {
            \App\Models\CommunityMessage::updateStatus($id, 'rejected');
        }
        header('Location: /admin/moderation?success=rejected');
        exit;
    }

    /**
     * Créer un message communautaire avec un faux nom (usurpation d'identité)
     * L'admin peut poster en tant qu'un autre utilisateur
     */
    public function createCommunityMessage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/moderation');
            exit;
        }

        $userId = $_POST['user_id'] ?? Session::get('user_id');
        $fakeName = trim($_POST['fake_name'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $status = $_POST['status'] ?? 'approved'; // Par défaut approuvé pour l'admin

        if (empty($message)) {
            header('Location: /admin/moderation?error=empty_message');
            exit;
        }

        try {
            // Créer le message avec fake_name
            $result = \App\Models\CommunityMessage::create([
                'user_id' => $userId,
                'message' => $message,
                'status' => $status,
                'fake_name' => $fakeName
            ]);

            if ($result) {
                header('Location: /admin/moderation?success=message_created');
            } else {
                header('Location: /admin/moderation?error=creation_failed');
            }
        } catch (\Exception $e) {
            error_log("Erreur création message admin: " . $e->getMessage());
            header('Location: /admin/moderation?error=creation_failed');
        }
        exit;
    }

    private function uploadMedia($file, $targetDir = 'uploads/plans/')
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $allowedVideoTypes = ['video/mp4', 'video/webm', 'video/quicktime'];

        $allAllowed = array_merge($allowedImageTypes, $allowedVideoTypes);

        if (!in_array($file['type'], $allAllowed)) {
            return null;
        }

        $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $targetPath = public_path($targetDir . $filename);

        // Ensure directory exists
        if (!is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/' . $targetDir . $filename;
        }

        return null;
    }

    // APK Version Management
    public function appVersions()
    {
        $versions = \App\Models\AppVersion::all();
        $activeVersion = \App\Models\AppVersion::getActiveVersion();

        return $this->view('admin/app_versions/index', [
            'title' => 'Gestion des Versions APK',
            'versions' => $versions,
            'activeVersion' => $activeVersion
        ]);
    }

    public function uploadApk()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/app-versions');
            exit;
        }

        $versionName = $_POST['version_name'] ?? '';
        $versionCode = $_POST['version_code'] ?? 0;
        $notes = $_POST['notes'] ?? '';

        if (empty($versionName) || empty($versionCode)) {
            Session::set('error', 'Le nom et le code de version sont requis.');
            header('Location: /admin/app-versions');
            exit;
        }

        // Handle APK file upload
        if (!isset($_FILES['apk_file']) || $_FILES['apk_file']['error'] !== UPLOAD_ERR_OK) {
            Session::set('error', 'Veuillez sélectionner un fichier APK valide.');
            header('Location: /admin/app-versions');
            exit;
        }

        $file = $_FILES['apk_file'];

        // Validate APK file
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($fileExt !== 'apk') {
            Session::set('error', 'Seuls les fichiers APK sont autorisés.');
            header('Location: /admin/app-versions');
            exit;
        }

        // Check file size (max 100MB)
        if ($file['size'] > 100 * 1024 * 1024) {
            Session::set('error', 'Le fichier APK ne doit pas dépasser 100MB.');
            header('Location: /admin/app-versions');
            exit;
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = 'uploads/apk/';
        if (!is_dir(public_path($uploadDir))) {
            mkdir(public_path($uploadDir), 0755, true);
        }

        // Generate unique filename
        $filename = 'investian_v' . $versionCode . '_' . time() . '.apk';
        $targetPath = public_path($uploadDir . $filename);

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Save to database
            $success = \App\Models\AppVersion::create([
                'version_name' => $versionName,
                'version_code' => (int) $versionCode,
                'apk_file_path' => '/' . $uploadDir . $filename,
                'file_size' => $file['size'],
                'uploaded_by' => Session::get('user_id'),
                'notes' => $notes
            ]);

            if ($success) {
                Session::set('success', 'Version APK uploadée avec succès ! Elle sera active pendant 14 jours.');
            } else {
                Session::set('error', 'Erreur lors de l\'enregistrement de la version.');
            }
        } else {
            Session::set('error', 'Erreur lors de l\'upload du fichier APK.');
        }

        header('Location: /admin/app-versions');
        exit;
    }

    public function deleteApkVersion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/app-versions');
            exit;
        }

        $id = $_POST['version_id'] ?? 0;

        if (\App\Models\AppVersion::delete($id)) {
            Session::set('success', 'Version supprimée avec succès.');
        } else {
            Session::set('error', 'Erreur lors de la suppression.');
        }

        header('Location: /admin/app-versions');
        exit;
    }
}
