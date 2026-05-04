<?php
class Conge {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Récupérer toutes les demandes pour le RH
    public function getAllConges() {
        // On récupère tout, trié par la demande la plus récente
        $stmt = $this->db->query("SELECT * FROM demandes_conge ORDER BY date_demande DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mettre à jour le statut (validé / refusé)
    public function updateStatus($id, $status) {
        // IMPORTANT : On ajoute date_validation = NOW() pour savoir QUAND le RH a cliqué
        $stmt = $this->db->prepare("UPDATE demandes_conge SET statut = ?, date_validation = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}