<?php
require_once 'models/AbsenceModel.php';
require_once 'models/NotificationModel.php';

class AbsenceController {
    private $model;
    private $notifModel;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AbsenceModel($pdo);
        $this->notifModel = new NotificationModel($pdo);
    }

    /**
     * Calcule le type et la plage horaire selon les séances sélectionnées pour un jour donné.
     * 1 séance = 1h30
     * Matin: 06:00-07:30 / 07:30-09:00 / 09:00-10:30 / 10:30-11:30(1h)
     * Après-midi: 12:00-13:30 / 13:30-15:00 / 15:00-16:00(1h)
     */
    private function calculerTypeEtHeure($seances_du_jour) {
        // Ordre chronologique des séances
        $ordre = [
            "06:00-07:30", "07:30-09:00", "09:00-10:30", "10:30-11:30",
            "12:00-13:30", "13:30-15:00", "15:00-16:00"
        ];

        // Heures de début/fin par séance
        $heures = [
            "06:00-07:30" => ["06:00", "07:30"],
            "07:30-09:00" => ["07:30", "09:00"],
            "09:00-10:30" => ["09:00", "10:30"],
            "10:30-11:30" => ["10:30", "11:30"],
            "12:00-13:30" => ["12:00", "13:30"],
            "13:30-15:00" => ["13:30", "15:00"],
            "15:00-16:00" => ["15:00", "16:00"],
        ];

        $nb = count($seances_du_jour);

        // Journée complète (toutes les séances ou toggle journée)
        if ($nb >= 7) {
            return ['journee', '06:00-16:00'];
        }

        // Demi-journée = exactement les 4 séances du matin
        $matin = ["06:00-07:30", "07:30-09:00", "09:00-10:30", "10:30-11:30"];
        $aprem = ["12:00-13:30", "13:30-15:00", "15:00-16:00"];

        $seances_triees = $seances_du_jour;
        usort($seances_triees, function($a, $b) use ($ordre) {
            return array_search($a, $ordre) - array_search($b, $ordre);
        });

        if ($nb === 4 && $seances_triees === $matin) {
            return ['demi_journee', '06:00-11:30'];
        }

        // Séances spécifiques : calculer de début à fin
        $debut = null;
        $fin = null;
        foreach ($seances_triees as $s) {
            if (isset($heures[$s])) {
                if ($debut === null) $debut = $heures[$s][0];
                $fin = $heures[$s][1];
            }
        }

        $plage = ($debut && $fin) ? "$debut-$fin" : implode(',', $seances_triees);
        return ['seances', $plage];
    }

    // Dans AbsenceController.php, modifiez la méthode submit()

public function submit() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_data'])) {
        $user_id = $_SESSION['matricule'] ?? $_SESSION['user_id'] ?? 'EMP-01';
        $seances = json_decode($_POST['selected_data'], true);
        
        if (empty($seances)) {
            header("Location: index.php?action=declaration_absence&status=empty");
            exit();
        }

        $par_date = [];
        foreach ($seances as $seance) {
            if (strpos($seance, '|') !== false) {
                list($date, $heure) = explode('|', $seance);
                $par_date[$date][] = $heure;
            }
        }

        // --- VERIFICATION DE DOUBLON ---
        foreach ($par_date as $date => $seances_du_jour) {
            // On vérifie si une absence existe déjà pour ce jour précis
            if ($this->model->existeDeja($user_id, $date)) {
                // Si trouvé, on redirige immédiatement avec le message d'erreur
                header("Location: index.php?action=declaration_absence&status=exists");
                exit();
            }
        }

        // --- ENREGISTREMENT (si aucun doublon n'a été trouvé) ---
        foreach ($par_date as $date => $seances_du_jour) {
            list($type, $heure_seance) = $this->calculerTypeEtHeure($seances_du_jour);
            
            $this->model->enregistrerAbsence($user_id, $date, $type, $heure_seance, $_POST['motif'], $_POST['remarque']);
            
            // Notification RH
            $this->notifModel->createForRh($user_id, "Nouvelle absence : $user_id le $date", 'absence');
        }

        header("Location: index.php?action=declaration_absence&status=success");
        exit();
    }
}

}