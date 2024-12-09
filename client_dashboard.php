<?php
session_start();
include 'config.php';
// Vérifier si l'utilisateur est connecté et est soit un client soit un employé (serveur ou cuisinier)
if (!isset($_SESSION['user']) || 
    ($_SESSION['user']['role'] != 'client' && $_SESSION['user']['poste'] != 'serveur')) {
    header('Location: login.php');
    exit();
}

// Récupérer les produits depuis la base de données
$query = "SELECT * FROM produit";
$stmt = $pdo->prepare($query);
$stmt->execute();
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculer le total du panier
$totalPanier = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $stmt = $pdo->prepare("SELECT prix FROM produit WHERE id = ?");
        $stmt->execute([$item['id']]);
        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalPanier += $produit['prix'] * $item['quantite'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="client_dashboard.css">
</head>
<body>
    <!-- Navbar Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Pizzeria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="commandes.php">Mes Commandes</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Se déconnecter</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center">Bienvenue dans le monde des Pizzas, Monsieur <?php echo htmlspecialchars($_SESSION['user']['nom']); ?> !</h2>

        <!-- Menu -->
        <div class="my-4">
            <h3 class="border-bottom pb-2">Menu</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($produits as $produit): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <img src="<?php echo htmlspecialchars($produit['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produit['nom']); ?>" style="object-fit: cover; height: 200px;">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($produit['nom']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($produit['description']); ?></p>
                            <p class="price fw-bold"><?php echo number_format($produit['prix'], 2, ',', ' ') . ' MAD'; ?></p>
                            <a href="panier.php?action=ajouter&id=<?php echo $produit['id']; ?>" class="btn btn-primary">Ajouter au panier</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Panier -->
        <div class="cart mt-5">
            <h3 class="border-bottom pb-2">Votre Panier</h3>
            <div class="cart-items">
                <?php
                if (isset($_SESSION['panier']) && !empty($_SESSION['panier'])) {
                    foreach ($_SESSION['panier'] as $item) {
                        $stmt = $pdo->prepare("SELECT * FROM produit WHERE id = ?");
                        $stmt->execute([$item['id']]);
                        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
                        echo '<div class="d-flex justify-content-between align-items-center py-2 border-bottom">';
                        echo '<span>' . htmlspecialchars($produit['nom']) . ' (x' . $item['quantite'] . ')</span>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-muted">Votre panier est vide.</p>';
                }
                ?>
            </div>
            <div class="cart-total text-center mt-3">
                <p>Total : <span id="cart-total" class="fw-bold"><?php echo number_format($totalPanier, 2, ',', ' ') . ' MAD'; ?></span></p>
            </div>

            <!-- Formulaire de paiement -->
            <form method="POST" action="traitement_commande.php">
                <div class="payment-method mt-4">
                    <h4>Mode de paiement</h4>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="mode_paiement" value="carte bancaire" required checked>
                        <label class="form-check-label">Carte bancaire</label>
                    </div>
                    <div class="form-check">
                        <input type="radio" class="form-check-input" name="mode_paiement" value="espèces">
                        <label class="form-check-label">Espèces (à la livraison)</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-4 w-100">Passer la commande</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
