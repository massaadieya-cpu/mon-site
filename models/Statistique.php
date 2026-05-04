<?php

class Statistique {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les informations de base de l'enquête (Titre et Structure JSON)
     */
    public function getEnqueteInfo($id_enquete) {
        try {
            $sql = "SELECT titre, structure_json FROM enquetes WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id_enquete]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL getEnqueteInfo : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère toutes les réponses soumises (JSON)
     */
    public function getReponsesParEnquete($id_enquete) {
        try {
            // On récupère uniquement le JSON des réponses pour l'analyse
            $sql = "SELECT reponse_json FROM enquete_reponses WHERE enquete_id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id_enquete]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL getReponsesParEnquete : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Optionnel : Récupère les questions si elles sont stockées séparément
     */
    public function getQuestionsByEnquete($enquete_id) {
        try {
            $sql = "SELECT id, titre, type_question FROM questions WHERE enquete_id = :id ORDER BY id ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $enquete_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur SQL getQuestionsByEnquete : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Utile pour ton PFE : Compte le nombre exact de participants
     */
    public function compterParticipants($id_enquete) {
        $sql = "SELECT COUNT(*) as total FROM enquete_reponses WHERE enquete_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id_enquete]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res['total'] ?? 0;
    }
}