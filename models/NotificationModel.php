<?php
class NotificationModel {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    /**
     * Pour le RH : créer une alerte (Générique) pour un employé
     * (Table : notification)
     */
    public function create($userId, $message, $type, $url = null, $linkedId = null) {
        $sql = "INSERT INTO notification (user_id, message, type, url, linked_id) VALUES (?, ?, ?, ?, ?)";
        return $this->pdo->prepare($sql)->execute([$userId, $message, $type, $url, $linkedId]);
    }

    /**
     * Pour l'Employé : compter les messages non lus
     */
    public function getCount($userId) {
        $sql = "SELECT COUNT(*) FROM notification WHERE user_id = ? AND is_read = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    /**
     * Pour l'Employé : voir ses messages non lus
     */
    public function getUnreadNotifications($userId) {
        $sql = "SELECT * FROM notification WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Historique complet pour l'employé
     */
    public function getAllNotifications($userId) {
        $sql = "SELECT * FROM notification WHERE user_id = ? ORDER BY created_at DESC LIMIT 20";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marque comme lu (Table employé)
     */
    public function markAsRead($id) {
        $sql = "UPDATE notification SET is_read = 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    // ============================================================
    // NOUVELLES FONCTIONNALITÉS : POUR LE RH (Table notifications)
    // ============================================================

    /**
     * Enregistrer une notification quand l'employé déclare une absence/congé
     */
    // Dans NotificationModel.php
    public function createForRh($matricule, $message, $type) {
    // On met 0 ici pour "Non lu"
        $sql = "INSERT INTO notifications (type_notif, matricule_user, message, statut_lecture, date_creation) 
            VALUES (?, ?, ?, 0, NOW())";
        $stmt = $this->pdo->prepare($sql);
       return $stmt->execute([$type, $matricule, $message]);
    }
    /**
     * Récupérer les notifications non lues pour le Dashboard RH
     */
    /**
 * Récupérer les notifications pour le Dashboard RH (Historique complet)
 * On a enlevé le WHERE statut_lecture = 0 pour que l'affichage ne soit pas vide
 */
public function getNotificationsForRh() {
    // On garde la jointure avec 'utilisateur' pour afficher le Nom et Prénom
    $sql = "SELECT n.*, u.nom, u.prenom 
            FROM notifications n 
            JOIN utilisateur u ON n.matricule_user = u.matricule 
            ORDER BY n.date_creation DESC";
            
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    /**
     * Compter les notifications RH (pour le badge sur votre image)
     */
    public function getRhCount() {
        $sql = "SELECT COUNT(*) FROM notifications WHERE statut_lecture = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * Marquer comme lu (Table RH)
     */
    public function markAsReadRh($id) {
        $sql = "UPDATE notifications SET statut_lecture = 1 WHERE id = ?";
        return $this->pdo->prepare($sql)->execute([$id]);
    }
   
}