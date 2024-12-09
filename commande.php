<?php
session_start();
include 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'client') {
    header('Location: connexion.php');
    exit();
}

// Vérifier si le panier est vide
if (empty($_SESSION['panier'])) {
    echo 'Votre panier est vide. Veuillez ajouter des produits.';
    exit();
}

// Calculer le total du panier
$totalPanier = 0;
foreach ($_SESSION['panier'] as $item) {
    $stmt = $pdo->prepare("SELECT prix FROM produit WHERE id = ?");
    $stmt->execute([$item['id']]);
    $produit = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalPanier += $produit['prix'] * $item['quantite'];
}

// Insérer la commande dans la table `commandes`
$client_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("INSERT INTO commandes (client_id, statut, date_commande, type_commande) VALUES (?, 'en cours', NOW(), 'en ligne')");
$stmt->execute([$client_id]);

// Récupérer l'ID de la commande
$commande_id = $pdo->lastInsertId();

// Insérer les lignes de commande
foreach ($_SESSION['panier'] as $item) {
    $stmt = $pdo->prepare("INSERT INTO ligne_de_commande (commande_id, produit_id, quantite) VALUES (?, ?, ?)");
    $stmt->execute([$commande_id, $item['id'], $item['quantite']]);
}

// Insérer le paiement
$stmt = $pdo->prepare("INSERT INTO paiements (commande_id, mode, statut, date_paiement) VALUES (?, ?, 'en attente', NOW())");
$stmt->execute([$commande_id, 'carte bancaire']);  // Exemple : paiement par carte bancaire

// Vider le panier après la commande
unset($_SESSION['panier']);

echo 'Commande passée avec succès!';
?>
