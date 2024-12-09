<?php
session_start();
include 'config.php'; // Connexion à la base de données

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête préparée pour vérifier l'email dans la table des clients et des employés
    $query = "
        SELECT id,nom, email, mot_de_passe, role, NULL AS poste FROM Users WHERE email = :email
        UNION
        SELECT id,nom, email, mot_de_passe, NULL AS role, poste FROM employes WHERE email = :email
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();  // Exécuter la requête

    // Vérifier si l'utilisateur existe
    if ($stmt->rowCount() > 0) {
        // Récupérer les informations de l'utilisateur
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe
        if (password_verify($password, $user['mot_de_passe'])) {
            // Si le mot de passe est correct, démarrer la session
            $_SESSION['user'] = $user;

            // Vérifier si l'utilisateur est un client, serveur, ou administrateur
            if (isset($user['role'])) { // Si c'est un client ou admin
                if ($user['role'] == 'admin') {
                    header('Location: admin_dashboard.php'); // Page d'administration
                    exit;
                } else {
                    header('Location: client_dashboard.php'); // Page d'accueil client
                    exit;
                }
            } elseif (isset($user['poste'])) { // Si c'est un employé (serveur ou cuisinier)
                if ($user['poste'] == 'serveur') {
                    header('Location: client_dashboard.php'); // Page serveur
                    exit;
                } elseif ($user['poste'] == 'cuisinier') {
                    header('Location: cuisinier_dashboard.php'); // Page cuisinier
                    exit;
                }
            }
            exit();
        } else {
            $error = "Identifiants incorrects";
        }
    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-group input:focus {
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
        <h2>Connexion</h2>
        <form method="POST">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Entrez votre email" required>
                </div>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Entrez votre mot de passe" required>
                </div>
            </div>
            <div class="form-group">
                <button type="submit">Se connecter</button>
            </div>
            <div class="text-center mt-3">
                <a href="inscription.php" class="text-decoration-none">Créer un compte</a>
            </div>
        </form>
    </div>

    <!-- Lien vers Bootstrap JS et Bootstrap Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>
</html>

<?php
$pdo = null;
?>
