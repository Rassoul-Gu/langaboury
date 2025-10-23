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

    <script>
        // Gestion du formulaire de connexion
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('api_simple.php?action=login_player', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // ✅ Stocker les données dans la session PHP via l'API
                const sessionResult = await storeSessionData(data.player);
                
                if (sessionResult.success) {
                    showMessage('✅ Connexion réussie ! Redirection...', 'success');
                    
                    // Animation de succès
                    submitBtn.innerHTML = `
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span>✅</span>
                            <span>Connecté !</span>
                        </div>
                    `;
                    
                    // Redirection après 1.5 secondes
                    setTimeout(() => {
                        window.location.href = data.redirect_url || '/player.php';
                    }, 1500);
                } else {
                    throw new Error('Erreur lors de la création de la session');
                }
                
            } else {
                throw new Error(data.error || 'Échec de la connexion');
            }
        });

        // Fonction pour stocker les données dans la session PHP via l'API
        async function storeSessionData(playerData) {
            try {
                const response = await fetch('api_simple.php?action=set_session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        player_data: playerData
                    })
                });
                
                const result = await response.json();
                return result;
                
            } catch (error) {
                console.error('Erreur stockage session:', error);
                return { success: false, error: error.message };
            }
        }
    </script>
</body>
</html>

</html>
