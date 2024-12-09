<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Rediriger si non connecté
    exit();
}

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];
$user_poste = $_SESSION['user']['poste'];

if (!isset($_GET['commande_id'])) {
    echo "Aucune commande sélectionnée.";
    exit();
}

$commande_id = $_GET['commande_id'];

// Requête pour les informations utilisateur
if ($user_role === 'client' || $user_role === 'admin') {
    $query_user_info = "SELECT * FROM Users WHERE id = :user_id";
} elseif ($user_poste === 'serveur') {
    $query_user_info = "SELECT * FROM employes WHERE id = :user_id";
} else {
    echo "Accès non autorisé.";
    exit();
}

$stmt_user_info = $pdo->prepare($query_user_info);
$stmt_user_info->execute(['user_id' => $user_id]);
$user_info = $stmt_user_info->fetch(PDO::FETCH_ASSOC);

// Requête pour les détails de la commande
if ($user_role === 'client') {
    $query_commande = "
        SELECT c.id, c.date_commande, c.statut, p.mode
        FROM commandes c
        LEFT JOIN Users u ON c.client_id = u.id
        LEFT JOIN paiements p ON c.id = p.commande_id
        WHERE c.client_id = :user_id AND c.id = :commande_id
    ";
} elseif ($user_poste === 'serveur' || $user_role === 'admin') {
    $query_commande = "
        SELECT c.id, c.date_commande, c.statut, p.mode
        FROM commandes c
        LEFT JOIN employes e ON c.serveur_id = e.id
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
    echo 'Commande introuvable.';
    exit();
}

// Fonction pour récupérer les produits d'une commande
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - Pizzeria Delicioso</title>
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
            max-width: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e67e22;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .total {
            font-weight: bold;
            color: #e74c3c;
            text-align: right;
            font-size: 1.25rem;
        }
        .btn-print {
            display: block;
            width: 20%;
            background-color: #3498db;
            color: white;
            font-size: 1.2rem;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin: auto;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Pizzeria Delicioso</h1>
        <p>Facture pour la commande #<?php echo htmlspecialchars($commande['id']); ?></p>
    </div>

    <!-- Informations utilisateur -->
    <?php if ($user_role === 'client'): ?>
        <div>
            <strong>Informations sur le client</strong>
            <p><strong>Nom:</strong> <?php echo htmlspecialchars($user_info['nom']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_info['email']); ?></p>
        </div>
    <?php elseif ($user_poste === 'serveur'): ?>
        <div>
            <strong>Informations sur le serveur</strong>
            <p><strong>Nom:</strong> <?php echo htmlspecialchars($user_info['nom']); ?></p>
            <p><strong>Poste:</strong> <?php echo htmlspecialchars($user_info['poste']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Détails de la commande -->
    <div>
        <strong>Détails de la commande</strong>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($commande['date_commande']); ?></p>
        <p><strong>Statut:</strong> <?php echo htmlspecialchars($commande['statut']); ?></p>
        <p><strong>Mode de paiement:</strong> <?php echo htmlspecialchars($commande['mode']); ?></p>
    </div>

    <!-- Produits de la commande -->
    <?php
    $produits = getProduitsCommande($commande['id'], $pdo);
    $montant_total = 0;
    ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire (MAD)</th>
                <th>Total (MAD)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $produit): 
                $total_produit = $produit['quantite'] * $produit['prix'];
                $montant_total += $total_produit;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($produit['nom']); ?></td>
                    <td><?php echo htmlspecialchars($produit['quantite']); ?></td>
                    <td><?php echo number_format($produit['prix'], 2, ',', ' '); ?></td>
                    <td><?php echo number_format($total_produit, 2, ',', ' '); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Total -->
    <div class="total">
        <p>Total : <?php echo number_format($montant_total, 2, ',', ' ') . ' MAD'; ?></p>
    </div>

    <div class="footer">
        <p>Merci pour votre commande !</p>
    </div>

   
</div>
<button class="btn-print" onclick="window.print();">Imprimer la facture</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$pdo = null;
?>
