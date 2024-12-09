<?php
session_start();

// Vérifier si le panier existe, sinon créer un tableau vide
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Ajouter un produit au panier
if (isset($_GET['action']) && $_GET['action'] == 'ajouter' && isset($_GET['id'])) {
    $produit_id = $_GET['id'];
    $quantite = 1; // Quantité par défaut

    // Vérifier si le produit est déjà dans le panier
    $trouve = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['id'] == $produit_id) {
            $item['quantite'] += $quantite;
            $trouve = true;
            break;
        }
    }

    // Si le produit n'est pas trouvé, on l'ajoute
    if (!$trouve) {
        $_SESSION['panier'][] = ['id' => $produit_id, 'quantite' => $quantite];
    }
    header('Location: client_dashboard.php#menu'); // Rediriger vers la page menu
    exit();
}

// Vérification avant de passer à la commande
if (isset($_GET['action']) && $_GET['action'] == 'commander') {
    if (empty($_SESSION['panier'])) {
        // Si le panier est vide, rediriger avec un message d'erreur
        $_SESSION['message'] = "Votre panier est vide. Veuillez ajouter des produits avant de commander.";
        header('Location: client_dashboard.php#menu'); // Rediriger vers le menu
        exit();
    } else {
        // Rediriger vers la page de commande si le panier est rempli
        header('Location: passer_commande.php');
        exit();
    }
}

// Afficher le panier
if (isset($_GET['action']) && $_GET['action'] == 'afficher') {
    echo '<pre>';
    print_r($_SESSION['panier']);
    echo '</pre>';
}

// Lier cette page à l'interface utilisateur dans la page de menu.
?>
