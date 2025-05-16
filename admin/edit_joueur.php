<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config.php';

$error = "";
$success = false;

// Vérifier si l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$joueur_id = intval($_GET['id']);

try {
    // Récupérer les informations du joueur
    $stmt = $pdo->prepare("SELECT * FROM joueur WHERE id = ?");
    $stmt->execute([$joueur_id]);
    $joueur = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$joueur) {
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $num_telephone = trim($_POST['num_telephone']);
    $statut = $_POST['statut'];
    
    // Validation
    if (empty($nom) || empty($email) || empty($num_telephone)) {
        $error = "Le nom, l'email et le numéro de téléphone sont obligatoires";
    } elseif (!preg_match('/^[0-9]{8}$/', $num_telephone)) {
        $error = "Le numéro de téléphone doit contenir exactement 8 chiffres";
    } else {
        try {
            // Vérifier si l'email existe déjà et n'appartient pas à ce joueur
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM joueur WHERE email = ? AND id != ?");
            $stmt->execute([$email, $joueur_id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $error = "Cet email est déjà utilisé par un autre joueur";
            } else {
                // Si le mot de passe est vide, on garde l'ancien
                if (empty($mot_de_passe)) {
                    $stmt = $pdo->prepare("UPDATE joueur SET nom = ?, email = ?, num_telephone = ?, statut = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $num_telephone, $statut, $joueur_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE joueur SET nom = ?, email = ?, mot_de_passe = ?, num_telephone = ?, statut = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $mot_de_passe, $num_telephone, $statut, $joueur_id]);
                }
                
                header("Location: dashboard.php?joueur_success=1");
                exit();
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification du joueur: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier un joueur</title>
    <style>
        :root {
            --primary-color: rgb(37, 36, 75);
            --secondary-color: #536DFE;
            --white: #fff;
            --gray: #f5f5f5;
            --dark-gray: #5a5c69;
            --light-gray: #f8f9fc;
            --error-color: #ff3860;
            --success-color: #09c372;
            --warning-color: #ff9800;
            --sidebar-width: 250px;
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

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: var(--white);
            height: 100vh;
            position: fixed;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            padding: 20px;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .sidebar-brand i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .sidebar-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 0 15px;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
        }

        .sidebar-heading {
            padding: 10px 20px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: var(--white);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        .nav-link.active {
            color: var(--white);
            background-color: var(--secondary-color);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 1.8rem;
        }

        /* Form Styles */
        .form-container {
            background-color: var(--white);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-success {
            background-color: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #07a05e;
        }

        .btn-danger {
            background-color: var(--error-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        /* Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Password Toggle */
        .password-toggle {
            position: relative;
        }

        .password-toggle input {
            padding-right: 40px;
        }

        .password-toggle-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: var(--dark-gray);
        }

        .password-note {
            color: var(--dark-gray);
            font-size: 0.9em;
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar-brand-text,
            .nav-link span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-futbol"></i>
            <span class="sidebar-brand-text">Admin Dashboard</span>
        </div>
        <div class="sidebar-divider"></div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="terrains.php" class="nav-link">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Terrains</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="reservations.php" class="nav-link">
                    <i class="fas fa-calendar-check"></i>
                    <span>Réservations</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span>Joueurs</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Modifier un joueur</h1>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">Le joueur a été modifié avec succès !</div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="nom">Nom du joueur:</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?php echo htmlspecialchars($joueur['nom']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($joueur['email']); ?>" required>
                </div>
                
                <div class="form-group password-toggle">
                    <label for="mot_de_passe">Mot de passe:</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control">
                    <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                    <div class="password-note">Laissez vide pour conserver le mot de passe actuel</div>
                </div>

                <div class="form-group">
                    <label for="num_telephone">Numéro de téléphone:</label>
                    <input type="text" id="num_telephone" name="num_telephone" class="form-control" 
                           pattern="[0-9]{8}" title="8 chiffres requis" 
                           value="<?php echo htmlspecialchars($joueur['num_telephone']); ?>" required>
                    <div class="password-note">Format: 8 chiffres (ex: 12345678)</div>
                </div>
                
                <div class="form-group">
                    <label for="statut">Statut:</label>
                    <select id="statut" name="statut" class="form-control" required>
                        <option value="actif" <?php if($joueur['statut'] == 'actif') echo 'selected'; ?>>Actif</option>
                        <option value="bloqué" <?php if($joueur['statut'] == 'bloqué') echo 'selected'; ?>>Bloqué</option>
                    </select>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Enregistrer les modifications
                    </button>
                    <a href="dashboard.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('mot_de_passe');
            const toggleBtn = document.querySelector('.password-toggle-btn i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleBtn.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Format phone number input
        document.getElementById('num_telephone').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length > 8) {
                this.value = this.value.slice(0, 8);
            }
        });
    </script>
</body>
</html>