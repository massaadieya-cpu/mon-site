<?php
/**
 * DashboardModel.php — DWH RH · Altutex  v12 FINAL FIXED
 * ✅ Filtres dynamiques unifiés : dept, contrat, genre, statut, mois_debut, mois_fin
 * ✅ Noms de paramètres cohérents avec Controller et View
 */
class DashboardModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ── Helpers SQL ───────────────────────────────────────── */

    private function one(string $sql, array $p = []): ?array
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($p);
        return $s->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function all(string $sql, array $p = []): array
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($p);
        return $s->fetchAll(PDO::FETCH_ASSOC);
    }

    private function scalar(string $sql, array $p = []): mixed
    {
        $s = $this->pdo->prepare($sql);
        $s->execute($p);
        return $s->fetchColumn();
    }

    /**
     * buildFilters() — Génère clauses WHERE + paramètres.
     * NOMS UNIFIÉS : f_dept, f_contrat, f_genre, f_statut, f_mois_debut, f_mois_fin
     *
     * @param array  $p        Paramètres filtrés
     * @param string $eAlias   Alias dim_employe
     * @param string $tAlias   Alias dim_temps
     * @param bool   $withMois Appliquer filtre période
     */
    private function buildFilters(array $p, string $eAlias = 'e', string $tAlias = 't', bool $withMois = false): array
    {
        $clauses = [];
        $params  = [];

        if (!empty($p['f_dept'])) {
            $clauses[] = "{$eAlias}.departement = ?";
            $params[]  = $p['f_dept'];
        }
        if (!empty($p['f_contrat'])) {
            $clauses[] = "{$eAlias}.type_contrat = ?";
            $params[]  = $p['f_contrat'];
        }
        if (!empty($p['f_genre'])) {
            $clauses[] = "{$eAlias}.genre = ?";
            $params[]  = $p['f_genre'];
        }
        if (!empty($p['f_statut'])) {
            $clauses[] = "{$eAlias}.statut = ?";
            $params[]  = $p['f_statut'];
        }
        if ($withMois) {
            $debut = (int)($p['f_mois_debut'] ?? 1);
            $fin   = (int)($p['f_mois_fin']   ?? 12);
            if ($debut > $fin) {
                [$debut, $fin] = [$fin, $debut];
            }
            if ($debut > 1 || $fin < 12) {
                $clauses[] = "{$tAlias}.mois BETWEEN ? AND ?";
                $params[]  = $debut;
                $params[]  = $fin;
            }
        }

        return [$clauses, $params];
    }

    /* ════════════════════════════════════════════════════════
       ANNÉES DISPONIBLES
    ════════════════════════════════════════════════════════ */

    public function getAnnees(): array
    {
        $rows = $this->all("SELECT DISTINCT annee FROM dim_temps ORDER BY annee DESC LIMIT 10");
        if (!empty($rows)) return array_map(fn($r) => ['annee' => $r['annee']], $rows);
        $a = [];
        for ($y = (int)date('Y'); $y >= (int)date('Y') - 4; $y--) $a[] = ['annee' => $y];
        return $a;
    }

    /* ════════════════════════════════════════════════════════
       KPI DÉMOGRAPHIE
    ════════════════════════════════════════════════════════ */

    public function getKpiDemo(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);

        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';

        $eff  = (int)($this->scalar("SELECT COUNT(*) FROM dim_employe e WHERE 1=1{$ex}", $fp) ?? 0);
        $gs   = $this->all("SELECT e.genre, COUNT(*) AS nb FROM dim_employe e WHERE 1=1{$ex} GROUP BY e.genre", $fp);
        $nbF  = $nbH = 0;
        foreach ($gs as $g) {
            if ($g['genre'] === 'F') $nbF = (int)$g['nb'];
            else $nbH = (int)$g['nb'];
        }
        $pctF   = $eff > 0 ? round($nbF / $eff * 100, 1) : 0;
        $pctH   = $eff > 0 ? round($nbH / $eff * 100, 1) : 0;
        $nbDept = (int)($this->scalar("SELECT COUNT(DISTINCT e.departement) FROM dim_employe e WHERE 1=1{$ex}", $fp) ?? 0);
        $ancMoy = (float)($this->scalar("SELECT AVG(e.anciennete_ans) FROM dim_employe e WHERE 1=1{$ex}", $fp) ?? 0);

        $perf = (float)($this->scalar("
            SELECT AVG(f.performance_employe) FROM fact_vue_globale_rh f
            JOIN dim_temps t ON f.id_temps = t.id_temps WHERE t.annee = ?
        ", [$annee]) ?? 0);

        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin'] ?? 12);
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }
        $totFormations = (int)($this->scalar(
            "SELECT COUNT(*) FROM dim_formation WHERE YEAR(date_formation) = ? AND MONTH(date_formation) BETWEEN ? AND ?",
            [$annee, $moisDebut, $moisFin]
        ) ?? 0);

        return [
            'effectif_total'      => $eff,
            'nb_femmes'           => $nbF,
            'nb_hommes'           => $nbH,
            'pct_femmes'          => $pctF,
            'pct_hommes'          => $pctH,
            'nb_departements'     => $nbDept,
            'anciennete_moyenne'  => round($ancMoy, 1),
            'performance_moyenne' => round($perf, 1),
            'total_formations'    => $totFormations,
        ];
    }

    /* ════════════════════════════════════════════════════════
       KPI ABSENCES
    ════════════════════════════════════════════════════════ */

    public function getKpiAbsences(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';

        $row = $this->one("
            SELECT SUM(f.nombre_absences_total)   AS total_absences,
                   SUM(f.nb_employes_absents)      AS employes_absents,
                   AVG(f.taux_absenteisme_mensuel) AS taux_absenteisme_moyen
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
        ", array_merge([$annee], $fp)) ?? [];

        return [
            'total_absences'         => (int)($row['total_absences']            ?? 0),
            'employes_absents'       => (int)($row['employes_absents']           ?? 0),
            'taux_absenteisme_moyen' => round((float)($row['taux_absenteisme_moyen'] ?? 0), 2),
        ];
    }

    /* ════════════════════════════════════════════════════════
       KPI TURNOVER
    ════════════════════════════════════════════════════════ */

    public function getKpiTurnover(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';

        $row = $this->one("
            SELECT SUM(f.nombre_sorties_total) AS total_sorties,
                   SUM(f.nombre_demissions)    AS demissions,
                   SUM(f.nombre_fins_contrat)  AS fins_contrat,
                   AVG(f.taux_depart)          AS taux_depart_moyen
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
        ", array_merge([$annee], $fp)) ?? [];

        $tot = (int)($row['total_sorties'] ?? 0);
        $dem = (int)($row['demissions']    ?? 0);
        $fc  = (int)($row['fins_contrat']  ?? 0);

        return [
            'total_sorties'     => $tot,
            'demissions'        => $dem,
            'fins_contrat'      => $fc,
            'taux_depart_moyen' => round((float)($row['taux_depart_moyen'] ?? 0), 2),
            'pct_demissions'    => $tot > 0 ? round($dem / $tot * 100, 1) : 0,
        ];
    }

    /* ════════════════════════════════════════════════════════
       EFFECTIF PAR DÉPARTEMENT
    ════════════════════════════════════════════════════════ */

    public function getEffectifParDept(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.departement, COUNT(*) AS effectif
            FROM dim_employe e WHERE 1=1{$ex}
            GROUP BY e.departement ORDER BY effectif DESC
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       RÉPARTITION ÂGE
    ════════════════════════════════════════════════════════ */

    public function getRepartitionAge(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.tranche_age AS tranche,
                   SUM(e.genre='F') AS femmes,
                   SUM(e.genre='M') AS hommes,
                   COUNT(*) AS total
            FROM dim_employe e WHERE 1=1{$ex}
            GROUP BY e.tranche_age ORDER BY MIN(e.age)
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       RÉPARTITION CONTRAT
    ════════════════════════════════════════════════════════ */

    public function getRepartitionContrat(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.type_contrat AS contrat, COUNT(*) AS effectif
            FROM dim_employe e WHERE 1=1{$ex}
            GROUP BY e.type_contrat ORDER BY effectif DESC
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       ÉVOLUTION ABSENCES (barres uniquement — sans moyenne)
    ════════════════════════════════════════════════════════ */

    public function getEvolutionAbsences(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';

        $mL = $this->getMoisLabels();
        $rows = $this->all("
            SELECT t.mois AS mois_num,
                   SUM(f.nombre_absences_total)    AS nb_absences,
                   SUM(f.nb_employes_absents)       AS employes_absents,
                   AVG(f.taux_absenteisme_mensuel)  AS taux_mensuel
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY t.mois ORDER BY t.mois
        ", array_merge([$annee], $fp));

        $idx = [];
        foreach ($rows as $r) $idx[(int)$r['mois_num']] = $r;

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'mois_num'        => $m,
                'mois_label'      => $mL[$m],
                'nb_absences'     => (int)($idx[$m]['nb_absences']     ?? 0),
                'employes_absents'=> (int)($idx[$m]['employes_absents'] ?? 0),
                'taux_mensuel'    => round((float)($idx[$m]['taux_mensuel'] ?? 0), 2),
            ];
        }
        return $result;
    }

    /* Alias pour compatibilité page absentéisme */
    public function getEvolutionAbsencesAvecMoyenneMobile(array $p): array
    {
        return $this->getEvolutionAbsences($p); // Plus de moyenne mobile
    }

    /* ════════════════════════════════════════════════════════
       ÉVOLUTION TURNOVER
    ════════════════════════════════════════════════════════ */

    public function getEvolutionTurnover(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';

        $mL   = $this->getMoisLabels();
        $rows = $this->all("
            SELECT t.mois AS mois_num,
                   SUM(f.nombre_sorties_total) AS total_sorties,
                   SUM(f.nombre_demissions)    AS demissions,
                   SUM(f.nombre_fins_contrat)  AS fins_contrat
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY t.mois ORDER BY t.mois
        ", array_merge([$annee], $fp));

        $idx = [];
        foreach ($rows as $r) $idx[(int)$r['mois_num']] = $r;

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'mois_num'      => $m,
                'mois_label'    => $mL[$m],
                'total_sorties' => (int)($idx[$m]['total_sorties'] ?? 0),
                'demissions'    => (int)($idx[$m]['demissions']    ?? 0),
                'fins_contrat'  => (int)($idx[$m]['fins_contrat']  ?? 0),
            ];
        }
        return $result;
    }

    /* ════════════════════════════════════════════════════════
       ABSENCES PAR TYPE / MOTIF
    ════════════════════════════════════════════════════════ */

    public function getAbsencesParType(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT COALESCE(a.motif,'Non renseigné') AS motif,
                   a.type_declaration AS type_abs, COUNT(*) AS nb
            FROM fact_mouvements_rh f
            JOIN dim_absence a ON f.id_absence  = a.id_absence
            JOIN dim_employe e ON f.id_employe  = e.id_employe
            JOIN dim_temps   t ON f.id_temps    = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY a.motif, a.type_declaration ORDER BY nb DESC
        ", array_merge([$annee], $fp));
    }

    /* ════════════════════════════════════════════════════════
       TOP DÉPARTEMENTS ABSENCES
    ════════════════════════════════════════════════════════ */

    public function getTopDepartements(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.departement,
                   SUM(f.nombre_absences_total) AS nb_absences,
                   SUM(f.nb_employes_absents)   AS employes_absents
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY e.departement ORDER BY nb_absences DESC LIMIT 10
        ", array_merge([$annee], $fp));
    }

    /* ════════════════════════════════════════════════════════
       ABSENCES PAR GENRE
    ════════════════════════════════════════════════════════ */

    public function getAbsencesParGenre(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.genre,
                   SUM(f.nombre_absences_total) AS nb_absences,
                   SUM(f.nb_employes_absents)   AS employes_absents
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex} GROUP BY e.genre
        ", array_merge([$annee], $fp));
    }

    /* ════════════════════════════════════════════════════════
       TOP ABSENTÉISTES
    ════════════════════════════════════════════════════════ */

    public function getTopAbsenteistes(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.matricule, e.nom, e.prenom, e.departement,
                   SUM(f.nombre_absences_total) AS nb_absences
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY e.id_employe, e.matricule, e.nom, e.prenom, e.departement
            ORDER BY nb_absences DESC LIMIT 10
        ", array_merge([$annee], $fp));
    }

    /* ════════════════════════════════════════════════════════
       ABSENTÉISME PAR EMPLOYÉ (paginé, période filtrée)
    ════════════════════════════════════════════════════════ */

    public function getAbsenteismeParEmploye(array $p): array
    {
        $annee     = (int)$p['annee'];
        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin']   ?? 12);
        $limit     = (int)($p['limit']         ?? 15);

        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';

        return $this->all("
            SELECT e.matricule, CONCAT(e.nom,' ',e.prenom) AS employe,
                   e.departement,
                   SUM(f.nombre_absences_total)    AS nb_absences,
                   AVG(f.taux_absenteisme_mensuel) AS taux_moyen,
                   COUNT(DISTINCT t.mois)           AS mois_touches
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ? AND t.mois BETWEEN ? AND ?{$ex}
            GROUP BY e.id_employe, e.matricule, e.nom, e.prenom, e.departement
            HAVING nb_absences > 0
            ORDER BY nb_absences DESC LIMIT ?
        ", array_merge([$annee, $moisDebut, $moisFin], $fp, [$limit]));
    }

    /* ════════════════════════════════════════════════════════
       DISTRIBUTION ANCIENNETÉ
    ════════════════════════════════════════════════════════ */

    public function getDistributionAncienneteFine(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT CASE
                WHEN e.anciennete_ans <= 2  THEN '0–2 ans'
                WHEN e.anciennete_ans <= 5  THEN '3–5 ans'
                WHEN e.anciennete_ans <= 10 THEN '6–10 ans'
                ELSE '+10 ans' END AS tranche,
                COUNT(*) AS effectif,
                MIN(e.anciennete_ans) AS anc_min_tranche
            FROM dim_employe e
            WHERE e.anciennete_ans IS NOT NULL{$ex}
            GROUP BY tranche ORDER BY MIN(e.anciennete_ans)
        ", $fp);
    }

    public function getDistributionAnciennete(array $p): array
    {
        return $this->getDistributionAncienneteFine($p);
    }

    /* ════════════════════════════════════════════════════════
       TOP 5 FIDÈLES
    ════════════════════════════════════════════════════════ */

    public function getTop5Fideles(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.matricule, e.nom, e.prenom, e.departement,
                   e.fonction, e.anciennete_ans, e.date_entree, e.salaire_net
            FROM dim_employe e
            WHERE e.anciennete_ans IS NOT NULL{$ex}
            ORDER BY e.anciennete_ans DESC LIMIT 5
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       SYNTHÈSE TENDANCE ABSENCES
    ════════════════════════════════════════════════════════ */

    public function getSyntheseTendanceAbsences(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        $mL = $this->getMoisLabels();
        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin'] ?? 12);
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }

        $absences = $this->all("
            SELECT t.mois AS mois_num,
                   SUM(f.nombre_absences_total)    AS nb_absences,
                   SUM(f.nb_employes_absents)       AS nb_employes_absents,
                   AVG(f.taux_absenteisme_mensuel)  AS taux_moyen
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY t.mois ORDER BY t.mois
        ", array_merge([$annee], $fp));

        $deptMax = $this->all("
            SELECT t.mois AS mois_num, e.departement,
                   SUM(f.nombre_absences_total) AS nb
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?
            GROUP BY t.mois, e.departement ORDER BY t.mois, nb DESC
        ", [$annee]);

        $deptByMois = [];
        foreach ($deptMax as $d) {
            $m = (int)$d['mois_num'];
            if (!isset($deptByMois[$m])) $deptByMois[$m] = $d['departement'];
        }

        $absByMois = [];
        foreach ($absences as $a) $absByMois[(int)$a['mois_num']] = $a;

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $abs      = $absByMois[$m] ?? null;
            $result[] = [
                'mois_num'            => $m,
                'mois_label'          => $mL[$m],
                'nb_absences'         => (int)($abs['nb_absences']         ?? 0),
                'nb_employes_absents' => (int)($abs['nb_employes_absents'] ?? 0),
                'taux_moyen'          => round((float)($abs['taux_moyen']  ?? 0), 1),
                'dept_critique'       => $deptByMois[$m] ?? '—',
            ];
        }
        return $result;
    }

    /* ════════════════════════════════════════════════════════
       TURNOVER PAR DÉPARTEMENT
    ════════════════════════════════════════════════════════ */

    public function getTurnoverParDept(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.departement,
                   SUM(f.nombre_sorties_total) AS total_sorties,
                   SUM(f.nombre_demissions)    AS demissions
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY e.departement ORDER BY total_sorties DESC LIMIT 10
        ", array_merge([$annee], $fp));
    }

    /* ════════════════════════════════════════════════════════
       TURNOVER PAR CONTRAT
    ════════════════════════════════════════════════════════ */

    public function getTurnoverParContrat(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.type_contrat AS contrat,
                   SUM(f.nombre_sorties_total) AS total_sorties
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex}
            GROUP BY e.type_contrat ORDER BY total_sorties DESC
        ", array_merge([$annee], $fp));
    }

    /* ════════════════════════════════════════════════════════
       KPI FORMATIONS
    ════════════════════════════════════════════════════════ */

    public function getKpiFormations(array $p): array
    {
        $annee = (int)$p['annee'];
        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin'] ?? 12);
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }
        $total = (int)($this->scalar("SELECT COUNT(id_formation) FROM dim_formation WHERE YEAR(date_formation)=? AND MONTH(date_formation) BETWEEN ? AND ?", [$annee, $moisDebut, $moisFin]) ?? 0);
        $dist  = (int)($this->scalar("SELECT COUNT(DISTINCT nom_formation) FROM dim_formation WHERE YEAR(date_formation)=? AND MONTH(date_formation) BETWEEN ? AND ?", [$annee, $moisDebut, $moisFin]) ?? 0);
        return ['total_formations'=>$total,'employes_formes'=>0,'formations_distinctes'=>$dist,'taux_couverture'=>0];
    }

    /* ════════════════════════════════════════════════════════
       FORMATIONS PAR MOIS
    ════════════════════════════════════════════════════════ */

    public function getFormationsParMois(array $p): array
    {
        $annee = (int)$p['annee'];
        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin'] ?? 12);
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }
        $mL    = $this->getMoisLabels();
        $rows  = $this->all("
            SELECT MONTH(date_formation) AS mois_num,
                   COUNT(id_formation) AS nb_formations,
                   COUNT(DISTINCT nom_formation) AS formations_distinctes
            FROM dim_formation WHERE YEAR(date_formation)=? AND MONTH(date_formation) BETWEEN ? AND ?
            GROUP BY MONTH(date_formation) ORDER BY MONTH(date_formation)
        ", [$annee, $moisDebut, $moisFin]);

        $idx = [];
        foreach ($rows as $r) $idx[(int)$r['mois_num']] = $r;

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'mois_num'              => $m,
                'mois_label'            => $mL[$m],
                'nb_formations'         => (int)($idx[$m]['nb_formations']        ?? 0),
                'formations_distinctes' => (int)($idx[$m]['formations_distinctes'] ?? 0),
                'employes_formes'       => 0,
            ];
        }
        return $result;
    }

    public function getFormationsParMoisDashboard(array $p): array
    {
        return $this->getFormationsParMois($p);
    }

    public function getFormationsParDept(array $p): array
    {
        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin'] ?? 12);
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }
        return $this->all("
            SELECT nom_formation AS departement, COUNT(id_formation) AS nb_formations
            FROM dim_formation WHERE YEAR(date_formation)=? AND MONTH(date_formation) BETWEEN ? AND ?
            GROUP BY nom_formation ORDER BY nb_formations DESC LIMIT 10
        ", [(int)$p['annee'], $moisDebut, $moisFin]);
    }

    /* ════════════════════════════════════════════════════════
       SALAIRES PAR DÉPARTEMENT
    ════════════════════════════════════════════════════════ */

    public function getSalairesParDept(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.departement,
                   MIN(e.salaire_net) AS salaire_min,
                   AVG(e.salaire_net) AS salaire_moyen,
                   MAX(e.salaire_net) AS salaire_max,
                   COUNT(*) AS effectif
            FROM dim_employe e
            WHERE e.salaire_net IS NOT NULL{$ex}
            GROUP BY e.departement ORDER BY salaire_moyen DESC
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       ANCIENNETÉ PAR DÉPARTEMENT
    ════════════════════════════════════════════════════════ */

    public function getAncienneteParDept(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.departement,
                   AVG(e.anciennete_ans) AS anciennete_moyenne,
                   MAX(e.anciennete_ans) AS anciennete_max,
                   MIN(e.anciennete_ans) AS anciennete_min
            FROM dim_employe e
            WHERE e.anciennete_ans IS NOT NULL{$ex}
            GROUP BY e.departement ORDER BY anciennete_moyenne DESC
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       TOP FIDÈLES (top 10)
    ════════════════════════════════════════════════════════ */

    public function getTopFideles(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.matricule, e.nom, e.prenom, e.departement, e.fonction, e.anciennete_ans
            FROM dim_employe e
            WHERE e.anciennete_ans IS NOT NULL{$ex}
            ORDER BY e.anciennete_ans DESC LIMIT 10
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       TOP FONCTIONS
    ════════════════════════════════════════════════════════ */

    public function getTopFonctions(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.fonction, COUNT(*) AS effectif
            FROM dim_employe e
            WHERE e.fonction IS NOT NULL{$ex}
            GROUP BY e.fonction ORDER BY effectif DESC LIMIT 10
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       GENRE PAR DÉPARTEMENT
    ════════════════════════════════════════════════════════ */

    public function getGenreParDept(array $p): array
    {
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', false);
        if (empty($p['f_statut'])) { $cl[] = "e.statut = ?"; $fp[] = 'Actif'; }
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        return $this->all("
            SELECT e.departement,
                   SUM(e.genre='F') AS femmes,
                   SUM(e.genre='M') AS hommes,
                   COUNT(*) AS total
            FROM dim_employe e WHERE 1=1{$ex}
            GROUP BY e.departement ORDER BY total DESC
        ", $fp);
    }

    /* ════════════════════════════════════════════════════════
       SYNTHÈSE MENSUELLE RH
    ════════════════════════════════════════════════════════ */

    public function getSyntheseMensuelleRH(array $p): array
    {
        $annee = (int)$p['annee'];
        [$cl, $fp] = $this->buildFilters($p, 'e', 't', true);
        $ex = $cl ? ' AND ' . implode(' AND ', $cl) : '';
        $mL = $this->getMoisLabels();
        $moisDebut = (int)($p['f_mois_debut'] ?? 1);
        $moisFin   = (int)($p['f_mois_fin'] ?? 12);
        if ($moisDebut > $moisFin) {
            [$moisDebut, $moisFin] = [$moisFin, $moisDebut];
        }

        $sorties = $this->all("
            SELECT t.mois AS mois_num, SUM(f.nombre_sorties_total) AS nb_sorties
            FROM fact_mouvements_rh f
            JOIN dim_employe e ON f.id_employe = e.id_employe
            JOIN dim_temps   t ON f.id_temps   = t.id_temps
            WHERE t.annee = ?{$ex} GROUP BY t.mois ORDER BY t.mois
        ", array_merge([$annee], $fp));

        $formations = $this->all("
            SELECT MONTH(date_formation) AS mois_num, COUNT(id_formation) AS nb_formations
            FROM dim_formation WHERE YEAR(date_formation)=? AND MONTH(date_formation) BETWEEN ? AND ?
            GROUP BY MONTH(date_formation) ORDER BY MONTH(date_formation)
        ", [$annee, $moisDebut, $moisFin]);

        $sIdx = [];
        foreach ($sorties as $r) $sIdx[(int)$r['mois_num']] = (int)$r['nb_sorties'];
        $fIdx = [];
        foreach ($formations as $r) $fIdx[(int)$r['mois_num']] = (int)$r['nb_formations'];

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $nb     = $sIdx[$m] ?? 0;
            $vals   = [];
            for ($j = max(1, $m - 2); $j <= $m; $j++) $vals[] = $sIdx[$j] ?? 0;
            $result[] = [
                'mois_num'      => $m,
                'mois_label'    => $mL[$m],
                'nb_sorties'    => $nb,
                'nb_formations' => $fIdx[$m] ?? 0,
                'tendance'      => round(array_sum($vals) / count($vals), 1),
            ];
        }
        return $result;
    }

    /* ════════════════════════════════════════════════════════
       ANNUAIRE EMPLOYÉS (paginé)
    ════════════════════════════════════════════════════════ */

    public function getEmployes(array $p): array
    {
        $perPage = 20;
        $page    = max(1, (int)($p['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $where  = ["1=1"];
        $params = [];

        // Annuaire utilise ses propres params (non préfixés f_)
        if (!empty($p['departement']))  { $where[] = 'e.departement = ?';  $params[] = $p['departement']; }
        if (!empty($p['genre']))        { $where[] = 'e.genre = ?';        $params[] = $p['genre']; }
        if (!empty($p['type_contrat'])) { $where[] = 'e.type_contrat = ?'; $params[] = $p['type_contrat']; }
        if (!empty($p['statut']))       { $where[] = 'e.statut = ?';       $params[] = $p['statut']; }
        else                            { $where[] = "e.statut IN ('Actif','Inactif')"; }

        // Aussi les filtres globaux f_ si présents
        if (!empty($p['f_dept']))     { $where[] = 'e.departement = ?';  $params[] = $p['f_dept']; }
        if (!empty($p['f_contrat'])) { $where[] = 'e.type_contrat = ?'; $params[] = $p['f_contrat']; }
        if (!empty($p['f_genre']))   { $where[] = 'e.genre = ?';        $params[] = $p['f_genre']; }
        if (!empty($p['f_statut'])) { $where[] = 'e.statut = ?';        $params[] = $p['f_statut']; }

        $ws  = implode(' AND ', $where);
        $tot = (int)($this->scalar("SELECT COUNT(*) FROM dim_employe e WHERE $ws", $params) ?? 0);
        $rows = $this->all("
            SELECT e.matricule, e.nom, e.prenom, e.genre, e.departement,
                   e.fonction, e.type_contrat, e.salaire_net, e.anciennete_ans, e.statut
            FROM dim_employe e WHERE $ws ORDER BY e.nom, e.prenom
            LIMIT $perPage OFFSET $offset
        ", $params);

        return [
            'employes'    => $rows,
            'total'       => $tot,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($tot / $perPage),
        ];
    }

    /* ── Listes filtres ──────────────────────────────────── */

    public function getDepartements(): array
    {
        return $this->all("SELECT DISTINCT departement AS nom_departement FROM dim_employe WHERE departement IS NOT NULL ORDER BY departement");
    }

    public function getTypesContrat(): array
    {
        return $this->all("SELECT DISTINCT type_contrat FROM dim_employe WHERE type_contrat IS NOT NULL ORDER BY type_contrat");
    }

    public function getFonctions(): array
    {
        return $this->all("SELECT DISTINCT fonction FROM dim_employe WHERE fonction IS NOT NULL ORDER BY fonction LIMIT 50");
    }

    public function getStatuts(): array
    {
        return $this->all("SELECT DISTINCT statut FROM dim_employe WHERE statut IS NOT NULL ORDER BY statut");
    }

    /* ── Helper labels mois ──────────────────────────────── */

    private function getMoisLabels(): array
    {
        return [
            1=>'Jan',2=>'Fév',3=>'Mar',4=>'Avr',5=>'Mai',6=>'Jun',
            7=>'Jul',8=>'Aoû',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Déc'
        ];
    }
}
