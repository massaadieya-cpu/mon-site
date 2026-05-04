<?php
class DocumentModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Vérifie si l'employé a déjà reçu ce fichier exact
     * Utilisation de l'égalité stricte pour éviter les erreurs d'identification
     */
    public function documentExisteDeja($id_dest, $nom_f) {
        $sql = "SELECT COUNT(*) FROM documents 
                WHERE id_destinataire = ? 
                AND nom_fichier = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([trim($id_dest), $nom_f]);
        return $stmt->fetchColumn() > 0;
    }

    public function getTousLesEmployes() {
        return $this->pdo->query("SELECT id, matricule, nom, prenom FROM utilisateur WHERE role = 'employe'")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les documents d'un employé spécifique + les documents publics
     * CORRECTION : Suppression du LIKE pour éviter les doublons entre EMP-1 et EMP-10
     */
    public function getMyDocuments($matricule) {
    $sql = "SELECT * FROM documents 
            WHERE TRIM(id_destinataire) = :id 
            OR diffuse_a_tous = 1 
            ORDER BY date_envoi DESC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => trim($matricule)]); 
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}