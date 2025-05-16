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
            case 'annuler':
                // Changer le statut en annulé
                $stmt = $pdo->prepare("UPDATE reservation SET statut = 'annulé' WHERE id = ?");
                $stmt->execute([$id]);
                break;
                
            case 'delete':
                // Supprimer la réservation
                $stmt = $pdo->prepare("DELETE FROM reservation WHERE id = ?");
                $stmt->execute([$id]);
                break;
                
            default:
                // Action non reconnue
                header("Location: dashboard.php");
                exit();
        }
        
        header("Location: dashboard.php?reservation_success=1");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la modification de la réservation: " . $e->getMessage());
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>