<?php
session_start();
if (!isset($_SESSION['joueur_id'])) {
    header("Location: ../joueur_login.php");
    exit();
}
require_once '../config.php';

// Initialize variables with safe defaults
$joueur = [
    'nom' => '',
    'email' => '',
    'num_telephone' => '',
    'statut' => 'inconnu'
];
$error = '';
$success = '';

try {
    // Verify player status
    $stmt = $pdo->prepare("SELECT * FROM joueur WHERE id = ?");
    $stmt->execute([$_SESSION['joueur_id']]);
    $joueur = $stmt->fetch(PDO::FETCH_ASSOC) ?: $joueur;

    if (!$joueur || ($joueur['statut'] ?? '') == 'bloqué') {
        session_unset();
        session_destroy();
        header("Location: ../joueur_login.php?blocked=1");
        exit();
    }

    $joueur_id = $_SESSION['joueur_id'];
    $joueur_name = $_SESSION['joueur_name'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $num_telephone = trim($_POST['num_telephone'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($nom) || empty($email) || empty($num_telephone)) {
            $error = "Le nom, l'email et le numéro de téléphone sont obligatoires.";
        } elseif (!preg_match('/^[0-9]{8}$/', $num_telephone)) {
            $error = "Le numéro de téléphone doit comporter exactement 8 chiffres.";
        } elseif ($email != ($joueur['email'] ?? '')) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM joueur WHERE email = ? AND id != ?");
            $stmt->execute([$email, $joueur_id]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Cet email est déjà utilisé par un autre joueur.";
            }
        }

        if (!empty($new_password)) {
            if (!password_verify($current_password, $joueur['mot_de_passe'] ?? '')) {
                $error = "Le mot de passe actuel est incorrect.";
            } elseif ($new_password != $confirm_password) {
                $error = "Les nouveaux mots de passe ne correspondent pas.";
            } elseif (strlen($new_password) < 6) {
                $error = "Le nouveau mot de passe doit comporter au moins 6 caractères.";
            }
        }
        
        if (empty($error)) {
            try {
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE joueur SET nom = ?, email = ?, num_telephone = ?, mot_de_passe = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $num_telephone, $hashed_password, $joueur_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE joueur SET nom = ?, email = ?, num_telephone = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $num_telephone, $joueur_id]);
                }
                
                $success = "Votre profil a été mis à jour avec succès!";
                $_SESSION['joueur_name'] = $nom;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM joueur WHERE id = ?");
                $stmt->execute([$joueur_id]);
                $joueur = $stmt->fetch(PDO::FETCH_ASSOC) ?: $joueur;
            } catch (PDOException $e) {
                $error = "Erreur de mise à jour: " . $e->getMessage();
            }
        }
    }
} catch (PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon profil</title>
    <style>
        /* Keep your original CSS here */
        :root {
            --primary-color: rgb(37, 36, 75);
            --secondary-color: #536DFE;
            --white: #fff;
            --gray: #f5f5f5;
            --dark-gray: #5a5c69;
            --light-gray: #f8f9fc;
            --error-color: #ff3860;
            --success-color: #09c372;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-gray);
            min-height: 100vh;
        }

        .header {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

         /* Navigation Styles */
         .nav {
            background-color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .nav-list {
            display: flex;
            list-style: none;
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-link {
            display: inline-block;
            color: var(--white);
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .nav-link.active {
            background-color: var(--secondary-color);
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .section {
            background-color: var(--white);
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-title {
            margin-top: 0;
            color: var(--primary-color);
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-info {
            background-color: var(--gray);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: var(--primary-color);
            display: inline-block;
            width: 160px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-gray);
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--gray);
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 10px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 2px rgba(83, 109, 254, 0.1);
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--secondary-color);
            color: var(--white);
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .error-message {
            background-color: #ffebee;
            color: var(--error-color);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
        }

        .success-message {
            background-color: #e8f5e9;
            color: var(--success-color);
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
        }

        .logout {
            background-color: var(--error-color);
            color: var(--white);
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .logout:hover {
            background-color: #d32f2f;
        }

        @media (max-width: 768px) {
            .nav-list {
                flex-direction: column;
            }
            
            .info-label {
                width: auto;
                display: block;
                margin-bottom: 5px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="header">
        <div class="header-content">
            <h1><i class="fas fa-user-cog"></i> Mon profil</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($joueur_name); ?></span>
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px; background-color: var(--error-color);">
                    <i class="fas fa-sign-out-alt">  </i> Déconnexion
                </a>
            </div>
        </div>
    </div>
    
    <nav class="nav">
        <ul class="nav-list">
            <li><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li><a href="reservation.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Réserver un terrain</a></li>
            <li><a href="historique.php" class="nav-link"><i class="fas fa-history"></i> Historique complet</a></li>
            <li><a href="profil.php" class="nav-link active"><i class="fas fa-user-cog"></i> Mon profil</a></li>
        </ul>
    </nav>
    
    
    <div class="container">
        <div class="content">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2 class="section-title">Informations personnelles</h2>
                
                <div class="profile-info">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Nom:</span>
                        <?php echo htmlspecialchars($joueur['nom'] ?? ''); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-envelope"></i> Email:</span>
                        <?php echo htmlspecialchars($joueur['email'] ?? ''); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-phone"></i> Téléphone:</span>
                        <?php echo htmlspecialchars($joueur['num_telephone'] ?? ''); ?>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-info-circle"></i> Statut:</span>
                        <span style="background-color: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 20px;">
                            <?php echo htmlspecialchars($joueur['statut'] ?? 'inconnu'); ?>
                        </span>
                    </div>
                </div>

                <h3 class="section-title" style="margin-top: 30px; border-bottom: none;">
                    <i class="fas fa-edit"></i> Modifier le profil
                </h3>
                
                <form method="post">
                    <div class="form-group">
                        <label for="nom"><i class="fas fa-user"></i> Nom:</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($joueur['nom'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($joueur['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="num_telephone"><i class="fas fa-phone"></i> Téléphone:</label>
                        <input type="text" id="num_telephone" name="num_telephone" 
                               value="<?php echo htmlspecialchars($joueur['num_telephone'] ?? ''); ?>"
                               pattern="[0-9]{8}"
                               title="8 chiffres requis"
                               required>
                    </div>
                    
                    <h4 style="margin: 25px 0 15px 0; color: var(--primary-color);">
                        <i class="fas fa-lock"></i> Changer le mot de passe
                    </h4>
                    
                    <div class="form-group">
                        <label for="current_password"><i class="fas fa-key"></i> Mot de passe actuel:</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password"><i class="fas fa-key"></i> Nouveau mot de passe:</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-key"></i> Confirmation:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>