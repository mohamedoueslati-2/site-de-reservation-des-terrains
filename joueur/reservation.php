<?php
session_start();
if (!isset($_SESSION['joueur_id'])) {
    header("Location: ../joueur_login.php");
    exit();
}
require_once '../config.php';

// Vérifier si le joueur est toujours actif
$stmt = $pdo->prepare("SELECT statut FROM joueur WHERE id = ?");
$stmt->execute([$_SESSION['joueur_id']]);
$joueur = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$joueur || $joueur['statut'] == 'bloqué') {
    // Déconnecter le joueur
    session_unset();
    session_destroy();
    header("Location: ../joueur_login.php?blocked=1");
    exit();
}

$joueur_id = $_SESSION['joueur_id'];
$joueur_name = $_SESSION['joueur_name'];

// Récupérer la durée de réservation depuis la base de données
try {
    $stmt = $pdo->query("SELECT valeur FROM parametres WHERE nom = 'duree_reservation'");
    $duree_reservation = $stmt->fetchColumn();
    $duree_reservation = floatval($duree_reservation); // Convertir en nombre
    
    // Si aucune valeur n'est trouvée ou si la valeur est invalide, utiliser la valeur par défaut
    if (!$duree_reservation || $duree_reservation <= 0) {
        $duree_reservation = 1.5; // 1h30 par défaut
    }
} catch (PDOException $e) {
    // En cas d'erreur, utiliser la valeur par défaut
    $duree_reservation = 1.5;
}

// Formatage pour l'affichage de la durée de réservation
$duree_heures = floor($duree_reservation);
$duree_minutes = ($duree_reservation - $duree_heures) * 60;
$duree_affichage = ($duree_heures > 0 ? $duree_heures . 'h' : '') . 
                   ($duree_minutes > 0 ? $duree_minutes . 'min' : '');

// Récupérer les types de sports disponibles (uniquement ceux qui ont des terrains en bon état)
$stmt = $pdo->query("SELECT DISTINCT type FROM terrain WHERE etat = 'bon' ORDER BY type");
$types_sport = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Heures d'ouverture du complexe sportif
$heure_ouverture = 9; // 9h du matin
$heure_fermeture = 24; // minuit

// Initialiser les variables
$terrains_disponibles = [];
$date_selected = date('Y-m-d'); // Aujourd'hui par défaut
$type_sport_selected = '';
$error = '';
$success = '';

// Traitement de la réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    $terrain_id = $_POST['terrain_id'];
    $date = $_POST['date'];
    $heure_debut = $_POST['heure_debut'];
    
    // Calcul de l'heure de fin en fonction de la durée configurée
    $heure_debut_obj = new DateTime($heure_debut);
    $heure_fin_obj = clone $heure_debut_obj;
    $heure_fin_obj->add(new DateInterval('PT' . (int)($duree_reservation * 60) . 'M')); // Ajouter la durée en minutes
    $heure_fin = $heure_fin_obj->format('H:i:s');
    
    // Vérifier si le terrain est toujours disponible avec une requête SQL plus complète
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservation 
                          WHERE terrain_id = ? AND date = ? AND statut = 'confirmé'
                          AND (
                              (heure_debut >= ? AND heure_debut < ?)
                              OR
                              (heure_fin > ? AND heure_fin <= ?)
                              OR
                              (heure_debut <= ? AND heure_fin >= ?)
                          )");
    
    $stmt->execute([
        $terrain_id, 
        $date,
        $heure_debut,
        $heure_fin,
        $heure_debut,
        $heure_fin,
        $heure_debut,
        $heure_fin
    ]);
    $conflict_count = $stmt->fetchColumn();
    
    if ($conflict_count > 0) {
        $error = "Ce terrain n'est plus disponible à cette heure. Veuillez choisir un autre créneau.";
    } else {
        try {
            // Récupérer le tarif horaire du terrain
            $stmt = $pdo->prepare("SELECT tarif_horaire FROM terrain WHERE id = ?");
            $stmt->execute([$terrain_id]);
            $tarif_horaire = $stmt->fetchColumn();
            $tarif_horaire = floatval($tarif_horaire);

            // Calcul du montant total
            $debut = strtotime($heure_debut);
            $fin = strtotime($heure_fin);
            $duree_heures = ($fin - $debut) / 3600;
            $montant_total = round($tarif_horaire * $duree_heures, 2);

            // Ajouter la réservation avec paiement et montant_total
            $stmt = $pdo->prepare("INSERT INTO reservation (joueur_id, terrain_id, date, heure_debut, heure_fin, paiement, montant_total, statut) 
                                VALUES (?, ?, ?, ?, ?, 'non payé', ?, 'confirmé')");
            $stmt->execute([$joueur_id, $terrain_id, $date, $heure_debut, $heure_fin, $montant_total]);
            
            $success = "Votre réservation a été enregistrée avec succès!";
        } catch (PDOException $e) {
            $error = "Une erreur est survenue lors de la réservation: " . $e->getMessage();
        }
    }
}

