<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config.php';

$error = "";
$success = false;

// Récupérer la liste des terrains en bon état
try {
    $stmt = $pdo->query("SELECT * FROM terrain WHERE etat = 'bon' ORDER BY nom");
    $terrains = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des terrains: " . $e->getMessage();
    $terrains = [];
}

// Récupérer la liste des joueurs actifs
try {
    $stmt = $pdo->query("SELECT * FROM joueur WHERE statut = 'actif' ORDER BY nom");
    $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des joueurs: " . $e->getMessage();
    $joueurs = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $joueur_id = $_POST['joueur_id'];
    $terrain_id = $_POST['terrain_id'];
    $date = $_POST['date'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    
    // Validation
    if (empty($joueur_id) || empty($terrain_id) || empty($date) || empty($heure_debut) || empty($heure_fin)) {
        $error = "Tous les champs sont obligatoires";
    } elseif (strtotime($heure_fin) <= strtotime($heure_debut)) {
        $error = "L'heure de fin doit être postérieure à l'heure de début";
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = "La date de réservation ne peut pas être dans le passé";
    } else {
        // Vérifier si le terrain est toujours en bon état et récupérer le tarif_horaire
        $stmt = $pdo->prepare("SELECT etat, tarif_horaire FROM terrain WHERE id = ?");
        $stmt->execute([$terrain_id]);
        $terrain = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$terrain || $terrain['etat'] != 'bon') {
            $error = "Ce terrain n'est pas disponible pour réservation";
        } else {
            // Vérifier si le joueur est actif
            $stmt = $pdo->prepare("SELECT statut FROM joueur WHERE id = ?");
            $stmt->execute([$joueur_id]);
            $joueur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$joueur || $joueur['statut'] != 'actif') {
                $error = "Ce joueur n'est pas actif et ne peut pas effectuer de réservation";
            } else {
                // Vérifier les conflits de réservation
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservation 
                                      WHERE terrain_id = ? AND date = ? AND statut = 'confirmé'
                                      AND ((heure_debut <= ? AND heure_fin > ?) OR 
                                           (heure_debut < ? AND heure_fin >= ?) OR
                                           (heure_debut >= ? AND heure_fin <= ?))");
                $stmt->execute([$terrain_id, $date, $heure_debut, $heure_debut, $heure_fin, $heure_fin, $heure_debut, $heure_fin]);
                $conflict_count = $stmt->fetchColumn();
                
                if ($conflict_count > 0) {
                    $error = "Ce terrain est déjà réservé pendant cette période";
                } else {
                    // Calcul du montant total
                    $debut = strtotime($heure_debut);
                    $fin = strtotime($heure_fin);
                    $duree_heures = ($fin - $debut) / 3600;
                    if ($duree_heures <= 0) {
                        $error = "La durée de réservation doit être supérieure à 0";
                    } else {
                        $tarif_horaire = floatval($terrain['tarif_horaire']);
                        $montant_total = round($tarif_horaire * $duree_heures, 2);

                        // Insertion dans la base de données
                        try {
                            $stmt = $pdo->prepare("INSERT INTO reservation (joueur_id, terrain_id, date, heure_debut, heure_fin, paiement, montant_total, statut) 
                                                  VALUES (?, ?, ?, ?, ?, 'non payé', ?, 'confirmé')");
                            $stmt->execute([$joueur_id, $terrain_id, $date, $heure_debut, $heure_fin, $montant_total]);
                            
                            header("Location: dashboard.php?reservation_success=1");
                            exit();
                        } catch (PDOException $e) {
                            $error = "Erreur lors de l'ajout de la réservation: " . $e->getMessage();
                        }
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter une réservation</title>
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

        .alert-warning {
            background-color: #fcf8e3;
            border: 1px solid #faebcc;
            color: #8a6d3b;
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
                <a href="add_terrain.php" class="nav-link">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Terrains</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link active">
                    <i class="fas fa-calendar-check"></i>
                    <span>Réservations</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="add_joueur.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span>Joueurs</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1>Ajouter une réservation</h1>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Retour au tableau de bord
            </a>
        </div>
        
        <div class="form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">La réservation a été ajoutée avec succès !</div>
            <?php endif; ?>
            
            <?php if (empty($terrains)): ?>
                <div class="alert alert-warning">Aucun terrain en bon état n'est disponible pour réservation.</div>
            <?php endif; ?>
            
            <?php if (empty($joueurs)): ?>
                <div class="alert alert-warning">Aucun joueur actif n'est disponible pour réservation.</div>
            <?php endif; ?>
            
            <?php if (!empty($terrains) && !empty($joueurs)): ?>
            <form method="post">
                <div class="form-group">
                    <label for="joueur_id">Joueur:</label>
                    <select id="joueur_id" name="joueur_id" class="form-control" required>
                        <option value="">Sélectionner un joueur</option>
                        <?php foreach ($joueurs as $joueur): ?>
                            <option value="<?php echo $joueur['id']; ?>"><?php echo htmlspecialchars($joueur['nom']) . ' (' . htmlspecialchars($joueur['email']) . ')'; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="terrain_id">Terrain:</label>
                    <select id="terrain_id" name="terrain_id" class="form-control" required>
                        <option value="">Sélectionner un terrain</option>
                        <?php foreach ($terrains as $terrain): ?>
                            <option value="<?php echo $terrain['id']; ?>" data-tarif="<?php echo $terrain['tarif_horaire']; ?>">
                                <?php echo htmlspecialchars($terrain['nom']) . ' (' . htmlspecialchars($terrain['type']) . ') - ' . $terrain['tarif_horaire'] . ' DT/h'; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="heure_debut">Heure de début:</label>
                    <input type="time" id="heure_debut" name="heure_debut" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="heure_fin">Heure de fin:</label>
                    <input type="time" id="heure_fin" name="heure_fin" class="form-control" required>
                </div>
                
                <div class="form-group" id="montant-container" style="display: none;">
                    <label>Montant estimé:</label>
                    <div id="montant-estime" style="font-weight: bold; color: var(--primary-color);"></div>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Ajouter la réservation
                    </button>
                    <a href="dashboard.php" class="btn btn-danger">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Calculate estimated amount when time or terrain changes
        document.addEventListener('DOMContentLoaded', function() {
            const terrainSelect = document.getElementById('terrain_id');
            const heureDebut = document.getElementById('heure_debut');
            const heureFin = document.getElementById('heure_fin');
            const montantContainer = document.getElementById('montant-container');
            const montantEstime = document.getElementById('montant-estime');
            
            function calculateAmount() {
                const selectedOption = terrainSelect.options[terrainSelect.selectedIndex];
                const tarif = selectedOption ? parseFloat(selectedOption.getAttribute('data-tarif')) : 0;
                const debut = heureDebut.value;
                const fin = heureFin.value;
                
                if (tarif > 0 && debut && fin) {
                    const [debutH, debutM] = debut.split(':').map(Number);
                    const [finH, finM] = fin.split(':').map(Number);
                    
                    const totalMinutes = (finH * 60 + finM) - (debutH * 60 + debutM);
                    const totalHours = totalMinutes / 60;
                    
                    if (totalHours > 0) {
                        const montant = (tarif * totalHours).toFixed(2);
                        montantEstime.textContent = montant + ' DT';
                        montantContainer.style.display = 'block';
                        return;
                    }
                }
                
                montantContainer.style.display = 'none';
            }
            
            terrainSelect.addEventListener('change', calculateAmount);
            heureDebut.addEventListener('change', calculateAmount);
            heureFin.addEventListener('change', calculateAmount);
        });
    </script>
</body>
</html>