<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php'); // Rediriger vers la page de connexion si non admin
    exit();
}

// Vérifier si les dates de début et de fin sont définies
$date_debut = isset($_GET['date_debut']) ? $_GET['date_debut'] : null;
$date_fin = isset($_GET['date_fin']) ? $_GET['date_fin'] : null;

$query_commandes = "
    SELECT c.id, cl.nom, cl.email, c.date_commande, c.statut,
           SUM(p.prix * ld.quantite) AS montant_total
    FROM commandes c
    JOIN Users cl ON c.client_id = cl.id
    JOIN ligne_de_commande ld ON c.id = ld.commande_id
    JOIN produit p ON ld.produit_id = p.id
    WHERE 1=1
";

// Appliquer le filtrage par date si les dates sont définies
if ($date_debut && $date_fin) {
    $query_commandes .= " AND c.date_commande BETWEEN :date_debut AND :date_fin";
}

$query_commandes .= " GROUP BY c.id ORDER BY c.date_commande DESC";

$stmt_commandes = $pdo->prepare($query_commandes);

if ($date_debut && $date_fin) {
    $stmt_commandes->execute(['date_debut' => $date_debut, 'date_fin' => $date_fin]);
} else {
    $stmt_commandes->execute();
}

// Calculer le total de toutes les commandes
$totalCommandes = 0;
while ($commande = $stmt_commandes->fetch(PDO::FETCH_ASSOC)) {
    $totalCommandes += $commande['montant_total'];
}
$stmt_commandes->execute(); // Re-exécuter la requête pour l'affichage

// Récupérer tous les produits d'une commande spécifique
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
    <title>Tableau de bord Admin</title>
    <!-- Ajouter Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40;
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }
        .navbar-nav .nav-link:hover {
            color: #17a2b8 !important;
        }
        .thead-dark th {
            background-color: #343a40;
            color: white;
        }
        .table-bordered {
            border: 1px solid #ddd;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #117a8b;
        }
        .form-control {
            border-radius: .2rem;
        }
        .total {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .total p {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav-links" aria-controls="nav-links" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav-links">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="gerer_personnel.php">Gérer Personnel</a></li>
                <li class="nav-item"><a class="nav-link" href="gerer_produits.php">Gérer Produits</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2>Tableau de bord Administrateur</h2>

    <!-- Formulaire pour filtrer les commandes par période -->
    <form method="GET" action="admin_dashboard.php" class="mb-4">
        <div class="form-row">
            <div class="col">
                <label for="date_debut">Date de début :</label>
                <input type="date" id="date_debut" name="date_debut" class="form-control" required>
            </div>
            <div class="col">
                <label for="date_fin">Date de fin :</label>
                <input type="date" id="date_fin" name="date_fin" class="form-control" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary mt-4">Filtrer</button>
            </div>
        </div>
    </form>

    <h3>Liste des commandes passées par les clients</h3>

    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>ID Commande</th>
                <th>Client</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Montant</th>
                <th>Produits</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($commande = $stmt_commandes->fetch(PDO::FETCH_ASSOC)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($commande['id']) . '</td>';
                echo '<td>' . htmlspecialchars($commande['nom']) . '</td>';
                echo '<td>' . htmlspecialchars($commande['date_commande']) . '</td>';
                echo '<td>' . htmlspecialchars($commande['statut']) . '</td>';
                echo '<td>' . number_format($commande['montant_total'], 2, ',', ' ') . ' MAD</td>';

                // Récupérer les produits de la commande
                $produits = getProduitsCommande($commande['id'], $pdo);
                $produitsList = "";
                foreach ($produits as $produit) {
                    $produitsList .= htmlspecialchars($produit['nom']) . ' (Quantité: ' . $produit['quantite'] . ', Prix unitaire: ' . number_format($produit['prix'], 2, ',', ' ') . ' MAD)<br>';
                }

                echo '<td class="produits">' . $produitsList . '</td>';
                echo '<td><a class="btn btn-info btn-sm" href="generer_facture.php?commande_id=' . $commande['id'] . '" target="_blank">Facture</a></td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>

    <div class="total mt-4">
        <p>Total de toutes les commandes : <?php echo number_format($totalCommandes, 2, ',', ' ') . ' MAD'; ?></p>
    </div>
</div>

<!-- Ajouter Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$pdo = null;
?>
