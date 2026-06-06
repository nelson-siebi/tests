<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/helpers.php';

// Load environment variables
\App\Core\Env::load(__DIR__ . '/../.env');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Core\Router;

$router = new Router();

// Define routes
$router->add('GET', '/', 'HomeController@index');

// Auth Routes
$router->add('GET', '/login', 'AuthController@showLogin');
$router->add('POST', '/login', 'AuthController@login');
$router->add('GET', '/register', 'AuthController@showRegister');
$router->add('POST', '/register', 'AuthController@register');
$router->add('GET', '/logout', 'AuthController@logout');

// Dashboard Routes
$router->add('GET', '/dashboard', 'DashboardController@index');
$router->add('GET', '/support', 'DashboardController@support');
$router->add('GET', '/guide', 'DashboardController@guide');
$router->add('GET', '/profile', 'ProfileController@index');
$router->add('POST', '/profile', 'ProfileController@update');

// Recharge Routes
$router->get('/recharge', 'RechargeController@index');
$router->post('/recharge', 'RechargeController@store');

// Withdraw Routes
$router->get('/withdraw', 'WithdrawController@index');
$router->post('/withdraw', 'WithdrawController@store');

// Add the /lang route to update language session.
$router->get('/lang', function () {
    $lang = $_GET['l'] ?? 'en';
    \App\Core\Language::set($lang);
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/dashboard'));
    exit;
});

// Download/Install Page
$router->add('GET', '/download', 'DownloadController@index');
$router->add('GET', '/download/apk', 'DownloadController@downloadApk');

// Plan Routes
$router->add('GET', '/plans', 'PlanController@index');
$router->add('POST', '/invest', 'PlanController@invest');

// Ads Routes
$router->add('GET', '/ads', 'AdsController@index');
$router->add('POST', '/ads/watch', 'AdsController@watch');

// Admin Routes
$router->add('GET', '/admin', 'AdminController@index');
$router->add('POST', '/admin/migrations/run', 'AdminController@runMigrations');
$router->add('GET', '/admin/recharges', 'AdminController@recharges');
$router->add('POST', '/admin/recharges/approve', 'AdminController@approveRecharge');
$router->add('GET', '/admin/withdrawals', 'AdminController@withdrawals');
$router->add('POST', '/admin/withdrawals/approve', 'AdminController@approveWithdrawal');
$router->add('POST', '/admin/withdrawals/add-manual', 'AdminController@addManualWithdrawal');
$router->add('GET', '/admin/plans', 'AdminController@plans');
$router->add('GET', '/admin/plans/create', 'AdminController@showCreatePlan');
$router->add('POST', '/admin/plans/create', 'AdminController@storePlan');
$router->add('GET', '/admin/plans/edit', 'AdminController@editPlan');
$router->add('POST', '/admin/plans/edit', 'AdminController@updatePlan');
$router->add('POST', '/admin/plans/delete', 'AdminController@deletePlan');
$router->add('GET', '/admin/payout', 'AdminController@processPayouts');
$router->add('GET', '/cron/payout', 'CronController@processPayouts');
$router->add('GET', '/admin/ads', 'AdminController@ads');
$router->add('POST', '/admin/ads/create', 'AdminController@createAd');
$router->add('POST', '/admin/ads/update', 'AdminController@updateAd');
$router->add('POST', '/admin/ads/delete', 'AdminController@deleteAd');
$router->add('POST', '/admin/ads/toggle', 'AdminController@toggleAdStatus');

// Admin Guide Routes
$router->add('GET', '/admin/guides', 'AdminController@guides');
$router->add('GET', '/admin/guides/create', 'AdminController@showCreateGuide');
$router->add('POST', '/admin/guides/create', 'AdminController@storeGuide');
$router->add('GET', '/admin/guides/edit', 'AdminController@showEditGuide');
$router->add('POST', '/admin/guides/edit', 'AdminController@updateGuide');
$router->add('POST', '/admin/guides/delete', 'AdminController@deleteGuide');

// Community Routes
$router->add('GET', '/community', 'CommunityController@index');
$router->add('GET', '/community/fetch', 'CommunityController@fetch');
$router->add('POST', '/community/post', 'CommunityController@store');

// Admin User Routes
$router->add('GET', '/admin/users', 'AdminController@users');
$router->add('POST', '/admin/users/update', 'AdminController@updateUser');
$router->add('POST', '/admin/users/plan', 'AdminController@addPlanToUser');

// Admin APK Version Management
$router->add('GET', '/admin/app-versions', 'AdminController@appVersions');
$router->add('POST', '/admin/app-versions/upload', 'AdminController@uploadApk');
$router->add('POST', '/admin/app-versions/delete', 'AdminController@deleteApkVersion');

// Admin Moderation
$router->add('GET', '/admin/moderation', 'AdminController@moderation');
$router->add('POST', '/admin/moderation/approve', 'AdminController@approveMessage');
$router->add('POST', '/admin/moderation/reject', 'AdminController@rejectMessage');
$router->add('POST', '/admin/moderation/create', 'AdminController@createCommunityMessage');

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
