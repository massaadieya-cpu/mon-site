<?php
class AbsenceModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function existeDeja($user_id, $date) {
        // Correction des noms de colonnes : user_id et date_abs
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM absences WHERE user_id = ? AND date_abs = ?");
        $stmt->execute([$user_id, $date]);
        return $stmt->fetchColumn() > 0;
    }

    public function getDatesDeclarees($user_id) {
        // Correction : utiliser $this->pdo au lieu de $this->db
        $sql = "SELECT DISTINCT date_abs FROM absences WHERE user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function enregistrerAbsence($user_id, $date, $type, $heure_seance, $motif, $remarque) {
        // Correction : utiliser $this->pdo au lieu de $this->db
        $sql = "INSERT INTO absences (user_id, date_abs, type_declaration, heure_seance, motif, remarque) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$user_id, $date, $type, $heure_seance, $motif, $remarque]);
    }
}