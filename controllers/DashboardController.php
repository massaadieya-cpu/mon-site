<?php
/**
 * DashboardController.php — DWH RH · Altutex v12 FINAL FIXED
 * ✅ Paramètres filtres unifiés avec préfixe f_ : f_dept, f_contrat, f_genre, f_statut, f_mois_debut, f_mois_fin
 * ✅ Tous transmis au Model ET à la View de façon cohérente
 */
require_once __DIR__ . '/../models/DashboardModel.php';

class DashboardController
{
    private DashboardModel $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new DashboardModel($pdo);
    }

    /* ── Dispatch ─────────────────────────────────────────── */

    public function dispatch(): void
    {
        $routeAction = $_GET['action'] ?? 'dashboard';
        $action = $routeAction === 'reporting'
            ? ($_GET['reporting_view'] ?? 'dashboard')
            : $routeAction;
        $annee  = (int)($_GET['annee'] ?? date('Y'));

        $annees = $this->model->getAnnees();
        if (empty($annees)) $annees = [['annee' => date('Y')]];

        switch ($action) {
            case 'absenteisme': $this->actionAbsenteisme($annee, $annees); break;
            case 'turnover':    $this->actionTurnover($annee, $annees);    break;
            case 'formations':  $this->actionFormations($annee, $annees);  break;
            case 'salaires':    $this->actionSalaires($annee, $annees);    break;
            case 'employes':    $this->actionEmployes($annee, $annees);    break;
            default:            $this->actionDashboard($annee, $annees);   break;
        }
    }

    /* ── Helpers ──────────────────────────────────────────── */

    /**
     * Lit les filtres GET avec noms unifiés préfixe f_
     * Tous les filtres sont passés en paramètres au Model
     */
    private function buildFilterParams(): array
    {
        $moisUnique = (int)($_GET['f_mois'] ?? 0);
        if ($moisUnique >= 1 && $moisUnique <= 12) {
            $moisDebut = $moisUnique;
            $moisFin = $moisUnique;
        } else {
            $moisDebut = max(1, min(12, (int)($_GET['f_mois_debut'] ?? 1)));
            $moisFin   = max(1, min(12, (int)($_GET['f_mois_fin']   ?? 12)));
        }
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }

        return [
            'f_dept'      => trim($_GET['f_dept']      ?? ''),
            'f_contrat'   => trim($_GET['f_contrat']   ?? ''),
            'f_genre'     => '',
            'f_statut'    => '',
            'f_mois_debut'=> $moisDebut,
            'f_mois_fin'  => $moisFin,
        ];
    }

    /** Nombre de filtres actifs pour badge UI */
    private function countActiveFilters(array $fp): int
    {
        $n = 0;
        foreach (['f_dept','f_contrat'] as $k) {
            if (!empty($fp[$k])) $n++;
        }
        if ($fp['f_mois_debut'] > 1 || $fp['f_mois_fin'] < 12) $n++;
        return $n;
    }

    /** Listes pour les selects de filtrage (toutes les pages) */
    private function loadFilterLists(): array
    {
        return [
            'departements' => $this->model->getDepartements(),
            'contrats'     => $this->model->getTypesContrat(),
            'fonctions'    => $this->model->getFonctions(),
            'statuts'      => $this->model->getStatuts(),
        ];
    }

    /* ── DASHBOARD ───────────────────────────────────────── */

    private function actionDashboard(int $annee, array $annees): void
    {
        $fp     = $this->buildFilterParams();
        $params = array_merge(['annee' => $annee], $fp);

        $action         = 'dashboard';
        $filtres_actifs = $this->countActiveFilters($fp);
        $filtre_params  = $fp; // Transmis à la View pour pré-remplir les selects

        ['departements'=>$departements,'contrats'=>$contrats,'fonctions'=>$fonctions,'statuts'=>$statuts]
            = $this->loadFilterLists();

        $kpi_demo              = $this->model->getKpiDemo($params);
        $kpi_absences          = $this->model->getKpiAbsences($params);
        $kpi_turnover          = $this->model->getKpiTurnover($params);
        $effectif_dep          = $this->model->getEffectifParDept($params);
        $repartition_age       = $this->model->getRepartitionAge($params);
        $repartition_contrat   = $this->model->getRepartitionContrat($params);
        $genre_par_dept        = $this->model->getGenreParDept($params);
        $evolution_absences    = $this->model->getEvolutionAbsences($params);
        $evolution_turnover    = $this->model->getEvolutionTurnover($params);
        $distribution_anc_fine = $this->model->getDistributionAncienneteFine($params);
        $top5_fideles          = $this->model->getTop5Fideles($params);
        $par_mois_dash         = $this->model->getFormationsParMoisDashboard($params);
        $synthese_tendance_abs = $this->model->getSyntheseTendanceAbsences($params);
        $synthese_mensuelle_rh = $this->model->getSyntheseMensuelleRH($params);

        if (!defined('DWH_BOOT')) define('DWH_BOOT', true);
        require __DIR__ . '/../views/rh/reporting.php';
    }

    /* ── ABSENTÉISME ─────────────────────────────────────── */

    private function actionAbsenteisme(int $annee, array $annees): void
    {
        $fp     = $this->buildFilterParams();
        $params = array_merge(['annee' => $annee], $fp);

        $action         = 'absenteisme';
        $filtres_actifs = $this->countActiveFilters($fp);
        $filtre_params  = $fp;

        ['departements'=>$departements,'contrats'=>$contrats,'fonctions'=>$fonctions,'statuts'=>$statuts]
            = $this->loadFilterLists();

        $kpi                  = $this->model->getKpiAbsences($params);
        $evolution_mensuelle  = $this->model->getEvolutionAbsences($params);
        $evolution_abs_mobile = $this->model->getEvolutionAbsencesAvecMoyenneMobile($params);
        $par_type             = $this->model->getAbsencesParType($params);
        $top_departements     = $this->model->getTopDepartements($params);
        $par_genre            = $this->model->getAbsencesParGenre($params);
        $top_absenteistes     = $this->model->getTopAbsenteistes($params);
        $abs_par_employe      = $this->model->getAbsenteismeParEmploye(
            array_merge($params, ['limit' => 15])
        );

        if (!defined('DWH_BOOT')) define('DWH_BOOT', true);
        require __DIR__ . '/../views/rh/reporting.php';
    }

    /* ── TURNOVER ────────────────────────────────────────── */

    private function actionTurnover(int $annee, array $annees): void
    {
        $fp     = $this->buildFilterParams();
        $params = array_merge(['annee' => $annee], $fp);

        $action         = 'turnover';
        $filtres_actifs = $this->countActiveFilters($fp);
        $filtre_params  = $fp;

        ['departements'=>$departements,'contrats'=>$contrats,'fonctions'=>$fonctions,'statuts'=>$statuts]
            = $this->loadFilterLists();

        $kpi                   = $this->model->getKpiTurnover($params);
        $evolution             = $this->model->getEvolutionTurnover($params);
        $par_departement       = $this->model->getTurnoverParDept($params);
        $par_contrat           = $this->model->getTurnoverParContrat($params);
        $synthese_mensuelle_rh = $this->model->getSyntheseMensuelleRH($params);

        if (!defined('DWH_BOOT')) define('DWH_BOOT', true);
        require __DIR__ . '/../views/rh/reporting.php';
    }

    /* ── FORMATIONS ──────────────────────────────────────── */

    private function actionFormations(int $annee, array $annees): void
    {
        $fp     = $this->buildFilterParams();
        $params = array_merge(['annee' => $annee], $fp);

        $action         = 'formations';
        $filtres_actifs = $this->countActiveFilters($fp);
        $filtre_params  = $fp;

        ['departements'=>$departements,'contrats'=>$contrats,'fonctions'=>$fonctions,'statuts'=>$statuts]
            = $this->loadFilterLists();

        $kpi                   = $this->model->getKpiFormations($params);
        $par_mois              = $this->model->getFormationsParMois($params);
        $par_departement       = $this->model->getFormationsParDept($params);
        $synthese_tendance_abs = $this->model->getSyntheseTendanceAbsences($params);

        if (!defined('DWH_BOOT')) define('DWH_BOOT', true);
        require __DIR__ . '/../views/rh/reporting.php';
    }

    /* ── SALAIRES & FIDÉLITÉ ─────────────────────────────── */

    private function actionSalaires(int $annee, array $annees): void
    {
        $fp     = $this->buildFilterParams();
        $params = array_merge(['annee' => $annee], $fp);

        $action         = 'salaires';
        $filtres_actifs = $this->countActiveFilters($fp);
        $filtre_params  = $fp;

        ['departements'=>$departements,'contrats'=>$contrats,'fonctions'=>$fonctions,'statuts'=>$statuts]
            = $this->loadFilterLists();

        $salaires_dep          = $this->model->getSalairesParDept($params);
        $anciennete_dep        = $this->model->getAncienneteParDept($params);
        $distribution_anc      = $this->model->getDistributionAnciennete($params);
        $distribution_anc_fine = $this->model->getDistributionAncienneteFine($params);
        $top_fideles           = $this->model->getTopFideles($params);
        $top5_fideles          = $this->model->getTop5Fideles($params);
        $top_fonctions         = $this->model->getTopFonctions($params);

        if (!defined('DWH_BOOT')) define('DWH_BOOT', true);
        require __DIR__ . '/../views/rh/reporting.php';
    }

    /* ── ANNUAIRE ────────────────────────────────────────── */

    private function actionEmployes(int $annee, array $annees): void
    {
        $fp = $this->buildFilterParams();

        // Annuaire a ses propres paramètres de filtre (compatibilité ancienne)
        $params = [
            'annee'        => $annee,
            'page'         => (int)($_GET['page']         ?? 1),
            'departement'  => $_GET['departement']         ?? $fp['f_dept'],
            'genre'        => $_GET['genre']               ?? $fp['f_genre'],
            'type_contrat' => $_GET['type_contrat']        ?? $fp['f_contrat'],
            'statut'       => $_GET['statut']              ?? $fp['f_statut'],
            // Aussi les f_ pour que buildFilters du model fonctionne
            'f_dept'       => $fp['f_dept'],
            'f_contrat'    => $fp['f_contrat'],
            'f_genre'      => $fp['f_genre'],
            'f_statut'     => $fp['f_statut'],
        ];

        $action         = 'employes';
        $filtres_actifs = $this->countActiveFilters($fp);
        $filtre_params  = $fp;

        $result      = $this->model->getEmployes($params);
        $employes    = $result['employes'];
        $total       = $result['total'];
        $page        = $result['page'];
        $per_page    = $result['per_page'];
        $total_pages = $result['total_pages'];

        $departements = $this->model->getDepartements();
        $contrats     = $this->model->getTypesContrat();
        $fonctions    = $this->model->getFonctions();
        $statuts      = $this->model->getStatuts();

        $filters = [
            'departement'  => $params['departement'],
            'genre'        => $params['genre'],
            'type_contrat' => $params['type_contrat'],
            'statut'       => $params['statut'],
        ];

        if (!defined('DWH_BOOT')) define('DWH_BOOT', true);
        require __DIR__ . '/../views/rh/reporting.php';
    }
}
