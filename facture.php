<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Rediriger si non connecté
    exit();
}

$user_id = $_SESSION['user']['id']; // ID de l'utilisateur connecté
$user_role = $_SESSION['user']['role']; // Récupérer le rôle de l'utilisateur (client, serveur, admin)
$user_poste = $_SESSION['user']['poste']; // Récupérer le poste de l'utilisateur (serveur ou autre)

// Vérifier si une commande spécifique est demandée via l'URL
if (!isset($_GET['commande_id'])) {
    echo "Aucune commande sélectionnée.";
    exit();
}

$commande_id = $_GET['commande_id'];

// Vérifier les permissions selon le rôle de l'utilisateur
if ($user_role === 'admin' || $user_role === 'client') {
    // Le client peut voir ses propres commandes
    $query_commande = "
        SELECT c.id, c.date_commande, c.statut, p.mode
        FROM commandes c
        LEFT JOIN paiements p ON c.id = p.commande_id
        WHERE c.client_id = :user_id AND c.id = :commande_id
    ";
} elseif ($user_poste === 'serveur') {
    // Le serveur peut voir les commandes qu'il a traitées
    $query_commande = "
        SELECT c.id, c.date_commande, c.statut, p.mode
        FROM commandes c
        LEFT JOIN paiements p ON c.id = p.commande_id
        WHERE c.serveur_id = :user_id AND c.id = :commande_id
    ";
} else {
    echo "Accès non autorisé.";
    exit();
}

$stmt_commande = $pdo->prepare($query_commande);
$stmt_commande->execute(['user_id' => $user_id, 'commande_id' => $commande_id]);
$commande = $stmt_commande->fetch(PDO::FETCH_ASSOC);

if (!$commande) {
    echo 'Commande introuvable ou accès non autorisé.';
    exit();
}

// Récupérer les produits de la commande
function getProduitsCommande($commande_id, $pdo) {
    $query_produits = "
        SELECT p.nom, ld.quantite, p.prix
        FROM ligne_de_commande ld
        JOIN produit p ON ld.produit_id = p.id
        WHERE ld.commande_id = :commande_id
    ";
    $stmt_produits = $pdo->prepare($query_produits);
    $stmt_produits->execute(['commande_id' => $commande_id]);
    return $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
}

$produits = getProduitsCommande($commande['id'], $pdo);
$montant_total = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - Commande</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 80%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
        }
        table th, table td {
            padding: 0.75rem;
            text-align: left;
        }
        .total {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Facture de la Commande</h1>
        <p>Date: <?php echo htmlspecialchars($commande['date_commande']); ?></p>
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): ?>
                <?php $total = $produit['quantite'] * $produit['prix']; ?>
                <tr>
                    <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                    <td><?php echo htmlspecialchars($produit['quantite']); ?></td>
                    <td><?php echo number_format($produit['prix'], 2); ?> €</td>
                    <td><?php echo number_format($total, 2); ?> €</td>
                </tr>
                <?php $montant_total += $total; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Montant total: <?php echo number_format($montant_total, 2); ?> €
    </div>

    <div class="footer">
        <p>Mode de paiement: <?php echo htmlspecialchars($commande['mode']); ?></p>
        <p>Statut: <?php echo htmlspecialchars($commande['statut']); ?></p>
    </div>
</div>

</body>
</html>
