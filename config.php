<?php
$host = 'localhost';
$dbname = 'bd';         // <- nom de ta base visible dans phpMyAdmin
$username = 'root';     // <- nom d'utilisateur par défaut dans XAMPP
$password = '';         // <- mot de passe vide par défaut

try {
    // Connexion PDO existante
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ajouter la connexion mysqli pour compatibilité
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Vérifier la connexion mysqli
    if ($conn->connect_error) {
        throw new Exception("Connexion mysqli échouée : " . $conn->connect_error);
    }
    
    // Définir l'encodage des caractères
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Échec de la connexion : " . $e->getMessage());
}
?>