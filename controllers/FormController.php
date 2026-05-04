<?php
class FormController {
    public function index($pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titre = $_POST['form_title'] ?? 'Sans titre';
        $description = $_POST['form_desc'] ?? '';
        $questions_data = $_POST['questions'] ?? []; 

        try {
            // 1. On enregistre l'enquête
            $stmt = $pdo->prepare("INSERT INTO enquetes (titre, description, structure_json) VALUES (?, ?, ?)");
            $json_data = json_encode($questions_data, JSON_UNESCAPED_UNICODE);
            $stmt->execute([$titre, $description, $json_data]);

            // --- AJOUT : SYSTÈME DE NOTIFICATION ---
            // On prépare le message et l'URL vers la page des enquêtes
            $msg = "Nouvelle enquête disponible : " . $titre;
            $urlDestination = "index.php?action=enquetes";
            
            // On insère une notification pour tous les utilisateurs enregistrés
            $sqlNotif = "INSERT INTO notification (user_id, message, type, url, is_read, created_at) 
                         SELECT id, ?, 'enquete', ?, 0, NOW() FROM utilisateur";
            $stmtNotif = $pdo->prepare($sqlNotif);
            $stmtNotif->execute([$msg, $urlDestination]);
            // ---------------------------------------

            header('Location: index.php?action=dashboard&success=1');
            exit();
        } catch (Exception $e) {
            die("Erreur de création : " . $e->getMessage());
        }
    } else {
        require_once 'views/rh/create_form.php';
    }
}public function sauvegarderReponse($pdo) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enquete_id = $_POST['enquete_id'] ?? null;
        $user_id = $_SESSION['user_id'] ?? null; 
        // On récupère les réponses et on les encode proprement
        $reponses_json = json_encode($_POST['reponses'] ?? [], JSON_UNESCAPED_UNICODE);

        if (!$enquete_id || !$user_id) {
            die("Erreur : Données manquantes (ID enquête ou Session utilisateur).");
        }

        try {
            // Force PDO à afficher les erreurs si ce n'est pas déjà fait dans ton db.php
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $pdo->beginTransaction();

            // 1. Insertion dans enquetes_participation (Le verrou)
            // Utilise bien 'utilisateur_id' comme dans ta capture d'écran
            $stmt1 = $pdo->prepare("INSERT INTO enquete_participations (enquete_id, utilisateur_id, date_participation) VALUES (?, ?, NOW())");
            $stmt1->execute([$enquete_id, $user_id]);

            // 2. Insertion dans enquete_reponses (La donnée anonyme)
            $stmt2 = $pdo->prepare("INSERT INTO enquete_reponses (enquete_id, reponse_json) VALUES (?, ?)");
            $stmt2->execute([$enquete_id, $reponses_json]);

            $pdo->commit();
            
            // Redirection vers une page de succès ou dashboard
            header("Location: index.php?action=dashboard&voted=1");
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            // Si le code d'erreur est 23000, c'est que l'INDEX UNIQUE a bloqué le doublon
            if ($e->getCode() == 23000) {
                echo "<script>alert('Vous avez déjà répondu à cette enquête.'); window.location.href='index.php';</script>";
            } else {
                die("Erreur SQL : " . $e->getMessage());
            }
        }
    }
}
}