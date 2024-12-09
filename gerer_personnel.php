<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

// Ajouter un personnel
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajouter_personnel'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT); // Sécurisation du mot de passe
    $poste = $_POST['poste'];
    $date_embauche = $_POST['date_embauche'];
    $administrateur_id = $_SESSION['user']['id']; // Administrateur actuel

    // Préparer la requête d'ajout
    $query_add_personnel = "INSERT INTO employes (nom, email, mot_de_passe, poste, date_embauche, administrateur_id) 
                            VALUES (:nom, :email, :mot_de_passe, :poste, :date_embauche, :administrateur_id)";
    $stmt_add_personnel = $pdo->prepare($query_add_personnel);
    $stmt_add_personnel->bindParam(':nom', $nom);
    $stmt_add_personnel->bindParam(':email', $email);
    $stmt_add_personnel->bindParam(':mot_de_passe', $mot_de_passe);
    $stmt_add_personnel->bindParam(':poste', $poste);
    $stmt_add_personnel->bindParam(':date_embauche', $date_embauche);
    $stmt_add_personnel->bindParam(':administrateur_id', $administrateur_id);
    $stmt_add_personnel->execute();
}

// Modifier un personnel
if (isset($_GET['modifier_personnel'])) {
    $personnel_id = $_GET['modifier_personnel'];

    // Vérifier si l'ID est un entier valide
    if (filter_var($personnel_id, FILTER_VALIDATE_INT)) {
        // Récupérer les informations du personnel
        $query_personnel_details = "SELECT * FROM employes WHERE id = :id";
        $stmt_personnel_details = $pdo->prepare($query_personnel_details);
        $stmt_personnel_details->bindParam(':id', $personnel_id);
        $stmt_personnel_details->execute();
        $personnel = $stmt_personnel_details->fetch(PDO::FETCH_ASSOC);

        // Si le personnel existe, afficher le formulaire de modification
        if (!$personnel) {
            echo "Erreur: Le personnel n'a pas été trouvé.";
            exit();
        }
    } else {
        echo "ID de personnel invalide.";
        exit();
    }
}

// Traiter le formulaire de modification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modifier_personnel'])) {
    $nom = $_POST['nom'];
    $poste = $_POST['poste'];
    $date_embauche = $_POST['date_embauche'];
    $personnel_id = $_POST['personnel_id'];

    // Requête pour modifier le personnel
    $query_update_personnel = "UPDATE employes 
                               SET nom = :nom, poste = :poste, date_embauche = :date_embauche 
                               WHERE id = :id";
    $stmt_update_personnel = $pdo->prepare($query_update_personnel);
    $stmt_update_personnel->bindParam(':nom', $nom);
    $stmt_update_personnel->bindParam(':poste', $poste);
    $stmt_update_personnel->bindParam(':date_embauche', $date_embauche);
    $stmt_update_personnel->bindParam(':id', $personnel_id);
    $stmt_update_personnel->execute();

    // Rediriger après la mise à jour
    header('Location: gerer_personnel.php');
    exit();
}

// Supprimer un personnel
if (isset($_GET['supprimer_personnel'])) {
    $personnel_id = $_GET['supprimer_personnel'];

    // Vérifier si l'ID est un entier valide
    if (filter_var($personnel_id, FILTER_VALIDATE_INT)) {
        // Requête pour supprimer le personnel
        $query_delete_personnel = "DELETE FROM employes WHERE id = :id";
        $stmt_delete_personnel = $pdo->prepare($query_delete_personnel);
        $stmt_delete_personnel->bindParam(':id', $personnel_id);
        $stmt_delete_personnel->execute();
    }
}

// Récupérer la liste des employés
$query_personnel = "SELECT * FROM employes";
$stmt_personnel = $pdo->query($query_personnel);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer le Personnel</title>
    <link rel="stylesheet" href="gerer_personnel.css"> <!-- Votre fichier CSS -->
    <!-- Lien pour Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Lien pour Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .input-group-prepend .fas {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .input-group {
            position: relative;
        }

        .form-control {
            padding-left: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Gérer le Personnel</h2>

        <!-- Formulaire d'ajout de personnel -->
        <form method="POST">
            <h3>Ajouter un Personnel</h3>
            <div class="form-group">
                <label for="nom">Nom</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" name="nom" id="nom" class="form-control" placeholder="Nom" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                </div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" placeholder="Mot de passe" required>
                </div>
            </div>
            <div class="form-group">
                <label for="poste">Poste</label>
                <select name="poste" id="poste" class="form-control" required>
                    <option value="serveur">Serveur</option>
                    <option value="cuisinier">Cuisinier</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date_embauche">Date d'Embauche</label>
                <input type="date" name="date_embauche" id="date_embauche" class="form-control" required>
            </div>
            <button type="submit" name="ajouter_personnel" class="btn btn-primary">Ajouter</button>
        </form>

        <!-- Formulaire de modification -->
        <?php if (isset($personnel)) { ?>
            <form method="POST">
                <h3>Modifier un Personnel</h3>
                <input type="hidden" name="personnel_id" value="<?php echo $personnel['id']; ?>">
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" name="nom" id="nom" class="form-control" value="<?php echo $personnel['nom']; ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="poste">Poste</label>
                    <select name="poste" id="poste" class="form-control" required>
                        <option value="serveur" <?php echo ($personnel['poste'] == 'serveur') ? 'selected' : ''; ?>>Serveur</option>
                        <option value="cuisinier" <?php echo ($personnel['poste'] == 'cuisinier') ? 'selected' : ''; ?>>Cuisinier</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_embauche">Date d'Embauche</label>
                    <input type="date" name="date_embauche" id="date_embauche" class="form-control" value="<?php echo $personnel['date_embauche']; ?>" required>
                </div>
                <button type="submit" name="modifier_personnel" class="btn btn-warning">Modifier</button>
            </form>
        <?php } ?>

        <h3>Liste du Personnel</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Poste</th>
                    <th>Date d'Embauche</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($personnel = $stmt_personnel->fetch(PDO::FETCH_ASSOC)) { ?>
                    <tr>
                        <td><?php echo $personnel['nom']; ?></td>
                        <td><?php echo $personnel['poste']; ?></td>
                        <td><?php echo $personnel['date_embauche']; ?></td>
                        <td>
                            <a href="gerer_personnel.php?modifier_personnel=<?php echo $personnel['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                            <a href="gerer_personnel.php?supprimer_personnel=<?php echo $personnel['id']; ?>" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="admin_dashboard.php" class="btn btn-secondary">Terminer</a>
    </div>
</body>
</html>
