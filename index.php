<?php
include 'config.php';  // Connexion à la base de données

try {
    // Récupérer tous les produits depuis la table produit
    $query = "SELECT nom, description, prix, image FROM produit";
    $stmt = $pdo->prepare($query);
    $stmt->execute();  // Exécution de la requête
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Récupérer les résultats sous forme de tableau associatif
} catch (PDOException $e) {
    // Gérer les erreurs de connexion ou de requête
    die("Erreur lors de la récupération des produits : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Pizzeria</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Sections spécifiques à personnaliser avec Bootstrap */
        .about img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        footer {
            background: #1a252f;
            color: #fff;
            padding: 40px 20px;
        }

        .social-icons a {
            margin: 0 10px;
            font-size: 25px;
            color: #3498db;
            transition: transform 0.3s, color 0.3s;
        }

        .social-icons a:hover {
            transform: scale(1.2);
            color: #fff;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Pizzeria</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="offres.php">Offres</a></li>
                    <li class="nav-item"><a class="nav-link" href="inscription.php">Inscription</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Section Description -->
    <section class="about py-5 bg-light">
        <div class="container text-center">
            <div class="row">
                <div class="col-lg-6">
                    <img src="image/pizzeria.jpg" alt="Pizzeria Image" class="img-fluid rounded shadow-sm">
                </div>
                <div class="col-lg-6">
                    <h2 class="display-4">Bienvenue à la Pizzeria</h2>
                    <p class="lead text-muted">Découvrez les meilleures pizzas de Marrakech, préparées avec des ingrédients frais et une passion pour la cuisine italienne.</p>
                    <a href="#menu" class="btn btn-danger btn-lg">Découvrir nos pizzas</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Conteneur des produits -->
    <div class="container py-5" id="menu">
        <div class="row g-4">
            <?php if (!empty($produits)): ?>
                <?php foreach ($produits as $produit): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card product-card shadow-sm h-100">
                            <img src="<?php echo htmlspecialchars($produit['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($produit['nom']); ?>" style="height: 200px; object-fit: cover;">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($produit['nom']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($produit['description']); ?></p>
                                <p class="price fw-bold"><?php echo number_format($produit['prix'], 2, ',', ' ') . ' MAD'; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun produit disponible pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-contact text-center mb-3">
            <p>Email : contact@pizzeria.com</p>
            <p>Téléphone : +212 600 000 000</p>
            <p>Adresse : 123 Rue de la Pizza, Marrakech, Maroc</p>
        </div>
        <div class="social-icons text-center">
            <a href="https://facebook.com" target="_blank" class="fab fa-facebook"></a>
            <a href="https://twitter.com" target="_blank" class="fab fa-twitter"></a>
            <a href="https://instagram.com" target="_blank" class="fab fa-instagram"></a>
            <a href="https://github.com" target="_blank" class="fab fa-github"></a>
            <a href="https://wa.me/212632684091" target="_blank" class="fab fa-whatsapp"></a>
        </div>
    </footer>

    <!-- Lien vers Bootstrap JS et Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
