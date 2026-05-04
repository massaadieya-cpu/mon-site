<?php
class Formulaire {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function enregistrerEtDiffuser($titre, $lien) {
        // Enregistrement dans l'historique
        $sql = "INSERT INTO questionnaires (titre, lien_google_form) VALUES (?, ?)";
        $this->db->prepare($sql)->execute([$titre, $lien]);

        // Compter les employés pour confirmer l'envoi
        $stmt = $this->db->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'employe'");
        return $stmt->fetchColumn();
    }
}