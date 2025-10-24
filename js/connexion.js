
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
            //showMessage('✅ Connexion réussie ! Redirection...', 'success');
            
            // Animation de succès
            /*submitBtn.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <span>✅</span>
                    <span>Connecté !</span>
                </div>
            `;*/
            
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
