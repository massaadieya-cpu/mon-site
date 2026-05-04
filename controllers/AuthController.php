<?php
class AuthController {
    public function login($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login_input = trim($_POST['login'] ?? ''); 
            $password = $_POST['password'] ?? ''; 

            // 1. Chercher l'utilisateur d'abord par son matricule
            $sql = "SELECT * FROM utilisateur WHERE matricule = :matricule";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['matricule' => $login_input]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $now = new DateTime();

                // 2. Vérifier si le compte est actuellement bloqué
                if ($user['tentatives_echouees'] >= 3) {
                    $last_fail = new DateTime($user['dernier_echec']);
                    $interval = $now->diff($last_fail);
                    
                    // Si l'échec date de moins de 2 minutes
                    if ($interval->i < 2 && $interval->y == 0 && $interval->d == 0) {
                        header("Location: index.php?action=login&error=locked");
                        exit("Wait 2 minutes");
                    } else {
                        // Le délai est passé, on réinitialise pour laisser une chance
                        $this->resetAttempts($pdo, $user['id']);
                        $user['tentatives_echouees'] = 0; 
                    }
                }

                // 3. Vérification du mot de passe (Utilise password_verify si possible !)
                // Ici je garde md5 pour ton exemple, mais pense à changer pour password_verify
                if (md5($password) === $user['mot_de_passe']) {
                    
                    // Succès : Réinitialiser les tentatives
                    $this->resetAttempts($pdo, $user['id']);

                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['matricule'] = $user['matricule'];
                    $_SESSION['role']      = strtolower($user['role']);

                    header("Location: index.php?action=dashboard");
                    exit();
                } else {
                    // Échec du mot de passe : Incrémenter le compteur
                    $this->incrementAttempts($pdo, $user['id']);
                    header("Location: index.php?action=login&error=1");
                    exit();
                }
            } else {
                // Utilisateur inexistant
                header("Location: index.php?action=login&error=1");
                exit();
            }
        }
    }

    private function incrementAttempts($pdo, $userId) {
        $sql = "UPDATE utilisateur SET tentatives_echouees = tentatives_echouees + 1, dernier_echec = NOW() WHERE id = :id";
        $pdo->prepare($sql)->execute(['id' => $userId]);
    }

    private function resetAttempts($pdo, $userId) {
        $sql = "UPDATE utilisateur SET tentatives_echouees = 0, dernier_echec = NULL WHERE id = :id";
        $pdo->prepare($sql)->execute(['id' => $userId]);
    }
}