<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../config.php';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = intval($_GET['id']);
    
    try {
        switch ($action) {
            case 'bloquer':
                // Changer le statut en bloqué
                $stmt = $pdo->prepare("UPDATE joueur SET statut = 'bloqué' WHERE id = ?");
                $stmt->execute([$id]);
                
                // Annuler toutes les réservations confirmées du joueur
                $stmt = $pdo->prepare("UPDATE reservation SET statut = 'annulé' WHERE joueur_id = ? AND statut = 'confirmé'");
                $stmt->execute([$id]);
                
                // Compter le nombre de réservations annulées (pour information seulement)
                $nombreReservationsAnnulees = $stmt->rowCount();
                break;
                
            case 'activer':
                // Changer le statut en actif
                $stmt = $pdo->prepare("UPDATE joueur SET statut = 'actif' WHERE id = ?");
                $stmt->execute([$id]);
                break;
                
            case 'delete':
                // Supprimer le joueur (les réservations seront automatiquement supprimées grâce à ON DELETE CASCADE)
                $stmt = $pdo->prepare("DELETE FROM joueur WHERE id = ?");
                $stmt->execute([$id]);
                break;
                
            default:
                // Action non reconnue
                header("Location: dashboard.php");
                exit();
        }
        
        header("Location: dashboard.php?joueur_success=1");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la modification du joueur: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>