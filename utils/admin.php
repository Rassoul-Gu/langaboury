<?php
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


?>