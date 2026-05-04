<?php
require_once 'models/DocumentModel.php';

class DocumentController {
    
    public function index($pdo) {
        $model = new DocumentModel($pdo);
        $employes = $model->getTousLesEmployes();
        include 'views/rh/rh_documents.php';
    }

    public function upload($pdo) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = new DocumentModel($pdo);
            $folder = 'uploads/documents/';
            if (!is_dir($folder)) mkdir($folder, 0777, true);

            if (isset($_POST['action_paie_auto'])) {
                $this->gestionPaieAutomatique($pdo, $model, $folder);
                exit();
            }

            // Remplace la partie "ENVOI MANUEL" dans ta fonction upload par ceci :

if (isset($_FILES['fichier_upload'])) {
    $diffuseATous = isset($_POST['diffuse_all']) ? 1 : 0;
    $nomFichierOriginal = $_FILES['fichier_upload']['name'];
    $nomDocumentAffichage = trim($_POST['nom_document']);
    $idDestinataire = $_POST['target_user_id'] ?? null;
    $forceUpload = isset($_POST['force_upload']) ? true : false; // Nouveau champ

    $matriculeDest = ($diffuseATous == 1) ? 'TOUS' : $this->getMatricule($pdo, $idDestinataire);

    // Vérification de doublon
    if ($diffuseATous == 0 && !$forceUpload) {
        // On cherche si le document existe et on récupère sa date
        $stmtCheck = $pdo->prepare("SELECT date_envoi FROM documents WHERE id_destinataire = ? AND nom_fichier = ? LIMIT 1");
        $stmtCheck->execute([$matriculeDest, $nomFichierOriginal]);
        $dejaEnvoye = $stmtCheck->fetch();

        if ($dejaEnvoye) {
            $dateE = date('d/m/Y à H:i', strtotime($dejaEnvoye['date_envoi']));
            // On redirige avec les infos du doublon
            header("Location: index.php?action=documents&error=confirm_duplicate&date=$dateE&filename=" . urlencode($nomFichierOriginal));
            exit();
        }
    }

    $nomPhysique = time() . '_' . $nomFichierOriginal;
    $chemin = $folder . $nomPhysique;

    if (move_uploaded_file($_FILES['fichier_upload']['tmp_name'], $chemin)) {
        $this->enregistrerDocument($pdo, $nomDocumentAffichage, $nomFichierOriginal, $chemin, $matriculeDest, $diffuseATous);
        $urlCible = "index.php?action=mes_documents"; 
        $this->notifier($pdo, $matriculeDest, "Nouveau document : $nomDocumentAffichage", 'document', $urlCible);
        header("Location: index.php?action=documents&status=success");
        exit();
    }
}
        }
    }

    private function gestionPaieAutomatique($pdo, $model, $folder) {
        // SQL mis à jour avec le bon nom de colonne : salaire_net
        $sql = "SELECT u.*, i.salaire_net 
                FROM utilisateur u 
                LEFT JOIN information_employe i ON u.matricule = i.matricule 
                WHERE u.role = 'employe'";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $employesCibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $count = 0;
        $moisAnnee = date('mY'); 
        $nomAffichagePaie = "Fiche de Paie - " . date('M Y');

        foreach ($employesCibles as $emp) {
            $matricule = $emp['matricule'];
            
            // Vérification sur salaire_net
            if (empty($emp['salaire_net'])) continue;

            $nomFichierPaie = "paie_" . $matricule . "_" . $moisAnnee . ".pdf";
            
            if ($model->documentExisteDeja($matricule, $nomFichierPaie)) {
                continue; 
            }

            $cheminFinal = $folder . $nomFichierPaie;

            $this->genererPDFPaieTunisienne($emp, $cheminFinal);
            $this->enregistrerDocument($pdo, $nomAffichagePaie, $nomFichierPaie, $cheminFinal, $matricule, 0);
            
            $urlPaie = "index.php?action=mes_documents"; 
            $this->notifier($pdo, $matricule, "Votre fiche de paie de " . date('F') . " est disponible.", 'paie', $urlPaie);
            
            $count++;
        }

        if ($count == 0) {
            header("Location: index.php?action=documents&error=duplicate");
        } else {
            header("Location: index.php?action=documents&status=paie_sent&count=$count");
        }
    }

    private function genererPDFPaieTunisienne($data, $cheminFinal) {
        if(file_exists('libs/fpdf.php')) {
            require_once 'libs/fpdf.php';
        } else {
            die("Erreur: Bibliothèque FPDF manquante");
        }

        $pdf = new FPDF();
        $pdf->AddPage();
        
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode("ALTUTEX - FICHE DE PAIE"), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 10, utf8_decode("Période : ") . date('m/Y'), 0, 1, 'C');
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(100, 7, utf8_decode("Employé : " . $data['nom'] . " " . $data['prenom']), 0, 0);
        $pdf->Cell(0, 7, utf8_decode("Matricule : " . $data['matricule']), 0, 1);
        $pdf->Ln(10);

        // Tableau simplifié
        $pdf->SetFillColor(230, 230, 230);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(80, 7, utf8_decode("Désignation"), 1, 0, 'C', true);
        $pdf->Cell(30, 7, "Base", 1, 0, 'C', true);
        $pdf->Cell(40, 7, "Retenues", 1, 0, 'C', true);
        $pdf->Cell(40, 7, "Gains", 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 10);
        
        $salaireNet = (float)$data['salaire_net']; 

        $pdf->Cell(80, 7, "Salaire Net", 1);
        $pdf->Cell(30, 7, "1.00", 1, 0, 'C');
        $pdf->Cell(40, 7, "-", 1, 0, 'C');
        $pdf->Cell(40, 7, number_format($salaireNet, 3), 1, 1, 'R');

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(150, 10, "NET A PAYER (TND) : ", 0, 0, 'R');
        $pdf->Cell(40, 10, number_format($salaireNet, 3), 1, 1, 'C');

        $pdf->Output('F', $cheminFinal);
    }

    private function notifier($pdo, $matricule, $message, $type, $url) {
        if ($matricule == 'TOUS') {
            $sql = "INSERT INTO notification (user_id, message, type, url, is_read, created_at) 
                    SELECT id, ?, ?, ?, 0, NOW() FROM utilisateur";
            $pdo->prepare($sql)->execute([$message, $type, $url]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM utilisateur WHERE matricule = ?");
            $stmt->execute([$matricule]);
            $userId = $stmt->fetchColumn();
            if ($userId) {
                $sql = "INSERT INTO notification (user_id, message, type, url, is_read, created_at) VALUES (?, ?, ?, ?, 0, NOW())";
                $pdo->prepare($sql)->execute([$userId, $message, $type, $url]);
            }
        }
    }

    private function enregistrerDocument($pdo, $nomAffiche, $nomFichier, $chemin, $dest, $all) {
        $ext = pathinfo($nomFichier, PATHINFO_EXTENSION);
        $sql = "INSERT INTO documents (nom_affichage, nom_fichier, chemin, type_fichier, id_destinataire, diffuse_a_tous, date_envoi) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        return $pdo->prepare($sql)->execute([$nomAffiche, $nomFichier, $chemin, $ext, $dest, $all]);
    }

    private function getMatricule($pdo, $id) {
        $stmt = $pdo->prepare("SELECT matricule FROM utilisateur WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() ?: null;
    }
}