<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php'); // Rediriger vers la page de connexion si non connecté
    exit();
}

$user_id = $_SESSION['user']['id']; // ID de l'utilisateur connecté
$role = $_SESSION['user']['role']; // Rôle de l'utilisateur connecté (client ou serveur)

// Récupérer les commandes en fonction du rôle
if ($role === 'client') {
    // Commandes du client
    $query_commandes = "
        SELECT c.id, c.date_commande, c.statut
        FROM commandes c
        WHERE c.client_id = :user_id
        ORDER BY c.date_commande DESC
    ";
} elseif ($_SESSION['user']['poste'] === 'serveur') {
    // Commandes du serveur
    $query_commandes = "
        SELECT c.id, c.date_commande, c.statut
        FROM commandes c
        WHERE c.serveur_id = :user_id
        ORDER BY c.date_commande DESC
    ";
} else {
    echo "Erreur : Vous n'avez pas l'autorisation de consulter les commandes.";
    exit();
}

$stmt_commandes = $pdo->prepare($query_commandes);
$stmt_commandes->execute(['user_id' => $user_id]);
$commandes = $stmt_commandes->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Commandes</title>
    <link rel="stylesheet" href="commandes.css">
</head>
<body>

<div class="container">
    <h2>
        <?php echo $role === 'client' ? 'Mes Commandes' : 'Commandes Gérées'; ?>
    </h2>

    <table class="commande-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Statut</th>
                <th>Produits</th>
                <th>Montant Total</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($commandes as $commande) {
                // Récupérer les produits de la commande
                $query_produits = "
                    SELECT p.nom, ld.quantite, p.prix
                    FROM ligne_de_commande ld
                    JOIN produit p ON ld.produit_id = p.id
                    WHERE ld.commande_id = :commande_id
                ";
                $stmt_produits = $pdo->prepare($query_produits);
                $stmt_produits->execute(['commande_id' => $commande['id']]);
                $produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

                // Calculer le montant total de la commande
                $montant_total = 0;
                foreach ($produits as $produit) {
                    $montant_total += $produit['prix'] * $produit['quantite'];
                }
                
                // Affichage des informations de la commande
                echo '<tr>';
                echo '<td>' . htmlspecialchars($commande['date_commande']) . '</td>';
                echo '<td>' . htmlspecialchars($commande['statut']) . '</td>';

                // Afficher les produits
                $produits_affichage = '';
                foreach ($produits as $produit) {
                    $produits_affichage .= htmlspecialchars($produit['nom']) . ' (Quantité: ' . $produit['quantite'] . ')<br>';
                }
                echo '<td>' . $produits_affichage . '</td>';

                // Afficher le montant total
                echo '<td>' . number_format($montant_total, 2, ',', ' ') . ' MAD</td>';
                echo '<td><a href="generer_facture.php?commande_id=' . $commande['id'] . '" target="_blank">Générer Facture</a></td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>

<?php
$pdo = null;
?>
