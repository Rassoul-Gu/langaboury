<?php
	session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>🎮 Jouer - Chasse au Trésor</title>
    <meta name="theme-color" content="#667eea">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            -webkit-tap-highlight-color: transparent; 
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 10px;
        }
        
        .container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .team-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        
        .team-details h2 {
            font-size: 26px;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
        }
        
        .team-step {
            color: #6b7280;
            font-size: 14px;
            font-weight: 500;
        }
        
        .score-display {
            text-align: right;
        }
        
        .score-value {
            font-size: 36px;
            font-weight: bold;
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
        }
        
        .score-label {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .progress-bar {
            height: 12px;
            background: #e0e7eb;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            width: 0%;
            transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 10px rgba(16,185,129,0.5);
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h3 {
            font-size: 22px;
            color: #667eea;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        
        .scanner-box {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 35px 20px;
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
        }
        
        .scanner-icon {
            font-size: 90px;
            margin-bottom: 15px;
            animation: scanPulse 2s ease infinite;
        }
        
        @keyframes scanPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .scanner-text {
            color: #6b7280;
            font-size: 15px;
            font-weight: 500;
        }
        
        #reader {
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }
        
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .manual-input {
            display: none;
            margin-top: 20px;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .manual-input.show {
            display: block;
        }
        
        .input-group {
            display: flex;
            gap: 10px;
        }
        
        input {
            flex: 1;
            padding: 16px;
            border: 2px solid #d1d5db;
            border-radius: 12px;
            font-size: 17px;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        
        .enigme-box {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid #f59e0b;
            margin-bottom: 20px;
        }
        
        .enigme-text {
            font-size: 18px;
            line-height: 1.8;
            color: #78350f;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .answer-input {
            width: 100%;
            padding: 18px;
            border: 3px solid #f59e0b;
            border-radius: 12px;
            font-size: 20px;
            text-transform: uppercase;
            text-align: center;
            font-weight: bold;
            background: white;
        }
        
        .result {
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            margin-top: 20px;
            display: none;
            animation: bounceIn 0.5s ease;
        }
        
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.3); }
            50% { transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .result.show {
            display: block;
        }
        
        .result.success {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            border: 3px solid #10b981;
        }
        
        .result.error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 3px solid #ef4444;
        }
        
        .result-icon {
            font-size: 60px;
            margin-bottom: 15px;
        }
        
        .next-location {
            background: rgba(255,255,255,0.8);
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
            border-left: 5px solid #667eea;
            text-align: left;
        }
        
        .leaderboard-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px;
            background: #f9fafb;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.3s;
        }
        
        .leaderboard-item:hover {
            transform: translateX(5px);
            background: #f0f4ff;
        }
        
        .leaderboard-item.current {
            background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
            border: 2px solid #667eea;
        }
        
        .leaderboard-rank {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }
        
        .leaderboard-name {
            flex: 1;
            margin-left: 15px;
            font-weight: 600;
            font-size: 16px;
        }
        
        .leaderboard-score {
            font-size: 26px;
            font-weight: bold;
            color: #667eea;
        }
        
        .hidden {
            display: none !important;
        }
        
        .loader {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        select {
            width: 100%;
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #667eea;
            font-size: 16px;
            margin: 15px 0;
            background: white;
        }
        
        @media (max-width: 480px) {
            .header, .card {
                padding: 20px;
            }
            
            .team-details h2 {
                font-size: 22px;
            }
            
            .score-value {
                font-size: 30px;
            }
            
            h3 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Chargement en cours -->
        <div class="header" id="loadingGroup">
            <h3>🎮 Chargement...</h3>
            <div class="loader"></div>
            <p style="text-align: center; color: #6b7280; margin-top: 15px;">
                Récupération de vos informations...
            </p>
        </div>
        
        <!-- Interface de jeu -->
        <div id="gameInterface" class="hidden">
            <div class="header">
                <div class="team-info">
                    <div class="team-details">
                        <h2 id="teamName">Mon Groupe</h2>
                        <div class="team-step">
                            Étape <span id="currentStep">1</span>/<span id="totalSteps">0</span>
                        </div>
                    </div>
                    <div class="score-display">
                        <div class="score-value"><span id="score">0</span></div>
                        <div class="score-label">Points</div>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progress"></div>
                </div>
            </div>

            <!-- Scanner QR -->
            <div class="card" id="scannerCard">
                <h3><span>📷</span> Scanner le QR Code</h3>
                <div class="scanner-box">
                    <div class="scanner-icon">📱</div>
                    <p class="scanner-text">Scannez le QR code avec votre caméra</p>
                </div>
                <div id="reader"></div>
                <button class="btn btn-primary" id="startScanBtn" onclick="toggleScanner()">
                    <span>📷</span>
                    <span>Activer la Caméra</span>
                </button>
                <button class="btn btn-secondary" onclick="toggleManual()">
                    <span>⌨️</span>
                    <span>Entrer le code manuellement</span>
                </button>
                
                <div class="manual-input" id="manualInput">
                    <div class="input-group">
                        <input type="text" id="qrCodeInput" placeholder="Ex: X7K9M2P4" maxlength="12">
                        <button class="btn btn-primary" onclick="submitQRCode()" style="width: auto; padding: 15px 30px;">
                            ✓ OK
                        </button>
                    </div>
                </div>
            </div>

            <!-- Énigme -->
            <div class="card hidden" id="enigmeCard">
                <h3><span>🧩</span> Énigme</h3>
                <div class="enigme-box">
                    <div class="enigme-text" id="enigmeText"></div>
                    <input type="text" class="answer-input" id="answerInput" placeholder="VOTRE RÉPONSE" maxlength="50">
                </div>
                <button class="btn btn-primary" onclick="submitAnswer()">
                    <span>✓</span>
                    <span>Valider la Réponse</span>
                </button>
                <div class="result" id="result"></div>
            </div>

            <!-- Classement -->
            <div class="card">
                <h3><span>🏆</span> Classement</h3>
                <div id="leaderboard">
                    <div class="loader"></div>
                </div>
            </div>

            <!-- 🧩 Historique des bonnes réponses -->
            <div id="historique-container" style="margin-top:40px;">
                <h2 style="color:#2563EB;">Historique des bonnes réponses du groupe</h2>
                <div id="historique-content">
                    <p style="color:#555;">Chargement...</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let groupId = null;
        let currentEnigme = null;
        let scanner = null;
        let isScanning = false;

        // Charger les groupes au démarrage
        window.addEventListener('load', startGame);


        async function startGame() {
            try {
                // Récupérer le groupId depuis la session PHP
                const sessionGroupId = <?php echo isset($_SESSION['group_id']) ? $_SESSION['group_id'] : 'null'; ?>;
                
                if (!sessionGroupId) {
                    throw new Error('Aucun groupe trouvé dans la session');
                }
                
                groupId = sessionGroupId;
                
                // Masquer le loading et afficher l'interface de jeu
                document.getElementById('loadingGroup').classList.add('hidden');
                document.getElementById('gameInterface').classList.remove('hidden');
                
                // Charger les données initiales
                await loadGameState();
                await loadTeamName();
                startLeaderboardRefresh();
                
            } catch (error) {
                console.error('Erreur démarrage:', error);
                alert('Erreur: ' + error.message + '\nRedirection vers la connexion...');
                window.location.href = '/connexion.php';
            }
        }

        function fetchHistorique() {
            
            fetch(`api_simple.php?action=get_good_answers&group_id=${groupId}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('historique-content');
                    if (data.status === 'success') {
                        if (data.data.length === 0) {
                            container.innerHTML = '<p style="color:#555;">Aucune bonne réponse pour le moment.</p>';
                            return;
                        }

                        let html = `
                            <table style="width:100%; border-collapse:collapse; margin-top:10px;">
                                <thead>
                                    <tr style="background-color:#2563EB; color:white;">
                                        <th style="padding:10px; text-align:left;">Question</th>
                                        <th style="padding:10px; text-align:left;">Bonne réponse</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        for (const row of data.data) {
                            html += `
                                <tr style="border-bottom:1px solid #ddd;">
                                    <td style="padding:10px;">${row.question_name}</td>
                                    <td style="padding:10px;">${row.correct_answer}</td>
                                </tr>
                            `;
                        }

                        html += '</tbody></table>';
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<p style="color:red;">Erreur de chargement.</p>';
                    }
                })
                .catch(() => {
                    document.getElementById('historique-content').innerHTML = '<p style="color:red;">Erreur de connexion au serveur.</p>';
                });
            }

        async function loadGameState() {
            try {
                const res = await fetch(`api_simple.php?action=get_state&group_id=${groupId}`);
                const data = await res.json();
                
                document.getElementById('score').textContent = data.score;
                document.getElementById('currentStep').textContent = data.current_step;
                document.getElementById('totalSteps').textContent = data.total_steps;
                
                const progress = (data.current_step / data.total_steps) * 100;
                document.getElementById('progress').style.width = Math.min(progress, 100) + '%';
                
                loadLeaderboard();
            } catch (error) {
                console.error('Erreur:', error);
            }
        }



        async function loadTeamName() {
            try {
                // Récupérer le nom du groupe depuis la session PHP
                const teamName = "<?php echo isset($_SESSION['group_name']) ? htmlspecialchars($_SESSION['group_name']) : 'Mon Groupe'; ?>";
                document.getElementById('teamName').textContent = teamName;
                
            } catch (error) {
                console.error('Erreur chargement nom:', error);
                document.getElementById('teamName').textContent = 'Mon Groupe';
            }
        }

        async function loadLeaderboard() {
            try {
                const res = await fetch('api_simple.php?action=get_leaderboard&game_id=1');
                const data = await res.json();
                
                const container = document.getElementById('leaderboard');
                container.innerHTML = '';
                
                data.leaderboard.forEach((group, index) => {
                    const item = document.createElement('div');
                    item.className = 'leaderboard-item' + (group.id == groupId ? ' current' : '');
                    item.innerHTML = `
                        <div class="leaderboard-rank">${index + 1}</div>
                        <div class="leaderboard-name">${group.name}</div>
                        <div class="leaderboard-score">${group.score}</div>
                    `;
                    container.appendChild(item);
                });
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        function startLeaderboardRefresh() {
            setInterval(() => {
                loadLeaderboard();
                loadGameState();
            }, 5000);
        }

        function toggleScanner() {
            if (isScanning) {
                stopScanner();
            } else {
                startScanner();
            }
        }

        async function startScanner() {
            try {
                scanner = new Html5Qrcode("reader");
                const cameras = await Html5Qrcode.getCameras();
                
                if (cameras && cameras.length > 0) {
                    await scanner.start(
                        cameras[cameras.length - 1].id,
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (code) => {
                            stopScanner();
                            submitQRCode(code);
                        }
                    );
                    isScanning = true;
                    const btn = document.getElementById('startScanBtn');
                    btn.innerHTML = '<span>🛑</span><span>Arrêter la Caméra</span>';
                }
            } catch (error) {
                alert('Erreur caméra: ' + error.message);
            }
        }

        function stopScanner() {
            if (scanner && isScanning) {
                scanner.stop();
                isScanning = false;
                const btn = document.getElementById('startScanBtn');
                btn.innerHTML = '<span>📷</span><span>Activer la Caméra</span>';
            }
        }

        function toggleManual() {
            const manual = document.getElementById('manualInput');
            manual.classList.toggle('show');
            if (manual.classList.contains('show')) {
                document.getElementById('qrCodeInput').focus();
            }
        }

        async function submitQRCode(code) {
            if (!code) {
                code = document.getElementById('qrCodeInput').value.trim().toUpperCase();
            }
            
            if (!code) {
                alert('Entrez un code QR');
                return;
            }
        
            try {
                const res = await fetch('api_simple.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'scan_qr',
                        group_id: groupId,
                        qr_code: code
                    })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    currentEnigme = data.enigme;
                    showEnigme(data.enigme);
                } else {
                    alert(data.error || 'QR Code invalide');
                }
            } catch (error) {
                alert('Erreur réseau');
            }
        }

        function showEnigme(enigme) {
            document.getElementById('scannerCard').classList.add('hidden');
            document.getElementById('enigmeCard').classList.remove('hidden');
            document.getElementById('enigmeText').textContent = enigme.enigme_text;
            document.getElementById('answerInput').value = '';
            document.getElementById('answerInput').focus();
            document.getElementById('result').classList.remove('show');
        }

        async function submitAnswer() {
            const answer = document.getElementById('answerInput').value.trim().toUpperCase();
            
            if (!answer) {
                alert('Entrez une réponse');
                return;
            }
        
            try {
                const res = await fetch('api_simple.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'submit_answer',
                        group_id: groupId,
                        enigme_id: currentEnigme.id,
                        answer: answer
                    })
                });
                
                const data = await res.json();
                
                const resultDiv = document.getElementById('result');
                resultDiv.classList.add('show');
                
                if (data.correct) {
                    resultDiv.className = 'result show success';
                    resultDiv.innerHTML = `
                        <div class="result-icon">✅</div>
                        <div style="font-size: 24px; margin-bottom: 15px; font-weight: bold;">Bravo !</div>
                        <div style="font-size: 18px;">+${data.points} points</div>
                        ${data.next_location ? `
                            <div class="next-location">
                                <strong>📍 Prochaine étape:</strong><br>
                                ${data.next_location}
                            </div>
                        ` : '<div class="next-location"><strong>🎉 Félicitations ! Vous avez terminé !</strong></div>'}
                    `;
                    
                    setTimeout(() => {
                        document.getElementById('enigmeCard').classList.add('hidden');
                        document.getElementById('scannerCard').classList.remove('hidden');
                        loadGameState();
                    }, 5000);
                } else {
                    resultDiv.className = 'result show error';
                    resultDiv.innerHTML = `
                        <div class="result-icon">❌</div>
                        <div style="font-size: 22px; margin-bottom: 10px;">Mauvaise réponse</div>
                        <div style="margin-top: 15px; font-size: 16px;">Réessayez !</div>
                    `;
                    
                    setTimeout(() => {
                        resultDiv.classList.remove('show');
                        document.getElementById('answerInput').value = '';
                        document.getElementById('answerInput').focus();
                    }, 3000);
                }
            } catch (error) {
                alert('Erreur réseau');
            }
        }

        // Enter pour valider
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('answerInput')?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') submitAnswer();
            });
            document.getElementById('qrCodeInput')?.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') submitQRCode();
            });
        });

        

            // 🔁 Rafraîchir toutes les 2 secondes
            setInterval(fetchHistorique, 2000);

            // // 🏁 Charger une première fois au démarrage
            // document.addEventListener('DOMContentLoaded', fetchHistorique);

    </script>
</body>
</html>
