<?php

function generateAccessCode($length = 8) {
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $code;
}

function registerPlayer($pdo) {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '' || $surname === '' || $email === '') {
        echo json_encode(['status' => 'error', 'message' => 'Veuillez remplir tous les champs.']);
        return;
    }

    try {
        // Vérifie si l'email existe déjà
        $check = $pdo->prepare("SELECT id FROM players_table WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Cet e-mail est déjà inscrit.']);
            return;
        }

        // Génère un code d’accès unique
        $code = generateAccessCode(8);

        // Sélectionne un groupe aléatoire existant
        $stmtGroup = $pdo->query("SELECT id FROM groups_table ORDER BY RAND() LIMIT 1");
        $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);
        $group_id = $group ? $group['id'] : null;

        // Insère le joueur
        $insert = $pdo->prepare("
            INSERT INTO players_table (name, surname, email, access_code, group_id)
            VALUES (:name, :surname, :email, :access_code, :group_id)
        ");
        $insert->execute([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'access_code' => $code,
            'group_id' => $group_id
        ]);

        // Envoie le mail avec le code
        $subject = "Votre code d'accès Langaboury";
        $message = "
            <html><body style='font-family:Arial,sans-serif;'>
            <h2>Bienvenue sur Langaboury 🎉</h2>
            <p>Bonjour <strong>{$surname} {$name}</strong>,</p>
            <p>Votre code d'accès personnel est :</p>
            <h3 style='color:#667eea; font-size:24px;'>{$code}</h3>
            <p>Ce code vous permet de vous connecter sur : 
            <a href='http://aser-rouen.fr/connexion.php'>Langaboury - Connexion</a></p>
            <hr>
            <p style='color:#666;'>Ne partagez pas ce code. Bon jeu !</p>
            </body></html>
        ";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: Langaboury <admin@aser-rouen.fr>\r\n";

        @mail($email, $subject, $message, $headers);

        echo json_encode([
            'status' => 'success',
            'message' => "Inscription réussie ! Le code a été envoyé à {$email}.",
            'access_code' => $code
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erreur : ' . $e->getMessage()]);
    }
}


function login_player($pdo) {
    $email = trim($_POST['email'] ?? '');
    $code_access = strtoupper(trim($_POST['code_access'] ?? ''));
    
    if (!$email || !$code_access) {
        http_response_code(400);
        exit(json_encode(['error' => 'Email et code d\'accès requis']));
    }
    
    try {
        // Recherche du joueur
        $stmt = $pdo->prepare('
            SELECT p.*, g.name as group_name, g.color as group_color, g.score as group_score, g.current_step as group_step
            FROM players_table p 
            LEFT JOIN groups_table g ON p.group_id = g.id 
            WHERE p.email = ? AND p.access_code = ?
        ');
        $stmt->execute([$email, $code_access]);
        $player = $stmt->fetch();
        
        if (!$player) {
            http_response_code(401);
            exit(json_encode([
                'success' => false,
                'error' => 'Email ou code d\'accès incorrect'
            ]));
        }
        
        // Configuration de la session avec timeout de 2 heures
        session_set_cookie_params([
            'lifetime' => 30,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        session_regenerate_id(true);
        
        // Stocker les infos de session
        $_SESSION['player_id'] = $player['id'];
        $_SESSION['player_email'] = $player['email'];
        $_SESSION['player_name'] = $player['name'];
        $_SESSION['player_surname'] = $player['surname'];
        $_SESSION['group_id'] = $player['group_id'];
        $_SESSION['group_name'] = $player['group_name'];
        $_SESSION['login_time'] = time();
        $_SESSION['timeout'] = 7200;
	//header('Location : player.php');
	//exit;
        echo json_encode([
            'success' => true,
            'player' => [
                'id' => $player['id'],
                'name' => $player['name'],
                'surname' => $player['surname'],
                'email' => $player['email'],
                'group_id' => $player['group_id'],
                'group_name' => $player['group_name'],
                'group_color' => $player['group_color'],
                'group_score' => intval($player['group_score']),
                'group_step' => intval($player['group_step'])
            ],
            'session_id' => session_id(),
            'timeout' => 7200,
            'redirect_url' => '/player.html', // AJOUT DE LA REDIRECTION
            'message' => 'Connexion réussie'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
    }
}

function verify_session($pdo) {
    // Configuration session
    session_set_cookie_params([
        'lifetime' => 7200,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    session_start();
    
    // Vérifier si la session existe et n'est pas expirée
    if (!isset($_SESSION['player_id']) || !isset($_SESSION['login_time'])) {
        session_destroy();
        http_response_code(401);
        exit(json_encode(['error' => 'Session expirée ou invalide']));
    }
    
    // Vérifier le timeout (2 heures)
    $current_time = time();
    $login_time = $_SESSION['login_time'];
    $timeout_duration = $_SESSION['timeout'] ?? 7200;
    
    if (($current_time - $login_time) > $timeout_duration) {
        // Session expirée
        session_destroy();
        http_response_code(401);
        exit(json_encode(['error' => 'Session expirée']));
    }
    
    // Rafraîchir le timestamp de la session (optionnel - pour prolonger la session à chaque activité)
    $_SESSION['login_time'] = $current_time;
    
    // Vérifier que le joueur existe toujours en base
    $stmt = $pdo->prepare('
        SELECT p.*, g.name as group_name, g.color as group_color, g.score as group_score, g.current_step as group_step
        FROM players_table p 
        LEFT JOIN groups_table g ON p.group_id = g.id 
        WHERE p.id = ?
    ');
    $stmt->execute([$_SESSION['player_id']]);
    $player = $stmt->fetch();
    
    if (!$player) {
        session_destroy();
        http_response_code(401);
        exit(json_encode(['error' => 'Joueur introuvable']));
    }
    header('Location : /player.php');
    exit;
    echo json_encode([
        'authenticated' => true,
        'player' => [
            'id' => $player['id'],
            'name' => $player['name'],
            'surname' => $player['surname'],
            'email' => $player['email'],
            'group_id' => $player['group_id'],
            'group_name' => $player['group_name'],
            'group_color' => $player['group_color'],
            'group_score' => intval($player['group_score']),
            'group_step' => intval($player['group_step'])
        ],
        'session_remaining' => $timeout_duration - ($current_time - $login_time)
    ]);
}

function logout_player($pdo) {
    session_start();
    
    // Détruire complètement la session
    $_SESSION = [];
    
    // Supprimer le cookie de session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Déconnexion réussie'
    ]);
}

?>
