<?php
require_once 'models/Formation.php';

class FormationController {
    public function index($db) {
        $model = new Formation($db);
        
        // --- 0. RÉCUPÉRATION DU RÔLE (IMPORTANT) ---
        // On vérifie si l'utilisateur est RH ou Employé via la session
        $userRole = $_SESSION['role'] ?? 'employe'; 

        // --- 1. TRAITEMENT DES ACTIONS (RÉSERVÉ AU RH) ---
        if ($userRole === 'rh') {
            if (isset($_POST['envoyer'])) {
                $id = $_POST['id_formation'] ?? '';
                $nom = $_POST['nom_formation'];
                $date = $_POST['date']; 
                $heure = $_POST['heure'] . ":" . $_POST['minute'] . ":00";
                
                if(empty($id)) {
                    $model->ajouter($nom, $date, $heure);
                } else {
                    $model->modifier($id, $nom, $date, $heure);
                }
                header("Location: index.php?action=planning&week=" . $date);
                exit();
            }

            if (isset($_GET['delete_id'])) {
                $model->supprimer($_GET['delete_id']);
                $redirectWeek = $_GET['week'] ?? date('Y-m-d');
                header("Location: index.php?action=planning&week=" . $redirectWeek);
                exit();
            }
        }

        // --- 2. CALCUL DE LA SEMAINE ---
        $targetDate = $_GET['week'] ?? date('Y-m-d');
        $mondayTime = strtotime('monday this week', strtotime($targetDate));
        
        if (date('N', strtotime($targetDate)) == 7) {
            $mondayTime = strtotime('-6 days', strtotime($targetDate));
        }

        $monday = date('Y-m-d', $mondayTime);
        $prevWeek = date('Y-m-d', strtotime('-7 days', $mondayTime));
        $nextWeek = date('Y-m-d', strtotime('+7 days', $mondayTime));

        // --- 3. RÉCUPÉRATION DES FORMATIONS ---
        $formationsByDate = [];
        // Récupère les noms, dates et heures depuis la table formations
        foreach ($model->getAll() as $f) {
            $formationsByDate[$f['date_formation']][] = $f;
        }

        // --- 4. REDIRECTION VERS LA BONNE VUE ---
        if ($userRole === 'rh') {
            // Vue avec formulaire d'ajout et boutons d'édition
            require_once 'views\rh\planning.php';
        } else {
            // Vue en lecture seule uniquement pour l'employé
            require_once 'views\employe\planning_employe.php';
        }
    }
}