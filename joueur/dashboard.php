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

// Récupérer les réservations actives du joueur (à venir)
$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT r.*, t.nom as terrain_nom, t.type as terrain_type, t.etat as terrain_etat
                      FROM reservation r 
                      JOIN terrain t ON r.terrain_id = t.id 
                      WHERE r.joueur_id = ? AND r.statut = 'confirmé' AND (r.date > ? OR (r.date = ? AND r.heure_debut >= ?))
                      ORDER BY r.date ASC, r.heure_debut ASC
                      LIMIT 5");
$stmt->execute([$joueur_id, $today, $today, date('H:i:s')]);
$reservations_actives = $stmt->fetchAll();

// Récupérer l'historique des réservations (passées)
$stmt = $pdo->prepare("SELECT r.*, t.nom as terrain_nom, t.type as terrain_type
                      FROM reservation r 
                      JOIN terrain t ON r.terrain_id = t.id 
                      WHERE r.joueur_id = ? AND (r.date < ? OR (r.date = ? AND r.heure_fin < ?))
                      ORDER BY r.date DESC, r.heure_debut DESC
                      LIMIT 5");
$stmt->execute([$joueur_id, $today, $today, date('H:i:s')]);
$historique = $stmt->fetchAll();

// Récupérer les statistiques
$stmt = $pdo->prepare("SELECT COUNT(*) as total_reservations FROM reservation WHERE joueur_id = ?");
$stmt->execute([$joueur_id]);
$total_reservations = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT date) as jours_joues FROM reservation WHERE joueur_id = ? AND statut = 'confirmé' AND date <= ?");
$stmt->execute([$joueur_id, $today]);
$jours_joues = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Espace Joueur - Tableau de bord</title>
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
        }

        /* Card Styles */
        .card {
            border: 1px solid var(--gray);
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .card-header {
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--primary-color);
        }

        .card-body {
            color: var(--dark-gray);
        }

        /* Status Badges */
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-confirmed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-cancelled {
            background-color: #ffebee;
            color: #c62828;
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

        .btn-secondary {
            background-color: var(--dark-gray);
        }

        .btn-danger {
            background-color: var(--error-color);
        }

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-box {
            background-color: var(--white);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 10px 0;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--gray);
        }

        tr:hover {
            background-color: var(--gray);
        }

        /* Warning Text */
        .warning {
            color: var(--warning-color);
            font-weight: 600;
            margin-top: 10px;
            padding: 8px;
            background-color: #fff8e1;
            border-radius: 4px;
            display: inline-block;
        }

        /* See All Link */
        .see-all {
            text-align: right;
            margin-top: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-list {
                flex-direction: column;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-user"></i> Espace Joueur</h1>
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
            <li><a href="dashboard.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
            <li><a href="reservation.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Réserver un terrain</a></li>
            <li><a href="historique.php" class="nav-link"><i class="fas fa-history"></i> Historique complet</a></li>
            <li><a href="profil.php" class="nav-link"><i class="fas fa-user-cog"></i> Mon profil</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="section">
            <h2 class="section-title"><i class="fas fa-chart-line"></i> Mes statistiques</h2>
            <div class="stats-container">
                <div class="stat-box">
                    <div><i class="fas fa-calendar-check"></i> Réservations totales</div>
                    <div class="stat-number"><?php echo $total_reservations; ?></div>
                </div>
                <div class="stat-box">
                    <div><i class="fas fa-calendar-day"></i> Jours joués</div>
                    <div class="stat-number"><?php echo $jours_joues; ?></div>
                </div>
                <div class="stat-box">
                    <div><i class="fas fa-trophy"></i> Sport favori</div>
                    <div class="stat-number">
                        <?php
                        // Déterminer le sport favori
                        $stmt = $pdo->prepare("SELECT t.type, COUNT(*) as count 
                                             FROM reservation r 
                                             JOIN terrain t ON r.terrain_id = t.id 
                                             WHERE r.joueur_id = ? 
                                             GROUP BY t.type 
                                             ORDER BY count DESC 
                                             LIMIT 1");
                        $stmt->execute([$joueur_id]);
                        $sport_favori = $stmt->fetch();
                        echo $sport_favori ? ucfirst($sport_favori['type']) : 'N/A';
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Mes prochaines réservations</h2>
            <?php if (count($reservations_actives) > 0): ?>
                <div>
                    <?php foreach ($reservations_actives as $reservation): ?>
                        <div class="card">
                            <div class="card-header">
                                <div>
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?php echo htmlspecialchars($reservation['terrain_nom']) . ' (' . ucfirst($reservation['terrain_type']) . ')'; ?>
                                </div>
                                <span class="status status-confirmed">
                                    <i class="fas fa-check-circle"></i> <?php echo $reservation['statut']; ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <p><i class="fas fa-calendar-day"></i> Date: <?php echo date('d/m/Y', strtotime($reservation['date'])); ?></p>
                                <p><i class="fas fa-clock"></i> Heure: <?php echo substr($reservation['heure_debut'], 0, 5) . ' - ' . substr($reservation['heure_fin'], 0, 5); ?></p>
                                <?php if ($reservation['terrain_etat'] != 'bon'): ?>
                                    <p class="warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Attention: Ce terrain est actuellement <?php echo $reservation['terrain_etat']; ?>. Votre réservation pourrait être annulée.
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="see-all">
                    <a href="historique.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Voir toutes mes réservations
                    </a>
                </div>
            <?php else: ?>
                <p>Vous n'avez pas de réservations à venir.</p>
                <a href="reservation.php" class="btn">
                    <i class="fas fa-plus"></i> Réserver un terrain maintenant
                </a>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2 class="section-title"><i class="fas fa-history"></i> Historique récent</h2>
            <?php if (count($historique) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-map-marker-alt"></i> Terrain</th>
                            <th><i class="fas fa-calendar-day"></i> Date</th>
                            <th><i class="fas fa-clock"></i> Heure</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historique as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['terrain_nom']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reservation['date'])); ?></td>
                                <td><?php echo substr($reservation['heure_debut'], 0, 5) . ' - ' . substr($reservation['heure_fin'], 0, 5); ?></td>
                                <td>
                                    <span class="status <?php echo $reservation['statut'] == 'confirmé' ? 'status-confirmed' : 'status-cancelled'; ?>">
                                        <?php if($reservation['statut'] == 'confirmé'): ?>
                                            <i class="fas fa-check-circle"></i>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle"></i>
                                        <?php endif; ?>
                                        <?php echo $reservation['statut']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="see-all">
                    <a href="historique.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Voir tout l'historique
                    </a>
                </div>
            <?php else: ?>
                <p>Vous n'avez pas d'historique de réservation.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>