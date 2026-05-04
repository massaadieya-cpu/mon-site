<?php
require_once 'models/NotificationModel.php';

class NotificationController {

    // --- PARTIE EMPLOYÉ (Gardée exactement selon votre demande) ---
    
    // Page liste des notifications
    public function index($pdo) {
        if (!isset($_SESSION['user_id'])) { header('Location: index.php?action=login'); exit(); }
        $model = new NotificationModel($pdo);
        $notifications = $model->getAllNotifications($_SESSION['user_id']);
        include 'views/employe/notifications.php';
    }

    // Action marquer comme lu
    public function read($pdo) {
        if (isset($_GET['id'])) {
            $model = new NotificationModel($pdo);
            $model->markAsRead($_GET['id']);
        }
        header('Location: index.php?action=notifications');
    }

    // Pour le Sidebar / Accueil
    public function dashboard($pdo) {
        $userId = $_SESSION['user_id'];
        // Requête sur la table au singulier
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        $notifCount = $stmt->fetchColumn();

        $nomEmploye = $_SESSION['nom'];
        include 'views/employe/dashboard.php';
    }

    public function getCount($pdo) {
        if (!isset($_SESSION['user_id'])) return 0;
        
        // On utilise la table au singulier comme tu l'as précisé
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$_SESSION['user_id']]);
        $count = $stmt->fetchColumn();
        
        return $count ? $count : 0;
    }

    public function markReadAndRedirect($pdo) {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            
            // 1. On marque comme lu en BDD
            $stmt = $pdo->prepare("UPDATE notification SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);

            // 2. On récupère l'URL de destination
            $url = isset($_GET['url']) ? $_GET['url'] : 'index.php?action=notifications';

            // 3. Redirection forcée
            header("Location: " . $url);
            exit();
        }
    }

    // --- NOUVELLE SECTION RH (Ajoutée pour l'interface Wow) ---

   public function dashboardRh($pdo) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'rh') {
        header('Location: index.php?action=login');
        exit();
    }

    // --- ÉTAPE CRUCIALE : On marque tout comme lu ---
    // C'est cette ligne qui fait que le +1 disparaît quand vous chargez la page
    $pdo->query("UPDATE notifications SET statut_lecture = 1 WHERE statut_lecture = 0");

    $model = new NotificationModel($pdo);
    
    // 1. On récupère toutes les notifications (Table: notifications)
    $allNotifs = $model->getNotificationsForRh();

    // 2. Séparation pour les deux colonnes de la vue Bootstrap
    $absences = array_filter($allNotifs, function($n) {
        return strpos(strtolower($n['type_notif']), 'absence') !== false;
    });

    $conges = array_filter($allNotifs, function($n) {
        return strpos(strtolower($n['type_notif']), 'conge') !== false;
    });

    // 3. On charge la vue moderne
    include 'views/rh/notifications.php';
}

    public function markReadRh($pdo) {
        if (isset($_GET['id'])) {
            $model = new NotificationModel($pdo);
            $model->markAsReadRh($_GET['id']);
        }
        // Redirige vers la vue des notifications RH après action
        header('Location: index.php?action=notification_rh');
        exit();
    }
}