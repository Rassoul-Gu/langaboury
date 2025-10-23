<?php
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

function get_good_answers($pdo){
    header('Content-Type: application/json');

    $group_id = intval($_GET['group_id'] ?? 0);
    if ($group_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID groupe invalide']);
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT e.enigme_text AS question_name, e.answer AS correct_answer
            FROM submissions s
            INNER JOIN enigmes e ON e.id = s.enigme_id
            WHERE s.group_id = :group_id AND s.is_correct = 1
            ORDER BY s.id DESC
        ");
        $stmt->execute([':group_id' => $group_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $rows]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

?>