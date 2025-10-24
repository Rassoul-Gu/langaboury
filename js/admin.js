
const GAME_ID = 1;

// Charger données au démarrage
window.addEventListener('load', () => {
    refreshAll();
    startAutoRefresh();
});

function startAutoRefresh() {
    setInterval(refreshAll, 5000);
}

async function refreshAll() {
    await loadStats();
    await loadActivity();
}

async function loadStats() {
    try {
        const res = await fetch(`api_simple.php?action=admin_get_stats&game_id=${GAME_ID}`);
        const data = await res.json();

        // Mettre à jour stats
        document.getElementById('statGroups').textContent = data.stats.total_groups;
        document.getElementById('statEnigmes').textContent = data.stats.total_enigmes;
        document.getElementById('statSubmissions').textContent = data.stats.total_submissions;
        document.getElementById('statSuccess').textContent = data.stats.success_rate + '%';

        // Mettre à jour tables
        updateGroupsTable(data.groups);
        updateEnigmesTable(data.enigmes);
        updateLeaderboard(data.groups);

        // IMPORTANT: Mettre à jour le select des groupes
        updateGroupSelect(data.groups);
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur de chargement des données', 'error');
    }
}

function updateGroupSelect(groups) {
    const select = document.getElementById('enigmeGroupId');
    if (!select) return;

    // Sauvegarder la valeur sélectionnée
    const currentValue = select.value;

    // Garder l'option par défaut
    select.innerHTML = '<option value="">-- Sélectionner un groupe --</option>';

    groups.forEach(group => {
        const option = document.createElement('option');
        option.value = group.id;
        option.textContent = group.name;
        select.appendChild(option);
    });

    // Restaurer la valeur sélectionnée
    if (currentValue) {
        select.value = currentValue;
    }
}

