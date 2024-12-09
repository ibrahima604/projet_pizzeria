<?php
// Configuration de la base de données avec PDO
$host = 'localhost';  // Hôte de la base de données
$dbname = 'pizzeria';  // Nom de la base de données
$username = 'root';  // Nom d'utilisateur
$password = '';  // Mot de passe (vide si local)

try {
    // Création de la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Définir l'option pour lancer des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Définir l'encodage des caractères
    $pdo->exec("set names utf8");
} catch (PDOException $e) {
    // Si une erreur se produit lors de la connexion
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
