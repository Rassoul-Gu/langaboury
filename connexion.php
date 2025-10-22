<?php
// ====================================================================
//  Fichier : connexion.php
//  Projet  : Langaboury
//  Style   : Repris de player.html
// ====================================================================

session_start();
require_once 'config.php'; // Connexion à la base MySQL

// Si le formulaire est envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code = trim($_POST['code_acces']);

    if (empty($email) || empty($code)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND code_acces = :code");
            $stmt->execute(['email' => $email, 'code' => $code]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];

                header('Location: player.html');
                exit;
            } else {
                $error = "Email ou code d'accès incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur interne : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion - Langaboury</title>
  <link rel="stylesheet" href="style.css"> <!-- même CSS que player.html -->
  <style>
    /* Adaptation visuelle pour le style de player.html */
    body {
        background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
        font-family: 'Poppins', sans-serif;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        margin: 0;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 40px;
        width: 400px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
        backdrop-filter: blur(10px);
        text-align: center;
    }

    h2 {
        margin-bottom: 20px;
        color: #00d4ff;
    }

    input[type="email"], input[type="text"] {
        width: 80%;
        padding: 12px;
        border: none;
        border-radius: 10px;
        margin: 10px 0;
        font-size: 16px;
        outline: none;
        text-align: center;
    }

    button {
        background-color: #00d4ff;
        border: none;
        color: #fff;
        padding: 12px 25px;
        border-radius: 10px;
        font-size: 16px;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background-color: #008ab8;
        transform: scale(1.05);
    }

    .error {
        color: #ff7b7b;
        margin-bottom: 15px;
        font-weight: bold;
    }

    a {
        color: #00d4ff;
        text-decoration: none;
    }

    a:hover {
        text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Connexion à Langaboury</h2>

    <?php if (isset($error)) : ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="connexion.php" method="POST">
      <input type="email" name="email" placeholder="Adresse e-mail" required><br>
      <input type="text" name="code_acces" placeholder="Code d'accès" maxlength="20" required><br>
      <button type="submit">Se connecter</button>
    </form>

    <p style="margin-top:20px;">
      <a href="admin.html">Espace administrateur</a>
    </p>
  </div>
</body>
</html>