function updateGroupsTable(groups) {
    const tbody = document.querySelector('#groupsTable tbody');
    tbody.innerHTML = '';

    groups.forEach(group => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${group.id}</td>
            <td>${group.name}</td>
            <td><span class="group-color" style="background: ${group.color}"></span></td>
            <td><strong>${group.score}</strong></td>
            <td>${group.current_step}</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateEnigmesTable(enigmes) {
    const tbody = document.querySelector('#enigmesTable tbody');
    tbody.innerHTML = '';

    if (enigmes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#6b7280;">Aucune énigme créée</td></tr>';
        return;
    }

    enigmes.forEach(enigme => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <span class="group-color" style="background: ${enigme.group_color}; margin-right:8px;"></span>
                ${enigme.group_name}
            </td>
            <td><strong>${enigme.step_number}</strong></td>
            <td><code style="background:#f0f4ff;padding:5px 10px;border-radius:5px;">${enigme.qr_code}</code></td>
            <td>${enigme.enigme_text.substring(0, 50)}...</td>
            <td><strong>${enigme.answer}</strong></td>
            <td>${enigme.points} pts</td>
        `;
        tbody.appendChild(tr);
    });
}

function updateLeaderboard(groups) {
    const tbody = document.querySelector('#leaderboardTable tbody');
    tbody.innerHTML = '';

    groups.forEach((group, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong style="font-size:20px;">${index + 1}</strong></td>
            <td>
                <span class="group-color" style="background: ${group.color}; margin-right:10px;"></span>
                ${group.name}
            </td>
            <td><strong style="font-size:20px;color:#667eea;">${group.score}</strong></td>
            <td>Étape ${group.current_step}</td>
        `;
        tbody.appendChild(tr);
    });
}

async function loadActivity() {
    try {
        const res = await fetch(`api_simple.php?action=admin_get_activity&game_id=${GAME_ID}`);
        const data = await res.json();

        const container = document.getElementById('activityList');
        container.innerHTML = '';

        if (data.activity.length === 0) {
            container.innerHTML = '<p style="text-align:center;color:#6b7280;">Aucune activité pour le moment</p>';
            return;
        }

        data.activity.forEach(item => {
            const div = document.createElement('div');
            div.className = 'activity-item';
            div.style.borderLeftColor = item.group_color;

            const time = new Date(item.created_at).toLocaleTimeString('fr-FR');
            const badge = item.is_correct ?
                '<span class="badge badge-success">✓ Correct</span>' :
                '<span class="badge badge-error">✗ Incorrect</span>';

            div.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <strong>${item.group_name}</strong>
                    ${badge}
                </div>
                <div style="color:#6b7280;margin-bottom:5px;">
                    Étape ${item.step_number} - Réponse: <strong>${item.answer_submitted}</strong>
                </div>
                <div class="activity-time">${time}</div>
            `;
            container.appendChild(div);
        });
    } catch (error) {
        console.error('Erreur:', error);
    }
}

function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => {
        el.classList.remove('active');
    });

    document.querySelectorAll('.tab').forEach(el => {
        el.classList.remove('active');
    });

    event.target.classList.add('active');
    document.getElementById('tab-' + tabName).classList.add('active');
}

function showCreateGroupForm() {
    document.getElementById('createGroupForm').style.display = 'block';
}

function hideCreateGroupForm() {
    document.getElementById('createGroupForm').style.display = 'none';
    document.getElementById('groupName').value = '';
    document.getElementById('groupColor').value = '#3B82F6';
}

async function createGroup() {
    const name = document.getElementById('groupName').value.trim();
    const color = document.getElementById('groupColor').value;

    if (!name) {
        showAlert('Veuillez entrer un nom', 'error');
        return;
    }

    try {
        const res = await fetch('api_simple.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'admin_create_group',
                game_id: GAME_ID,
                name: name,
                color: color
            })
        });

        const data = await res.json();

        if (data.success) {
            showAlert('✓ Groupe créé avec succès !', 'success');
            hideCreateGroupForm();
            await refreshAll();
        } else {
            showAlert('Erreur: ' + (data.error || 'Erreur inconnue'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    }
}

function showCreateEnigmeForm() {
    document.getElementById('createEnigmeForm').style.display = 'block';
}

function hideCreateEnigmeForm() {
    document.getElementById('createEnigmeForm').style.display = 'none';
    document.getElementById('enigmeGroupId').value = '';
    document.getElementById('stepNumber').value = '';
    document.getElementById('enigmeText').value = '';
    document.getElementById('answer').value = '';
    document.getElementById('nextLocation').value = '';
    document.getElementById('points').value = '10';
}

async function createEnigme() {
    const groupId = document.getElementById('enigmeGroupId').value;
    const stepNumber = document.getElementById('stepNumber').value;
    const enigmeText = document.getElementById('enigmeText').value.trim();
    const answer = document.getElementById('answer').value.trim().toUpperCase();
    const nextLocation = document.getElementById('nextLocation').value.trim();
    const points = document.getElementById('points').value;

    if (!groupId || !stepNumber || !enigmeText || !answer) {
        showAlert('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    try {
        const res = await fetch('api_simple.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'admin_create_enigme',
                game_id: GAME_ID,
                group_id: parseInt(groupId),
                step_number: parseInt(stepNumber),
                enigme_text: enigmeText,
                answer: answer,
                next_location: nextLocation,
                points: parseInt(points)
            })
        });

        const data = await res.json();

        if (data.success) {
            showAlert('✓ Énigme créée ! Code QR: ' + data.qr_code, 'success');
            hideCreateEnigmeForm();
            await refreshAll();
        } else {
            showAlert('Erreur: ' + (data.error || 'Erreur inconnue'), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showAlert('Erreur de connexion au serveur', 'error');
    }
}

function showAlert(message, type) {
    const alertBox = document.getElementById('alertBox');
    alertBox.className = 'alert alert-' + type + ' show';
    alertBox.textContent = message;

    setTimeout(() => {
        alertBox.classList.remove('show');
    }, 5000);
}

// Auto-uppercase pour le champ réponse
document.addEventListener('DOMContentLoaded', () => {
    const answerInput = document.getElementById('answer');

    if (answerInput) {
        answerInput.addEventListener('input', (e) => {
            e.target.value = e.target.value.toUpperCase();
        });
    }
});

document.addEventListener('DOMContentLoaded', checkAdminSession);

function checkAdminSession() {
    fetch('api_simple.php?action=check_admin_session')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('admin-login').style.display = 'none';
                document.getElementById('admin-content').style.display = 'block';
            } else {
                document.getElementById('admin-login').style.display = 'block';
                document.getElementById('admin-content').style.display = 'none';
            }
        });
}

function loginAdmin() {
    const email = document.getElementById('admin-email').value.trim();
    const password = document.getElementById('admin-password').value.trim();
    const msg = document.getElementById('login-msg');

    fetch('api_simple.php?action=login_admin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                msg.style.color = 'green';
                msg.textContent = data.message;
                setTimeout(() => checkAdminSession(), 500);
            } else {
                msg.style.color = 'red';
                msg.textContent = data.message;
            }
        })
        .catch(() => {
            msg.style.color = 'red';
            msg.textContent = 'Erreur de connexion au serveur.';
        });
}

function logoutAdmin() {
    fetch('api_simple.php?action=logout_admin')
        .then(() => {
            document.getElementById('admin-login').style.display = 'block';
            document.getElementById('admin-content').style.display = 'none';
        });
}
