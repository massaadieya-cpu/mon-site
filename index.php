<?php
ob_start();
session_start();
require_once 'config.php';

// 1. Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=rh_altutex;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_dwh = new PDO("mysql:host=localhost;dbname=dwh_pfe", "root", "");
} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}

// 2. Vérification de l'authentification
$action = $_GET['action'] ?? 'dashboard';

if (!isset($_SESSION['user_id']) && $action !== 'auth_process') {
    require_once 'views/login.php'; 
    exit();
}

$notifCount = 0;
if (isset($_SESSION['user_id'])) {
    require_once 'controllers/NotificationController.php';
    $notifHelper = new NotificationController();
    // On utilise bien la table au singulier 'notification' via le controller
    $notifCount = $notifHelper->getCount($pdo);
}

// 3. Routage MVC
switch ($action) {
    
    case 'auth_process':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login($pdo);
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php");
        break;
    case 'dashboard':
        if (!isset($_SESSION['role'])) {
            header("Location: index.php");
            exit();
        }

        if ($_SESSION['role'] === 'rh') {
            // Assure-toi que ce chemin est EXACTEMENT celui de ton fichier
            require_once 'views/rh/dashboard.php'; 
        } else {
            // Logique pour l'employé
            require_once 'views/employe/dashboard_employe.php';
        }
        break;

    case 'autorisation':
        if ($_SESSION['role'] === 'rh') {
            require_once 'views/rh/validation_absences.php';
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

    case 'planning':
        require_once 'controllers/FormationController.php';
        $controller = new FormationController();
        $controller->index($pdo); 
        break;
    
    case 'formulaire': 
        if ($_SESSION['role'] === 'rh') {
            require_once 'controllers/FormController.php';
            $controller = new FormController();
            $controller->index($pdo); 
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

    case 'documents':
        require_once 'controllers/DocumentController.php';
        $controller = new DocumentController();
        if (isset($_GET['do']) && $_GET['do'] == 'upload') {
            $controller->upload($pdo);
        } else {
            $controller->index($pdo);
        }
        break;

    case 'declaration_absence': 
        $user_id = $_SESSION['user_id'] ?? 'EMP-01';
        $message_success = isset($_GET['status']) && $_GET['status'] == 'success';
        $message_error = isset($_GET['status']) && $_GET['status'] == 'exists';

        $monday = new DateTime('monday this week');
        $saturday = clone $monday;
        $saturday->modify('+5 days');
        $current_date = date('Y-m-d');

        $deja_declares = [];
        try {
            $check = $pdo->prepare("SELECT DISTINCT date_abs FROM absences WHERE user_id = ?");
            $check->execute([$user_id]);
            $deja_declares = $check->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) { }

        require_once 'views/employe/absence_form.php';
        break;

    case 'submit_absence': 
        require_once 'controllers/AbsenceController.php';
        $controller = new AbsenceController($pdo); 
        $controller->submit($pdo);
        break;

    case 'demande_conge':
        // CORRECTION : Ajout de l'extension .php
        require_once 'views/employe/demande_conge.php'; 
        break;

    case 'submit_conge':
        require_once 'controllers/CongeController.php';
        $controller = new CongeController($pdo); 
        $controller->submit(); 
        break;
    // Dans ton switch ($action)

  case 'update_conge_status':
    // Sécurité : Seul le RH peut valider/refuser
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'rh') {
        require_once 'controllers/CongeController.php';
        $controller = new CongeController($pdo);
        $controller->updateStatus();
    } else {
        // Redirection si l'utilisateur n'est pas RH
        header("Location: index.php?action=login");
        exit();
    }
    break;

   case 'mes_documents':
    // Utilise le matricule de celui qui est CONNECTÉ
    if (!isset($_SESSION['matricule'])) {
        header("Location: index.php?action=login");
        exit();
    }

    $matricule = $_SESSION['matricule'];

    require_once 'models/DocumentModel.php';
    $model = new DocumentModel($pdo);
    
    // Le Model va chercher UNIQUEMENT les docs de ce matricule
    $documents = $model->getMyDocuments($matricule); 

    require_once 'views/employe/employe_documents.php';
    break;

    case 'enquetes':
        $stmt = $pdo->query("SELECT * FROM enquetes ORDER BY date_creation DESC");
        $enquetes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once 'views/employe/liste_enquetes.php';
        break;

    case 'repondre_enquete':
        $id = $_GET['id'] ?? null;
        $stmt = $pdo->prepare("SELECT * FROM enquetes WHERE id = ?");
        $stmt->execute([$id]);
        $enquete = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$enquete) {
            header("Location: index.php?action=enquetes");
            exit();
        }
        require_once 'views/employe/repondre_enquete.php';
        break;

    case 'submit_reponse_anonyme':
        require_once 'controllers/FormController.php';
        $controller = new FormController();
        // On utilise la méthode du contrôleur qui contient les transactions et le verrou
        $controller->sauvegarderReponse($pdo); 
        break;
    
// ... tes autres cases (login, dashboard, etc.) ...

    case 'stats':
        if ($_SESSION['role'] === 'rh') {
            require_once 'models/Statistique.php';
            require_once 'controllers/StatistiqueController.php';
            $controller = new StatistiqueController($pdo);
            
            $id_enquete = $_GET['id'] ?? null;
            if (!$id_enquete) {
                $stmt = $pdo->query("SELECT id FROM enquetes ORDER BY id DESC LIMIT 1");
                $last = $stmt->fetch();
                $id_enquete = $last ? $last['id'] : 0;
            }
            $controller->afficherRapport($id_enquete);
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

    // --- AJOUTE CE BLOC ICI ---
   case 'analyser_ia':
    if ($_SESSION['role'] === 'rh') {
        require_once 'controllers/StatistiqueController.php'; // Important !
        $controller = new StatistiqueController($pdo);
        $id_enquete = $_GET['id'] ?? 0;
        $controller->genererAnalyse($id_enquete); // Cette ligne ne plantera plus
    }
    break;

    case 'notifications':
    require_once 'controllers/NotificationController.php';
    $controller = new NotificationController();
    $controller->index($pdo); // Appelle la méthode qui affiche la liste
    break;

   case 'supprimer_formation':
    $id = $_GET['id'];
    $formationModel->supprimer($id);
    header("Location: index.php?action=liste_formations"); // Redirection forcée pour voir le changement
    break;
    // Dans ton switch/case pour la modification
   case 'modifier_formation':
    $id = $_POST['id'];
    $nom = $_POST['nom_formation'];
    $date = $_POST['date_formation'];
    $heure = $_POST['heure_formation'];

    // On vide le tampon de sortie pour éviter l'erreur '<'
    ob_clean(); 

    if ($formationModel->modifier($id, $nom, $date, $heure)) {
        header("Location: index.php?action=liste_formations&status=updated");
        exit(); // TRÈS IMPORTANT : arrête le script après la redirection
    }
    break;
    case 'notif_open':
    require_once 'controllers/NotificationController.php';
    $controller = new NotificationController();
    // On passe $pdo et les paramètres GET
    $controller->markReadAndRedirect($pdo); 
    break;
  
    case 'notification_rh':
        if ($_SESSION['role'] === 'rh') {
            require_once 'controllers/NotificationController.php';
            $controller = new NotificationController($pdo);
            $controller->dashboardRh($pdo); // Cette méthode appelle votre vue Bootstrap
        } else {
            header("Location: index.php?action=dashboard");
        }
        break;

    // 2. Pour marquer comme lu quand le RH clique sur "Confirmer" ou "Réviser"
    case 'read_notif_rh':
        if ($_SESSION['role'] === 'rh') {
            require_once 'controllers/NotificationController.php';
            $controller = new NotificationController($pdo);
            $controller->markReadRh($pdo);
        }
        break;
    case 'mon_profil':
    require_once 'controllers/ProfileController.php';
    $controller = new ProfileController();
    $controller->showProfile();
    break;

   case 'update_password':
    require_once 'controllers/ProfileController.php';
    $controller = new ProfileController();
    $controller->changePassword($pdo); // On passe $pdo déjà défini en haut de l'index
    break;

   case 'reporting':
    require_once 'models/DashboardModel.php';      // ← modèle en premier
    require_once 'controllers/DashboardController.php';
    $controller = new DashboardController($pdo_dwh);
    $controller->dispatch();
    break;
}
    
