<?php
session_start();
include 'config.php';

// Vérifier si l'utilisateur est connecté et est soit un client soit un serveur
if (!isset($_SESSION['user']) || 
    ($_SESSION['user']['role'] != 'client' && $_SESSION['user']['poste'] != 'serveur')) {
    header('Location: login.php');
    exit();
}

// Récupérer le rôle et l'ID de l'utilisateur
$role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];

// Vérifier que le panier existe et n'est pas vide
if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    echo "Erreur : Votre panier est vide.";
    exit();
}

$panier = $_SESSION['panier']; // Tableau des produits dans le panier

// Vérifier que le mode de paiement est défini
if (!isset($_POST['mode_paiement']) || empty($_POST['mode_paiement'])) {
    echo "Erreur : Veuillez choisir un mode de paiement.";
    exit();
}
$mode_paiement = htmlspecialchars($_POST['mode_paiement'], ENT_QUOTES, 'UTF-8'); // Sécuriser la saisie

// Déterminer les champs de la commande
$client_id = null;
$serveur_id = null;

if ($role === 'client') {
    $client_id = $user_id; // Le client est l'utilisateur connecté
} elseif ($_SESSION['user']['poste'] === 'serveur') {
    $serveur_id = $user_id; // Le serveur est l'utilisateur connecté
}

try {
    // Commencer une transaction
    $pdo->beginTransaction();

    // 1. Créer une nouvelle commande
    $query = "INSERT INTO commandes (serveur_id, client_id, type_commande, statut, date_commande) 
              VALUES (?, ?, ?, 'en cours', NOW())";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$serveur_id, $client_id, $serveur_id ? 'sur place' : 'en ligne']); // Déterminer le type de commande
    $commande_id = $pdo->lastInsertId(); // Récupérer l'ID de la commande insérée

    // 2. Enregistrer le paiement
    $query_paiement = "INSERT INTO paiements (commande_id, mode, statut, date_paiement) 
                       VALUES (?, ?, 'en attente', NOW())";
    $stmt_paiement = $pdo->prepare($query_paiement);
    $stmt_paiement->execute([$commande_id, $mode_paiement]);

    // 3. Ajouter les produits au panier dans la table ligne_de_commande
    foreach ($panier as $item) {
        $produit_id = $item['id'];
        $quantite = $item['quantite'];

        // Récupérer le prix du produit pour éviter les incohérences
        $stmt_produit = $pdo->prepare("SELECT prix FROM produit WHERE id = ?");
        $stmt_produit->execute([$produit_id]);
        $produit = $stmt_produit->fetch(PDO::FETCH_ASSOC);

        if (!$produit) {
            throw new Exception("Le produit avec l'ID $produit_id n'existe pas.");
        }

        $prix = $produit['prix'];

        // Insertion dans la table ligne_de_commande
        $query_ligne_commande = "INSERT INTO ligne_de_commande (commande_id, produit_id, quantite) 
                                 VALUES (?, ?, ?)";
        $stmt_ligne_commande = $pdo->prepare($query_ligne_commande);
        $stmt_ligne_commande->execute([$commande_id, $produit_id, $quantite]);
    }

    // Si tout se passe bien, valider la transaction
    $pdo->commit();

    // Vider le panier après commande
    unset($_SESSION['panier']);
    echo "Commande bien réussie avec le numéro de commande : " . $commande_id;

} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    $pdo->rollBack();

    // Afficher l'erreur
    echo "Une erreur est survenue : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
?>
