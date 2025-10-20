<?php
// generate_qr.php - Générateur simple de QR codes
error_reporting(E_ALL);
ini_set('display_errors', 0); // Ne pas afficher les erreurs en production

require_once 'config.php';

// Vérifier que phpqrcode existe
if (!file_exists(__DIR__ . '/phpqrcode/qrlib.php')) {
    die('ERREUR: La bibliothèque phpqrcode est introuvable. Téléchargez-la depuis https://sourceforge.net/projects/phpqrcode/');
}

require_once __DIR__ . '/phpqrcode/qrlib.php';

$pdo = getDB();

// Dossier de stockage
$qr_dir = __DIR__ . '/qrcodes/';
if (!file_exists($qr_dir)) {
    mkdir($qr_dir, 0755, true);
}

// Action demandée
$action = $_GET['action'] ?? 'view';

if ($action === 'generate') {
    generateAllQRCodes($pdo, $qr_dir);
    header('Location: generate_qr.php?success=1');
    exit;
}

if ($action === 'download') {
    $file = $_GET['file'] ?? '';
    if ($file && file_exists($qr_dir . $file)) {
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile($qr_dir . $file);
        exit;
    }
}

function generateAllQRCodes($pdo, $qr_dir) {
    // Récupérer toutes les énigmes
    $stmt = $pdo->query('
        SELECT 
            e.id,
            e.qr_code,
            e.step_number,
            g.name as group_name
        FROM enigmes e
        JOIN groups_table g ON e.group_id = g.id
        ORDER BY g.name, e.step_number
    ');
    $enigmes = $stmt->fetchAll();
    
    // Créer la table si elle n'existe pas
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS qr_codes_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            enigme_id INT NOT NULL,
            qr_code VARCHAR(50) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_enigme (enigme_id)
        )
    ');
    
    foreach ($enigmes as $enigme) {
        // Nom du fichier: nomgroupe_etape1.png
        $clean_name = preg_replace('/[^a-zA-Z0-9]/', '', $enigme['group_name']);
        $filename = $clean_name . '_etape' . $enigme['step_number'] . '.png';
        $filepath = $qr_dir . $filename;
        
        // Générer le QR code (contient uniquement le code)
        QRcode::png($enigme['qr_code'], $filepath, QR_ECLEVEL_L, 10);
        
        // Enregistrer en base
        $stmt = $pdo->prepare('
            INSERT INTO qr_codes_files (enigme_id, qr_code, filename)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE filename = ?, qr_code = ?
        ');
        $stmt->execute([
            $enigme['id'],
            $enigme['qr_code'],
            $filename,
            $filename,
            $enigme['qr_code']
        ]);
    }
}

// Récupérer les QR codes par groupe
$stmt = $pdo->query('
    SELECT 
        qf.id,
        qf.filename,
        qf.qr_code,
        e.step_number,
        g.name as group_name,
        g.color as group_color
    FROM qr_codes_files qf
    JOIN enigmes e ON qf.enigme_id = e.id
    JOIN groups_table g ON e.group_id = g.id
    ORDER BY g.name, e.step_number
');
$qr_codes = $stmt->fetchAll();

// Organiser par groupe
$by_group = [];
foreach ($qr_codes as $qr) {
    $by_group[$qr['group_name']][] = $qr;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 Générateur QR Codes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f4f6;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .alert {
            background: #d1fae5;
            color: #065f46;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #10b981;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }
        
        .group-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .group-header {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #e5e7eb;
        }
        
        .group-color {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            margin-right: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .group-name {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }
        
        .qr-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .qr-card {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }
        
        .qr-card:hover {
            border-color: #667eea;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .qr-card img {
            width: 200px;
            height: 200px;
            margin-bottom: 15px;
            border-radius: 8px;
        }
        
        .qr-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .qr-code-text {
            font-family: monospace;
            background: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            color: #667eea;
            margin-bottom: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .btn-download {
            display: inline-block;
            padding: 8px 16px;
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-download:hover {
            background: #059669;
            transform: scale(1.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .stats {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-around;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 14px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 Générateur de QR Codes</h1>
            <p>Générez et téléchargez tous vos QR codes</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert">
                ✅ QR Codes générés avec succès !
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 30px;">
            <a href="?action=generate" class="btn" onclick="return confirm('Générer tous les QR codes ?')">
                🔄 Générer / Régénérer tous les QR Codes
            </a>
            <a href="admin.html" class="btn" style="background: #6b7280; margin-left: 10px;">
                ← Retour Admin
            </a>
        </div>

        <?php if (empty($by_group)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h2>Aucun QR code généré</h2>
                <p>Cliquez sur "Générer tous les QR Codes" pour commencer</p>
            </div>
        <?php else: ?>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-value"><?= count($by_group) ?></div>
                    <div class="stat-label">Groupes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?= count($qr_codes) ?></div>
                    <div class="stat-label">QR Codes</div>
                </div>
            </div>

            <?php foreach ($by_group as $group_name => $codes): ?>
                <div class="group-section">
                    <div class="group-header">
                        <div class="group-color" style="background: <?= htmlspecialchars($codes[0]['group_color']) ?>"></div>
                        <div class="group-name"><?= htmlspecialchars($group_name) ?></div>
                    </div>
                    
                    <div class="qr-grid">
                        <?php foreach ($codes as $qr): ?>
                            <div class="qr-card">
                                <img src="qrcodes/<?= htmlspecialchars($qr['filename']) ?>" alt="QR Code">
                                <div class="qr-label">Étape <?= $qr['step_number'] ?></div>
                                <div class="qr-code-text"><?= htmlspecialchars($qr['qr_code']) ?></div>
                                <a href="?action=download&file=<?= urlencode($qr['filename']) ?>" class="btn-download">
                                    ⬇️ Télécharger
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
        <?php endif; ?>
    </div>
</body>
</html>