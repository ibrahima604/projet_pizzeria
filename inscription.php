<?php
session_start();
include 'config.php'; // Connexion à la base de données

// Variable pour stocker les erreurs et messages de succès
$error = '';
$success = '';

// Vérifier si un administrateur existe déjà
$query_check_admin = "SELECT COUNT(*) AS count FROM Users WHERE role = 'admin'";
$stmt_check_admin = $pdo->query($query_check_admin);
$result = $stmt_check_admin->fetch(PDO::FETCH_ASSOC);
$adminExists = $result['count'] > 0;

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];
    $role = $_POST['role'];

    // Vérification si l'email existe déjà dans la base de données
    $query = "SELECT * FROM Users WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // L'email existe déjà
        $error = 'Cet email est déjà utilisé.';
    } else {
        // Hashage du mot de passe avant de l'enregistrer
        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Insertion des données dans la base de données
        $query = "INSERT INTO Users (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mot_de_passe, :role)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $mot_de_passe_hash);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
        } else {
            $error = 'Une erreur est survenue lors de l\'inscription.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Lien vers Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Style personnalisé -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #2980b9, #6dd5fa);
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #ffffff;
            max-width: 400px;
            width: 100%;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s ease-in-out;
        }
        .container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #34495e;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            box-shadow: 0 0 8px rgba(52, 152, 219, 0.5);
            outline: none;
        }
        .form-group button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            background-color: #3498db;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .form-group button:hover {
            background-color: #2980b9;
            transform: scale(1.03);
        }
        .form-group button:active {
            background-color: #1d6fa5;
            transform: scale(1);
        }
        .error {
            color: #e74c3c;
            background: #fdecea;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .success {
            color: #2ecc71;
            background: #d4edda;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 500px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Inscription</h2>

        <!-- Affichage des messages d'erreur ou de succès -->
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>

        <!-- Formulaire d'inscription -->
        <form method="POST">
            <div class="form-group">
                <label for="nom">Nom</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="nom" name="nom" class="form-control" placeholder="Entrez votre nom" required>
                </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Entrez votre email" required>
                </div>
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" placeholder="Entrez votre mot de passe" required>
                </div>
            </div>

            <?php if (!$adminExists): ?>
                <div class="form-group">
                    <label for="role">Rôle</label>
                    <select id="role" name="role" class="form-select">
                        <option value="client" selected>Client</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" id="role" name="role" value="client">
            <?php endif; ?>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </div>
        </form>
    </div>

    <!-- Lien vers Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion PDO
$pdo = null;
?>
