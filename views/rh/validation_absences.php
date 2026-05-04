<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALTUTEX | Management Board</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --green-dark:   #064e3b;
            --green-mid:    #059669;
            --green-light:  #10b981;
            --green-pale:   #ecfdf5;
            --green-border: #6ee7b7;
            --amber-bg:     #fffbeb;
            --amber-text:   #92400e;
            --amber-border: #fcd34d;
            --red-bg:       #fef2f2;
            --red-text:     #991b1b;
            --red-border:   #fca5a5;
            --blue-bg:      #eff6ff;
            --blue-text:    #1e40af;
            --blue-border:  #93c5fd;
            --surface:      #ffffff;
            --surface-2:    #f8fafc;
            --surface-3:    #f1f5f9;
            --border:       #e2e8f0;
            --border-2:     #cbd5e1;
            --text-primary: #0f172a;
            --text-secondary:#475569;
            --text-muted:   #94a3b8;
            --shadow-sm:    0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md:    0 4px 16px rgba(0,0,0,0.06), 0 2px 6px rgba(0,0,0,0.04);
            --shadow-lg:    0 20px 40px rgba(0,0,0,0.08), 0 8px 16px rgba(0,0,0,0.04);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--surface-3);
            font-family: 'DM Sans', sans-serif;
            color: var(--text-primary);
            min-height: 100vh;
            padding-bottom: 60px;
        }

        /* ── HEADER ── */
        .rh-header {
            background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-mid) 60%, var(--green-light) 100%);
            padding: 28px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .rh-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }
        .header-brand { display: flex; align-items: center; gap: 18px; }
        .header-icon-wrap {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .header-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: white;
            letter-spacing: -0.01em;
        }
        .header-sub { font-size: 0.78rem; color: rgba(255,255,255,0.6); margin-top: 2px; }
        .btn-back-header {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            padding: 9px 20px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-back-header:hover { background: white; color: var(--green-dark); }

        /* ── PAGE BODY ── */
        .page-body { padding: 32px 40px; }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s;
        }
        .stat-card:hover { box-shadow: var(--shadow-md); }
        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 1.1rem;
        }
        .stat-icon.green  { background: var(--green-pale); color: var(--green-mid); }
        .stat-icon.amber  { background: var(--amber-bg);   color: #b45309; }
        .stat-icon.red    { background: var(--red-bg);     color: #b91c1c; }
        .stat-label { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
        .stat-value { font-family: 'Syne', sans-serif; font-size: 1.7rem; font-weight: 700; line-height: 1; }
        .stat-value.green { color: var(--green-mid); }
        .stat-value.amber { color: #b45309; }
        .stat-value.red   { color: #b91c1c; }

        /* ── NAV PILLS ── */
        .nav-wrap { margin-bottom: 22px; }
        .nav-pills-rh {
            display: inline-flex;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
        }
        .nav-pill {
            padding: 8px 22px;
            border-radius: 9px;
            font-size: 0.83rem;
            font-weight: 500;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 7px;
        }
        .nav-pill:hover { color: var(--text-primary); background: var(--surface-2); }
        .nav-pill.active {
            background: var(--green-mid);
            color: white;
            box-shadow: 0 4px 12px rgba(5,150,105,.3);
        }

        /* ── MAIN CARD ── */
        .main-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .card-topbar {
            padding: 18px 26px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface-2);
        }
        .card-topbar-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .search-field {
            display: flex; align-items: center; gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 7px 12px;
        }
        .search-field input {
            border: none; outline: none; background: transparent;
            font-size: 0.82rem; color: var(--text-primary); width: 180px;
            font-family: 'DM Sans', sans-serif;
        }
        .search-field input::placeholder { color: var(--text-muted); }

        /* ── TABLE ── */
        .rh-table { width: 100%; border-collapse: collapse; }
        .rh-table thead th {
            background: var(--surface-2);
            padding: 13px 22px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .rh-table tbody td {
            padding: 16px 22px;
            border-bottom: 1px solid var(--surface-3);
            vertical-align: middle;
        }
        .rh-table tbody tr:last-child td { border-bottom: none; }
        .rh-table tbody tr { transition: background 0.15s; }
        .rh-table tbody tr:hover td { background: #f8fffe; }

        /* ── CELLS ── */
        .cell-mat {
            display: inline-flex; align-items: center;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 4px 10px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            letter-spacing: .3px;
        }
        .cell-date-main { font-size: 0.88rem; font-weight: 600; color: var(--text-primary); }
        .cell-date-sub  { font-size: 0.73rem; color: var(--text-muted); margin-top: 2px; }

        /* ── PILLS ── */
        .rh-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 11px;
            border-radius: 7px;
            font-size: 0.73rem;
            font-weight: 600;
            white-space: nowrap;
        }
        .rh-pill .dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .pill-red    { background: var(--red-bg);   color: var(--red-text);   border: 1px solid var(--red-border); }
        .pill-amber  { background: var(--amber-bg); color: var(--amber-text); border: 1px solid var(--amber-border); }
        .pill-blue   { background: var(--blue-bg);  color: var(--blue-text);  border: 1px solid var(--blue-border); }
        .pill-green  { background: var(--green-pale); color: var(--green-dark); border: 1px solid var(--green-border); }
        .dot-red   { background: #ef4444; }
        .dot-amber { background: #f59e0b; }
        .dot-blue  { background: #3b82f6; }
        .dot-green { background: var(--green-mid); }

        /* ── PLAGE HORAIRE ── */
        .plage-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: var(--surface-2);
            border: 1px solid var(--border);
            border-radius: 7px;
            padding: 6px 12px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
        }
        .plage-badge i { color: var(--text-muted); font-size: 0.7rem; }

        /* ── MOTIF CELL ── */
        .motif-main { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
        .motif-sub  { font-size: 0.75rem; color: var(--text-muted); margin-top: 3px; font-style: italic; }

        /* ── AVATAR ── */
        .avatar {
            width: 38px; height: 38px;
            background: var(--green-pale);
            color: var(--green-dark);
            border: 1px solid var(--green-border);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 0.78rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* ── ACTION BUTTONS ── */
        .btn-act {
            width: 33px; height: 33px;
            border-radius: 8px; border: 1px solid transparent;
            display: inline-flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; font-size: 0.78rem;
        }
        .btn-act-ok  { background: var(--green-pale); color: var(--green-mid); border-color: var(--green-border); }
        .btn-act-ok:hover  { background: var(--green-mid); color: white; }
        .btn-act-ko  { background: var(--red-bg);    color: #dc2626; border-color: var(--red-border); }
        .btn-act-ko:hover  { background: #ef4444; color: white; }
        .btn-act:hover { transform: translateY(-1px); }

        /* ── MODAL ── */
        .modal-content { border-radius: 20px; border: 1px solid var(--border); box-shadow: var(--shadow-lg); }
        .modal-icon { font-size: 2.5rem; margin-bottom: 12px; opacity: 0.2; }
        .modal-title-custom { font-family: 'Syne', sans-serif; font-size: 1.1rem; font-weight: 700; }
        .btn-modal-cancel {
            background: var(--surface-2); border: 1px solid var(--border);
            color: var(--text-secondary); border-radius: 10px;
            padding: 9px 22px; font-weight: 500; font-size: 0.85rem;
            transition: 0.2s; cursor: pointer;
        }
        .btn-modal-cancel:hover { background: var(--surface-3); }
        .btn-modal-ok {
            background: var(--green-mid); color: white; border: none;
            border-radius: 10px; padding: 9px 22px;
            font-weight: 600; font-size: 0.85rem;
            transition: 0.2s; cursor: pointer;
        }
        .btn-modal-ok:hover { background: var(--green-dark); }
        .btn-modal-danger {
            background: #ef4444; color: white; border: none;
            border-radius: 10px; padding: 9px 22px;
            font-weight: 600; font-size: 0.85rem;
            transition: 0.2s; cursor: pointer;
        }
        .btn-modal-danger:hover { background: #dc2626; }

        /* ── EMPTY STATE ── */
        .empty-state {
            padding: 64px 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .empty-state i { font-size: 2rem; margin-bottom: 12px; opacity: 0.3; display: block; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .page-body { padding: 20px 16px; }
            .stats-row { grid-template-columns: 1fr; }
            .rh-header { padding: 20px 16px; flex-direction: column; gap: 16px; align-items: flex-start; }
        }
    </style>
</head>
<body>

<?php $tab = $_GET['tab'] ?? 'absences'; ?>

<!-- ── HEADER ── -->
<header class="rh-header">
    <div class="header-brand">
        <div class="header-icon-wrap">
            <i class="fas <?php echo ($tab == 'absences') ? 'fa-user-clock' : 'fa-calendar-check'; ?> text-white" style="font-size:1.3rem"></i>
        </div>
        <div>
            <div class="header-title">ALTUTEX <span style="font-weight:300;opacity:.6">|</span> Management Board</div>
            <div class="header-sub">Supervision des présences et validations RH</div>
        </div>
    </div>
    <a href="index.php?action=dashboard" class="btn-back-header">
        <i class="fas fa-arrow-left" style="font-size:.8rem"></i> Dashboard
    </a>
</header>

<div class="page-body">

    <!-- ── STATS ── -->
    <?php if ($tab == 'absences' && isset($pdo)):
        $totalAbs   = $pdo->query("SELECT COUNT(*) FROM absences")->fetchColumn();
        $journees   = $pdo->query("SELECT COUNT(*) FROM absences WHERE type_declaration='journee'")->fetchColumn();
        $demiPlus   = $totalAbs - $journees;
    ?>
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-calendar-xmark"></i></div>
            <div>
                <div class="stat-label">Total absences</div>
                <div class="stat-value red"><?php echo $totalAbs; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber"><i class="fas fa-sun"></i></div>
            <div>
                <div class="stat-label">Journées entières</div>
                <div class="stat-value amber"><?php echo $journees; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-clock-rotate-left"></i></div>
            <div>
                <div class="stat-label">Demi-j. / Séances</div>
                <div class="stat-value green"><?php echo $demiPlus; ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── NAV ── -->
    <div class="nav-wrap">
        <div class="nav-pills-rh">
            <a href="?action=autorisation&tab=absences"
               class="nav-pill <?php echo ($tab == 'absences') ? 'active' : ''; ?>">
                <i class="fas fa-user-clock" style="font-size:.8rem"></i> Absences journalières
            </a>
            <a href="?action=autorisation&tab=conges"
               class="nav-pill <?php echo ($tab == 'conges') ? 'active' : ''; ?>">
                <i class="fas fa-file-signature" style="font-size:.8rem"></i> Demandes de congés
            </a>
        </div>
    </div>

    <!-- ── MAIN CARD ── -->
    <div class="main-card">

        <?php if ($tab == 'absences'): ?>

        <div class="card-topbar">
            <span class="card-topbar-title">
                <i class="fas fa-list-ul me-2" style="color:var(--green-mid)"></i>
                Déclarations d'absence
            </span>
            <div class="search-field">
                <i class="fas fa-magnifying-glass" style="color:var(--text-muted);font-size:.8rem"></i>
                <input type="text" id="searchInput" placeholder="Rechercher un matricule…">
            </div>
        </div>

        <div class="table-responsive">
            <table class="rh-table" id="absTable">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Date d'absence</th>
                        <th>Type</th>
                        <th>Plage horaire</th>
                        <th>Motif</th>
                        <th>Remarque</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (isset($pdo)):
                    $stmt = $pdo->query("SELECT * FROM absences ORDER BY created_at DESC");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($rows)): ?>
                        <tr><td colspan="6">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                Aucune absence enregistrée
                            </div>
                        </td></tr>
                    <?php else:
                        foreach ($rows as $row):
                            $type = $row['type_declaration'] ?? '';
                            if ($type === 'journee') {
                                $pillClass = 'pill-red';
                                $dotClass  = 'dot-red';
                                $label     = 'Journée entière';
                                $icon      = 'fa-calendar-xmark';
                            } elseif ($type === 'demi_journee') {
                                $pillClass = 'pill-amber';
                                $dotClass  = 'dot-amber';
                                $label     = 'Demi-journée';
                                $icon      = 'fa-clock';
                            } else {
                                $pillClass = 'pill-blue';
                                $dotClass  = 'dot-blue';
                                $label     = 'Séances';
                                $icon      = 'fa-layer-group';
                            }
                    ?>
                    <tr>
                        <td>
                            <span class="cell-mat">#<?php echo htmlspecialchars($row['user_id']); ?></span>
                        </td>
                        <td>
                            <div class="cell-date-main"><?php echo date('d M Y', strtotime($row['date_abs'])); ?></div>
                            <div class="cell-date-sub">Saisi le <?php echo date('d/m à H:i', strtotime($row['created_at'])); ?></div>
                        </td>
                        <td>
                            <span class="rh-pill <?php echo $pillClass; ?>">
                                <span class="dot <?php echo $dotClass; ?>"></span>
                                <?php echo $label; ?>
                            </span>
                        </td>
                        <td>
                            <span class="plage-badge">
                                <i class="fas fa-hourglass-half"></i>
                                <?php echo htmlspecialchars($row['heure_seance']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="motif-main"><?php echo htmlspecialchars($row['motif']); ?></div>
                        </td>
                        <td>
                            <div class="motif-sub"><?php echo $row['remarque'] ? htmlspecialchars($row['remarque']) : 'Aucun détail'; ?></div>
                        </td>
                    </tr>
                    <?php endforeach; endif; endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: /* ── CONGÉS ── */ ?>

        <div class="card-topbar">
            <span class="card-topbar-title">
                <i class="fas fa-file-signature me-2" style="color:var(--green-mid)"></i>
                Demandes de congés
            </span>
        </div>

        <div class="table-responsive">
            <table class="rh-table">
                <thead>
                    <tr>
                        <th>Collaborateur</th>
                        <th>Fonction</th>
                        <th>Période de congé</th>
                        <th>Statut</th>
                        <th style="text-align:center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (isset($pdo)):
                    $stmt = $pdo->query("SELECT * FROM demandes_conge ORDER BY date_demande DESC");
                    $conges = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (empty($conges)): ?>
                        <tr><td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                Aucune demande de congé
                            </div>
                        </td></tr>
                    <?php else:
                        foreach ($conges as $c):
                            $statutBrut = !empty($c['statut']) ? strtolower($c['statut']) : 'en attente';
                            if ($statutBrut == 'validé' || $statutBrut == 'accepté') {
                                $st = ['pill'=>'pill-green','label'=>'Validé','dot'=>'dot-green','pending'=>false];
                            } elseif ($statutBrut == 'refusé') {
                                $st = ['pill'=>'pill-red','label'=>'Refusé','dot'=>'dot-red','pending'=>false];
                            } else {
                                $st = ['pill'=>'pill-amber','label'=>'En attente','dot'=>'dot-amber','pending'=>true];
                            }
                            $modalId = "m" . $c['id'];
                    ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px">
                                <div class="avatar"><?php echo strtoupper(substr($c['nom_complet'], 0, 2)); ?></div>
                                <div class="cell-date-main"><?php echo htmlspecialchars($c['nom_complet']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="motif-main"><?php echo htmlspecialchars($c['poste']); ?></div>
                            <div class="cell-date-sub">CIN : <?php echo htmlspecialchars($c['cin']); ?></div>
                        </td>
                        <td>
                            <div class="motif-main">Du <?php echo date('d/m/y', strtotime($c['date_debut'])); ?> au <?php echo date('d/m/y', strtotime($c['date_fin'])); ?></div>
                            <div class="cell-date-sub"><?php echo htmlspecialchars($c['cause']); ?></div>
                        </td>
                        <td>
                            <span class="rh-pill <?php echo $st['pill']; ?>">
                                <span class="dot <?php echo $st['dot']; ?>"></span>
                                <?php echo $st['label']; ?>
                            </span>
                        </td>
                        <td style="text-align:center">
                            <?php if ($st['pending']): ?>
                            <div style="display:flex;justify-content:center;gap:8px">
                                <button class="btn-act btn-act-ok" data-bs-toggle="modal" data-bs-target="#acc<?php echo $modalId; ?>" title="Valider">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn-act btn-act-ko" data-bs-toggle="modal" data-bs-target="#ref<?php echo $modalId; ?>" title="Refuser">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <!-- Modal Valider -->
                            <div class="modal fade" id="acc<?php echo $modalId; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content p-4">
                                        <div class="modal-body text-center px-2">
                                            <div class="modal-icon text-success"><i class="fas fa-circle-check"></i></div>
                                            <div class="modal-title-custom mb-2">Confirmer la validation ?</div>
                                            <p class="text-muted" style="font-size:.83rem">L'employé pourra s'absenter aux dates indiquées.</p>
                                            <form action="index.php?action=update_conge_status" method="POST"
                                                  class="mt-4 d-flex gap-2 justify-content-center">
                                                <input type="hidden" name="id_conge" value="<?php echo $c['id']; ?>">
                                                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Annuler</button>
                                                <button name="decision" value="validé" class="btn-modal-ok">Valider</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Refuser -->
                            <div class="modal fade" id="ref<?php echo $modalId; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content p-4">
                                        <div class="modal-body text-center px-2">
                                            <div class="modal-icon text-danger"><i class="fas fa-circle-xmark"></i></div>
                                            <div class="modal-title-custom mb-2">Refuser cette demande ?</div>
                                            <p class="text-muted" style="font-size:.83rem">Cette action est définitive.</p>
                                            <form action="index.php?action=update_conge_status" method="POST"
                                                  class="mt-4 d-flex gap-2 justify-content-center">
                                                <input type="hidden" name="id_conge" value="<?php echo $c['id']; ?>">
                                                <button type="button" class="btn-modal-cancel" data-bs-dismiss="modal">Annuler</button>
                                                <button name="decision" value="refusé" class="btn-modal-danger">Refuser</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php else: ?>
                            <i class="fas fa-lock" style="color:var(--text-muted);font-size:.85rem"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; endif; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </div><!-- /main-card -->

</div><!-- /page-body -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Recherche live par matricule
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const val = this.value.toLowerCase();
        document.querySelectorAll('#absTable tbody tr').forEach(tr => {
            const mat = tr.querySelector('.cell-mat');
            if (!mat) return;
            tr.style.display = mat.textContent.toLowerCase().includes(val) ? '' : 'none';
        });
    });
}
</script>
</body>
</html>