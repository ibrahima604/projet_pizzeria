<?php
session_start(); // Démarrer la session

// Détruire toutes les données de session
session_unset();
session_destroy();

// Rediriger vers la page de connexion
header('Location: index.php');
exit();
