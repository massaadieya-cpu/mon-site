<?php
class StatistiqueController {
    private $pdo;
    private $model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        require_once 'models/Statistique.php';
        $this->model = new Statistique($this->pdo);
    }

    public function afficherRapport($id_enquete = null) {
        if (!$id_enquete) {
            header("Location: index.php?action=liste_enquetes");
            exit();
        }

        $enqueteInfo = $this->model->getEnqueteInfo($id_enquete);
        if (!$enqueteInfo) die("Enquête introuvable.");

        $questions = json_decode($enqueteInfo['structure_json'], true) ?? [];
        $reponsesRaw = $this->model->getReponsesParEnquete($id_enquete);
        
        $totalParticipants = count($reponsesRaw);
        $donneesRapport = $this->preparerDonneesRapport($reponsesRaw, $questions);
        
        $detailsQuestions = $donneesRapport['details_questions'];
        $suggestionsBrutes = $donneesRapport['corpus_ia'];
        $titreEnquete = $enqueteInfo['titre'];

        require_once 'views/rh/rapport.php';
    }

    public function genererAnalyse($id_enquete) {
        if (!$id_enquete) {
            header("Location: index.php?action=liste_enquetes");
            exit();
        }

        if (!$this->model) {
            $this->model = new Statistique($this->pdo);
        }

        $enqueteInfo = $this->model->getEnqueteInfo($id_enquete);
        $reponsesRaw = $this->model->getReponsesParEnquete($id_enquete);

        if (empty($reponsesRaw)) {
            die("<div style='padding:50px; text-align:center;'><h2>⚠️ Aucune donnée à analyser</h2><a href='index.php?action=stats&id=$id_enquete'>Retour</a></div>");
        }

        $questions = json_decode($enqueteInfo['structure_json'], true) ?? [];
        $donneesBrutes = $this->preparerDonneesRapport($reponsesRaw, $questions);
        
        // On envoie à l'IA uniquement les problèmes réels et les longs commentaires
        $payload = json_encode([
            "corpus" => array_values(array_unique($donneesBrutes['corpus_ia'])),
            "stats_faibles" => array_values(array_filter($donneesBrutes['details_questions'], function($q) {
                return $q['taux'] < 50; 
            }))
        ]);

        $ch = curl_init('http://127.0.0.1:5000/predict');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            die("<div style='padding:50px; text-align:center; font-family:sans-serif;'>
                    <h2 style='color:#dc3545;'>❌ Service IA (Python) déconnecté</h2>
                    <p>Veuillez lancer <b>python analytics_service.py</b> dans votre terminal.</p>
                    <a href='index.php?action=stats&id=$id_enquete'>Retour aux statistiques</a>
                </div>");
        }

        $donneesIA = json_decode($response, true);
        require_once 'views/rh/resultat_ia.php';
    } 

    private function preparerDonneesRapport($reponsesRaw, $questions) {
        $stats = [];
        $corpus = [];
        $details_questions = [];

        foreach ($reponsesRaw as $row) {
            $data = json_decode($row['reponse_json'], true);
            if (!$data) continue;
            foreach ($data as $q_idx => $rep) {
                $val = is_array($rep) ? ($rep['val'] ?? '') : $rep;
                $justif = is_array($rep) ? ($rep['justif'] ?? '') : '';
                
                if (!empty($val) && strlen($val) < 50) {
                    $stats[$q_idx][$val] = ($stats[$q_idx][$val] ?? 0) + 1;
                }

                // --- CORRECTION DÉTECTION FAUSSE ---
                // On n'ajoute au corpus IA que les textes porteurs de sens (> 15 caractères)
                // Cela évite que l'IA analyse les questions ou les "Oui/Non"
                if (!empty($justif) && strlen($justif) > 15) {
                    $corpus[] = $justif;
                }
                if (strlen($val) > 20) {
                    $corpus[] = $val;
                }
            }
        }

        foreach ($questions as $index => $q) {
            $q_id = (string)($q['id'] ?? $index);
            $q_stats = $stats[$q_id] ?? [];
            $totalQ = array_sum($q_stats);
            $titre = $q['label'] ?? $q['titre'] ?? "Question";

            $motsClesInverses = ['pression', 'stress', 'difficulté', 'problème', 'ضغط', 'صعوبة', 'conflit', 'fatigue'];
            $estInversee = false;
            foreach($motsClesInverses as $mot) {
                if(str_contains(mb_strtolower($titre), $mot)) { 
                    $estInversee = true; 
                    break; 
                }
            }

            $positifsCount = 0;
            foreach($q_stats as $reponse => $nombre) {
                if ($this->analyseSatisfactionIntelligente($reponse, $estInversee)) {
                    $positifsCount += $nombre;
                }
            }

            $taux = ($totalQ > 0) ? round(($positifsCount / $totalQ) * 100) : 0;
            $color = ($taux >= 70) ? '#198754' : ($taux >= 40 ? '#ffc107' : '#dc3545');

            $details_questions[$q_id] = [
                'titre'       => $titre,
                'total'       => $totalQ,
                'taux'        => $taux,
                'color'       => $color,
                'estInversee' => $estInversee,
                'stats'       => $q_stats 
            ];
        }

        return ['details_questions' => $details_questions, 'corpus_ia' => $corpus];
    }

    private function analyseSatisfactionIntelligente($texte, $estInversee = false) {
        $texte = mb_strtolower(trim((string)$texte));
        if (empty($texte)) return false;

        $positifs = ['oui', 'نعم', 'bien', 'bon', 'excellent', 'parfait', 'satisfait', 'موافق', 'ممتاز', 'j\'aime', 'vrai', 'صحيح'];
        $negatifs = ['non', 'لا', 'mauvais', 'pas', 'nul', 'difficile', 'ضعيف', 'سيء', 'faux', 'خطأ'];

        $estPositif = false;
        if (preg_match('/\d+/', $texte, $matches)) {
            $estPositif = ((int)$matches[0] >= 3);
        } else {
            foreach ($positifs as $p) {
                if (str_contains($texte, $p)) {
                    $estPositif = true;
                    foreach ($negatifs as $n) {
                        if (str_contains($texte, $n)) { $estPositif = false; break; }
                    }
                    break;
                }
            }
        }
        return $estInversee ? !$estPositif : $estPositif;
    }
}