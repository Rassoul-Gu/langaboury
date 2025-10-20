<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'difo5341_langa');
define('DB_USER', 'difo5341_langa'); 
define('DB_PASS', 'langabouri@2025'); 
define('DB_CHARSET', 'utf8mb4');

// === CONFIGURATION SYSTÈME ===
define('BASE_URL', 'https://aser-rouen.fr/aser/langaboury');
define('QR_STORAGE_PATH', __DIR__ . '/qrcodes/');

// === CRÉER DOSSIERS SI INEXISTANTS ===
if (!file_exists(__DIR__ . '/qrcodes/')) {
    @mkdir(__DIR__ . '/qrcodes/', 0755, true);
}
if (!file_exists(__DIR__ . '/logs/')) {
    @mkdir(__DIR__ . '/logs/', 0755, true);
}

// === CONNEXION BASE DE DONNÉES ===
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Erreur BDD: " . $e->getMessage());
            die('Erreur de connexion à la base de données');
        }
    }
    
    return $pdo;
}

// === GÉNÉRATION CODE QR ALÉATOIRE ===
function generateSecureQRCode($pdo) {
    $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $max_attempts = 100;
    
    for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM enigmes WHERE qr_code = ?');
        $stmt->execute([$code]);
        
        if ($stmt->fetch()['count'] == 0) {
            return $code;
        }
    }
    
    return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}