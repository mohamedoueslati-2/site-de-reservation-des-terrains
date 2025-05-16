<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config.php';

// Traitement des actions pour les terrains
if (isset($_GET['action']) && $_GET['action'] == 'delete_terrain' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        // Récupérer l'image avant suppression
        $stmt = $pdo->prepare("SELECT images FROM terrain WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && !empty($row['images'])) {
            // Supprimer l'image du serveur si elle existe
            $image_path = "../uploads/terrains/" . $row['images'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Supprimer le terrain
        $stmt = $pdo->prepare("DELETE FROM terrain WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: dashboard.php?deleted=1");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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
            display: flex;
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

        /* Card Styles */
        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px;
            background-color: var(--white);
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-header h3 {
            color: var(--primary-color);
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 12px 15px;
            text-align: left;
        }

        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background-color: var(--gray);
        }

        /* Button Styles */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
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

        .btn-danger {
            background-color: var(--error-color);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        .btn-success {
            background-color: var(--success-color);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #07a05e;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }

        /* Image Styles */
        .terrain-img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
    <!-- You can add Font Awesome for icons if needed -->
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
                <a href="dashboard.php" class="nav-link active">
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
                <a href="add_reservation.php" class="nav-link">
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
            <h1>Tableau de bord Admin</h1>
            <a href="../logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
        
        <?php if(isset($_GET['success']) || isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Opération réussie !</div>
        <?php endif; ?>

        <!-- Terrains Section -->
        <div class="card">
            <div class="card-header">
                <h3>Gestion des terrains</h3>
                <a href="add_terrain.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un terrain
                </a>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>État</th>
                            <th>Tarif horaire (DT)</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM terrain ORDER BY id DESC");
                            $terrains = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($terrains) > 0) {
                                foreach ($terrains as $row) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['etat']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tarif_horaire']) . "</td>";
                                    echo "<td>";
                                    if (!empty($row['images'])) {
                                        echo "<img src='../uploads/terrains/" . htmlspecialchars($row['images']) . "' class='terrain-img'>";
                                    } else {
                                        echo "Aucune image";
                                    }
                                    echo "</td>";
                                    echo "<td>
                                        <a href='edit_terrain.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>
                                            <i class='fas fa-edit'></i> Modifier
                                        </a>
                                        <a href='dashboard.php?action=delete_terrain&id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce terrain ?\")'>
                                            <i class='fas fa-trash'></i> Supprimer
                                        </a>
                                    </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7'>Aucun terrain trouvé</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='7'>Erreur: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Réservations Section -->
        <div class="card">
            <div class="card-header">
                <h3>Gestion des réservations</h3>
                <a href="add_reservation.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter une réservation
                </a>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Joueur</th>
                            <th>Terrain</th>
                            <th>Date</th>
                            <th>Heure début</th>
                            <th>Heure fin</th>
                            <th>Paiement</th>
                            <th>Montant total (DT)</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT r.*, j.nom AS joueur_nom, t.nom AS terrain_nom 
                                                FROM reservation r
                                                LEFT JOIN joueur j ON r.joueur_id = j.id
                                                LEFT JOIN terrain t ON r.terrain_id = t.id
                                                ORDER BY r.date DESC, r.heure_debut DESC");
                            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($reservations) > 0) {
                                foreach ($reservations as $reservation) {
                                    echo "<tr>";
                                    echo "<td>" . $reservation['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['joueur_nom']) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['terrain_nom']) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['heure_debut']) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['heure_fin']) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['paiement']) . "</td>";
                                    echo "<td>" . htmlspecialchars(number_format($reservation['montant_total'], 2)) . "</td>";
                                    echo "<td>" . htmlspecialchars($reservation['statut']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='edit_reservation.php?id=" . $reservation['id'] . "' class='btn btn-primary btn-sm'>
                                            <i class='fas fa-edit'></i> Modifier
                                        </a>";
                                    
                                    if ($reservation['statut'] == 'confirmé') {
                                        echo "<a href='manage_reservation.php?action=annuler&id=" . $reservation['id'] . "' class='btn btn-warning btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir annuler cette réservation ?\")'>
                                                <i class='fas fa-times'></i> Annuler
                                            </a>";
                                    }
                                    
                                    echo "<a href='manage_reservation.php?action=delete&id=" . $reservation['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer cette réservation ?\")'>
                                            <i class='fas fa-trash'></i> Supprimer
                                        </a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10'>Aucune réservation trouvée</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='10'>Erreur: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Joueurs Section -->
        <div class="card">
            <div class="card-header">
                <h3>Gestion des joueurs</h3>
                <a href="add_joueur.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un joueur
                </a>
            </div>
            <div class="card-body">
                <?php if(isset($_GET['joueur_success'])): ?>
                    <div class="alert alert-success">Opération sur le joueur réussie !</div>
                <?php endif; ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Numéro de téléphone</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $pdo->query("SELECT * FROM joueur ORDER BY id DESC");
                            $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            if (count($joueurs) > 0) {
                                foreach ($joueurs as $joueur) {
                                    echo "<tr>";
                                    echo "<td>" . $joueur['id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($joueur['nom']) . "</td>";
                                    echo "<td>" . htmlspecialchars($joueur['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($joueur['num_telephone']) . "</td>";
                                    echo "<td>" . htmlspecialchars($joueur['statut']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='edit_joueur.php?id=" . $joueur['id'] . "' class='btn btn-primary btn-sm'>
                                            <i class='fas fa-edit'></i> Modifier
                                        </a>";
                                    
                                    if ($joueur['statut'] == 'actif') {
                                        echo "<a href='manage_joueur.php?action=bloquer&id=" . $joueur['id'] . "' class='btn btn-warning btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir bloquer ce joueur ?\")'>
                                                <i class='fas fa-ban'></i> Bloquer
                                            </a>";
                                    } else {
                                        echo "<a href='manage_joueur.php?action=activer&id=" . $joueur['id'] . "' class='btn btn-success btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir activer ce joueur ?\")'>
                                                <i class='fas fa-check'></i> Activer
                                            </a>";
                                    }
                                    
                                    echo "<a href='manage_joueur.php?action=delete&id=" . $joueur['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce joueur ?\")'>
                                            <i class='fas fa-trash'></i> Supprimer
                                        </a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>Aucun joueur trouvé</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6'>Erreur: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>