<?php
$host = 'localhost';
$dbname = 'difo5341_langa';
$username = 'difo5341_langa'; // ou 'root' si tu n’as pas créé d’utilisateur
$password = 'langabouri@2025';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
