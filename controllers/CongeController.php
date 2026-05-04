<?php
class CongeController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. RÉCUPÉRATION DU MATRICULE
            $matricule = $_SESSION['matricule'] ?? ''; 
            if (empty($matricule) && isset($_SESSION['user_id'])) {
                $stmtUser = $this->pdo->prepare("SELECT matricule FROM utilisateur WHERE id = ?");
                $stmtUser->execute([$_SESSION['user_id']]);
                $matricule = $stmtUser->fetchColumn();
            }

            if (empty($matricule)) {
                die("Erreur : Matricule introuvable.");
            }
            
            // 2. DONNÉES ET DATES
            $date_debut = $_POST['date_debut'] ?? '';
            $date_fin   = $_POST['date_fin'] ?? '';
            $debut = new DateTime($date_debut);
            $fin   = new DateTime($date_fin);
            $aujourdhui = new DateTime();
            $aujourdhui->setTime(0, 0, 0); // On ignore l'heure

            // --- CONDITION A : Bloquer les jours passés ---
            if ($debut < $aujourdhui) {
                header("Location: index.php?action=demande_conge&error=date_passee");
                exit();
            }

            // --- CONDITION B : Durée Max 14 jours ---
            $intervalle = $debut->diff($fin);
            $nb_jours = $intervalle->days + 1; 
            if ($nb_jours > 14 || $fin < $debut) {
                header("Location: index.php?action=demande_conge&error=duree_max");
                exit();
            }

            // --- CONDITION C : Quota 2 fois par an ---
            $sql_count = "SELECT COUNT(*) FROM demandes_conge WHERE matricule = ? AND YEAR(date_debut) = YEAR(CURDATE())";
            $stmt = $this->pdo->prepare($sql_count);
            $stmt->execute([$matricule]);
            if ($stmt->fetchColumn() >= 2) {
                header("Location: index.php?action=demande_conge&error=quota_annuel");
                exit();
            }

            // 3. INSERTION
            $sql = "INSERT INTO demandes_conge (matricule, nom_complet, cin, poste, adresse, date_debut, date_fin, cause, statut) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en attente')";
            
            try {
                $stmt_insert = $this->pdo->prepare($sql);
                $stmt_insert->execute([
                    $matricule, 
                    $_POST['nom'], 
                    $_POST['cin'], 
                    $_POST['fonction'], 
                    $_POST['adresse'], 
                    $date_debut, 
                    $date_fin, 
                    $_POST['motif']
                ]);
                
               // ... après l'insertion de la demande de congé réussie ...

                require_once 'models/NotificationModel.php';
                   $notifModel = new NotificationModel($this->pdo);

                  $messageNotif = "Nouvelle demande de congé soumise par le matricule " . $matricule;

                  // On appelle avec : matricule, message, type
                   $notifModel->createForRh($matricule, $messageNotif, 'conge');
                
                header("Location: index.php?action=demande_conge&success=1");
                exit();
            } catch (PDOException $e) {
                die("Erreur SQL : " . $e->getMessage());
            }
        }
    }
       public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Correspondance avec les noms de vos modales (id_conge et decision)
            $id = $_POST['id_conge'] ?? null;
            $status = $_POST['decision'] ?? ''; 

            if ($id && !empty($status)) {
                require_once 'models/CongeModel.php';
                $model = new Conge($this->pdo);
                $model->updateStatus($id, $status);
            }
            
            header("Location: index.php?action=autorisation&tab=conges");
            exit();
        }
    }
}