// Traitement de la recherche
if (isset($_GET['search'])) {
    $type_sport_selected = isset($_GET['sport']) ? $_GET['sport'] : '';
    $date_selected = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    
    // Valider la date (ne pas accepter les dates passées)
    if (strtotime($date_selected) < strtotime(date('Y-m-d'))) {
        $error = "Vous ne pouvez pas réserver pour une date passée.";
        $date_selected = date('Y-m-d'); // Réinitialiser à aujourd'hui
    }
    
    if (!empty($type_sport_selected)) {
        // Récupérer tous les terrains du type sélectionné qui sont en bon état
        $stmt = $pdo->prepare("SELECT * FROM terrain WHERE type = ? AND etat = 'bon' ORDER BY nom");
        $stmt->execute([$type_sport_selected]);
        $terrains = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Générer les créneaux horaires disponibles pour chaque terrain
        foreach ($terrains as $terrain) {
            $terrains_disponibles[$terrain['id']] = [
                'nom' => $terrain['nom'],
                'type' => $terrain['type'],
                'creneaux' => []
            ];
            
            // Créer une liste précise de créneaux sans chevauchement
            $creneaux_horaires = [];
            
            // Générer des créneaux fixes selon la durée configurée
            $heure_courante = $heure_ouverture;
            while ($heure_courante + $duree_reservation <= $heure_fermeture) {
                // Calculer l'heure de début et de fin
                $heures_debut = floor($heure_courante);
                $minutes_debut = ($heure_courante - $heures_debut) * 60;
                
                $heure_debut = sprintf("%02d:%02d:00", $heures_debut, $minutes_debut);
                
                $heure_debut_obj = new DateTime($heure_debut);
                $heure_fin_obj = clone $heure_debut_obj;
                $heure_fin_obj->add(new DateInterval('PT' . (int)($duree_reservation * 60) . 'M'));
                $heure_fin = $heure_fin_obj->format('H:i:s');
                
                $creneaux_horaires[] = [
                    'debut' => $heure_debut,
                    'fin' => $heure_fin,
                    'affichage' => $heure_debut_obj->format('H:i') . ' - ' . $heure_fin_obj->format('H:i')
                ];
                
                // Passer au créneau suivant sans chevauchement
                $heure_courante += $duree_reservation;
            }
            
            // Vérifier pour chaque créneau s'il est disponible avec une requête SQL plus complète
            foreach ($creneaux_horaires as $creneau) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservation 
                                      WHERE terrain_id = ? AND date = ? AND statut = 'confirmé'
                                      AND (
                                          (heure_debut >= ? AND heure_debut < ?)
                                          OR
                                          (heure_fin > ? AND heure_fin <= ?)
                                          OR
                                          (heure_debut <= ? AND heure_fin >= ?)
                                      )");
                
                $stmt->execute([
                    $terrain['id'], 
                    $date_selected,
                    $creneau['debut'],
                    $creneau['fin'],
                    $creneau['debut'],
                    $creneau['fin'],
                    $creneau['debut'],
                    $creneau['fin']
                ]);
                $is_reserved = $stmt->fetchColumn() > 0;
                
                if (!$is_reserved) {
                    $terrains_disponibles[$terrain['id']]['creneaux'][] = $creneau;
                }
            }
            
            // Si aucun créneau n'est disponible pour ce terrain, le retirer de la liste
            if (empty($terrains_disponibles[$terrain['id']]['creneaux'])) {
                unset($terrains_disponibles[$terrain['id']]);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Espace Joueur - Réserver un terrain</title>
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

        /* Header Styles */
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
            display: block;
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

        /* Main Content Styles */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
        }

        /* Section Styles */
        .section {
            background-color: var(--white);
            margin-bottom: 30px;
            padding: 25px;
            border-radius: 8px;
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

        /* Form Styles */
        .search-form {
            background-color: var(--gray);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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

        select, input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }

        select:focus, input[type="date"]:focus {
            outline: none;
            border-color: var(--secondary-color);
        }

        /* Button Styles */
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

        .btn-danger {
            background-color: var(--error-color);
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        /* Terrain List Styles */
        .terrain-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .terrain-card {
            border: 1px solid var(--gray);
            border-radius: 8px;
            padding: 20px;
            background-color: var(--white);
            transition: all 0.3s;
        }

        .terrain-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }

        .terrain-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .creneaux-list {
            margin-top: 15px;
        }

        .creneau-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .creneau-form {
            display: inline;
        }

        .reserve-btn {
            padding: 6px 12px;
            background-color: var(--secondary-color);
            color: var(--white);
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .reserve-btn:hover {
            background-color: var(--primary-color);
        }

        /* Message Styles */
        .error-message {
            color: var(--error-color);
            padding: 15px;
            background-color: #ffebee;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .success-message {
            color: var(--success-color);
            padding: 15px;
            background-color: #e8f5e9;
            border-radius: 4px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            background-color: var(--secondary-color);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            gap: 5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-list {
                flex-direction: column;
            }
            
            .terrain-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-calendar-plus"></i> Réserver un terrain</h1>
            <div>
                <span>Bienvenue, <?php echo htmlspecialchars($joueur_name); ?></span>
                <a href="../logout.php" class="btn btn-danger" style="margin-left: 15px;">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
    
    <nav class="nav">
        <ul class="nav-list">
            <li><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li><a href="reservation.php" class="nav-link active"><i class="fas fa-calendar-plus"></i> Réserver un terrain</a></li>
            <li><a href="historique.php" class="nav-link"><i class="fas fa-history"></i> Historique complet</a></li>
            <li><a href="profil.php" class="nav-link"><i class="fas fa-user-cog"></i> Mon profil</a></li>
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
                <h2 class="section-title">
                    <i class="fas fa-search"></i> Rechercher un terrain disponible
                    <span class="info-badge">
                        <i class="fas fa-clock"></i> Durée: <?php echo $duree_affichage; ?>
                    </span>
                </h2>
                
                <div class="search-form">
                    <form method="get">
                        <div class="form-group">
                            <label for="sport"><i class="fas fa-running"></i> Type de sport:</label>
                            <select id="sport" name="sport" required>
                                <option value="">Sélectionner un sport</option>
                                <?php foreach ($types_sport as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php if ($type_sport_selected == $type) echo 'selected'; ?>>
                                        <?php echo ucfirst(htmlspecialchars($type)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date"><i class="fas fa-calendar-day"></i> Date:</label>
                            <input type="date" id="date" name="date" value="<?php echo $date_selected; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <button type="submit" name="search" class="btn">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                    </form>
                </div>
            </div>
            
            <?php if (isset($_GET['search']) && !empty($type_sport_selected)): ?>
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-map-marker-alt"></i> Terrains disponibles pour <?php echo ucfirst(htmlspecialchars($type_sport_selected)); ?> le <?php echo date('d/m/Y', strtotime($date_selected)); ?>
                    </h2>
                    
                    <?php if (empty($terrains_disponibles)): ?>
                        <p>Aucun terrain disponible pour cette date et ce sport. Veuillez essayer une autre date ou un autre sport.</p>
                    <?php else: ?>
                        <div class="terrain-list">
                            <?php foreach ($terrains_disponibles as $terrain_id => $terrain): ?>
                                <div class="terrain-card">
                                    <div class="terrain-name">
                                        <i class="fas fa-square-full"></i> <?php echo htmlspecialchars($terrain['nom']); ?>
                                    </div>
                                    <div><i class="fas fa-tag"></i> Type: <?php echo ucfirst(htmlspecialchars($terrain['type'])); ?></div>
                                    <div class="creneaux-list">
                                        <h4><i class="fas fa-clock"></i> Créneaux disponibles:</h4>
                                        <?php foreach ($terrain['creneaux'] as $creneau): ?>
                                            <div class="creneau-item">
                                                <span><?php echo $creneau['affichage']; ?></span>
                                                <form method="post" class="creneau-form">
                                                    <input type="hidden" name="terrain_id" value="<?php echo $terrain_id; ?>">
                                                    <input type="hidden" name="date" value="<?php echo $date_selected; ?>">
                                                    <input type="hidden" name="heure_debut" value="<?php echo $creneau['debut']; ?>">
                                                    <button type="submit" name="reserver" class="reserve-btn">
                                                        <i class="fas fa-bookmark"></i> Réserver
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>