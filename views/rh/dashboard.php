<?php
require_once 'models/NotificationModel.php';
$notifModel = new NotificationModel($pdo);
$countRh = $notifModel->getRhCount();

try {
    $kpi = $pdo->query("
        SELECT
            COUNT(*)                                                    AS total_employes,
            SUM(CASE WHEN statut = 'Actif'  THEN 1 ELSE 0 END)        AS actifs,
            SUM(CASE WHEN statut != 'Actif' THEN 1 ELSE 0 END)        AS inactifs,
            SUM(CASE WHEN genre = 'F'       THEN 1 ELSE 0 END)        AS femmes,
            SUM(CASE WHEN genre = 'M'       THEN 1 ELSE 0 END)        AS hommes,
            COUNT(DISTINCT departement)                                  AS nb_departements,
            ROUND(AVG(anciennete_ans), 1)                               AS anciennete_moy,
            SUM(CASE WHEN motif_sortie IN ('Démission','DEMISSION') THEN 1 ELSE 0 END) AS demissions
        FROM information_employe
    ")->fetch(PDO::FETCH_ASSOC);

    $absMonth = $pdo->query("
        SELECT COUNT(*) AS nb, COUNT(DISTINCT user_id) AS employes_absents
        FROM absences
        WHERE MONTH(date_abs)=MONTH(CURDATE()) AND YEAR(date_abs)=YEAR(CURDATE())
    ")->fetch(PDO::FETCH_ASSOC);

    $formTotal = $pdo->query("
        SELECT COUNT(*) AS nb FROM formations
    ")->fetch(PDO::FETCH_ASSOC);

    $formMois = $pdo->query("
        SELECT COUNT(*) AS nb FROM formations
        WHERE MONTH(date_formation)=MONTH(CURDATE()) AND YEAR(date_formation)=YEAR(CURDATE())
    ")->fetch(PDO::FETCH_ASSOC);

    $tauxAbs = ($kpi['actifs'] > 0)
        ? round(($absMonth['nb'] / $kpi['actifs']) * 100, 1)
        : 0;

    $tauxDepart = ($kpi['total_employes'] > 0)
        ? round(($kpi['inactifs'] / $kpi['total_employes']) * 100, 1)
        : 0;

} catch (Exception $e) {
    $kpi = ['total_employes'=>'—','actifs'=>'—','femmes'=>'—','hommes'=>'—',
            'nb_departements'=>'—','anciennete_moy'=>'—','demissions'=>'—','inactifs'=>'—'];
    $absMonth = ['nb'=>'—','employes_absents'=>'—'];
    $formTotal = ['nb'=>'—'];
    $formMois  = ['nb'=>'—'];
    $tauxAbs   = '—';
    $tauxDepart= '—';
}

$moisFr = ['Janvier','Février','Mars','Avril','Mai','Juin',
           'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$moisCourant = $moisFr[(int)date('n') - 1];

$pctF = ($kpi['total_employes'] > 0 && is_numeric($kpi['femmes']))
    ? round($kpi['femmes'] / $kpi['total_employes'] * 100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALTUTEX — Dashboard RH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy:      #0b1f3a;
            --navy-2:    #122444;
            --blue:      #1e6ff1;
            --blue-lt:   #e8f1fe;
            --teal:      #0ea5a0;
            --teal-lt:   #e6f8f7;
            --amber:     #f59e0b;
            --amber-lt:  #fff8e6;
            --rose:      #e5405e;
            --rose-lt:   #fdeef1;
            --violet:    #7c3aed;
            --violet-lt: #f3effe;
            --green:     #16a34a;
            --green-lt:  #edfdf4;
            --pink:      #ec4899;
            --pink-lt:   #fdf2f8;
            --bg:        #f1f4f9;
            --card:      #ffffff;
            --border:    #e4e8f0;
            --text:      #0b1f3a;
            --muted:     #64748b;
            --sidebar:   260px;
            --radius:    16px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* ━━━━━━━━━━━━━━━ SIDEBAR ━━━━━━━━━━━━━━━ */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar);
            height: 100vh;
            background: var(--navy);
            display: flex;
            flex-direction: column;
            padding: 0 0 24px;
            overflow-y: auto;
            z-index: 200;
        }

        .sidebar-brand {
            padding: 28px 22px 20px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            margin-bottom: 8px;
        }
        .sidebar-brand .logo-text {
            font-family: 'DM Serif Display', serif;
            font-size: 1.6rem;
            color: #fff;
            letter-spacing: .5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar-brand .logo-dot {
            width: 8px; height: 8px;
            background: var(--blue);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--blue);
            flex-shrink: 0;
        }
        .sidebar-brand small {
            display: block;
            font-size: .72rem;
            color: rgba(255,255,255,.4);
            margin-top: 2px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .sidebar-section {
            padding: 14px 22px 6px;
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255,255,255,.25);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 22px;
            color: rgba(255,255,255,.55);
            text-decoration: none;
            font-size: .875rem;
            font-weight: 500;
            border-radius: 0;
            transition: all .2s;
            position: relative;
        }
        .nav-link i {
            width: 18px;
            text-align: center;
            font-size: .95rem;
            flex-shrink: 0;
        }
        .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.06);
        }
        .nav-link.active {
            color: #fff;
            background: rgba(30,111,241,.18);
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 6px; bottom: 6px;
            width: 3px;
            background: var(--blue);
            border-radius: 0 3px 3px 0;
        }

        .badge-notif {
            margin-left: auto;
            background: var(--rose);
            color: #fff;
            font-size: .65rem;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .sidebar-divider {
            margin: 10px 22px;
            border: none;
            border-top: 1px solid rgba(255,255,255,.07);
        }

        .btn-logout {
            margin: auto 22px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            background: rgba(229,64,94,.12);
            color: #f87171;
            border-radius: 12px;
            text-decoration: none;
            font-size: .875rem;
            font-weight: 600;
            border: 1px solid rgba(229,64,94,.2);
            transition: .2s;
        }
        .btn-logout:hover { background: var(--rose); color: #fff; }

        /* ━━━━━━━━━━━━━━━ MAIN ━━━━━━━━━━━━━━━ */
        .main {
            margin-left: var(--sidebar);
            padding: 36px 38px 48px;
            min-height: 100vh;
        }

        /* Top bar */
        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 28px;
        }
        .topbar h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.2;
        }
        .topbar h1 span { color: var(--blue); }
        .topbar p {
            color: var(--muted);
            font-size: .88rem;
            margin-top: 4px;
        }
        .topbar-meta {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .chip {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 7px 15px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
            white-space: nowrap;
        }
        .chip i { color: var(--blue); }
        .chip.live i { color: var(--rose); animation: pulse 1.4s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* Section label */
        .sec-label {
            font-size: .65rem;
            font-weight: 700;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 14px;
        }

        /* ━━━━━━━━━━━━━━━ 5 KPI — UNE SEULE LIGNE ━━━━━━━━━━━━━━━ */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
            margin-bottom: 32px;
        }

        .kpi {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 16px;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
        }
        .kpi:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 26px rgba(0,0,0,.08);
        }
        .kpi-accent {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: var(--radius) var(--radius) 0 0;
        }
        .kpi-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .kpi-ico {
            width: 34px; height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }
        .kpi-badge {
            font-size: .62rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 20px;
            white-space: nowrap;
        }
        .kpi-val {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 3px;
        }
        .kpi-name {
            font-size: .7rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            line-height: 1.3;
        }
        .kpi-foot {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: .7rem;
            color: var(--muted);
            margin-top: 9px;
            padding-top: 9px;
            border-top: 1px solid var(--border);
        }
        .kpi-foot a {
            color: var(--blue);
            font-weight: 600;
            text-decoration: none;
        }

        /* Genre split */
        .gender-row {
            display: flex;
            gap: 18px;
            margin-bottom: 5px;
        }
        .gender-item .g-val {
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1;
        }
        .gender-item .g-lbl {
            font-size: .63rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
        }
        .prog-bar {
            height: 5px;
            background: var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-top: 6px;
        }
        .prog-fill-f {
            height: 100%;
            background: var(--pink);
        }

        /* Couleurs */
        .c-blue   { color: var(--blue); }
        .c-rose   { color: var(--rose); }
        .c-green  { color: var(--green); }
        .c-violet { color: var(--violet); }
        .c-pink   { color: var(--pink); }
        .c-amber  { color: var(--amber); }

        .bg-blue   { background: var(--blue-lt);   color: #0C447C; }
        .bg-rose   { background: var(--rose-lt);   color: #A32D2D; }
        .bg-green  { background: var(--green-lt);  color: #3B6D11; }
        .bg-violet { background: var(--violet-lt); color: #534AB7; }
        .bg-pink   { background: var(--pink-lt);   color: #993556; }
        .bg-amber  { background: var(--amber-lt);  color: #854F0B; }

        .ico-blue   { background: var(--blue-lt);   color: var(--blue); }
        .ico-rose   { background: var(--rose-lt);   color: var(--rose); }
        .ico-green  { background: var(--green-lt);  color: var(--green); }
        .ico-violet { background: var(--violet-lt); color: var(--violet); }
        .ico-pink   { background: var(--pink-lt);   color: var(--pink); }
        .ico-amber  { background: var(--amber-lt);  color: var(--amber); }

        .acc-blue   { background: linear-gradient(90deg,var(--blue),#60a5fa); }
        .acc-rose   { background: linear-gradient(90deg,var(--rose),#fb7185); }
        .acc-green  { background: linear-gradient(90deg,var(--green),#4ade80); }
        .acc-violet { background: linear-gradient(90deg,var(--violet),#a78bfa); }
        .acc-pink   { background: linear-gradient(90deg,var(--pink),#3b82f6); }
        .acc-amber  { background: linear-gradient(90deg,var(--amber),#fcd34d); }

        /* ━━━━━━━━━━━━━━━ MODULES 3 COLONNES ━━━━━━━━━━━━━━━ */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }

        .module {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            transition: transform .22s, box-shadow .22s;
        }
        .module:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 32px rgba(0,0,0,.09);
            color: inherit;
        }

        .module-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 18px;
            border-bottom: 1px solid var(--border);
        }
        .module-ico {
            width: 65px; height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .9rem;
            flex-shrink: 0;
        }
        .module-title {
            font-size: .9rem;
            font-weight: 700;
            color: var(--text);
        }

        .module-body {
            padding: 14px 18px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .module-body p {
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.55;
            flex: 1;
            margin: 0 0 13px;
        }
        .module-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--blue);
            background: var(--blue-lt);
            padding: 6px 13px;
            border-radius: 30px;
            transition: .2s;
            align-self: flex-start;
        }
        .module:hover .module-btn {
            background: var(--blue);
            color: #fff;
        }

        .module.highlight {
            border-color: var(--blue);
        }
        .module.highlight .module-head {
            background: var(--blue-lt);
        }
        .module.highlight .module-btn {
            background: var(--blue);
            color: #fff;
        }

        /* ━━━━━━━━━━━━━━━ RESPONSIVE ━━━━━━━━━━━━━━━ */
        /* Modules de gestion - plus lisibles, pastels et coherents */
        .modules-grid {
            gap: 18px;
            align-items: stretch;
            grid-auto-rows: 1fr;
        }
        .module {
            min-height: 198px;
            height: 100%;
            border-color: rgba(203,213,225,.85);
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(15,31,58,.045);
            position: relative;
        }
        .module::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: var(--blue);
            opacity: .55;
        }
        .module:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 34px rgba(15,31,58,.10);
            border-color: rgba(148,163,184,.95);
        }
        .module-head {
            gap: 14px;
            padding: 17px 18px 13px 20px;
            border-bottom: 0;
        }
        .module-ico {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            font-size: 1.05rem;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.65);
        }
        .module-title {
            font-size: 1rem;
            line-height: 1.2;
            letter-spacing: .1px;
        }
        .module-body {
            padding: 0 18px 18px 20px;
        }
        .module-body p {
            font-size: .875rem;
            line-height: 1.55;
            color: #56657a;
            margin-bottom: 16px;
            min-height: 44px;
        }
        .module-btn {
            padding: 7px 14px;
            font-size: .68rem;
            letter-spacing: .7px;
            border: 1px solid rgba(30,111,241,.10);
        }
        .module:nth-child(1) { background: linear-gradient(180deg,#ffffff 0%,#f3f8ff 100%); }
        .module:nth-child(1)::before { background:#4f8df7; }
        .module:nth-child(1) .module-ico { background:#eaf3ff !important; color:#2563eb !important; }
        .module:nth-child(1) .module-btn { background:#eaf3ff; color:#2563eb; }
        .module:nth-child(1):hover .module-btn { background:#2563eb; color:#fff; }

        .module:nth-child(2) { background: linear-gradient(180deg,#ffffff 0%,#fff8ed 100%); }
        .module:nth-child(2)::before { background:#f3b24d; }
        .module:nth-child(2) .module-ico { background:#fff1d9 !important; color:#c97909 !important; }
        .module:nth-child(2) .module-btn { background:#fff1d9; color:#b36a08; }
        .module:nth-child(2):hover .module-btn { background:#d97706; color:#fff; }

        .module:nth-child(3) { background: linear-gradient(180deg,#ffffff 0%,#f8f4ff 100%); }
        .module:nth-child(3)::before { background:#a78bfa; }
        .module:nth-child(3) .module-ico { background:#f0e9ff !important; color:#7c3aed !important; }
        .module:nth-child(3) .module-btn { background:#f0e9ff; color:#7c3aed; }
        .module:nth-child(3):hover .module-btn { background:#7c3aed; color:#fff; }

        .module:nth-child(4) { background: linear-gradient(180deg,#ffffff 0%,#f0fbf5 100%); }
        .module:nth-child(4)::before { background:#4ade80; }
        .module:nth-child(4) .module-ico { background:#e5f8ed !important; color:#16a34a !important; }
        .module:nth-child(4) .module-btn { background:#e5f8ed; color:#15803d; }
        .module:nth-child(4):hover .module-btn { background:#16a34a; color:#fff; }

        .module:nth-child(5) { background: linear-gradient(180deg,#ffffff 0%,#fff3f5 100%); }
        .module:nth-child(5)::before { background:#fb7185; }
        .module:nth-child(5) .module-ico { background:#ffe5ea !important; color:#dc2626 !important; }
        .module:nth-child(5) .module-btn { background:#ffe5ea; color:#be123c; }
        .module:nth-child(5):hover .module-btn { background:#e5405e; color:#fff; }

        .module.highlight {
            background: linear-gradient(180deg,#ffffff 0%,#eef7ff 100%);
            border-color: rgba(96,165,250,.55);
        }
        .module.highlight::before { background:#1e6ff1; opacity:.85; }
        .module.highlight .module-head { background: transparent; }
        .module.highlight .module-ico { background:#e1f0ff !important; color:#1e6ff1 !important; }
        .module.highlight .module-title { color:#174ea6 !important; }
        .module.highlight .module-btn {
            background:#ddecff;
            color:#1e6ff1;
        }
        .module.highlight:hover .module-btn {
            background:#1e6ff1;
            color:#fff;
        }

        /* KPI - touche pastel sans changer les tailles */
        .kpi:nth-child(1) {
            background: linear-gradient(180deg,#ffffff 0%,#f4f8ff 100%);
            border-color: rgba(147,197,253,.45);
        }
        .kpi:nth-child(2) {
            background: linear-gradient(180deg,#ffffff 0%,#fdf4fb 100%);
            border-color: rgba(244,114,182,.32);
        }
        .kpi:nth-child(3) {
            background: linear-gradient(180deg,#ffffff 0%,#fff5f6 100%);
            border-color: rgba(251,113,133,.34);
        }
        .kpi:nth-child(4) {
            background: linear-gradient(180deg,#ffffff 0%,#f1fbf5 100%);
            border-color: rgba(74,222,128,.34);
        }
        .kpi:nth-child(5) {
            background: linear-gradient(180deg,#ffffff 0%,#f8f5ff 100%);
            border-color: rgba(167,139,250,.38);
        }
        .kpi {
            box-shadow: 0 8px 22px rgba(15,31,58,.045);
        }

        @media (max-width: 1400px) {
            .kpi-row { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 1100px) {
            .kpi-row { grid-template-columns: repeat(2, 1fr); }
            .modules-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 900px) {
            .main { margin-left: 0; padding: 20px; }
            .sidebar { display: none; }
            .kpi-row { grid-template-columns: 1fr 1fr; }
            .modules-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 560px) {
            .kpi-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════ SIDEBAR ══════════════════════ -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <div class="logo-text">
            <span class="logo-dot"></span>
            ALTUTEX
        </div>
        <small>Ressources Humaines</small>
    </div>

    <div class="sidebar-section">Navigation</div>
    <a href="index.php?action=dashboard" class="nav-link active">
        <i class="fas fa-th-large"></i> Accueil
    </a>
    <a href="index.php?action=notification_rh" onclick="clearNotifBadge()" class="nav-link">
        <i class="fas fa-bell"></i> Notifications
        <?php if (!empty($countRh) && $countRh > 0): ?>
            <span id="notif-badge" class="badge-notif"><?= $countRh ?></span>
        <?php endif; ?>
    </a>
    <a href="index.php?action=mon_profil" class="nav-link">
        <i class="fas fa-user-circle"></i> Mon Profil
    </a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Modules</div>
    <a href="index.php?action=planning"     class="nav-link"><i class="fas fa-calendar-check"></i> Planning</a>
    <a href="index.php?action=documents"    class="nav-link"><i class="fas fa-file-invoice"></i> Documents</a>
    <a href="index.php?action=formulaire"   class="nav-link"><i class="fas fa-clipboard-list"></i> Formulaires</a>
    <a href="index.php?action=autorisation" class="nav-link"><i class="fas fa-user-shield"></i> Autorisations</a>

    <div class="sidebar-divider"></div>
    <div class="sidebar-section">Analytique</div>
    <a href="index.php?action=reporting" class="nav-link">
        <i class="fas fa-chart-bar"></i> Reporting & KPI
    </a>
    <a href="index.php?action=stats" class="nav-link">
        <i class="fas fa-chart-pie"></i> Statistiques
    </a>

    <a href="index.php?action=logout" class="btn-logout">
        <i class="fas fa-sign-out-alt"></i> Déconnexion
    </a>
</nav>

<!-- ══════════════════════ MAIN ══════════════════════ -->
<main class="main">

    <!-- Top bar -->
    <div class="topbar">
        <div>
            <h1>Tableau de bord <span>RH</span></h1>
            <p>Vue d'ensemble — Ressources Humaines ALTUTEX</p>
        </div>
        <div class="topbar-meta">
            <div class="chip live"><i class="fas fa-circle" style="font-size:.5rem"></i> Temps réel</div>
            <div class="chip"><i class="fas fa-calendar-alt"></i> <?= $moisCourant . ' ' . date('Y') ?></div>
        </div>
    </div>

    <!-- ══════ 5 KPI — UNE SEULE LIGNE ══════ -->
    <div class="sec-label">Indicateurs clés</div>
    <div class="kpi-row">

        <!-- 1. Effectif total -->
        <div class="kpi">
            <div class="kpi-accent acc-blue"></div>
            <div class="kpi-head">
                <div class="kpi-ico ico-blue"><i class="fas fa-users"></i></div>
                <span class="kpi-badge bg-blue">Actifs <?= $kpi['actifs'] ?></span>
            </div>
            <div class="kpi-val c-blue"><?= $kpi['total_employes'] ?></div>
            <div class="kpi-name">Effectif total</div>
            <div class="kpi-foot">
                <i class="fas fa-building"></i>
                <?= $kpi['nb_departements'] ?> département<?= ($kpi['nb_departements'] > 1 ? 's' : '') ?>
            </div>
        </div>

        <!-- 2. Genre -->
        <div class="kpi">
            <div class="kpi-accent acc-pink"></div>
            <div class="kpi-head">
                <div class="kpi-ico ico-pink"><i class="fas fa-venus-mars"></i></div>
                <span class="kpi-badge bg-pink"><?= $pctF ?>% F</span>
            </div>
            <div class="gender-row">
                <div class="gender-item">
                    <div class="g-val c-pink"><?= $kpi['femmes'] ?></div>
                    <div class="g-lbl c-pink"><i class="fas fa-venus"></i> Femmes</div>
                </div>
                <div class="gender-item">
                    <div class="g-val c-blue"><?= $kpi['hommes'] ?></div>
                    <div class="g-lbl c-blue"><i class="fas fa-mars"></i> Hommes</div>
                </div>
            </div>
            <div class="prog-bar">
                <div class="prog-fill-f" style="width:<?= $pctF ?>%"></div>
            </div>
        </div>

        <!-- 3. Absences ce mois -->
        <div class="kpi">
            <div class="kpi-accent acc-rose"></div>
            <div class="kpi-head">
                <div class="kpi-ico ico-rose"><i class="fas fa-calendar-times"></i></div>
                <span class="kpi-badge bg-rose"><?= $moisCourant ?></span>
            </div>
            <div class="kpi-val c-rose"><?= $absMonth['nb'] ?></div>
            <div class="kpi-name">Absences ce mois</div>
            <div class="kpi-foot">
                <i class="fas fa-user-slash"></i>
                <?= $absMonth['employes_absents'] ?> employé(s) concerné(s)
            </div>
        </div>

        <!-- 4. Formations -->
        <div class="kpi">
            <div class="kpi-accent acc-green"></div>
            <div class="kpi-head">
                <div class="kpi-ico ico-green"><i class="fas fa-graduation-cap"></i></div>
                <span class="kpi-badge bg-green"><?= $formMois['nb'] ?> ce mois</span>
            </div>
            <div class="kpi-val c-green"><?= $formTotal['nb'] ?></div>
            <div class="kpi-name">Formations organisées</div>
            <div class="kpi-foot">
                <i class="fas fa-chart-bar"></i>
                <a href="index.php?action=reporting">Voir par période →</a>
            </div>
        </div>

        <!-- 5. Départements -->
        <div class="kpi">
            <div class="kpi-accent acc-violet"></div>
            <div class="kpi-head">
                <div class="kpi-ico ico-violet"><i class="fas fa-sitemap"></i></div>
                <span class="kpi-badge bg-violet">Structure</span>
            </div>
            <div class="kpi-val c-violet"><?= $kpi['nb_departements'] ?></div>
            <div class="kpi-name">Départements actifs</div>
            <div class="kpi-foot">
                <i class="fas fa-chart-bar"></i>
                <a href="index.php?action=reporting">Répartition complète →</a>
            </div>
        </div>

    </div>
    <!-- ── fin KPI row ── -->

    <!-- ══════ MODULES 3 COLONNES ══════ -->
    <div class="sec-label">Modules de gestion</div>
    <div class="modules-grid">

        <a href="index.php?action=planning" class="module">
            <div class="module-head">
                <div class="module-ico" style="background:#eff6ff;color:#2563eb"><i class="fas fa-calendar-check"></i></div>
                <span class="module-title">Planning</span>
            </div>
            <div class="module-body">
                <p>Planifier et gérer les sessions de formation en cours.</p>
                <span class="module-btn">Ouvrir <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="index.php?action=documents" class="module">
            <div class="module-head">
                <div class="module-ico" style="background:#fffbeb;color:#d97706"><i class="fas fa-file-invoice"></i></div>
                <span class="module-title">Documents</span>
            </div>
            <div class="module-body">
                <p>Gérez l'envoi des fiches de paie et attestations.</p>
                <span class="module-btn">Ouvrir <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="index.php?action=formulaire" class="module">
            <div class="module-head">
                <div class="module-ico" style="background:#faf5ff;color:#9333ea"><i class="fas fa-clipboard-list"></i></div>
                <span class="module-title">Formulaires</span>
            </div>
            <div class="module-body">
                <p>Enquêtes de satisfaction et sondages internes.</p>
                <span class="module-btn">Ouvrir <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="index.php?action=autorisation" class="module">
            <div class="module-head">
                <div class="module-ico" style="background:#f0fdf4;color:#16a34a"><i class="fas fa-user-shield"></i></div>
                <span class="module-title">Autorisations</span>
            </div>
            <div class="module-body">
                <p>Validation des congés et suivi des absences.</p>
                <span class="module-btn">Ouvrir <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="index.php?action=notification_rh" class="module">
            <div class="module-head">
                <div class="module-ico" style="background:#fef2f2;color:#dc2626"><i class="fas fa-bell"></i></div>
                <span class="module-title">Notifications</span>
            </div>
            <div class="module-body">
                <p>Consultez les dernières alertes et demandes des employés.</p>
                <span class="module-btn">Ouvrir <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="index.php?action=reporting" class="module highlight">
            <div class="module-head">
                <div class="module-ico" style="background:#d1e9ff;color:#1e6ff1"><i class="fas fa-chart-bar"></i></div>
                <span class="module-title" style="color:#1e6ff1">Dashboard & Reporting</span>
            </div>
            <div class="module-body">
                <p>KPI avancés, pyramide des âges, absentéisme, turnover et formations — analyse complète en temps réel.</p>
                <span class="module-btn">Accéder <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

    </div>

</main>

<script>
function clearNotifBadge() {
    const badge = document.getElementById('notif-badge');
    if (badge) {
        alert("Il y a un nouveau message dans votre espace de notification !");
        badge.style.display = 'none';
    }
}
</script>
</body>
</html>
