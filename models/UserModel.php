<?php
class UserModel {
    private $db;

    public function __construct() {
        try {
            // Nom de la base : rh_altutex | Table : utilisateur
            $this->db = new PDO('mysql:host=localhost;dbname=rh_altutex;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function getUserById($id) {
        // Table 'utilisateur' au singulier
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($id, $newHashedPassword) {
    // On utilise bien 'mot_de_passe' ici aussi
    $stmt = $this->db->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
    return $stmt->execute([$newHashedPassword, $id]);
}
}