<?php
require_once 'config.php'; // Ton fichier de connexion à la base (avec $dbHost, $dbName, $dbUser, $dbPass)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name) || empty($surname) || empty($email)) {
        die("Veuillez remplir tous les champs.");
    }

    try {
        // Connexion à la BDD
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Vérifier si l'utilisateur existe déjà
        $check = $pdo->prepare("SELECT id FROM players_table WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            die("Un compte existe déjà avec cet email.");
        }

        // Générer un code d’accès alphanumérique unique
        $access_code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);

        // ✅ Attribution équilibrée stricte et aléatoire
        $pdo->beginTransaction();

        // On compte les joueurs par groupe, y compris ceux sans joueur
        $stmt = $pdo->query("
            SELECT g.id AS group_id,
                COALESCE(COUNT(p.id), 0) AS player_count
            FROM groups_table g
            LEFT JOIN players_table p ON p.group_id = g.id
            GROUP BY g.id
            ORDER BY g.id
            FOR UPDATE
        ");

        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$groups) {
            $pdo->rollBack();
            die("Aucun groupe trouvé.");
        }

        // Trouver le nombre minimal de joueurs parmi tous les groupes
        $min_count = PHP_INT_MAX;
        foreach ($groups as $g) {
            if ($g['player_count'] < $min_count) {
                $min_count = $g['player_count'];
            }
        }

        // Récupérer la liste des groupes qui ont ce minimum
        $least_full_groups = [];
        foreach ($groups as $g) {
            if ($g['player_count'] == $min_count) {
                $least_full_groups[] = $g['group_id'];
            }
        }

        // Si plusieurs groupes ont le minimum, on choisit au hasard parmi eux
        if (count($least_full_groups) === 0) {
            $pdo->rollBack();
            die("Aucun groupe disponible pour attribution.");
        }
        $group_id = $least_full_groups[array_rand($least_full_groups)];

        // Insérer le joueur dans ce groupe
        $insert = $pdo->prepare("
            INSERT INTO players_table (name, surname, email, access_code, group_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([$name, $surname, $email, $access_code, $group_id]);

        // Valider la transaction
        $pdo->commit();


        // ✅ Envoi du mail via php.ini
        $subject = "Votre code d’accès au jeu Langaboury";
        $message = "Bonjour $name $surname,\n\n".
                   "Votre code d’accès : $access_code\n".
                   "Groupe attribué : #$group_id\n\n".
                   "Gardez ce code pour vous connecter au jeu.\n\n".
                   "Cordialement,\nL’équipe Langaboury";

        $headers = "From: admin@aser-rouen.fr\r\n";
        $headers .= "Reply-To: admin@aser-rouen.fr\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($email, $subject, $message, $headers);

        // Redirection vers la page du joueur
        header("Location: player.php?code=$access_code");
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>📝 Inscription - Chasse au Trésor</title>
    <meta name="theme-color" content="#667eea">
    <style>
        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        h1 {
            font-size: 2em;
            margin-bottom: 20px;
            text-align: center;
        }
        form {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            outline: none;
        }
        button {
            margin-top: 20px;
            background: #5a67d8;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            width: 100%;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #434190;
        }
        .message { margin-top: 15px; text-align: center; }
        .error { color: #ff8080; }
        .success { color: #9ae6b4; }
        .warning { color: #f6e05e; }
        a {
            color: #c3dafe;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1>📝 Inscription</h1>
    <div class="message"><?= $message ?></div>

    <form id="registerForm">
        <label>Nom :</label>
        <input type="text" name="name" required>

        <label>Prénom :</label>
        <input type="text" name="surname" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <button type="submit">S'inscrire</button>

        <p style="margin-top:15px; text-align:center;">
            Déjà inscrit ? <a href="connexion.php">Se connecter</a>
        </p>
    </form>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const res = await fetch('api_simple.php?action=register_player', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        alert(data.message);
    });
    </script>

</body>
</html>
