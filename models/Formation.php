<?php
class Formation {
    private $db;
    public function __construct($db) { $this->db = $db; }

    // --- AJOUTER ---
    public function ajouter($nom, $date, $heure) {
        $sql = "INSERT INTO formations (nom_formation, date_formation, heure_formation) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([$nom, $date, $heure]);
        
        if ($success) {
            $lastId = $this->db->lastInsertId();
            $message = "Nouvelle formation : " . $nom;
            $url = "index.php?action=planning"; // L'URL vers laquelle l'employé sera redirigé

            // Utilisation d'une requête préparée pour la notification (SÉCURITÉ)
            $sqlNotif = "INSERT INTO notification (user_id, message, type, url, linked_id, is_read) 
                         SELECT id, ?, 'formation', ?, ?, 0 
                         FROM utilisateur WHERE role = 'employe'";
            
            $this->db->prepare($sqlNotif)->execute([$message, $url, $lastId]);
        }
        return $success;
    }

    // --- MODIFIER ---
    public function modifier($id, $nom, $date, $heure) {
        $sql = "UPDATE formations SET nom_formation = ?, date_formation = ?, heure_formation = ? WHERE id = ?";
        $success = $this->db->prepare($sql)->execute([$nom, $date, $heure, $id]);
        
        if ($success) {
            // On remet la notification en "non lu" (0) pour que les employés voient le changement
            $messageMAJ = "MAJ : La formation '$nom' a été modifiée";
            $sqlNotif = "UPDATE notification SET message = ?, is_read = 0 
                         WHERE linked_id = ? AND type = 'formation'";
            
            $this->db->prepare($sqlNotif)->execute([$messageMAJ, $id]);
        }
        return $success;
    }

    // --- SUPPRIMER ---
    public function supprimer($id) {
        // 1. On supprime proprement les notifications pour ne pas laisser de liens morts
        $this->db->prepare("DELETE FROM notification WHERE linked_id = ? AND type = 'formation'")->execute([$id]);
        
        // 2. On supprime la formation
        // Vérifie si ta colonne est 'id' ou 'id_formation'
        return $this->db->prepare("DELETE FROM formations WHERE id = ?")->execute([$id]);
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM formations ORDER BY date_formation ASC, heure_formation ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
}