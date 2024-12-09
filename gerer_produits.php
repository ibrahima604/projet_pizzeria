<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Ajouter un produit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_produit'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $url_image = $_POST['image'];
    $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;

    // Préparer la requête d'ajout
    $query_add_produit = "INSERT INTO produit (nom, description, prix, disponibilite, image) 
                          VALUES (:nom, :description, :prix, :disponibilite, :url_image)";
    $stmt_add_produit = $pdo->prepare($query_add_produit);
    $stmt_add_produit->bindParam(':nom', $nom);
    $stmt_add_produit->bindParam(':description', $description);
    $stmt_add_produit->bindParam(':prix', $prix);
    $stmt_add_produit->bindParam(':disponibilite', $disponibilite);
    $stmt_add_produit->bindParam(':url_image', $url_image);
    $stmt_add_produit->execute();

    // Message de confirmation
    $message = "Produit ajouté avec succès!";
}

// Modifier un produit
if (isset($_GET['modifier_produit'])) {
    $produit_id = $_GET['modifier_produit'];

    // Vérifier si l'ID est un entier valide
    if (filter_var($produit_id, FILTER_VALIDATE_INT)) {
        // Récupérer les informations du produit
        $query_produit_details = "SELECT * FROM produit WHERE id = :id";
        $stmt_produit_details = $pdo->prepare($query_produit_details);
        $stmt_produit_details->bindParam(':id', $produit_id);
        $stmt_produit_details->execute();
        $produit = $stmt_produit_details->fetch(PDO::FETCH_ASSOC);

        // Si le produit existe, afficher le formulaire de modification
        if (!$produit) {
            echo "Erreur: Le produit n'a pas été trouvé.";
            exit();
        }
    } else {
        echo "ID de produit invalide.";
        exit();
    }
}

// Traiter le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_produit'])) {
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $prix = $_POST['prix'];
    $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;
    $produit_id = $_POST['produit_id'];

    // Requête pour modifier le produit
    $query_update_produit = "UPDATE produit 
                             SET nom = :nom, description = :description, prix = :prix, disponibilite = :disponibilite 
                             WHERE id = :id";
    $stmt_update_produit = $pdo->prepare($query_update_produit);
    $stmt_update_produit->bindParam(':nom', $nom);
    $stmt_update_produit->bindParam(':description', $description);
    $stmt_update_produit->bindParam(':prix', $prix);
    $stmt_update_produit->bindParam(':disponibilite', $disponibilite);
    $stmt_update_produit->bindParam(':id', $produit_id);
    $stmt_update_produit->execute();

    // Message de confirmation
    $message = "Produit mis à jour avec succès!";
}

// Supprimer un produit
if (isset($_GET['supprimer_produit'])) {
    $produit_id = $_GET['supprimer_produit'];

    // Vérifier si l'ID est un entier valide
    if (filter_var($produit_id, FILTER_VALIDATE_INT)) {
        // Requête pour supprimer le produit
        $query_delete_produit = "DELETE FROM produit WHERE id = :id";
        $stmt_delete_produit = $pdo->prepare($query_delete_produit);
        $stmt_delete_produit->bindParam(':id', $produit_id);
        $stmt_delete_produit->execute();

        // Message de confirmation
        $message = "Produit supprimé avec succès!";
    }
}

// Récupérer la liste des produits
$query_produits = "SELECT * FROM produit";
$stmt_produits = $pdo->query($query_produits);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Produits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="text-center mb-4">Gérer les Produits</h2>

        <?php if (isset($message)) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Succès!</strong> <?php echo $message; ?>
                <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php } ?>

        <!-- Formulaire d'ajout de produit -->
        <form method="POST" class="mb-4">
            <h3><i class="fas fa-plus-circle"></i> Ajouter un Produit</h3>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" name="nom" id="nom" placeholder="Nom du produit" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" id="description" placeholder="Description du produit" required></textarea>
            </div>
            <div class="mb-3">
                <label for="prix" class="form-label">Prix (MAD)</label>
                <input type="number" class="form-control" name="prix" id="prix" placeholder="Prix du produit" required step="0.01">
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">URL de l'image</label>
                <input type="text" class="form-control" name="image" id="image" placeholder="URL de l'image">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="disponibilite" id="disponibilite" checked>
                <label class="form-check-label" for="disponibilite">Disponible</label>
            </div>
            <button type="submit" name="ajouter_produit" class="btn btn-primary"><i class="fas fa-check-circle"></i> Ajouter</button>
        </form>

        <!-- Formulaire de modification -->
        <?php if (isset($produit)) { ?>
            <form method="POST" class="mb-4">
                <h3><i class="fas fa-edit"></i> Modifier un Produit</h3>
                <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" class="form-control" name="nom" id="nom" value="<?php echo $produit['nom']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" id="description" required><?php echo $produit['description']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="prix" class="form-label">Prix (MAD)</label>
                    <input type="number" class="form-control" name="prix" id="prix" value="<?php echo $produit['prix']; ?>" required step="0.01">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="disponibilite" id="disponibilite" <?php echo ($produit['disponibilite'] == 1) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="disponibilite">Disponible</label>
                </div>
                <button type="submit" name="modifier_produit" class="btn btn-warning"><i class="fas fa-save"></i> Modifier</button>
            </form>
        <?php } ?>

        <h3 class="mb-3">Liste des Produits</h3>
        <div class="row">
            <?php while ($produit = $stmt_produits->fetch(PDO::FETCH_ASSOC)) { ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <img src="<?php echo $produit['image']; ?>" class="card-img-top" alt="Image de <?php echo $produit['nom']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $produit['nom']; ?></h5>
                            <p class="card-text"><?php echo $produit['description']; ?></p>
                            <p class="card-text"><strong><?php echo $produit['prix']; ?> MAD</strong></p>
                            <p class="card-text"><small class="text-muted"><?php echo ($produit['disponibilite'] == 1) ? 'Disponible' : 'Indisponible'; ?></small></p>
                            <a href="gerer_produits.php?modifier_produit=<?php echo $produit['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Modifier</a>
                            <a href="gerer_produits.php?supprimer_produit=<?php echo $produit['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')"><i class="fas fa-trash-alt"></i> Supprimer</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <a href="admin_dashboard.php" class="btn btn-secondary mt-4"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
