<?php

require_once 'config.php';
require_once 'utils/connexion.php';
require_once 'utils/players.php';
require_once 'utils/admin.php';

// Headers CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$pdo = getDB();

// Récupérer l'action
$action = $_GET['action'] ?? '';
if (empty($action)) {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}

switch ($action) {
    case 'get_groups':
        getGroups($pdo);
        break;
    
    case 'get_state':
        getState($pdo);
        break;
    
    case 'get_leaderboard':
        getLeaderboard($pdo);
        break;
    
    case 'scan_qr':
        scanQR($pdo);
        break;
    
    case 'submit_answer':
        submitAnswer($pdo);
        break;
    
    case 'admin_create_group':
        adminCreateGroup($pdo);
        break;
    
    case 'admin_create_enigme':
        adminCreateEnigme($pdo);
        break;
    
    case 'admin_get_stats':
        adminGetStats($pdo);
        break;
    
    case 'admin_get_activity':
        adminGetActivity($pdo);
        break;
    
    case 'admin_get_enigmes_by_group':
        adminGetEnigmesByGroup($pdo);
        break;
    
    case 'register_player':
        registerPlayer($pdo);
        break;

    case 'login_player':
        login_player($pdo);
        break;

    case 'set_session':
        set_session($pdo);
        break;
    
    case 'verify_session':
        verify_session($pdo);
        break;
        
    case 'logout_player':
        logout_player($pdo);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action invalide: ' . $action]);
}

