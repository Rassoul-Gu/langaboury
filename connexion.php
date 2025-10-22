<?php
// ====================================================================
// Connexion - Langaboury (style calqué sur player.html)
// ====================================================================
session_start();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>Connexion - Langaboury</title>
    <meta name="theme-color" content="#667eea">
    <style>
        /* ======= Base : mêmes fond/typo que player.html ======= */
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 10px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        .header {
            background: rgba(255,255,255,0.98);
            border-radius: 20px;
            padding: 20px;
            margin: 10px 0 20px 0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.7);
        }
        .header h3 {
            font-size: 20px;
            color: #111827;
            margin-bottom: 8px;
        }

        /* ======= Carte (style “glass” cohérent) ======= */
        .card {
            background: rgba(255,255,255,0.98);
            border-radius: 20px;
            padding: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.7);
        }

        /* ======= Formulaire ======= */
        .form-title {
            font-size: 22px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 16px;
            text-align: center;
        }
        .form-group { margin-bottom: 14px; }
        label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 600;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 14px 16px;
            border-radius: 14px;
            border: 2px solid #e5e7eb;
            outline: none;
            background: #f9fafb;
            font-size: 16px;
            transition: all .2s ease;
        }
        input[type="email"]:focus, input[type="text"]:focus {
            border-color: #667eea;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(102,126,234,0.15);
        }

        /* ======= Boutons : mêmes classes .btn / .btn-primary que player.html ======= */
        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .btn:active { transform: scale(0.98); }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(102,126,234,0.3);
        }
        .btn-primary:hover { box-shadow: 0 14px 26px rgba(102,126,234,0.35); }

        /* ======= Alertes (cohérentes avec palette de player) ======= */
        .alert {
            border-radius: 14px;
            padding: 14px 16px;
            font-weight: 600;
            margin-bottom: 12px;
            text-align: center;
        }
        .alert.error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 3px solid #ef4444;
        }

        /* ======= Layout ======= */
        .content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .card .hint {
            color: #6b7280;
            font-size: 14px;
            text-align: center;
            margin-top: 8px;
        }

        @media (min-width: 700px) {
            .container { max-width: 560px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête visuel aligné à player.html -->
        <div class="header">
            <h3>🔐 Connexion</h3>
            <p style="color:#6b7280;">Accédez à votre espace joueur avec votre email et votre code d’accès.</p>
        </div>

        <div class="content">
            <div class="card">
                <h2 class="form-title">Se connecter à Langaboury</h2>

                <?php if ($error): ?>
                    <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form autocomplete="on">
                    <div class="form-group">
                        <label for="email">Adresse e-mail</label>
                        <input type="email" id="email" name="email" placeholder="exemple@aser-rouen.fr" required />
                    </div>

                    <div class="form-group">
                        <label for="code_acces">Code d'accès</label>
                        <input type="text" id="code_acces" name="code_acces" maxlength="20" placeholder="Votre code alphanumérique" required />
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <span>✅</span><span>Se connecter</span>
                    </button>

                    <p class="hint">Besoin d’aide ? Contactez l’admin.</p>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestion du formulaire de connexion
        document.querySelector('form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('api_simple.php?action=login_player', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.success) {
                // Afficher message de succès
                showMessage('✅ Connexion réussie ! Redirection...', 'success');
                
                // Redirection vers player.html après 1.5 secondes
                setTimeout(() => {
                    window.location.href = '/player.html';
                }, 500);
            } else {
                showMessage('❌ ' + (data.error || 'Erreur de connexion'), 'error');
            }
            alert(data.message);
        });
    </script>
</body>
</html>

</html>
