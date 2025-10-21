<?php

require_once 'config.php';

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
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Action invalide: ' . $action]);
}

// ==================== FONCTIONS JOUEUR ====================

function getGroups($pdo) {
    $game_id = intval($_GET['game_id'] ?? 1);
    
    $stmt = $pdo->prepare('SELECT id, name, color FROM groups_table WHERE game_id = ? ORDER BY name');
    $stmt->execute([$game_id]);
    $groups = $stmt->fetchAll();
    
    echo json_encode(['groups' => $groups]);
}

function getState($pdo) {
    $group_id = intval($_GET['group_id'] ?? 0);
    
    if (!$group_id) {
        http_response_code(400);
        exit(json_encode(['error' => 'group_id requis']));
    }
    
    $stmt = $pdo->prepare('SELECT * FROM groups_table WHERE id = ?');
    $stmt->execute([$group_id]);
    $group = $stmt->fetch();
    
    if (!$group) {
        http_response_code(404);
        exit(json_encode(['error' => 'Groupe introuvable']));
    }
    
    // Compter le nombre total d'énigmes pour ce groupe
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM enigmes WHERE group_id = ?');
    $stmt->execute([$group_id]);
    $total = $stmt->fetch()['total'];
    
    echo json_encode([
        'group_id' => $group['id'],
        'name' => $group['name'],
        'score' => intval($group['score']),
        'current_step' => intval($group['current_step']),
        'total_steps' => intval($total)
    ]);
}

