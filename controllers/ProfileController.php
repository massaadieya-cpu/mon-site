<?php
class ProfileController {

    /**
     * Récupère $pdo depuis la variable globale définie dans index.php
     */
    private function getPdo() {
        global $pdo;
        if (!$pdo) {
            die("Erreur : connexion base de données introuvable.");
        }
        return $pdo;
    }

    /**
     * Récupère le profil de l'employé connecté depuis information_employe
     * Compatible avec $_SESSION['user_id'] OU $_SESSION['matricule']
     */
    private function loadEmploye() {
        $pdo = $this->getPdo();

        if (!isset($_SESSION['user_id']) && !isset($_SESSION['matricule'])) {
            header("Location: index.php");
            exit();
        }

        try {
            if (isset($_SESSION['matricule'])) {
                $stmt = $pdo->prepare("
                    SELECT id, matricule, nom, prenom, genre, age,
                           departement, fonction, type_contrat,
                           date_entree, anciennete_ans, statut, created_at
                    FROM information_employe
                    WHERE matricule = ?
                    LIMIT 1
                ");
                $stmt->execute([$_SESSION['matricule']]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT id, matricule, nom, prenom, genre, age,
                           departement, fonction, type_contrat,
                           date_entree, anciennete_ans, statut, created_at
                    FROM information_employe
                    WHERE id = ?
                    LIMIT 1
                ");
                $stmt->execute([$_SESSION['user_id']]);
            }

            $employe = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$employe) {
                session_destroy();
                header("Location: index.php?error=session_invalide");
                exit();
            }

            return $employe;

        } catch (PDOException $e) {
            die("Erreur base de données : " . htmlspecialchars($e->getMessage()));
        }
    }

    /**
     * Affiche la page profil
     */
    public function showProfile() {
        $user    = $this->loadEmploye();
        $message = null;

        $path = __DIR__ . '/../views/rh/mon_profil.php';
        if (file_exists($path)) {
            require_once $path;
        } else {
            die("Erreur critique : La vue mon_profil.php est introuvable à : " . $path);
        }
    }

    /**
     * Traite le changement de mot de passe
     */
    public function changePassword($pdo) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->showProfile();
            return;
        }

        if (!isset($_SESSION['user_id']) && !isset($_SESSION['matricule'])) {
            header("Location: index.php");
            exit();
        }

        $message = null;

        $oldPass     = trim($_POST['old_password']     ?? '');
        $newPass     = trim($_POST['new_password']     ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');

        if (empty($oldPass) || empty($newPass) || empty($confirmPass)) {
            $message = ['type' => 'error', 'text' => 'Tous les champs sont obligatoires.'];

        } elseif ($newPass !== $confirmPass) {
            $message = ['type' => 'error', 'text' => 'Les nouveaux mots de passe ne correspondent pas.'];

        } elseif (strlen($newPass) < 8) {
            $message = ['type' => 'error', 'text' => 'Le mot de passe doit contenir au moins 8 caractères.'];

        } else {
            try {
                if (isset($_SESSION['user_id'])) {
                    $stmt = $pdo->prepare("SELECT id, mot_de_passe FROM utilisateur WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                } else {
                    $stmt = $pdo->prepare("SELECT id, mot_de_passe FROM utilisateur WHERE matricule = ?");
                    $stmt->execute([$_SESSION['matricule']]);
                }

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row && $row['mot_de_passe'] === md5($oldPass)) {
                    $hashed = md5($newPass);
                    $update = $pdo->prepare("UPDATE utilisateur SET mot_de_passe = ? WHERE id = ?");
                    $update->execute([$hashed, $row['id']]);
                    $message = ['type' => 'success', 'text' => 'Mot de passe mis à jour avec succès.'];
                } else {
                    $message = ['type' => 'error', 'text' => "L'ancien mot de passe saisi est incorrect."];
                }

            } catch (PDOException $e) {
                $message = ['type' => 'error', 'text' => 'Erreur technique : ' . htmlspecialchars($e->getMessage())];
            }
        }

        $user = $this->loadEmploye();

        $path = __DIR__ . '/../views/rh/mon_profil.php';
        if (file_exists($path)) {
            require_once $path;
        } else {
            die("Erreur critique : La vue mon_profil.php est introuvable.");
        }
    }
}