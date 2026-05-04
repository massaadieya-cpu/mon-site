<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Statistique - ALTUTEX BI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f0f4fa; font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ══ HEADER ══ */
        .hdr {
            background: linear-gradient(135deg, #0c2d5a 0%, #1a4a8a 100%);
            padding: 44px 35px;
            border-radius: 0 0 20px 20px;
            margin-bottom: 22px;
            position: relative;
            overflow: hidden;
        }
        .hdr::before {
            content: '';
            position: absolute;
            top: -40px; right: -40px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: rgba(255,255,255,0.04);
            pointer-events: none;
        }
        .hdr::after {
            content: '';
            position: absolute;
            bottom: -60px; right: 80px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            pointer-events: none;
        }
        .hdr-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            position: relative;
            z-index: 1;
        }
        .hdr-left { display: flex; flex-direction: column; gap: 12px; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 6px; width: fit-content;
            background: rgba(255,255,255,0.09); color: rgba(255,255,255,0.65);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 8px;
            padding: 6px 14px; font-size: 12px; cursor: pointer;
            text-decoration: none; transition: background .2s;
            font-family: 'DM Mono', monospace;
        }
        .back-btn:hover { background: rgba(255,255,255,0.16); color: #fff; }

        .hdr-brand { display: flex; align-items: center; gap: 14px; }

        .icon-wrap {
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .icon-wrap i { color: white; font-size: 18px; }

        .hdr-text h1 {
            font-size: 20px; font-weight: 700; color: #fff;
            margin: 0 0 4px; letter-spacing: -0.3px;
        }
        .hdr-text p { font-size: 13px; color: rgba(255,255,255,0.5); margin: 0; }
        .hdr-text p strong { color: rgba(255,255,255,0.85); font-weight: 600; }

        .hdr-right {
            display: flex; align-items: center;
            gap: 16px; flex-wrap: wrap;
        }
        .kpi-row { display: flex; gap: 10px; }
        .kpi {
            background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.13);
            border-radius: 12px; padding: 10px 18px;
            text-align: center; min-width: 100px;
        }
        .kpi span {
            display: block; font-size: 10px; text-transform: uppercase;
            letter-spacing: 0.8px; color: rgba(255,255,255,0.45);
            margin-bottom: 5px; font-family: 'DM Mono', monospace;
        }
        .kpi strong {
            font-size: 28px; font-weight: 700; color: #fff;
            line-height: 1; letter-spacing: -1.5px; display: block;
        }

        .hdr-divider {
            width: 1px; height: 48px;
            background: rgba(255,255,255,0.12); align-self: center;
        }

        .sel-wrap { display: flex; flex-direction: column; gap: 5px; }
        .sel-wrap label {
            font-size: 10px; text-transform: uppercase; letter-spacing: 0.8px;
            color: rgba(255,255,255,0.45); font-family: 'DM Mono', monospace;
        }
        .sel-wrap select {
            background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.16);
            border-radius: 9px; padding: 10px 34px 10px 14px;
            color: rgba(255,255,255,0.88); font-size: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            outline: none; min-width: 230px; cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 12px center;
        }
        .sel-wrap select option { background: #0c2d5a; color: #fff; }

        /* ══ CARD ══ */
        .wrap { padding: 0 20px 50px; }
        .card {
            background: #fff; border-radius: 16px;
            border: 0.5px solid #dde5f0; overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .card-head {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 10px;
            padding: 16px 20px; border-bottom: 0.5px solid #eef2f8;
        }
        .card-head h4 {
            font-size: 15px; font-weight: 600; color: #1a2b42;
            margin: 0; letter-spacing: -0.2px;
        }
        .btn-row { display: flex; gap: 8px; }

        .btn-p {
            background: transparent; color: #64748b;
            border: 0.5px solid #cbd5e1; border-radius: 8px;
            padding: 7px 14px; font-size: 12px; cursor: pointer;
            display: inline-flex; align-items: center; gap: 5px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background .15s;
        }
        .btn-p:hover { background: #f8fafc; }

        .btn-ia {
            background: #4f46e5; color: #fff; border: none;
            border-radius: 8px; padding: 7px 16px; font-size: 12px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-flex; align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: background .15s;
        }
        .btn-ia:hover { background: #4338ca; color: #fff; }

        /* ══ TABLE ══ */
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead th {
            font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px;
            color: #94a3b8; font-weight: 500;
            padding: 12px 20px; background: #f8fafc;
            border-bottom: 0.5px solid #eef2f8; text-align: left;
            font-family: 'DM Mono', monospace;
        }
        tbody tr { transition: background .12s; }
        tbody tr:hover td { background: #fafbfe; }
        td { padding: 18px 20px; border-bottom: 0.5px solid #f1f5fb; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }

        /* ══ QUESTION ══ */
        .q-name { font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 5px; line-height: 1.4; }
        .inv-badge {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 11px; color: #92400e;
            background: #fef3c7; border: 0.5px solid #fcd34d;
            border-radius: 5px; padding: 3px 9px; margin-top: 4px;
            font-family: 'DM Mono', monospace;
        }

        /* ══ CHIPS ══ */
        .chips { display: flex; flex-wrap: wrap; gap: 5px; }
        .chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f8fafc; border: 0.5px solid #e2e8f0;
            border-radius: 7px; padding: 5px 10px;
            font-size: 12px; color: #64748b;
            font-family: 'DM Mono', monospace;
        }
        .chip-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .chip strong { color: #1e293b; font-weight: 600; }

        /* ══ DONUT ══ */
        .donut-cell { text-align: center; }
        .donut-wrap { position: relative; width: 100px; height: 100px; margin: 0 auto 10px; }
        .donut-center {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            text-align: center; pointer-events: none;
        }
        .donut-pct { font-size: 17px; font-weight: 700; line-height: 1; letter-spacing: -0.5px; }
        .donut-lbl {
            font-size: 9px; color: #94a3b8; margin-top: 2px;
            font-family: 'DM Mono', monospace; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .legend { display: flex; flex-wrap: wrap; gap: 4px; justify-content: center; }
        .legend-item { display: flex; align-items: center; gap: 3px; font-size: 11px; color: #64748b; font-family: 'DM Mono', monospace; }
        .legend-dot { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }

        /* ══ SCORE ══ */
        .score-cell { text-align: center; }
        .sat-num { font-size: 26px; font-weight: 700; letter-spacing: -1.5px; margin-bottom: 10px; line-height: 1; }
        .prog-track {
            height: 7px; border-radius: 7px;
            background: #f1f5f9; border: 0.5px solid #e2e8f0;
            overflow: hidden;
        }
        .prog-fill { height: 100%; border-radius: 7px; width: 0%; transition: width 1s cubic-bezier(.4,0,.2,1); }
        .score-lbl {
            font-size: 10px; color: #94a3b8; margin-top: 6px;
            font-family: 'DM Mono', monospace; text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ══ EMPTY STATE ══ */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: #94a3b8; font-size: 13px;
        }
        .empty-state i { font-size: 32px; margin-bottom: 12px; opacity: .4; display: block; }

        /* ══ PRINT ══ */
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .hdr {
                background: white !important;
                border-bottom: 2px solid #0c2d5a;
                border-radius: 0;
            }
            .hdr-text h1 { color: #0c2d5a !important; }
            .hdr-text p  { color: #444 !important; }
            .kpi strong  { color: #0c2d5a !important; }
            .kpi span    { color: #555 !important; }
            .kpi { background: #f5f7fa !important; border-color: #ccc !important; }
            .card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════
     HEADER
══════════════════════════════════════ -->
<div class="hdr">
    <div class="hdr-top">

        <div class="hdr-left">
            <a href="index.php?action=dashboard" class="back-btn no-print">
                <i class="fas fa-arrow-left" style="font-size:10px"></i>
                Retour au Dashboard
            </a>
            <div class="hdr-brand">
                <div class="icon-wrap">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="hdr-text">
                    <h1>Rapport Statistique</h1>
                    <p>Analyse BI — <strong><?= htmlspecialchars($titreEnquete) ?></strong></p>
                </div>
            </div>
        </div>

        <div class="hdr-right">
            <div class="kpi-row">
                <div class="kpi">
                    <span>Participants</span>
                    <strong><?= $totalParticipants ?></strong>
                </div>
                <div class="kpi">
                    <span>Taux moyen</span>
                    <strong>
                        <?php
                            $avg = count($detailsQuestions) > 0
                                ? array_sum(array_column($detailsQuestions, 'taux')) / count($detailsQuestions)
                                : 0;
                            echo round($avg) . '%';
                        ?>
                    </strong>
                </div>
            </div>

            <div class="hdr-divider no-print"></div>

            <div class="sel-wrap no-print">
                <form action="index.php" method="GET">
                    <input type="hidden" name="action" value="stats">
                    <label>Enquête</label>
                    <select name="id" onchange="this.form.submit()">
                        <option value="">— Sélectionner l'enquête —</option>
                        <?php
                            $list = $this->pdo->query("SELECT id, titre FROM enquetes ORDER BY id DESC");
                            while ($row = $list->fetch()):
                        ?>
                            <option value="<?= $row['id'] ?>"
                                <?= (isset($_GET['id']) && $_GET['id'] == $row['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['titre']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </form>
            </div>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════
     CONTENT
══════════════════════════════════════ -->
<div class="wrap">
    <?php if (!empty($detailsQuestions)): ?>

        <?php
        $palettes = [
            ['#2563eb','#60a5fa','#93c5fd','#bfdbfe'],
            ['#7c3aed','#a78bfa','#c4b5fd','#ede9fe'],
            ['#059669','#34d399','#6ee7b7','#d1fae5'],
            ['#0891b2','#22d3ee','#67e8f9','#cffafe'],
            ['#dc2626','#f87171','#fca5a5','#fee2e2'],
            ['#d97706','#fbbf24','#fde68a','#fef9c3'],
        ];
        ?>

        <div class="card">
            <div class="card-head">
                <h4>Indicateurs de Performance</h4>
                <div class="btn-row">
                    <button onclick="window.print()" class="btn-p no-print">
                        <i class="fas fa-print" style="font-size:11px"></i> Imprimer
                    </button>
                    <a href="index.php?action=analyser_ia&id=<?= $id_enquete ?>" class="btn-ia no-print">
                        ✦ Analyse Prédictive IA
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th style="width:28%">Question</th>
                            <th style="width:30%">Détail des réponses</th>
                            <th style="width:20%;text-align:center">Graphique</th>
                            <th style="width:22%;text-align:center">Satisfaction</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pi = 0;
                        foreach ($detailsQuestions as $q_id => $info):
                            $pal   = $palettes[$pi % count($palettes)];
                            $taux  = $info['taux'];
                            $color = $taux >= 75 ? '#059669' : ($taux >= 55 ? '#2563eb' : '#dc2626');
                            $total = array_sum($info['stats']);
                        ?>
                        <tr>
                            <!-- Question -->
                            <td>
                                <div class="q-name"><?= htmlspecialchars($info['titre']) ?></div>
                                <?php if ($info['estInversee']): ?>
                                    <span class="inv-badge">
                                        <i class="fas fa-sync-alt fa-xs"></i> Logique inversée
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Chips réponses -->
                            <td>
                                <div class="chips">
                                <?php $j = 0; foreach ($info['stats'] as $val => $count): ?>
                                    <span class="chip">
                                        <span class="chip-dot" style="background:<?= $pal[$j % count($pal)] ?>"></span>
                                        <?= htmlspecialchars($val) ?>&nbsp;<strong><?= $count ?></strong>
                                    </span>
                                <?php $j++; endforeach; ?>
                                </div>
                            </td>

                            <!-- Donut -->
                            <td class="donut-cell">
                                <div class="donut-wrap">
                                    <canvas id="chart-<?= $q_id ?>" width="100" height="100"
                                        role="img"
                                        aria-label="Répartition : <?= htmlspecialchars($info['titre']) ?>">
                                    </canvas>
                                    <div class="donut-center">
                                        <div class="donut-pct" style="color:<?= $color ?>"><?= $taux ?>%</div>
                                        <div class="donut-lbl">score</div>
                                    </div>
                                </div>
                                <div class="legend">
                                    <?php $j = 0; foreach ($info['stats'] as $val => $count):
                                        $pct = $total > 0 ? round($count / $total * 100) : 0;
                                    ?>
                                        <span class="legend-item">
                                            <span class="legend-dot" style="background:<?= $pal[$j % count($pal)] ?>"></span>
                                            <?= $pct ?>%
                                        </span>
                                    <?php $j++; endforeach; ?>
                                </div>
                            </td>

                            <!-- Score -->
                            <td class="score-cell">
                                <div class="sat-num" style="color:<?= $color ?>"><?= $taux ?>%</div>
                                <div class="prog-track">
                                    <div class="prog-fill" id="pb-<?= $q_id ?>" style="background:<?= $color ?>"></div>
                                </div>
                                <div class="score-lbl">satisfaction</div>
                            </td>
                        </tr>
                        <?php $pi++; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <i class="fas fa-chart-bar"></i>
                Aucune donnée disponible pour cette enquête.
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════
     CHARTS + ANIMATIONS
══════════════════════════════════════ -->
<script>
<?php $pi = 0; foreach ($detailsQuestions as $q_id => $info):
    $pal = $palettes[$pi % count($palettes)];
?>
(function(){
    setTimeout(function(){
        var pb = document.getElementById('pb-<?= $q_id ?>');
        if (pb) pb.style.width = '<?= $info['taux'] ?>%';

        var ctx = document.getElementById('chart-<?= $q_id ?>');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_keys($info['stats'])) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($info['stats'])) ?>,
                    backgroundColor: <?= json_encode($pal) ?>,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 5
                }]
            },
            options: {
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.label + ' : ' + ctx.parsed;
                            }
                        }
                    }
                },
                maintainAspectRatio: false,
                responsive: false
            }
        });
    }, <?= $pi * 90 ?>);
})();
<?php $pi++; endforeach; ?>
</script>

</body>
</html>