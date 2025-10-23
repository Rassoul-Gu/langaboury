<?php
require_once 'config.php';

// Configuration SMTP via ini_set
ini_set("SMTP", "smtp.aser-rouen.fr");
ini_set("smtp_port", "587");
ini_set("sendmail_from", "admin@aser-rouen.fr");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name) || empty($surname) || empty($email)) {
        die("Veuillez remplir tous les champs.");
    }

    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Vérifier si l'utilisateur existe déjà
        $check = $pdo->prepare("SELECT id FROM players_table WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            die("Un compte existe déjà avec cet email.");
        }

        // Générer un code d'accès
        $access_code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8);

        // ✅ CORRECTION : Attribution proportionnelle des groupes
        $pdo->beginTransaction();

        // Récupérer les groupes avec leur nombre de joueurs
        $stmt = $pdo->query("
            SELECT g.id, COUNT(p.id) AS player_count
            FROM groups_table g
            LEFT JOIN players_table p ON p.group_id = g.id
            GROUP BY g.id
            ORDER BY player_count ASC
        ");

        $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$groups) {
            $pdo->rollBack();
            die("Aucun groupe n'existe dans la base.");
        }

        // Trouver le nombre minimal de joueurs
        $min_count = $groups[0]['player_count'];
        
        // Filtrer les groupes ayant le minimum de joueurs
        $available_groups = [];
        foreach ($groups as $group) {
            if ($group['player_count'] == $min_count) {
                $available_groups[] = $group;
            }
        }

        // Choisir aléatoirement parmi les groupes disponibles
        $selected_group = $available_groups[array_rand($available_groups)];
        $group_id = $selected_group['id'];

        // Insérer le joueur
        $insert = $pdo->prepare("
            INSERT INTO players_table (name, surname, email, access_code, group_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([$name, $surname, $email, $access_code, $group_id]);

        $pdo->commit();

        // Envoi du mail
        $subject = "Votre code d'accès au jeu Langaboury";
        $message = "Bonjour $name $surname,\n\n".
                   "Voici votre code d'accès : $access_code\n".
                   "Vous avez été ajouté au groupe #$group_id.\n\n".
                   "Conservez bien ce code pour vous connecter.\n\n".
                   "Bonne chance !";
        $headers = "From: admin@aser-rouen.fr\r\n";
        $headers .= "Reply-To: admin@aser-rouen.fr\r\n";

        mail($email, $subject, $message, $headers);

        header("Location: player.php?code=$access_code");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
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
