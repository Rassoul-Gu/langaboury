<?php
require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>📝 Inscription - Chasse au Trésor</title>
    <meta name="theme-color" content="#667eea">
    <link rel="stylesheet" href="style/connexion.css">
</head>
<body>

    <h1>📝 Connexion</h1>
    <div class="message"><?= $message ?? '' ?></div>

    <form id="registerForm">


        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Code d'accès :</label>
        <input type="text" name="code_access" required>

        <button type="submit">Se connecter</button>

        <p style="margin-top:15px; text-align:center;">
            Pas encore inscrit ? <a href="connexion.php">S'inscrire</a>
        </p>
    </form>

    <script src="js/connexion.js"></script>
</body>
</html>
