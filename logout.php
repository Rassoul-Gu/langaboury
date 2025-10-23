<?php
session_start();

// Logger la déconnexion
if (isset($_SESSION['player_id'])) {
    error_log("Déconnexion joueur ID: " . $_SESSION['player_id']);
}

// Détruire la session
session_destroy();

// Réponse JSON pour les appels AJAX
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Déconnexion réussie']);
    exit;
}

// Redirection normale
header('Location: connexion.php?logout=1');
exit;
?>
