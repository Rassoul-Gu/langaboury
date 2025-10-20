<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🏆 Chasse au Trésor - Jeu d'Aventure - Langaboury</title>
    <meta name="description" content="Jeu de chasse au trésor avec énigmes, QR codes sécurisés et classement en temps réel">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animation de fond */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: scroll 20s linear infinite;
            z-index: 0;
        }
        
        @keyframes scroll {
            0% { transform: translate(0, 0); }
            100% { transform: translate(-50px, -50px); }
        }
        
        .container {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 30px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            min-height: 650px;
            animation: fadeInUp 0.8s ease;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Partie gauche */
        .hero-left {
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .logo {
            font-size: 70px;
            margin-bottom: 20px;
            animation: bounce 2s ease infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            25% { transform: translateY(-10px) rotate(-5deg); }
            75% { transform: translateY(-10px) rotate(5deg); }
        }
        
        h1 {
            font-size: 44px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .subtitle {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 35px;
            line-height: 1.7;
        }
        
        .features {
            margin-bottom: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 12px;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .feature-item:hover {
            background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
            transform: translateX(8px);
            box-shadow: 0 4px 12px rgba(102,126,234,0.2);
        }
        
        .feature-icon {
            font-size: 32px;
            margin-right: 18px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(102,126,234,0.3);
        }
        
        .feature-text {
            font-size: 16px;
            color: #374151;
            font-weight: 600;
        }
        
        .cta-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 20px 40px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 220px;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.4);
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 3px solid #667eea;
        }
        
        .btn-secondary:hover {
            background: #f0f4ff;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.2);
        }
        
        /* Partie droite */
        .hero-right {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 40px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-right::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 2px, transparent 2px);
            background-size: 40px 40px;
            animation: drift 30s linear infinite;
        }
        
        @keyframes drift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(40px, 40px); }
        }
        
        .hero-image {
            font-size: 200px;
            animation: float 3s ease-in-out infinite;
            filter: drop-shadow(0 15px 35px rgba(0,0,0,0.3));
            z-index: 1;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(-5deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }
        
        .security-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(16,185,129,0.4);
            z-index: 2;
            animation: pulse 2s ease infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .stats-grid {
            position: absolute;
            bottom: 30px;
            left: 30px;
            right: 30px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            z-index: 1;
        }
        
        .stat-card {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(15px);
            padding: 20px 15px;
            border-radius: 15px;
            text-align: center;
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .stat-label {
            font-size: 11px;
            opacity: 0.95;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
            }
            
            .hero-left {
                padding: 40px 30px;
            }
            
            .hero-right {
                min-height: 450px;
            }
            
            h1 {
                font-size: 32px;
            }
            
            .logo {
                font-size: 50px;
            }
            
            .hero-image {
                font-size: 140px;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .btn {
                min-width: 100%;
            }
            
            .stats-grid {
                gap: 10px;
            }
            
            .stat-number {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-left">
            <div class="logo">🏆</div>
            <h1>Chasse au Trésor<br>Langaboury</h1>
            <p class="subtitle">
                Vivez une aventure unique en explorant votre environnement ! 
                Résolvez des énigmes, scannez des QR codes sécurisés, et affrontez 
                d'autres équipes dans une compétition palpitante.
            </p>

            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">🔐</div>
                    <div class="feature-text">QR Codes aléatoires impossibles à deviner</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🧩</div>
                    <div class="feature-text">Énigmes personnalisées par groupe</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">👥</div>
                    <div class="feature-text">Mode compétitif multi-groupes</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🏅</div>
                    <div class="feature-text">Classement et scores en temps réel</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🛡️</div>
                    <div class="feature-text">Système anti-triche intégré</div>
                </div>
            </div>

            <div class="cta-buttons">
                <a href="player.html" class="btn btn-primary">
                    <span>🎮</span>
                    <span>Jouer Maintenant</span>
                </a>
                <a href="admin.html" class="btn btn-secondary">
                    <span>⚙️</span>
                    <span>Espace Admin</span>
                </a>
            </div>
        </div>

        <div class="hero-right">
            <div class="security-badge">
                <span>🔒</span>
                <span>100% Sécurisé</span>
            </div>
            
            <div class="hero-image">🗺️</div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Joueurs Actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Parties Créées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">200+</div>
                    <div class="stat-label">Énigmes Résolues</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animation parallaxe sur l'image
        document.addEventListener('mousemove', (e) => {
            if (window.innerWidth > 968) {
                const heroImage = document.querySelector('.hero-image');
                const x = (e.clientX / window.innerWidth - 0.5) * 30;
                const y = (e.clientY / window.innerHeight - 0.5) * 30;
                heroImage.style.transform = `translate(${x}px, ${y}px) rotate(${x/5}deg)`;
            }
        });

        // Effet au survol des boutons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Animation des statistiques au chargement
        window.addEventListener('load', () => {
            const stats = document.querySelectorAll('.stat-number');
            stats.forEach((stat, index) => {
                setTimeout(() => {
                    stat.style.animation = 'pulse 1s ease';
                }, index * 200);
            });
        });
    </script>
</body>
</html>