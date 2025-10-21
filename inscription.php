<?php
require_once 'config.php';
session_start();

// Fonction pour générer un code alphanumérique
function genererCode($longueur = 6) {
    $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($caracteres), 0, $longueur);
}

// Traitement du formulaire
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $groupe = trim($_POST['groupe']);

    if ($nom && $prenom && $email) {
        $code = genererCode();

        try {
            $db = getDB();

            // Vérifie si l'email existe déjà
            $check = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
            $check->execute([$email]);
            if ($check->rowCount() > 0) {
                $message = "<p class='error'>❌ Cet e-mail est déjà inscrit.</p>";
            } else {
                // Insertion
                $sql = "INSERT INTO utilisateurs (nom, prenom, email, code_acces, groupe)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$nom, $prenom, $email, $code, $groupe]);

                // Envoi du mail
                $to = $email;
                $subject = "Votre code d'accès - Chasse au Trésor Langaboury";
                $body = "Bonjour $prenom $nom,\n\nVoici votre code d'accès : $code\n\nConnectez-vous sur : " . BASE_URL . "/connexion.php\n\nBonne chance dans le jeu !\n\n— L'équipe ASER Rouen";
                $headers = "From: admin@aser-rouen.fr";

                if (mail($to, $subject, $body, $headers)) {
                    $message = "<p class='success'>✅ Inscription réussie ! Votre code a été envoyé à votre e-mail.</p>";
                } else {
                    $message = "<p class='warning'>⚠️ Inscription réussie, mais l'envoi du mail a échoué.</p>";
                }
            }
        } catch (PDOException $e) {
            $message = "<p class='error'>Erreur : " . $e->getMessage() . "</p>";
        }
    } else {
        $message = "<p class='error'>❗ Veuillez remplir tous les champs obligatoires.</p>";
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

    <form method="POST" action="">
        <label>Nom :</label>
        <input type="text" name="nom" required>

        <label>Prénom :</label>
        <input type="text" name="prenom" required>

        <label>Email :</label>
        <input type="email" name="email" required>

        <button type="submit">S'inscrire</button>

        <p style="margin-top:15px; text-align:center;">
            Déjà inscrit ? <a href="connexion.php">Se connecter</a>
        </p>
    </form>

</body>
</html>
