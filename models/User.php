<?php
class User {
    private $db;

    public function __construct() {
        try {
            // Utilisation du nom de votre base : rh_altutex
            $this->db = new PDO('mysql:host=localhost;dbname=rh_altutex;charset=utf8', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            die('Erreur de connexion : ' . $e->getMessage());
        }
    }

    public function login($nom, $password) {
        // Recherche de l'utilisateur par nom
        $query = "SELECT * FROM utilisateur WHERE nom = :nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['nom' => $nom]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Puisque votre hachage est en MD5 (e10adc...), on compare en MD5
            // Note : Pour une sécurité "Wow" et professionnelle, il faudra passer à password_hash plus tard
            if (md5($password) === $user['mot_de_passe']) {
                return $user; 
            }
        }
        return false;
    }
}
?>