function getLeaderboard($pdo) {
    $game_id = intval($_GET['game_id'] ?? 1);
    
    $stmt = $pdo->prepare('
        SELECT id, name, color, score, current_step
        FROM groups_table 
        WHERE game_id = ?
        ORDER BY score DESC, current_step DESC
    ');
    $stmt->execute([$game_id]);
    $leaderboard = $stmt->fetchAll();
    
    echo json_encode(['leaderboard' => $leaderboard]);
}

function scanQR($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $group_id = intval($input['group_id'] ?? 0);
    $qr_code = strtoupper(trim($input['qr_code'] ?? ''));
    
    if (!$group_id || !$qr_code) {
        http_response_code(400);
        exit(json_encode(['error' => 'Paramètres manquants']));
    }
    
    // Récupérer le groupe
    $stmt = $pdo->prepare('SELECT * FROM groups_table WHERE id = ?');
    $stmt->execute([$group_id]);
    $group = $stmt->fetch();
    
    if (!$group) {
        http_response_code(404);
        exit(json_encode(['error' => 'Groupe introuvable']));
    }
    
    // Vérifier que le QR code appartient à CE groupe
    $stmt = $pdo->prepare('
        SELECT * FROM enigmes 
        WHERE qr_code = ? AND group_id = ?
    ');
    $stmt->execute([$qr_code, $group_id]);
    $enigme = $stmt->fetch();
    
    if (!$enigme) {
        // Vérifier si le QR existe mais pour un autre groupe
        $stmt = $pdo->prepare('
            SELECT g.name 
            FROM enigmes e
            JOIN groups_table g ON e.group_id = g.id
            WHERE e.qr_code = ?
        ');
        $stmt->execute([$qr_code]);
        $other_group = $stmt->fetch();
        
        if ($other_group) {
            exit(json_encode([
                'success' => false,
                'error' => 'Ce QR code appartient au groupe "' . $other_group['name'] . '". Utilisez vos propres QR codes !'
            ]));
        }
        
        exit(json_encode([
            'success' => false,
            'error' => 'QR Code invalide'
        ]));
    }
    
    // Vérifier que c'est la bonne étape
    if ($enigme['step_number'] != $group['current_step']) {
        exit(json_encode([
            'success' => false,
            'error' => 'Ce QR code correspond à l\'étape ' . $enigme['step_number'] . '. Vous êtes à l\'étape ' . $group['current_step']
        ]));
    }
    
    echo json_encode([
        'success' => true,
        'enigme' => [
            'id' => $enigme['id'],
            'step_number' => $enigme['step_number'],
            'enigme_text' => $enigme['enigme_text']
        ]
    ]);
}

function submitAnswer($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $group_id = intval($input['group_id'] ?? 0);
    $enigme_id = intval($input['enigme_id'] ?? 0);
    $answer = strtoupper(trim($input['answer'] ?? ''));
    
    if (!$group_id || !$enigme_id || !$answer) {
        http_response_code(400);
        exit(json_encode(['error' => 'Paramètres manquants']));
    }
    
    // Récupérer l'énigme
    $stmt = $pdo->prepare('SELECT * FROM enigmes WHERE id = ?');
    $stmt->execute([$enigme_id]);
    $enigme = $stmt->fetch();
    
    if (!$enigme) {
        http_response_code(404);
        exit(json_encode(['error' => 'Énigme introuvable']));
    }
    
    // Vérifier la réponse
    $correct_answer = strtoupper(trim($enigme['answer']));
    $is_correct = ($answer === $correct_answer);
    
    // Enregistrer la soumission
    $stmt = $pdo->prepare('
        INSERT INTO submissions (group_id, enigme_id, answer_submitted, is_correct)
        VALUES (?, ?, ?, ?)
    ');
    $stmt->execute([$group_id, $enigme_id, $answer, $is_correct ? 1 : 0]);
    
    if ($is_correct) {
        // Mettre à jour le score et l'étape du groupe
        $stmt = $pdo->prepare('
            UPDATE groups_table 
            SET score = score + ?, current_step = current_step + 1
            WHERE id = ?
        ');
        $stmt->execute([$enigme['points'], $group_id]);
        
        echo json_encode([
            'correct' => true,
            'points' => intval($enigme['points']),
            'next_location' => $enigme['next_location']
        ]);
    } else {
        echo json_encode([
            'correct' => false,
            'message' => 'Mauvaise réponse'
        ]);
    }
}

// ==================== FONCTIONS ADMIN ====================

function adminCreateGroup($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $game_id = intval($input['game_id'] ?? 1);
    $name = trim($input['name'] ?? '');
    $color = trim($input['color'] ?? '#3B82F6');
    
    if (!$name) {
        http_response_code(400);
        exit(json_encode(['error' => 'Nom requis']));
    }
    
    try {
        // Créer le groupe
        $stmt = $pdo->prepare('
            INSERT INTO groups_table (game_id, name, color)
            VALUES (?, ?, ?)
        ');
        $stmt->execute([$game_id, $name, $color]);
        $group_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'id' => $group_id,
            'message' => 'Groupe créé avec succès'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
    }
}

function adminCreateEnigme($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $game_id = intval($input['game_id'] ?? 1);
    $group_id = intval($input['group_id'] ?? 0);
    $step_number = intval($input['step_number'] ?? 0);
    $enigme_text = trim($input['enigme_text'] ?? '');
    $answer = strtoupper(trim($input['answer'] ?? ''));
    $next_location = trim($input['next_location'] ?? '');
    $points = intval($input['points'] ?? 10);
    
    if (!$group_id || !$step_number || !$enigme_text || !$answer) {
        http_response_code(400);
        exit(json_encode(['error' => 'Paramètres manquants (group_id, step_number, enigme_text, answer requis)']));
    }
    
    // Générer un QR code aléatoire sécurisé
    $qr_code = generateSecureQRCode($pdo);
    
    try {
        // Créer l'énigme
        $stmt = $pdo->prepare('
            INSERT INTO enigmes (game_id, group_id, step_number, enigme_text, answer, next_location, points, qr_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$game_id, $group_id, $step_number, $enigme_text, $answer, $next_location, $points, $qr_code]);
        $enigme_id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'id' => $enigme_id,
            'qr_code' => $qr_code,
            'message' => 'Énigme créée avec succès'
        ]);
    } catch (PDOException $e) {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur : ' . $e->getMessage()]);
    }
}

function adminGetStats($pdo) {
    $game_id = intval($_GET['game_id'] ?? 1);
    
    // Nombre de groupes
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM groups_table WHERE game_id = ?');
    $stmt->execute([$game_id]);
    $total_groups = $stmt->fetch()['total'];
    
    // Nombre d'énigmes
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM enigmes WHERE game_id = ?');
    $stmt->execute([$game_id]);
    $total_enigmes = $stmt->fetch()['total'];
    
    // Nombre de soumissions
    $stmt = $pdo->prepare('
        SELECT COUNT(*) as total 
        FROM submissions s
        JOIN groups_table g ON s.group_id = g.id
        WHERE g.game_id = ?
    ');
    $stmt->execute([$game_id]);
    $total_submissions = $stmt->fetch()['total'];
    
    // Taux de réussite
    $stmt = $pdo->prepare('
        SELECT 
            COUNT(*) as total,
            SUM(is_correct) as correct
        FROM submissions s
        JOIN groups_table g ON s.group_id = g.id
        WHERE g.game_id = ?
    ');
    $stmt->execute([$game_id]);
    $success_data = $stmt->fetch();
    $success_rate = $success_data['total'] > 0 
        ? round(($success_data['correct'] / $success_data['total']) * 100) 
        : 0;
    
    // Groupes
    $stmt = $pdo->prepare('
        SELECT id, name, color, score, current_step
        FROM groups_table
        WHERE game_id = ?
        ORDER BY score DESC
    ');
    $stmt->execute([$game_id]);
    $groups = $stmt->fetchAll();
    
    // Énigmes organisées par groupe
    $stmt = $pdo->prepare('
        SELECT 
            e.id,
            e.group_id,
            e.step_number,
            e.enigme_text,
            e.answer,
            e.qr_code,
            e.points,
            g.name as group_name,
            g.color as group_color
        FROM enigmes e
        JOIN groups_table g ON e.group_id = g.id
        WHERE e.game_id = ?
        ORDER BY g.name, e.step_number
    ');
    $stmt->execute([$game_id]);
    $enigmes = $stmt->fetchAll();
    
    echo json_encode([
        'stats' => [
            'total_groups' => intval($total_groups),
            'total_enigmes' => intval($total_enigmes),
            'total_submissions' => intval($total_submissions),
            'success_rate' => intval($success_rate)
        ],
        'groups' => $groups,
        'enigmes' => $enigmes
    ]);
}

function adminGetActivity($pdo) {
    $game_id = intval($_GET['game_id'] ?? 1);
    
    $stmt = $pdo->prepare('
        SELECT 
            s.id,
            s.answer_submitted,
            s.is_correct,
            s.created_at,
            g.name as group_name,
            g.color as group_color,
            e.step_number,
            e.enigme_text
        FROM submissions s
        JOIN groups_table g ON s.group_id = g.id
        JOIN enigmes e ON s.enigme_id = e.id
        WHERE g.game_id = ?
        ORDER BY s.created_at DESC
        LIMIT 20
    ');
    $stmt->execute([$game_id]);
    $activity = $stmt->fetchAll();
    
    echo json_encode(['activity' => $activity]);
}

function adminGetEnigmesByGroup($pdo) {
    $game_id = intval($_GET['game_id'] ?? 1);
    
    $stmt = $pdo->prepare('
        SELECT 
            e.id,
            e.group_id,
            e.step_number,
            e.enigme_text,
            e.answer,
            e.qr_code,
            e.points,
            g.name as group_name,
            g.color as group_color
        FROM enigmes e
        JOIN groups_table g ON e.group_id = g.id
        WHERE e.game_id = ?
        ORDER BY g.name, e.step_number
    ');
    $stmt->execute([$game_id]);
    $enigmes = $stmt->fetchAll();
    
    echo json_encode(['enigmes' => $enigmes]);
}