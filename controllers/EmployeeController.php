<?php
require_once 'models/EmployeeDocument.php';

class EmployeeController {

    /**
     * Espace Personnel de l'Employé
     * Action : index.php?action=mes_documents
     */
    public function showMyDocuments($pdo) {
        // 1. Sécurité : On vérifie la session
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['matricule'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $matricule = $_SESSION['matricule'];

        // 2. Récupération des documents (Perso + Généraux) via le Model
        $model = new EmployeeDocument($pdo);
        $documents = $model->getMyDocuments($matricule);

        // 3. Affichage de la vue employé
        // Vérifie bien que le dossier est 'views/' et non 'views/employe/' si tu as tout mis à la racine
        require_once 'views/employe/employe_documents.php';
    }

    /**
     * Confirmation de lecture (Bonus pour le rapport PFE)
     */
    public function markAsRead($pdo) {
        if (isset($_GET['doc_type'])) {
            // Logique optionnelle : enregistrer en BDD que le doc est lu
            header("Location: index.php?action=mes_documents&status=read");
            exit();
        }
    }
}