<?php
session_start();
if (!isset($_SESSION['joueur_id'])) {
    header("Location: ../joueur_login.php");
    exit();
}
require_once '../config.php';
$reservations = [];
$types = [];
$statut_filter = $type_filter = $date_debut = $date_fin = '';
$joueur_name = $_SESSION['joueur_name'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Espace Joueur - Historique</title>
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

        /* Filter Styles */
        .filters {
            background-color: var(--gray);
            padding: 20px;
            border-radius: 8px;
        }

        .filter-group {
            margin-bottom: 15px;
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

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--white);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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

        .warning {
            color: var(--warning-color);
            font-weight: 600;
            font-size: 12px;
            margin-left: 5px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-list {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1><i class="fas fa-history"></i> Historique des réservations</h1>
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
            <li><a href="reservation.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Réserver un terrain</a></li>
            <li><a href="historique.php" class="nav-link active"><i class="fas fa-history"></i> Historique complet</a></li>
            <li><a href="profil.php" class="nav-link"><i class="fas fa-user-cog"></i> Mon profil</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <div class="section">
            <h2 class="section-title"><i class="fas fa-filter"></i> Filtres de recherche</h2>
            <div class="filters">
                <form method="get">
                    <div class="filter-group">
                        <label><i class="fas fa-info-circle"></i> Statut:</label>
                        <select id="statut" name="statut">
                            <option value="">Tous</option>
                            <option value="confirmé" <?php if ($statut_filter == 'confirmé') echo 'selected'; ?>>Confirmé</option>
                            <option value="annulé" <?php if ($statut_filter == 'annulé') echo 'selected'; ?>>Annulé</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-tag"></i> Type de terrain:</label>
                        <select id="type" name="type">
                            <option value="">Tous</option>
                            <?php foreach ($types as $type): ?>
                                <option value="<?php echo $type; ?>" <?php if ($type_filter == $type) echo 'selected'; ?>>
                                    <?php echo ucfirst(htmlspecialchars($type)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-day"></i> Date début:</label>
                        <input type="date" id="date_debut" name="date_debut" value="<?php echo $date_debut; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-day"></i> Date fin:</label>
                        <input type="date" id="date_fin" name="date_fin" value="<?php echo $date_fin; ?>">
                    </div>
                    
                    <button type="submit" class="btn"><i class="fas fa-filter"></i> Appliquer les filtres</button>
                    <a href="historique.php" class="btn btn-secondary" style="margin-left: 10px;">
                        <i class="fas fa-sync"></i> Réinitialiser
                    </a>
                </form>
            </div>
        </div>
        
        <div class="section">
            <h2 class="section-title"><i class="fas fa-list"></i> Liste des réservations</h2>
            
            <?php if (count($reservations) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Terrain</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Montant (DT)</th>
                            <th>Paiement</th>
                            <th>Statut</th>
                            <th>État du terrain</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($reservation['terrain_nom']); ?></td>
                                <td><?php echo ucfirst(htmlspecialchars($reservation['terrain_type'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($reservation['date'])); ?></td>
                                <td><?php echo substr($reservation['heure_debut'], 0, 5) . ' - ' . substr($reservation['heure_fin'], 0, 5); ?></td>
                                <td><?php echo number_format($reservation['montant_total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($reservation['paiement']); ?></td>
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
                                <td>
                                    <?php
                                        echo ucfirst(htmlspecialchars($reservation['terrain_etat']));
                                        if ($reservation['statut'] == 'confirmé' && $reservation['terrain_etat'] != 'bon') {
                                            echo ' <span class="warning"><i class="fas fa-exclamation-triangle"></i> Risque d\'annulation</span>';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="section">
                    <p>Aucune réservation trouvée avec les critères sélectionnés.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>