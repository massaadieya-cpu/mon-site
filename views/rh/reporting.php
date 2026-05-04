<?php
/**
 * reporting.php — DWH RH · Altutex  v9 FIXED
 */
if (!defined('DWH_BOOT')) { http_response_code(403); exit('Accès direct interdit.'); }

function jj(mixed $v): string {
    return json_encode($v ?? [], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
}
function safe(mixed $v, string $d = '—'): string {
    return htmlspecialchars((string)($v ?? $d), ENT_QUOTES, 'UTF-8');
}
function pct(mixed $v): string  { return number_format((float)($v ?? 0), 1) . '%'; }
function nbf(mixed $v): string  { return number_format((float)($v ?? 0), 0, ',', ' '); }
function mnt(mixed $v): string  { return number_format((float)($v ?? 0), 0, ',', ' ') . ' DT'; }

$action = $action ?? 'dashboard';
$annee  = $annee  ?? date('Y');
$annees = $annees ?? [];

$STATIC_BASE = 'http://localhost/pfe/index.php';
define('HOME_URL', $STATIC_BASE . '?action=dashboard&annee=' . (int)$annee);

$navItems = [
    ['action'=>'dashboard',   'icon'=>'grid_view',     'label'=>'Vue Globale',        'color'=>'#2ec0ff','bg'=>'rgba(56,189,248,.15)'],
    ['action'=>'absenteisme', 'icon'=>'sick',          'label'=>'Absentéisme',        'color'=>'#ff6767','bg'=>'rgba(248,113,113,.15)'],
    ['action'=>'turnover',    'icon'=>'trending_down', 'label'=>'Turnover',           'color'=>'#FB923C','bg'=>'rgba(251,146,60,.15)'],
    ['action'=>'formations',  'icon'=>'school',        'label'=>'Formations',         'color'=>'#34D399','bg'=>'rgba(52,211,153,.15)'],
    ['action'=>'salaires',    'icon'=>'payments',      'label'=>'Salaires & Fidélité','color'=>'#A78BFA','bg'=>'rgba(167,139,250,.15)'],
    ['action'=>'employes',    'icon'=>'badge',         'label'=>'Annuaire',           'color'=>'#ff64b4','bg'=>'rgba(244,114,182,.15)'],
];

$pageTitles = [
    'dashboard'   => ['Vue Globale RH',       'Synthèse des indicateurs clés'],
    'absenteisme' => ['Absentéisme',           'Analyse des absences'],
    'turnover'    => ['Turnover & Rétention',  'Mouvements du personnel'],
    'formations'  => ['Formations',            'Suivi des compétences'],
    'salaires'    => ['Salaires & Fidélité',   'Masse salariale et ancienneté'],
    'employes'    => ['Annuaire Employés',     'Répertoire des collaborateurs'],
];
[$pageTitle, $pageSub] = $pageTitles[$action] ?? ['Dashboard',''];
$activeNav = array_values(array_filter($navItems, fn($n) => $n['action'] === $action));
$pageColor = $activeNav[0]['color'] ?? '#38BDF8';
$pageBg    = $activeNav[0]['bg']    ?? 'rgba(56,189,248,.15)';
$pageIcon  = $activeNav[0]['icon']  ?? 'analytics';

$PAL    = ['#38BDF8','#34D399','#F87171','#A78BFA','#FB923C','#F472B6','#22D3EE','#FBBF24','#84CC16','#E879F9'];
$PAL_JS = json_encode($PAL);

$kd_g = $kpi_demo     ?? [];
$ka_g = $kpi_absences ?? [];
$kt_g = $kpi_turnover ?? [];

$moisNomsSb = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

// Couleurs genre : rose vif pour Femmes, bleu pour Hommes
define('COLOR_F', '#FF2D8B');
define('COLOR_M', '#0EA5E9');
define('COLOR_F_BG', 'rgba(255,45,139,.75)');
define('COLOR_M_BG', 'rgba(14,165,233,.75)');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= safe($pageTitle) ?> · Altutex RH</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons+Round">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root {
  --sb-border: rgba(255,255,255,.09);
  --sb-txt:    rgba(255,255,255,.7);
  --sb-txt-m:  rgba(255,255,255,.38);
  --bg:       #f0f5fe;
  --surface:  #FFFFFF;
  --border:   #E2E8F4;
  --border2:  #C8D5EE;
  --txt1: #0B1D3A;
  --txt2: #1E3456;
  --txt3: #4A5E80;
  --txt4: #7A8EAE;
  --vivid-blue: #0fa8ef;
  --sidebar-w: 200px;
  --topbar-h:  60px;
  --r-sm: 8px;
  --r:    12px;
  --r-lg: 16px;
  /* Genre colors */
  --clr-f: #FF2D8B;
  --clr-m: #0EA5E9;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height:100%; font-family:'Inter',sans-serif; font-size:14px; color:var(--txt1); background:var(--bg); -webkit-font-smoothing:antialiased; }
::-webkit-scrollbar { width:4px; height:4px; }
::-webkit-scrollbar-thumb { background:var(--border2); border-radius:4px; }
::-webkit-scrollbar-thumb:hover { background:var(--vivid-blue); }

.app { display:flex; min-height:100vh; }

/* SIDEBAR */
.sidebar {
  width:var(--sidebar-w);
  background:linear-gradient(180deg,#29426c 0%,#4d6dad 50%,#6d81a2 100%);
  display:flex; flex-direction:column;
  position:fixed; top:0; left:0; bottom:0;
  z-index:200; overflow:hidden;
  box-shadow:3px 0 20px rgba(10,25,60,.35);
}
.sb-logo { padding:20px 18px 16px; border-bottom:1px solid var(--sb-border); flex-shrink:0; }
.sb-logo-row { display:flex; align-items:center; gap:9px; margin-bottom:3px; }
.sb-logo-dot { width:10px; height:10px; border-radius:50%; background:#EF4444; box-shadow:0 0 8px rgba(239,68,68,.6); flex-shrink:0; }
.sb-logo-name { font-size:18px; font-weight:800; color:#fff; letter-spacing:-.3px; }
.sb-logo-sub { font-size:9px; font-weight:600; color:var(--sb-txt-m); text-transform:uppercase; letter-spacing:2px; padding-left:19px; }

.sb-section { padding:14px 16px 6px; font-size:9px; font-weight:700; color:var(--sb-txt-m); text-transform:uppercase; letter-spacing:1.8px; display:flex; align-items:center; gap:8px; }
.sb-section::after { content:''; flex:1; height:1px; background:var(--sb-border); }

.sb-filters { padding:0 10px 10px; flex-shrink:0; overflow-y:auto; }
.sb-filter-group { margin-bottom:8px; }
.sb-filter-lbl { font-size:9px; font-weight:700; color:var(--sb-txt-m); text-transform:uppercase; letter-spacing:1.2px; display:block; margin-bottom:5px; padding-left:2px; }
.sb-fsel {
  width:100%;
  background:rgba(255,255,255,.07);
  border:1px solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.85);
  border-radius:8px;
  padding:8px 28px 8px 10px;
  font-size:12px; font-family:'Inter',sans-serif; font-weight:500;
  outline:none; cursor:pointer; appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='rgba(255,255,255,.4)'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right 10px center; background-size:10px;
  transition:border-color .15s;
}
.sb-fsel:focus { border-color:rgba(56,189,248,.6); background-color:rgba(56,189,248,.08); }
.sb-fsel option { background:#162E5F; color:#fff; }

.sb-filter-row { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-bottom:8px; }
.sb-filter-row .sb-fsel { font-size:11px; padding:7px 24px 7px 8px; }

.sb-filter-btns { display:flex; gap:6px; margin-top:4px; }
.sb-btn-apply {
  flex:1; display:flex; align-items:center; justify-content:center; gap:5px;
  padding:9px; border-radius:8px; border:none; cursor:pointer;
  font-size:12px; font-weight:700; font-family:'Inter',sans-serif;
  background:#0EA5E9; color:#fff; transition:all .15s; text-decoration:none;
}
.sb-btn-apply:hover { background:#38BDF8; }
.sb-btn-apply .material-icons-round { font-size:14px; }
.sb-btn-reset {
  display:flex; align-items:center; justify-content:center; gap:4px;
  padding:9px 11px; border-radius:8px; border:1px solid rgba(255,255,255,.15);
  cursor:pointer; font-size:11px; font-weight:600; font-family:'Inter',sans-serif;
  background:rgba(255,255,255,.07); color:rgba(255,255,255,.6); transition:all .15s; text-decoration:none;
}
.sb-btn-reset:hover { background:rgba(255,255,255,.12); color:#fff; }
.sb-btn-reset .material-icons-round { font-size:13px; }

.sb-deconnect {
  margin:6px 10px 14px; display:flex; align-items:center; justify-content:center; gap:8px;
  padding:11px; border-radius:10px; background:rgba(239,68,68,.18);
  border:1px solid rgba(239,68,68,.3); color:#FCA5A5; font-size:13px; font-weight:700;
  cursor:pointer; text-decoration:none; transition:all .16s; flex-shrink:0;
}
.sb-deconnect:hover { background:rgba(239,68,68,.28); color:#fff; }
.sb-deconnect .material-icons-round { font-size:17px; }

.sb-footer {
  padding:10px 16px; border-top:1px solid var(--sb-border);
  font-size:10px; color:var(--sb-txt-m); flex-shrink:0;
  display:flex; align-items:center; gap:7px; margin-top:auto;
}
.sb-dot { width:6px; height:6px; border-radius:50%; background:#10B981; box-shadow:0 0 6px #10B981; animation:pulse 2s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1;box-shadow:0 0 6px #10B981} 50%{opacity:.5;box-shadow:0 0 12px #10B981} }

/* MAIN */
.main { flex:1; margin-left:var(--sidebar-w); display:flex; flex-direction:column; min-height:100vh; }

.topbar {
  height:var(--topbar-h); background:#fff; border-bottom:1px solid var(--border);
  display:flex; align-items:center; justify-content:space-between;
  padding:0 26px; position:sticky; top:0; z-index:100;
  box-shadow:0 1px 8px rgba(0,0,0,.06);
}
.tbar-left { display:flex; align-items:center; gap:13px; }
.tbar-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; }
.tbar-icon .material-icons-round { font-size:19px; }
.tbar-div { width:1px; height:22px; background:var(--border2); }
.tbar-left h1 { font-size:15px; font-weight:800; color:var(--txt1); letter-spacing:-.3px; }
.tbar-left p  { font-size:11px; color:var(--txt4); margin-top:1px; }
.tbar-right { display:flex; align-items:center; gap:7px; }

.btn { display:inline-flex; align-items:center; gap:6px; padding:8px 16px; border-radius:var(--r-sm); font-size:12px; font-weight:700; border:none; cursor:pointer; transition:all .16s; text-decoration:none; font-family:'Inter',sans-serif; white-space:nowrap; }
.btn .material-icons-round { font-size:15px; }
.btn-primary { background:#0EA5E9; color:#fff; box-shadow:0 3px 10px rgba(14,165,233,.3); }
.btn-primary:hover { background:#38BDF8; transform:translateY(-1px); }
.btn-ghost { background:var(--bg); color:var(--txt3); border:1.5px solid var(--border2); }
.btn-ghost:hover { background:#EFF9FF; color:var(--vivid-blue); border-color:var(--vivid-blue); }

.content { padding:22px 26px; flex:1; }

.alert { display:flex; align-items:flex-start; gap:12px; padding:13px 16px; border-radius:var(--r-lg); margin-bottom:20px; border:1px solid; font-size:13px; line-height:1.6; }
.alert .material-icons-round { font-size:19px; flex-shrink:0; margin-top:1px; }
.alert strong { font-weight:700; }
.alert-danger  { background:#FFF1F2; border-color:rgba(239,68,68,.25); color:#7F1D1D; }
.alert-danger .material-icons-round { color:#EF4444; }
.alert-warning { background:#FFF7ED; border-color:rgba(249,115,22,.25); color:#7C2D12; }
.alert-warning .material-icons-round { color:#F97316; }
.alert-success { background:#ECFDF5; border-color:rgba(16,185,129,.25); color:#064E3B; }
.alert-success .material-icons-round { color:#10B981; }
.alert-info    { background:#F0F9FF; border-color:rgba(14,165,233,.25); color:#0C4A6E; }
.alert-info .material-icons-round { color:#0EA5E9; }

.kpi-strip { display:grid; grid-template-columns:repeat(auto-fit,minmax(155px,1fr)); gap:13px; margin-bottom:22px; }
.kpi-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); overflow:hidden; transition:all .2s cubic-bezier(.22,1,.36,1); box-shadow:0 1px 4px rgba(0,0,0,.05),0 4px 12px rgba(0,0,0,.04); }
.kpi-card:hover { transform:translateY(-4px); box-shadow:0 6px 24px rgba(0,0,0,.1); border-color:var(--border2); }
.kpi-top-bar { height:4px; width:100%; }
.kpi-body { padding:15px 15px 17px; }
.kpi-ico-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:11px; }
.kpi-ico { width:40px; height:40px; border-radius:11px; display:flex; align-items:center; justify-content:center; }
.kpi-ico .material-icons-round { font-size:19px; }
.kpi-v { font-size:25px; font-weight:800; line-height:1; font-family:'JetBrains Mono',monospace; letter-spacing:-.5px; }
.kpi-lbl { font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--txt4); margin-top:6px; }
.kpi-s2 { font-size:11px; color:var(--txt4); margin-top:3px; }

.badge { display:inline-flex; align-items:center; gap:3px; padding:3px 8px; border-radius:20px; font-size:10px; font-weight:700; }
.badge-r { background:#FFF1F2; color:#B91C1C; border:1px solid rgba(239,68,68,.2); }
.badge-o { background:#FFF7ED; color:#C2410C; border:1px solid rgba(249,115,22,.2); }
.badge-g { background:#ECFDF5; color:#047857; border:1px solid rgba(16,185,129,.2); }
.badge-b { background:#F0F9FF; color:#0369A1; border:1px solid rgba(14,165,233,.2); }
.badge-v { background:#F5F3FF; color:#5B21B6; border:1px solid rgba(139,92,246,.2); }
.badge-p { background:#FFF0F7; color:#BE185D; border:1px solid rgba(255,45,139,.2); }

.row { display:grid; gap:14px; margin-bottom:14px; }
.c2  { grid-template-columns:1fr 1fr; }
.c3  { grid-template-columns:1fr 1fr 1fr; }
.c4  { grid-template-columns:1fr 1fr 1fr 1fr; }
.c12 { grid-template-columns:1fr 2fr; }
.c21 { grid-template-columns:2fr 1fr; }

.card { background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); padding:18px; transition:all .2s cubic-bezier(.22,1,.36,1); box-shadow:0 1px 4px rgba(0,0,0,.05),0 4px 12px rgba(0,0,0,.04); }
.card:hover { border-color:var(--border2); box-shadow:0 4px 20px rgba(0,0,0,.09); transform:translateY(-2px); }
.card-head { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:14px; }
.card-title { font-size:13px; font-weight:700; color:var(--txt1); letter-spacing:-.2px; }
.card-sub   { font-size:10.5px; color:var(--txt4); margin-top:2px; }
.card-tag { font-size:9.5px; padding:3px 9px; border-radius:20px; background:var(--bg); color:var(--txt4); font-weight:600; white-space:nowrap; border:1px solid var(--border2); }
.chart-w { position:relative; }
.chart-w canvas { display:block; }

.st { display:flex; align-items:center; gap:8px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:var(--txt4); margin-bottom:13px; padding-bottom:10px; border-bottom:2px solid var(--border); }
.st .material-icons-round { font-size:15px; }
.st-badge { margin-left:auto; font-size:9.5px; padding:2px 9px; border-radius:20px; background:#EFF9FF; color:#0EA5E9; font-weight:700; border:1px solid rgba(14,165,233,.2); }

.fm-heatrow { display:flex; gap:4px; margin-bottom:8px; }
.fm-cell { flex:1; border-radius:7px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:6px 2px; transition:all .18s; cursor:default; border:1px solid transparent; min-height:42px; }
.fm-cell:hover { transform:translateY(-3px); }
.fm-cell .fc-lbl { font-size:10px; font-weight:700; }
.fm-cell .fc-val { font-size:9.5px; font-weight:600; margin-top:1px; }
.fm-0 { background:#F0FDF4; border-color:#D1FAE5; } .fm-0 .fc-lbl{color:#6EE7B7}
.fm-1 { background:#DCFCE7; border-color:#BBF7D0; } .fm-1 .fc-lbl{color:#10B981}
.fm-2 { background:#A7F3D0; border-color:#6EE7B7; } .fm-2 .fc-lbl{color:#047857}
.fm-3 { background:#6EE7B7; border-color:#34D399; } .fm-3 .fc-lbl{color:#065F46}
.fm-4 { background:#34D399; border-color:#10B981; } .fm-4 .fc-lbl{color:#fff}
.fm-5 { background:#10B981; border-color:#059669; } .fm-5 .fc-lbl{color:#fff}
.hm-legend { display:flex; align-items:center; gap:5px; font-size:9.5px; color:var(--txt4); font-weight:600; padding:5px 0 3px; }
.hm-swatch { width:12px; height:12px; border-radius:3px; }

.podium-wrap { display:flex; gap:9px; flex-wrap:wrap; margin-top:8px; }
.podium-card { flex:1; min-width:115px; background:var(--bg); border:1px solid var(--border); border-radius:var(--r); padding:13px 11px; position:relative; overflow:hidden; transition:all .2s; }
.podium-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,.1); }
.podium-card.rk1 { border-color:rgba(245,158,11,.35); background:#FFFBEB; }
.pod-rank { position:absolute; top:9px; right:9px; font-size:17px; }
.pod-anc { font-size:22px; font-weight:800; font-family:'JetBrains Mono',monospace; letter-spacing:-.5px; margin-bottom:3px; }
.pod-name { font-size:12px; font-weight:700; color:var(--txt1); }
.pod-dept { font-size:10px; color:var(--txt4); margin-top:2px; }
.pod-sal  { font-size:11px; color:#10B981; margin-top:5px; font-family:'JetBrains Mono',monospace; font-weight:700; }

/* Genre bar — rose vif F, bleu M */
.gender-bar { height:10px; border-radius:5px; overflow:hidden; display:flex; margin:9px 0 8px; }
.gf { background:linear-gradient(90deg,#FF2D8B,#FF6EB4); }
.gm { background:linear-gradient(90deg,#0EA5E9,#38BDF8); }
.gender-leg { display:flex; gap:13px; font-size:11.5px; color:var(--txt2); }
.gdot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }

.tbl-wrap { overflow-x:auto; }
table.tbl { width:100%; border-collapse:collapse; font-size:12px; }
.tbl thead th { padding:9px 13px; text-align:left; font-size:9.5px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--txt4); border-bottom:2px solid var(--border2); background:var(--bg); white-space:nowrap; }
.tbl tbody tr { border-bottom:1px solid var(--border); transition:background .12s; }
.tbl tbody tr:hover { background:#F0F9FF; }
.tbl tbody tr:last-child { border-bottom:none; }
.tbl td { padding:10px 13px; color:var(--txt2); vertical-align:middle; }
.tbl td strong { color:var(--txt1); font-weight:700; }
.risk-high td { border-left:3px solid #EF4444; }
.risk-med  td { border-left:3px solid #F97316; }

.pill { display:inline-block; padding:3px 9px; border-radius:20px; font-size:10.5px; font-weight:700; }
.pr  { background:#FFF1F2; color:#B91C1C; }
.po  { background:#FFF7ED; color:#C2410C; }
.pg  { background:#ECFDF5; color:#047857; }
.pb  { background:#F0F9FF; color:#0369A1; }
.pv  { background:#F5F3FF; color:#5B21B6; }
.pm  { background:#FFF0F7; color:#BE185D; }
.pgy { background:var(--bg); color:var(--txt3); border:1px solid var(--border2); }

.rank-item { display:flex; align-items:center; gap:9px; padding:7px 0; border-bottom:1px solid var(--border); }
.rank-item:last-child { border-bottom:none; }
.rank-num  { width:20px; font-size:10px; font-weight:700; color:var(--txt4); text-align:center; flex-shrink:0; }
.rank-label { width:115px; font-size:11.5px; color:var(--txt2); font-weight:500; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; flex-shrink:0; }
.rank-bar-o { flex:1; height:7px; background:var(--bg); border-radius:4px; overflow:hidden; }
.rank-bar-f { height:100%; border-radius:4px; transition:width .7s; }
.rank-v { width:30px; font-size:11.5px; font-weight:800; color:var(--txt1); text-align:right; flex-shrink:0; font-family:'JetBrains Mono',monospace; }

.mp { width:100%; height:6px; background:var(--bg); border-radius:3px; overflow:hidden; }
.mp-f { height:100%; border-radius:3px; }

.empty { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:44px; color:var(--txt4); gap:9px; font-size:13px; }
.empty .material-icons-round { font-size:34px; opacity:.2; }

.filters { display:flex; align-items:center; gap:9px; flex-wrap:wrap; padding-bottom:14px; }
.filter-lbl { font-size:9.5px; color:var(--txt4); font-weight:700; text-transform:uppercase; letter-spacing:.8px; }
.filter-sel { background:var(--surface); border:1.5px solid var(--border2); color:var(--txt1); border-radius:var(--r-sm); padding:7px 11px; font-size:12px; font-family:'Inter',sans-serif; cursor:pointer; outline:none; font-weight:500; }
.filter-sel:focus { border-color:#0EA5E9; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
.filter-count { margin-left:auto; font-size:12px; color:var(--txt4); font-weight:600; }

.pagination { display:flex; align-items:center; justify-content:space-between; padding-top:13px; font-size:11.5px; color:var(--txt4); }
.pag-links { display:flex; gap:4px; }
.pag-btn { width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:var(--r-sm); background:var(--bg); border:1.5px solid var(--border2); color:var(--txt2); cursor:pointer; font-size:12px; font-weight:700; text-decoration:none; transition:all .14s; }
.pag-btn:hover { border-color:#0EA5E9; color:#0EA5E9; background:#EFF9FF; }
.pag-btn.active { background:#0EA5E9; color:#fff; border-color:transparent; box-shadow:0 3px 10px rgba(14,165,233,.35); }

/* Visual refresh */
:root {
  --bg: #F4F7FB;
  --surface: #FFFFFF;
  --border: #DDE6F1;
  --border2: #B9C8DA;
  --txt1: #102033;
  --txt2: #263B53;
  --txt3: #52667D;
  --txt4: #8292A7;
  --vivid-blue: #2563EB;
  --sb-border: rgba(255,255,255,.14);
  --sb-txt: rgba(255,255,255,.82);
  --sb-txt-m: rgba(255,255,255,.56);
}
html, body {
  background:
    radial-gradient(circle at top left, rgba(37,99,235,.10), transparent 32rem),
    linear-gradient(180deg, #F7FAFD 0%, #e5f2ff 100%);
}
.sidebar {
  background:
    linear-gradient(180deg, rgba(3, 26, 59, 0.96) 0%, rgba(33,66,98,.98) 48%, rgba(3, 54, 67, 0.98) 100%);
  box-shadow: 8px 0 28px rgba(15,23,42,.22);
}
.sb-logo { padding:22px 18px 18px; }
.sb-logo-dot {
  background:#F59E0B;
  box-shadow:0 0 0 4px rgba(245,158,11,.14), 0 0 14px rgba(245,158,11,.55);
}
.sb-logo-name { letter-spacing:.2px; }
.sb-section { color:rgba(255,255,255,.64); }
.sb-filter-group {
  margin-bottom:10px;
  padding:9px;
  border:1px solid rgba(255,255,255,.08);
  border-radius:10px;
  background:rgba(255,255,255,.045);
}
.sb-filter-lbl { color:rgba(255,255,255,.62); }
.sb-filters .sb-filter-group:nth-of-type(5) .sb-filter-lbl {
  font-size:0;
}
.sb-filters .sb-filter-group:nth-of-type(5) .sb-filter-lbl::after {
  content:"Mois";
  font-size:9px;
}
.sb-fsel {
  min-height:38px;
  background-color:rgba(255,255,255,.10);
  border-color:rgba(255,255,255,.18);
  color:#FFFFFF;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.07);
}
.sb-fsel:hover { border-color:rgba(94,234,212,.48); background-color:rgba(255,255,255,.13); }
.sb-fsel:focus {
  border-color:#5EEAD4;
  background-color:rgba(20,184,166,.16);
  box-shadow:0 0 0 3px rgba(94,234,212,.16);
}
.sb-btn-apply {
  min-height:38px;
  background:linear-gradient(135deg,#2563EB,#14B8A6);
  box-shadow:0 10px 22px rgba(20,184,166,.18);
}
.sb-btn-apply:hover { background:linear-gradient(135deg,#1D4ED8,#0F766E); }
.sb-btn-reset {
  min-width:40px;
  min-height:38px;
  background:rgba(255,255,255,.10);
  border-color:rgba(255,255,255,.18);
  color:rgba(255,255,255,.78);
}
.sb-deconnect {
  background:rgba(244,63,94,.14);
  border-color:rgba(251,113,133,.28);
  color:#FDA4AF;
}
.topbar {
  background:rgba(255,255,255,.92);
  backdrop-filter:blur(14px);
  border-bottom:1px solid rgba(185,200,218,.72);
  box-shadow:0 10px 24px rgba(15,23,42,.06);
}
.tbar-icon {
  border:1px solid rgba(37,99,235,.14);
  box-shadow:0 8px 18px rgba(37,99,235,.10);
}
.tbar-left h1 { font-size:16px; letter-spacing:0; }
.content { padding:26px 30px; }
.alert, .card, .kpi-card {
  border-color:rgba(185,200,218,.72);
  box-shadow:0 10px 28px rgba(15,23,42,.07);
}
.card, .kpi-card { border-radius:14px; }
.card:hover, .kpi-card:hover {
  transform:translateY(-3px);
  box-shadow:0 18px 38px rgba(15,23,42,.11);
}
.card-head {
  padding-bottom:10px;
  border-bottom:1px solid rgba(221,230,241,.75);
}
.card-title { font-size:13.5px; letter-spacing:0; }
.kpi-top-bar { height:5px; }
.kpi-body { padding:17px; }
.kpi-ico {
  border-radius:12px;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.65);
}
.kpi-v { letter-spacing:0; }
.st {
  color:#334155;
  border-bottom:1px solid rgba(185,200,218,.8);
  padding:10px 0 12px;
}
.st .material-icons-round { color:#14B8A6; }
.tbl thead th {
  background:#F7FAFD;
  color:#64748B;
  border-bottom-color:#D5E0EC;
}
.tbl tbody tr:hover { background:#EFF6FF; }
.filter-sel, .pag-btn {
  background:#FFFFFF;
  border-color:#C9D6E4;
}
.btn-primary {
  background:linear-gradient(135deg,#2563EB,#14B8A6);
  box-shadow:0 8px 18px rgba(37,99,235,.20);
}
.btn-primary:hover { background:linear-gradient(135deg,#1D4ED8,#0F766E); }
.btn-ghost:hover {
  background:#F0FDFA;
  color:#0F766E;
  border-color:#5EEAD4;
}

/* Alignement avec dashboard.php */
:root {
  --navy: #0b1f3a;
  --navy-2: #122444;
  --blue: #1e71f7;
  --blue-lt: #e8f1fe;
  --teal: #0cbeb8;
  --teal-lt: #e6f8f7;
  --amber: #f59e0b;
  --amber-lt: #fff8e6;
  --rose: #e5405e;
  --rose-lt: #fdeef1;
  --violet: #823df9;
  --violet-lt: #f3effe;
  --green: #16a34a;
  --green-lt: #edfdf4;
  --pink: #f7449d;
  --pink-lt: #fdf2f8;
  --bg: #f1f4f9;
  --surface: #ffffff;
  --border: #e4e8f0;
  --border2: #cbd5e1;
  --txt1: #0b1f3a;
  --txt2: #26364d;
  --txt3: #526174;
  --txt4: #64748b;
  --vivid-blue: var(--blue);
  --sidebar-w: 260px;
  --topbar-h: 72px;
  --r-sm: 10px;
  --r: 14px;
  --r-lg: 16px;
}
html, body {
  font-family:'DM Sans', sans-serif;
  background:var(--bg);
  color:var(--txt1);
}
.sidebar {
  width:var(--sidebar-w);
  background:var(--navy);
  padding-bottom:24px;
  box-shadow:none;
}
.sb-logo {
  padding:28px 22px 20px;
  border-bottom:1px solid rgba(255,255,255,.07);
  margin-bottom:8px;
}
.sb-logo-name {
  font-family:'DM Serif Display', serif;
  font-size:1.6rem;
  font-weight:400;
  letter-spacing:.5px;
}
.sb-logo-dot {
  width:8px;
  height:8px;
  background:var(--blue);
  box-shadow:0 0 8px var(--blue);
}
.sb-logo-sub {
  color:rgba(255,255,255,.4);
  font-size:.72rem;
  letter-spacing:1.5px;
  padding-left:18px;
}
.sb-section {
  padding:14px 22px 8px;
  color:rgba(255,255,255,.25);
  font-size:.65rem;
  letter-spacing:2px;
}
.sb-section::after { display:none; }
.sb-filters { padding:0 16px 14px; }
.sb-filter-group {
  padding:0;
  margin-bottom:12px;
  background:transparent;
  border:0;
  border-radius:0;
}
.sb-filter-lbl {
  color:rgba(255,255,255,.45);
  font-size:.66rem;
  letter-spacing:1.6px;
  margin-bottom:6px;
}
.sb-fsel {
  min-height:42px;
  border-radius:12px;
  background-color:rgba(255,255,255,.075);
  border:1px solid rgba(255,255,255,.12);
  color:rgba(255,255,255,.88);
  font-family:'DM Sans',sans-serif;
  font-size:.84rem;
  padding:9px 34px 9px 12px;
}
.sb-fsel:hover,
.sb-fsel:focus {
  background-color:rgba(30,111,241,.16);
  border-color:rgba(30,111,241,.42);
  box-shadow:none;
}
.sb-fsel option { background:var(--navy-2); color:#fff; }
.sb-filter-row { margin-bottom:10px; }
.sb-btn-apply {
  min-height:42px;
  border-radius:12px;
  background:var(--blue);
  font-family:'DM Sans',sans-serif;
  box-shadow:none;
}
.sb-btn-apply:hover { background:#155fd8; }
.sb-btn-reset {
  min-width:42px;
  min-height:42px;
  border-radius:12px;
  background:rgba(255,255,255,.08);
  border-color:rgba(255,255,255,.14);
}
.sb-deconnect {
  margin:auto 22px 0;
  border-radius:12px;
  background:rgba(229,64,94,.12);
  border:1px solid rgba(229,64,94,.2);
  color:#f87171;
  padding:11px 16px;
}
.sb-deconnect:hover { background:var(--rose); color:#fff; }
.sb-footer {
  margin-top:14px;
  padding:12px 22px 0;
  border-top:1px solid rgba(255,255,255,.07);
  color:rgba(255,255,255,.35);
}
.main {
  margin-left:var(--sidebar-w);
  background:var(--bg);
}
.topbar {
  height:auto;
  min-height:var(--topbar-h);
  position:sticky;
  padding:18px 38px;
  background:rgba(241,244,249,.94);
  border-bottom:0;
  box-shadow:none;
  backdrop-filter:blur(10px);
}
.tbar-icon {
  width:42px;
  height:42px;
  border-radius:12px;
  background:var(--blue-lt) !important;
  border:0;
  box-shadow:none;
}
.tbar-icon .material-icons-round { color:var(--blue) !important; }
.tbar-div { display:none; }
.tbar-left h1 {
  font-size:1.35rem;
  font-weight:700;
  color:var(--txt1);
}
.tbar-left p {
  color:var(--txt4);
  font-size:.88rem;
}
.content {
  padding:22px 38px 48px;
}
.btn {
  border-radius:30px;
  padding:8px 15px;
  font-family:'DM Sans',sans-serif;
  font-size:.8rem;
}
.btn-primary {
  background:var(--blue);
  box-shadow:none;
}
.btn-primary:hover { background:#155fd8; }
.btn-ghost {
  background:#fff;
  border:1px solid var(--border);
  color:var(--txt4);
}
.alert {
  border-radius:16px;
  padding:15px 18px;
  box-shadow:0 8px 22px rgba(15,31,58,.045);
}
.kpi-strip {
  gap:14px;
  margin-bottom:32px;
}
.kpi-card {
  border-radius:16px;
  border:1px solid var(--border);
  box-shadow:0 8px 20px rgba(15,31,58,.045);
}
.kpi-card:nth-child(6n+1) { background:linear-gradient(180deg,#fff 0%,#f4f8ff 100%); }
.kpi-card:nth-child(6n+2) { background:linear-gradient(180deg,#fff 0%,#fdf4fb 100%); }
.kpi-card:nth-child(6n+3) { background:linear-gradient(180deg,#fff 0%,#fff5f6 100%); }
.kpi-card:nth-child(6n+4) { background:linear-gradient(180deg,#fff 0%,#f1fbf5 100%); }
.kpi-card:nth-child(6n+5) { background:linear-gradient(180deg,#fff 0%,#f8f5ff 100%); }
.kpi-card:nth-child(6n) { background:linear-gradient(180deg,#fff 0%,#fff8ed 100%); }
.kpi-card:hover,
.card:hover {
  transform:translateY(-3px);
  box-shadow:0 14px 32px rgba(15,31,58,.09);
}
.kpi-body { padding:18px 16px; }
.kpi-ico {
  width:34px;
  height:34px;
  border-radius:10px;
  box-shadow:none;
}
.kpi-v {
  font-size:1.75rem;
  font-weight:800;
  font-family:'DM Sans',sans-serif;
}
.kpi-lbl {
  color:var(--txt4);
  font-size:.7rem;
  letter-spacing:.5px;
}
.kpi-s2 { color:var(--txt4); font-size:.76rem; }
.card {
  border-radius:16px;
  border:1px solid var(--border);
  box-shadow:0 8px 20px rgba(15,31,58,.045);
  padding:18px;
}
.card-head {
  border-bottom:0;
  padding-bottom:0;
  margin-bottom:14px;
}
.card-title {
  font-size:.95rem;
  font-weight:700;
  color:var(--txt1);
}
.card-sub {
  font-size:.8rem;
  color:var(--txt4);
  line-height:1.35;
}
.card-tag,
.badge,
.pill {
  border-radius:30px;
}
.st {
  font-size:.65rem;
  font-weight:700;
  letter-spacing:1.8px;
  color:var(--txt4);
  border-bottom:0;
  margin:2px 0 14px;
  padding:0;
}
.st-badge {
  background:var(--blue-lt);
  color:var(--blue);
  border:0;
}
.tbl thead th {
  background:#f8fafc;
  color:var(--txt4);
  border-bottom:1px solid var(--border);
}
.tbl tbody tr:hover { background:#f8fbff; }
.podium-card {
  border-radius:16px;
  background:#fff;
  border-color:var(--border);
}
.filter-sel,
.pag-btn {
  border-radius:12px;
  font-family:'DM Sans',sans-serif;
}

@media(max-width:1200px) { .c3,.c4{grid-template-columns:1fr 1fr} }
@media(max-width:960px)  { .c2,.c12,.c21{grid-template-columns:1fr} .main{margin-left:0}.sidebar{display:none}.topbar{padding:18px 20px}.content{padding:20px} }
@media(max-width:640px)  { .c3,.c4{grid-template-columns:1fr} .content{padding:13px 11px} }
</style>
</head>
<body>
<div class="app">

<aside class="sidebar">
  <div class="sb-logo">
    <div class="sb-logo-row">
      <div class="sb-logo-dot"></div>
      <div class="sb-logo-name">ALTUTEX</div>
    </div>
    <div class="sb-logo-sub">Ressources Humaines</div>
  </div>

  <div class="sb-section">Recherche & Filtres</div>
  <form class="sb-filters" method="GET" action="<?= $STATIC_BASE ?>">
    <input type="hidden" name="action" value="reporting">
    <input type="hidden" name="reporting_view" value="<?= safe($action) ?>">
    <input type="hidden" name="annee" value="<?= (int)$annee ?>">

    <div class="sb-filter-group">
      <label class="sb-filter-lbl">Département</label>
      <select name="f_dept" class="sb-fsel" onchange="this.form.submit()">
        <option value="">Tous les départements</option>
        <?php foreach(($departements ?? []) as $d): ?>
        <option value="<?= safe($d['nom_departement']) ?>"
          <?= (($_GET['f_dept'] ?? '') === $d['nom_departement']) ? 'selected' : '' ?>>
          <?= safe($d['nom_departement']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sb-filter-group">
      <label class="sb-filter-lbl">Type de contrat</label>
      <select name="f_contrat" class="sb-fsel" onchange="this.form.submit()">
        <option value="">Tous les contrats</option>
        <?php foreach(($contrats ?? []) as $c): ?>
        <option value="<?= safe($c['type_contrat']) ?>"
          <?= (($_GET['f_contrat'] ?? '') === $c['type_contrat']) ? 'selected' : '' ?>>
          <?= safe($c['type_contrat']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sb-filter-group" style="display:none">
      <label class="sb-filter-lbl" style="display:none">Genre</label>
      <select name="f_genre" class="sb-fsel" style="display:none" disabled>
        <option value="">Tous</option>
        <option value="F" <?= (($_GET['f_genre'] ?? '') === 'F') ? 'selected' : '' ?>>♀ Femmes</option>
        <option value="M" <?= (($_GET['f_genre'] ?? '') === 'M') ? 'selected' : '' ?>>♂ Hommes</option>
      </select>
    </div>

    <div class="sb-filter-group" style="display:none">
      <label class="sb-filter-lbl" style="display:none">Statut</label>
      <select name="f_statut" class="sb-fsel" style="display:none" disabled>
        <option value="">Tous les statuts</option>
        <?php foreach(($statuts ?? []) as $s): ?>
        <option value="<?= safe($s['statut']) ?>"
          <?= (($_GET['f_statut'] ?? '') === $s['statut']) ? 'selected' : '' ?>>
          <?= safe($s['statut']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="sb-filter-group">
      <label class="sb-filter-lbl">Période (mois)</label>
      <div class="sb-filter-row" style="display:block">
        <select name="f_mois" class="sb-fsel" onchange="this.form.submit()">
          <option value="">Toute l'annee</option>
          <?php foreach($moisNomsSb as $mi => $mn): ?>
          <option value="<?= $mi+1 ?>" <?= (int)($_GET['f_mois'] ?? 0) === $mi+1 ? 'selected' : '' ?>><?= $mn ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="sb-filter-btns">
      <button type="submit" class="sb-btn-apply">
        <span class="material-icons-round">filter_list</span>Appliquer
      </button>
      <a href="<?= $STATIC_BASE ?>?action=reporting&reporting_view=<?= safe($action) ?>&annee=<?= (int)$annee ?>" class="sb-btn-reset">
        <span class="material-icons-round">restart_alt</span>
      </a>
    </div>
  </form>

  <a href="<?= $STATIC_BASE ?>?action=logout" class="sb-deconnect">
    <span class="material-icons-round">logout</span>Déconnexion
  </a>

  <div class="sb-footer">
    <span class="sb-dot"></span>
    DWH · MariaDB · <?= date('d/m/Y') ?>
  </div>
</aside>

<div class="main">
  <div class="topbar">
    <div class="tbar-left">
      <div class="tbar-icon" style="background:<?= $pageBg ?>">
        <span class="material-icons-round" style="color:<?= $pageColor ?>;font-size:19px"><?= $pageIcon ?></span>
      </div>
      <div class="tbar-div"></div>
      <div>
        <h1><?= safe($pageTitle) ?></h1>
        <p><?= safe($pageSub) ?> · <?= (int)$annee ?></p>
      </div>
    </div>
    <div class="tbar-right">
      <a href="<?= HOME_URL ?>" class="btn btn-primary">
        <span class="material-icons-round">home</span>Accueil
      </a>
      <button class="btn btn-ghost" onclick="window.print()">
        <span class="material-icons-round">print</span>
      </button>
    </div>
  </div>

  <div class="content">

<?php
/* ════════════════════════════
   DASHBOARD
════════════════════════════ */
if($action==='dashboard'):
  $kd=$kpi_demo??[];$ka=$kpi_absences??[];$kt=$kpi_turnover??[];
  $eff    =(int)($kd['effectif_total']??0);
  $pctF   =(float)($kd['pct_femmes']??0);$pctM=(float)($kd['pct_hommes']??0);
  $nbF    =(int)($kd['nb_femmes']??0);$nbH=(int)($kd['nb_hommes']??0);
  $nbDept =(int)($kd['nb_departements']??0);
  $ancMoy =(float)($kd['anciennete_moyenne']??0);
  $txAbs  =(float)($ka['taux_absenteisme_moyen']??0);
  $totAbs =(int)($ka['total_absences']??0);
  $txDep  =(float)($kt['taux_depart_moyen']??0);
  $totSor =(int)($kt['total_sorties']??0);
  $perf   =(float)($kd['performance_moyenne']??0);
  $fmNorm=$par_mois_dash??[];
  if(empty($fmNorm)){
    $moisAbr=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
    for($mi=1;$mi<=12;$mi++) $fmNorm[]=['mois_num'=>$mi,'mois_label'=>$moisAbr[$mi-1],'nb_formations'=>0];
  }
  $fmVals=array_column($fmNorm,'nb_formations');
  $fmMax=!empty($fmVals)&&max($fmVals)>0?max($fmVals):1;
  $totForm=array_sum($fmVals);
  $clsAbs=$txAbs>10?'alert-danger':($txAbs>5?'alert-warning':'alert-success');
  $icoAbs=$txAbs>10?'error':($txAbs>5?'warning':'check_circle');
?>
<div class="alert <?= $clsAbs ?>">
  <span class="material-icons-round"><?= $icoAbs ?></span>
  <div>
    <strong><?= $txAbs>10?'Absentéisme critique — Intervention requise':($txAbs>5?'Absentéisme élevé — Surveillance recommandée':'Indicateurs RH dans les normes') ?></strong>
    &nbsp;·&nbsp;Absentéisme <strong><?= pct($txAbs) ?></strong> · <?= nbf($totAbs) ?> absences · Turnover <strong><?= pct($txDep) ?></strong>
  </div>
</div>

<div class="kpi-strip">
<?php
$kpis=[
  ['lbl'=>'Effectif actif',  'v'=>nbf($eff),              's'=>$nbDept.' départements',  'ico'=>'groups',        'c'=>'#11aef7','bg'=>'#d5efff','gr'=>'linear-gradient(135deg,#0EA5E9,#38BDF8)','tr'=>'','tc'=>''],
  ['lbl'=>'Absentéisme',     'v'=>pct($txAbs),             's'=>nbf($totAbs).' absences', 'ico'=>'sick',          'c'=>$txAbs>10?'#ff2b2b':($txAbs>5?'#F97316':'#ff7931'),'bg'=>$txAbs>10?'#FFF1F2':($txAbs>5?'#FFF7ED':'#ECFDF5'),'gr'=>$txAbs>10?'linear-gradient(135deg,#EF4444,#F87171)':($txAbs>5?'linear-gradient(135deg,#F97316,#FB923C)':'linear-gradient(135deg,#10B981,#34D399)'),'tr'=>$txAbs>10?'⚠ Critique':($txAbs>5?'↑ Élevé':'✓ Normal'),'tc'=>$txAbs>10?'badge-r':($txAbs>5?'badge-o':'badge-g')],
  ['lbl'=>'Taux de départ',  'v'=>pct($txDep),             's'=>nbf($totSor).' sorties',  'ico'=>'trending_down', 'c'=>$txDep>10?'#EF4444':($txDep>5?'#ff7411':'#76d313'),'bg'=>$txDep>10?'#FFF1F2':($txDep>5?'#FFF7ED':'#ECFDF5'),'gr'=>$txDep>10?'linear-gradient(135deg,#EF4444,#F87171)':($txDep>5?'linear-gradient(135deg,#F97316,#FB923C)':'linear-gradient(135deg,#10B981,#34D399)'),'tr'=>$txDep>10?'⚠ Critique':($txDep>5?'↑ Élevé':'✓ OK'),'tc'=>$txDep>10?'badge-r':($txDep>5?'badge-o':'badge-g')],
  ['lbl'=>'Ancienneté moy.', 'v'=>number_format($ancMoy,1),'s'=>'années de service',     'ico'=>'schedule',      'c'=>'#b743ff','bg'=>'#ddd6ff','gr'=>'linear-gradient(135deg,#8B5CF6,#A78BFA)','tr'=>'','tc'=>''],
  ['lbl'=>'Formations',      'v'=>nbf($totForm),            's'=>'sessions · '.(int)$annee,'ico'=>'school',       'c'=>'#31c417','bg'=>'#ceffe8','gr'=>'linear-gradient(135deg,#10B981,#34D399)','tr'=>'','tc'=>''],
  ['lbl'=>'Performance',     'v'=>number_format($perf,1),   's'=>'score moyen /100',      'ico'=>'star',          'c'=>'#F59E0B','bg'=>'#fff7d5','gr'=>'linear-gradient(135deg,#F59E0B,#FCD34D)','tr'=>'','tc'=>''],
];
foreach($kpis as $k): ?>
  <div class="kpi-card">
    <div class="kpi-top-bar" style="background:<?= $k['gr'] ?>"></div>
    <div class="kpi-body">
      <div class="kpi-ico-row">
        <div class="kpi-ico" style="background:<?= $k['bg'] ?>">
          <span class="material-icons-round" style="color:<?= $k['c'] ?>"><?= $k['ico'] ?></span>
        </div>
        <?php if($k['tr']): ?><span class="badge <?= $k['tc'] ?>"><?= $k['tr'] ?></span><?php endif; ?>
      </div>
      <div class="kpi-v" style="color:<?= $k['c'] ?>"><?= $k['v'] ?></div>
      <div class="kpi-lbl"><?= $k['lbl'] ?></div>
      <div class="kpi-s2"><?= $k['s'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="st"><span class="material-icons-round" style="color:#0EA5E9">people</span>Démographie<span class="st-badge"><?= (int)$annee ?></span></div>
<div class="row c3">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Analyse de la Structure des Effectifs </div><div class="card-sub">par Genre H / F</div></div></div>
    <div class="gender-bar">
      <div class="gf" style="width:<?= $pctF ?>%"></div>
      <div class="gm" style="width:<?= $pctM ?>%"></div>
    </div>
    <div class="gender-leg" style="margin-bottom:13px">
      <span><span class="gdot" style="background:#FF2D8B"></span>Femmes <strong style="color:#FF2D8B"><?= $nbF ?></strong> (<?= pct($pctF) ?>)</span>
      <span><span class="gdot" style="background:#0EA5E9"></span>Hommes <strong style="color:#0EA5E9"><?= $nbH ?></strong> (<?= pct($pctM) ?>)</span>
    </div>
    <div class="chart-w" style="height:125px"><canvas id="cGenre"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Pyramide des âges</div><div class="card-sub">H vs F par tranche</div></div><span class="card-tag">Structure</span></div>
    <div class="chart-w" style="height:185px"><canvas id="cPyramide"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Segmentation par types de contrats</div><div class="card-sub">Répartition personnel</div></div></div>
    <div class="chart-w" style="height:185px"><canvas id="cContrats"></canvas></div>
  </div>
</div>

<div class="st"><span class="material-icons-round" style="color:#EF4444">trending_down</span>Absences & Turnover</div>
<div class="row c2">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Répartition des effectif par département</div><div class="card-sub">Collaborateurs actifs</div></div></div>
    <div class="chart-w" style="height:220px"><canvas id="cDept"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head">
      <div><div class="card-title">Évaluation Quantitative du Volume d'Absentéisme Mensuel </div><div class="card-sub">Volume mensuel par mois</div></div>
      <span class="badge <?= $txAbs>10?'badge-r':($txAbs>5?'badge-o':'badge-g') ?>"><?= pct($txAbs) ?></span>
    </div>
    <div class="chart-w" style="height:220px"><canvas id="cAbsMobile"></canvas></div>
  </div>
</div>

<div class="st"><span class="material-icons-round" style="color:#F59E0B">workspace_premium</span>Fidélité & Ancienneté</div>
<div class="row c2">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Répartition des effectifs par tranches d'ancienneté</div><div class="card-sub">0–2 · 3–5 · 6–10 · 10+ ans</div></div><span class="card-tag">Fidélité</span></div>
    <div class="chart-w" style="height:215px"><canvas id="cDistAnc"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Classement des cinq employés les plus anciens </div><div class="card-sub">Collaborateurs les plus fidèles</div></div><span class="badge badge-o">Ancienneté</span></div>
    <?php $top5=$top5_fideles??[];$medals=['🥇','🥈','🥉','④','⑤'];$mC=['#F97316','#64748B','#F59E0B','#8B5CF6','#09b1ff']; ?>
    <?php if(empty($top5)): ?><div class="empty"><span class="material-icons-round">emoji_events</span>Aucune donnée</div>
    <?php else: ?>
    <div class="podium-wrap">
      <?php foreach($top5 as $i=>$f): ?>
      <div class="podium-card rk<?= $i+1 ?>">
        <div class="pod-rank"><?= $medals[$i]??($i+1) ?></div>
        <div class="pod-anc" style="color:<?= $mC[$i]??'var(--txt2)' ?>"><?= number_format((float)($f['anciennete_ans']??0),1) ?><span style="font-size:11px;font-weight:400;color:var(--txt4)"> ans</span></div>
        <div class="pod-name"><?= safe(($f['nom']??'').' '.($f['prenom']??'')) ?></div>
        <div class="pod-dept"><?= safe($f['departement']??'') ?></div>
        <?php if(!empty($f['salaire_net'])): ?><div class="pod-sal"><?= mnt($f['salaire_net']) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="row c2">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Évolution du turnover par mois</div><div class="card-sub">Sorties et démissions </div></div><span class="badge <?= $txDep>10?'badge-r':($txDep>5?'badge-o':'badge-g') ?>"><?= pct($txDep) ?></span></div>
    <div class="chart-w" style="height:215px"><canvas id="cTovMois"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Répartition de la parité par département</div><div class="card-sub">Parité H/F</div></div></div>
    <div class="chart-w" style="height:215px"><canvas id="cGenreDept"></canvas></div>
  </div>
</div>

<div class="st"><span class="material-icons-round" style="color:#10B981">school</span>Formations<span class="st-badge"><?= nbf($totForm) ?> sessions</span></div>
<div class="row c2">
  <div class="card">
    <div class="card-head">
      <div><div class="card-title">Suivi mensuel du nombre de formations réalisées  — <?= (int)$annee ?></div><div class="card-sub">formations mensuel </div></div>
      <span class="badge badge-g"><?= nbf($totForm) ?> total</span>
    </div>
    <div class="fm-heatrow">
      <?php foreach($fmNorm as $fm):
        $val=(int)($fm['nb_formations']??0);
        $ratio=$fmMax>0?$val/$fmMax:0;
        $cls=$ratio<.05?'fm-0':($ratio<.2?'fm-1':($ratio<.4?'fm-2':($ratio<.6?'fm-3':($ratio<.8?'fm-4':'fm-5'))));
      ?>
      <div class="fm-cell <?= $cls ?>" title="<?= safe($fm['mois_label']??'') ?> : <?= $val ?> formation(s)">
        <span class="fc-lbl"><?= safe($fm['mois_label']??'') ?></span>
        <?php if($val>0): ?><span class="fc-val"><?= $val ?></span><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="hm-legend">
      <span style="margin-right:3px;text-transform:uppercase;letter-spacing:.4px">Intensité :</span>
      <div class="hm-swatch" style="background:#F0FDF4;border:1px solid #D1FAE5"></div>
      <div class="hm-swatch" style="background:#A7F3D0"></div>
      <div class="hm-swatch" style="background:#6EE7B7"></div>
      <div class="hm-swatch" style="background:#34D399"></div>
      <div class="hm-swatch" style="background:#10B981"></div>
    </div>
    <div class="chart-w" style="height:175px"><canvas id="cFormMoisHeat"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head">
      <div><div class="card-title">Tendance absences par département</div><div class="card-sub">Absences totales vs employés touchés</div></div>
    </div>
    <div class="chart-w" style="height:275px"><canvas id="cSynthTendAbs"></canvas></div>
  </div>
</div>

<script>
(function(){
  const C=Chart;
  C.defaults.font.family="'Inter',sans-serif";
  C.defaults.color='#7A8EAE';
  const grid='rgba(200,213,238,.4)';
  const tt={backgroundColor:'#fff',borderColor:'#E2E8F4',borderWidth:1,padding:12,titleColor:'#0B1D3A',bodyColor:'#1E3456',cornerRadius:10,titleFont:{weight:'700',size:12},bodyFont:{size:11.5}};
  const sc={x:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'}},y:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'},beginAtZero:true}};
  const pal=<?= $PAL_JS ?>;

  /* Genre — rose vif F, bleu M */
  new C(document.getElementById('cGenre'),{
    type:'doughnut',
    data:{labels:['Femmes','Hommes'],datasets:[{data:[<?= $pctF ?>,<?= $pctM ?>],backgroundColor:['#FF2D8B','#0EA5E9'],borderWidth:0,hoverOffset:8}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'78%',plugins:{legend:{display:false},tooltip:{...tt}}}
  });

  const pyD=<?= jj($repartition_age??[]) ?>;
  new C(document.getElementById('cPyramide'),{
    type:'bar',
    data:{labels:pyD.map(r=>r.tranche),datasets:[
      {label:'Femmes',data:pyD.map(r=>r.femmes),backgroundColor:'rgba(255,45,139,.75)',borderRadius:5},
      {label:'Hommes',data:pyD.map(r=>r.hommes),backgroundColor:'rgba(14,165,233,.75)',borderRadius:5}
    ]},
    options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9,padding:11}},tooltip:{...tt}},scales:sc}
  });

  const ctD=<?= jj($repartition_contrat??[]) ?>;
  new C(document.getElementById('cContrats'),{
    type:'doughnut',
    data:{labels:ctD.map(r=>r.contrat),datasets:[{data:ctD.map(r=>r.effectif),backgroundColor:pal,borderWidth:0,hoverOffset:8}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{display:true,position:'right',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}}}
  });

  const dpD=<?= jj($effectif_dep??[]) ?>;
  new C(document.getElementById('cDept'),{
    type:'bar',
    data:{labels:dpD.map(r=>r.departement),datasets:[{data:dpD.map(r=>r.effectif),backgroundColor:dpD.map((_,i)=>pal[i%pal.length]),borderRadius:7,borderSkipped:false}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}
  });

  /* Absences dashboard — barres simples SANS moyenne mobile */
  const abM=<?= jj($evolution_absences??[]) ?>;
  const absV=abM.map(r=>r.nb_absences);
  new C(document.getElementById('cAbsMobile'),{
    type:'bar',
    data:{
      labels:abM.map(r=>r.mois_label||r.mois_num),
      datasets:[{
        label:'Absences',
        data:absV,
        backgroundColor:absV.map(v=>v>0?'rgba(239,68,68,.65)':'rgba(239,68,68,.15)'),
        borderColor:'rgba(239,68,68,.5)',
        borderWidth:1.5,
        borderRadius:7,
        borderSkipped:false
      }]
    },
    options:{
      responsive:true,maintainAspectRatio:false,
      plugins:{legend:{display:false},tooltip:{...tt,callbacks:{title:ctx=>ctx[0].label,label:ctx=>' '+ctx.parsed.y+' absences'}}},
      scales:{
        x:{...sc.x,grid:{display:false}},
        y:{...sc.y,ticks:{...sc.y.ticks,stepSize:1}}
      }
    }
  });

  const dA=<?= jj($distribution_anc_fine??$distribution_anc??[]) ?>;
  new C(document.getElementById('cDistAnc'),{
    type:'bar',
    data:{labels:dA.map(r=>r.tranche),datasets:[{label:'Employés',data:dA.map(r=>r.effectif),backgroundColor:['rgba(249,115,22,.85)','rgba(139,92,246,.85)','rgba(14,165,233,.85)','rgba(16,185,129,.85)'],borderRadius:10,borderSkipped:false}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}
  });

  const tvM=<?= jj($evolution_turnover??[]) ?>;
  new C(document.getElementById('cTovMois'),{
    type:'bar',
    data:{labels:tvM.map(r=>r.mois_label||r.mois_num),datasets:[
      {label:'Sorties totales',data:tvM.map(r=>r.total_sorties),backgroundColor:'rgba(148,163,184,.3)',borderRadius:4},
      {label:'Démissions',data:tvM.map(r=>r.demissions),backgroundColor:'rgba(239,68,68,.8)',borderRadius:4},
      {label:'Fins contrat',data:tvM.map(r=>r.fins_contrat),backgroundColor:'rgba(249,115,22,.8)',borderRadius:4}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:sc}
  });

  /* Genre par dept — rose vif F */
  const gdD=<?= jj($genre_par_dept??[]) ?>;
  new C(document.getElementById('cGenreDept'),{
    type:'bar',
    data:{labels:gdD.map(r=>r.departement),datasets:[
      {label:'Femmes',data:gdD.map(r=>r.femmes),backgroundColor:'rgba(255,45,139,.8)',borderRadius:4},
      {label:'Hommes',data:gdD.map(r=>r.hommes),backgroundColor:'rgba(14,165,233,.8)',borderRadius:4}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:{...sc,x:{...sc.x,stacked:true},y:{...sc.y,stacked:true}}}
  });

  const fmNormJS=<?= jj($fmNorm) ?>;
  const fmV=fmNormJS.map(r=>r.nb_formations||0);
  const fmMaxJ=Math.max(...fmV,1);
  const fmTrend=fmV.map((v,i)=>i===0?v:+((v+fmV[i-1])/2).toFixed(1));
  new C(document.getElementById('cFormMoisHeat'),{
    type:'bar',
    data:{labels:fmNormJS.map(r=>r.mois_label),datasets:[
      {type:'bar',label:'Formations',data:fmV,backgroundColor:fmV.map(v=>{const r=v/fmMaxJ;return r>=.8?'rgba(16,185,129,.9)':r>=.6?'rgba(16,185,129,.7)':r>=.4?'rgba(52,211,153,.65)':r>=.2?'rgba(110,231,183,.7)':r>0?'rgba(167,243,208,.75)':'rgba(209,250,229,.4)'}),borderWidth:1.5,borderRadius:8,borderSkipped:false,order:2},
      {type:'line',label:'Tendance',data:fmTrend,borderColor:'#10B981',backgroundColor:'rgba(16,185,129,.07)',tension:.45,fill:true,pointRadius:4,pointBackgroundColor:'#10B981',pointBorderColor:'#fff',pointBorderWidth:2,borderWidth:2.5,order:1}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:{...sc,y:{...sc.y,ticks:{...sc.y.ticks,stepSize:1}}}}
  });

  const synthAbs=<?= jj($synthese_tendance_abs??[]) ?>;
  new C(document.getElementById('cSynthTendAbs'),{
    type:'bar',
    data:{labels:synthAbs.map(r=>r.mois_label),datasets:[
      {type:'bar',label:'Absences totales',data:synthAbs.map(r=>r.nb_absences),backgroundColor:'rgba(14,165,233,.5)',borderColor:'rgba(14,165,233,.7)',borderWidth:1.5,borderRadius:7,order:2},
      {type:'line',label:'Employés absents',data:synthAbs.map(r=>r.nb_employes_absents),borderColor:'#F97316',backgroundColor:'rgba(249,115,22,.07)',tension:.45,fill:true,pointRadius:4,pointBackgroundColor:'#F97316',pointBorderColor:'#fff',pointBorderWidth:2,borderWidth:2.5,order:1,yAxisID:'y2'}
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt,callbacks:{afterBody:ctx=>{const i=ctx[0]?.dataIndex;const d=synthAbs[i]?.dept_critique;return d&&d!=='—'?[`Dept : ${d}`]:[]}}}},scales:{...sc,y2:{position:'right',grid:{display:false},ticks:{font:{size:10.5},color:'#7A8EAE'},beginAtZero:true}}}
  });
})();
</script>

<?php
/* ════════════════════════════
   ABSENTÉISME
════════════════════════════ */
elseif($action==='absenteisme'):
  $kpi=$kpi??[];
  $txAbs=(float)($kpi['taux_absenteisme_moyen']??0);
  $totAbs=(int)($kpi['total_absences']??0);
  $empAbs=(int)($kpi['employes_absents']??0);
  $moyEmp=$empAbs>0?round($totAbs/$empAbs,1):0;
  $cls=$txAbs>10?'alert-danger':($txAbs>5?'alert-warning':'alert-success');
  $ico=$txAbs>10?'error':($txAbs>5?'warning':'check_circle');
  /* ✅ CORRIGÉ : f_mois_debut / f_mois_fin */
  $moisDebut=(int)($filtre_params['f_mois_debut']??1);
  $moisFin=(int)($filtre_params['f_mois_fin']??12);
  $moisNoms=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>
<div class="alert <?= $cls ?>">
  <span class="material-icons-round"><?= $ico ?></span>
  <div><strong><?= $txAbs>10?'Absentéisme critique':($txAbs>5?'Absentéisme élevé':'Absentéisme maîtrisé') ?></strong>
  &nbsp;·&nbsp;<strong><?= nbf($totAbs) ?></strong> absences · <strong><?= nbf($empAbs) ?></strong> employés · Moy. <strong><?= $moyEmp ?></strong>/employé · Taux <strong><?= pct($txAbs) ?></strong>
  · Période : <strong><?= $moisNoms[$moisDebut-1] ?> → <?= $moisNoms[$moisFin-1] ?></strong></div>
</div>

<div class="kpi-strip">
<?php
$clrA=$txAbs>10?'#EF4444':($txAbs>5?'#F97316':'#10B981');
$bgA =$txAbs>10?'#FFF1F2':($txAbs>5?'#FFF7ED':'#ECFDF5');
$grA =$txAbs>10?'linear-gradient(135deg,#EF4444,#F87171)':($txAbs>5?'linear-gradient(135deg,#F97316,#FB923C)':'linear-gradient(135deg,#10B981,#34D399)');
$abKpis=[
  ['lbl'=>'Total absences',   'v'=>nbf($totAbs),'s'=>'enregistrements',   'ico'=>'event_busy','c'=>'#EF4444','bg'=>'#FFF1F2','gr'=>'linear-gradient(135deg,#EF4444,#F87171)','tr'=>'','tc'=>''],
  ['lbl'=>'Employés absents', 'v'=>nbf($empAbs),'s'=>'touchés',           'ico'=>'person_off', 'c'=>'#F97316','bg'=>'#FFF7ED','gr'=>'linear-gradient(135deg,#F97316,#FB923C)','tr'=>'','tc'=>''],
  ['lbl'=>'Taux moyen',       'v'=>pct($txAbs), 's'=>'mensuel',           'ico'=>'percent',    'c'=>$clrA,    'bg'=>$bgA,    'gr'=>$grA, 'tr'=>$txAbs>10?'⚠ Critique':($txAbs>5?'↑ Élevé':'✓ OK'),'tc'=>$txAbs>10?'badge-r':($txAbs>5?'badge-o':'badge-g')],
  ['lbl'=>'Moy./employé',     'v'=>$moyEmp,     's'=>'absences/personne', 'ico'=>'equalizer',  'c'=>'#0EA5E9','bg'=>'#EFF9FF','gr'=>'linear-gradient(135deg,#0EA5E9,#38BDF8)','tr'=>'','tc'=>''],
];
foreach($abKpis as $k): ?>
  <div class="kpi-card">
    <div class="kpi-top-bar" style="background:<?= $k['gr'] ?>"></div>
    <div class="kpi-body">
      <div class="kpi-ico-row">
        <div class="kpi-ico" style="background:<?= $k['bg'] ?>"><span class="material-icons-round" style="color:<?= $k['c'] ?>"><?= $k['ico'] ?></span></div>
        <?php if($k['tr']): ?><span class="badge <?= $k['tc'] ?>"><?= $k['tr'] ?></span><?php endif; ?>
      </div>
      <div class="kpi-v" style="color:<?= $k['c'] ?>"><?= $k['v'] ?></div>
      <div class="kpi-lbl"><?= $k['lbl'] ?></div>
      <div class="kpi-s2"><?= $k['s'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="st"><span class="material-icons-round" style="color:#EF4444">timeline</span>Évolution temporelle</div>
<div class="row c2">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Tendance + Moy. mobile 3 mois</div><div class="card-sub">Barres + courbe lissée</div></div></div>
    <div class="chart-w" style="height:235px"><canvas id="cAbsTend"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Taux d'absentéisme mensuel (%)</div><div class="card-sub">(jours absence / jours travaillés) × 100</div></div></div>
    <div class="chart-w" style="height:235px"><canvas id="cTxMois"></canvas></div>
  </div>
</div>

<div class="st"><span class="material-icons-round" style="color:#F97316">pie_chart</span>Répartitions</div>
<div class="row c3">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Absences par motif</div><div class="card-sub">Causes</div></div></div>
    <div class="chart-w" style="height:205px"><canvas id="cMotifs"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Par département</div><div class="card-sub">Services critiques</div></div><span class="card-tag">⚠ Risque</span></div>
    <div class="chart-w" style="height:205px"><canvas id="cAbsDept"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Par genre</div><div class="card-sub">F vs H</div></div></div>
    <div class="chart-w" style="height:205px"><canvas id="cAbsGenre"></canvas></div>
  </div>
</div>

<div class="st"><span class="material-icons-round" style="color:#0EA5E9">person_search</span>Absentéisme par employé</div>
<div class="card">
  <div class="card-head"><div><div class="card-title">Absentéisme par collaborateur</div><div class="card-sub">Trié par absences décroissantes · <?= $moisNoms[$moisDebut-1] ?> → <?= $moisNoms[$moisFin-1] ?></div></div><span class="badge badge-r">⚠ Risque RH</span></div>
  <?php $absEmp=$abs_par_employe??[];$maxAbsEmp=!empty($absEmp)?max(array_column($absEmp,'nb_absences')):1; ?>
  <?php if(empty($absEmp)): ?><div class="empty"><span class="material-icons-round">check_circle</span>Aucune absence sur cette période</div>
  <?php else: ?>
  <div class="tbl-wrap">
    <table class="tbl">
      <thead><tr><th>#</th><th>Collaborateur</th><th>Département</th><th>Absences</th><th>Taux moy.</th><th>Mois touchés</th><th>Risque</th></tr></thead>
      <tbody>
      <?php foreach($absEmp as $i=>$e):
        $risk=(int)$e['nb_absences']>=8?'high':((int)$e['nb_absences']>=4?'med':'');
        $rL=$risk==='high'?'<span class="pill pr">⚠ Élevé</span>':($risk==='med'?'<span class="pill po">Modéré</span>':'<span class="pill pgy">Faible</span>');
      ?>
      <tr class="<?= $risk?'risk-'.$risk:'' ?>">
        <td><span class="pill pgy"><?= $i+1 ?></span></td>
        <td><strong><?= safe($e['employe']??($e['matricule']??'—')) ?></strong></td>
        <td><?= safe($e['departement']) ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:7px">
            <span style="font-family:'JetBrains Mono',monospace;font-weight:800;color:<?= $risk==='high'?'#EF4444':($risk==='med'?'#F97316':'var(--txt1)') ?>"><?= (int)$e['nb_absences'] ?></span>
            <div class="mp" style="width:65px"><div class="mp-f" style="width:<?= round(((int)$e['nb_absences']/$maxAbsEmp)*100) ?>%;background:<?= $risk==='high'?'#EF4444':($risk==='med'?'#F97316':'#0EA5E9') ?>"></div></div>
          </div>
        </td>
        <td style="color:var(--txt3)"><?= pct($e['taux_moyen']??0) ?></td>
        <td><span class="pill pb"><?= (int)($e['mois_touches']??0) ?> mois</span></td>
        <td><?= $rL ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="card" style="margin-top:13px">
  <div class="card-head"><div><div class="card-title">Top 10 — Employés les plus absents</div><div class="card-sub">Année complète</div></div><span class="badge badge-r">⚠ Action requise</span></div>
  <?php $tops=$top_absenteistes??[];$maxA=!empty($tops)?max(array_column($tops,'nb_absences')):1; ?>
  <?php if(empty($tops)): ?><div class="empty"><span class="material-icons-round">check_circle</span>Aucune absence significative</div>
  <?php else: ?>
    <?php foreach($tops as $i=>$t): ?>
    <div class="rank-item">
      <div class="rank-num"><?= $i+1 ?></div>
      <div class="rank-label" title="<?= safe($t['nom'].' '.$t['prenom']) ?>"><?= safe($t['matricule']) ?> — <?= safe($t['nom']) ?></div>
      <div class="rank-bar-o"><div class="rank-bar-f" style="width:<?= round(($t['nb_absences']/$maxA)*100) ?>%;background:<?= $PAL[$i%count($PAL)] ?>"></div></div>
      <div class="rank-v"><?= (int)$t['nb_absences'] ?></div>
      <span class="pill pgy" style="margin-left:5px;font-size:10px"><?= safe($t['departement']) ?></span>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
(function(){
  const C=Chart;C.defaults.font.family="'Inter',sans-serif";C.defaults.color='#7A8EAE';
  const grid='rgba(200,213,238,.4)';
  const tt={backgroundColor:'#fff',borderColor:'#E2E8F4',borderWidth:1,padding:12,titleColor:'#0B1D3A',bodyColor:'#1E3456',cornerRadius:10,titleFont:{weight:'700',size:12},bodyFont:{size:11.5}};
  const sc={x:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'}},y:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'},beginAtZero:true}};
  const pal=<?= $PAL_JS ?>;

  const abM=<?= jj($evolution_abs_mobile??$evolution_mensuelle??[]) ?>;
  const absV=abM.map(r=>r.nb_absences);
  const mm3=absV.map((_,i)=>i<2?null:+((absV[i]+absV[i-1]+absV[i-2])/3).toFixed(1));
  new C(document.getElementById('cAbsTend'),{type:'bar',data:{labels:abM.map(r=>r.mois_label||r.mois_num),datasets:[
    {type:'bar',label:'Absences',data:absV,backgroundColor:'rgba(239,68,68,.12)',borderColor:'rgba(239,68,68,.4)',borderWidth:1.5,borderRadius:6,order:2},
    {type:'line',label:'Moy. mobile 3 mois',data:mm3,borderColor:'#F97316',backgroundColor:'transparent',tension:.45,pointRadius:4,pointBackgroundColor:'#F97316',pointBorderColor:'#fff',pointBorderWidth:2,borderWidth:2.5,order:1}
  ]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:sc}});

  new C(document.getElementById('cTxMois'),{type:'line',data:{labels:abM.map(r=>r.mois_label||r.mois_num),datasets:[
    {label:'Taux (%)',data:abM.map(r=>r.taux_mensuel),borderColor:'#0EA5E9',backgroundColor:'rgba(14,165,233,.08)',tension:.45,fill:true,pointRadius:4,pointBackgroundColor:'#0EA5E9',pointBorderColor:'#fff',pointBorderWidth:2,borderWidth:2.5}
  ]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...tt}},scales:{...sc,y:{...sc.y,ticks:{...sc.y.ticks,callback:v=>v+'%'}}}}});

  const mD=<?= jj($par_type??[]) ?>;
  new C(document.getElementById('cMotifs'),{type:'bar',data:{labels:mD.map(r=>r.motif||r.type_abs||'?'),datasets:[{data:mD.map(r=>r.nb),backgroundColor:pal.slice(0,mD.length),borderRadius:7}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}});

  const dD=<?= jj($top_departements??[]) ?>;
  new C(document.getElementById('cAbsDept'),{type:'bar',data:{labels:dD.map(r=>r.departement),datasets:[{data:dD.map(r=>r.nb_absences),backgroundColor:dD.map((_,i)=>pal[i%pal.length]),borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}});

  /* Absences par genre — rose vif F */
  const gD=<?= jj($par_genre??[]) ?>;
  new C(document.getElementById('cAbsGenre'),{type:'doughnut',data:{labels:gD.map(r=>r.genre==='F'?'Femmes':'Hommes'),datasets:[{data:gD.map(r=>r.nb_absences),backgroundColor:['#FF2D8B','#0EA5E9'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'70%',plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}}}});
})();
</script>

<?php
/* ════════════════════════════
   TURNOVER
════════════════════════════ */
elseif($action==='turnover'):
  $kpi=$kpi??[];$txDep=(float)($kpi['taux_depart_moyen']??0);$totSor=(int)($kpi['total_sorties']??0);
  $dem=(int)($kpi['demissions']??0);$fc=(int)($kpi['fins_contrat']??0);$pctDem=(float)($kpi['pct_demissions']??0);
  $autres=max(0,$totSor-$dem-$fc);
  $cls=$txDep>10?'alert-danger':($txDep>5?'alert-warning':'alert-success');
?>
<div class="alert <?= $cls ?>">
  <span class="material-icons-round"><?= $txDep>10?'error':($txDep>5?'warning':'check_circle') ?></span>
  <div><strong><?= $txDep>10?'Turnover critique — Risque sous-effectif':($txDep>5?'Turnover élevé — Plan de rétention recommandé':'Turnover sous contrôle') ?></strong>
  &nbsp;·&nbsp;Taux <strong><?= pct($txDep) ?></strong> · <?= nbf($totSor) ?> sorties · <?= nbf($dem) ?> démissions (<?= pct($pctDem) ?>)</div>
</div>
<div class="kpi-strip">
<?php
$clrD=$txDep>10?'#EF4444':($txDep>5?'#F97316':'#10B981');
$bgD =$txDep>10?'#FFF1F2':($txDep>5?'#FFF7ED':'#ECFDF5');
$grD =$txDep>10?'linear-gradient(135deg,#EF4444,#F87171)':($txDep>5?'linear-gradient(135deg,#F97316,#FB923C)':'linear-gradient(135deg,#10B981,#34D399)');
$tvK=[
  ['lbl'=>'Sorties totales','v'=>nbf($totSor),'s'=>'sur la période',       'ico'=>'logout',       'c'=>'#0EA5E9','bg'=>'#EFF9FF','gr'=>'linear-gradient(135deg,#0EA5E9,#38BDF8)','tr'=>'','tc'=>''],
  ['lbl'=>'Démissions',     'v'=>nbf($dem),   's'=>pct($pctDem).' sorties','ico'=>'exit_to_app',  'c'=>'#EF4444','bg'=>'#FFF1F2','gr'=>'linear-gradient(135deg,#EF4444,#F87171)','tr'=>'','tc'=>''],
  ['lbl'=>'Fins de contrat','v'=>nbf($fc),    's'=>'CDD échus',            'ico'=>'timer_off',    'c'=>'#F97316','bg'=>'#FFF7ED','gr'=>'linear-gradient(135deg,#F97316,#FB923C)','tr'=>'','tc'=>''],
  ['lbl'=>'Taux de départ', 'v'=>pct($txDep), 's'=>'moyen mensuel',        'ico'=>'trending_down','c'=>$clrD,    'bg'=>$bgD,    'gr'=>$grD,'tr'=>$txDep>10?'⚠ Critique':($txDep>5?'↑ Élevé':'✓ OK'),'tc'=>$txDep>10?'badge-r':($txDep>5?'badge-o':'badge-g')],
];
foreach($tvK as $k): ?>
  <div class="kpi-card">
    <div class="kpi-top-bar" style="background:<?= $k['gr'] ?>"></div>
    <div class="kpi-body">
      <div class="kpi-ico-row">
        <div class="kpi-ico" style="background:<?= $k['bg'] ?>"><span class="material-icons-round" style="color:<?= $k['c'] ?>"><?= $k['ico'] ?></span></div>
        <?php if($k['tr']): ?><span class="badge <?= $k['tc'] ?>"><?= $k['tr'] ?></span><?php endif; ?>
      </div>
      <div class="kpi-v" style="color:<?= $k['c'] ?>"><?= $k['v'] ?></div>
      <div class="kpi-lbl"><?= $k['lbl'] ?></div>
      <div class="kpi-s2"><?= $k['s'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<div class="row c2">
  <div class="card"><div class="card-head"><div><div class="card-title">Évolution mensuelle</div><div class="card-sub">Sorties, démissions, fins contrat</div></div></div><div class="chart-w" style="height:245px"><canvas id="cTovMois"></canvas></div></div>
  <div class="card"><div class="card-head"><div><div class="card-title">Motifs de départ</div><div class="card-sub">Répartition globale</div></div></div><div class="chart-w" style="height:245px"><canvas id="cTovMotifs"></canvas></div></div>
</div>
<div class="row c2">
  <div class="card"><div class="card-head"><div><div class="card-title">Par département</div><div class="card-sub">Où partent les collaborateurs ?</div></div></div><div class="chart-w" style="height:225px"><canvas id="cTovDept"></canvas></div></div>
  <div class="card"><div class="card-head"><div><div class="card-title">Par type de contrat</div><div class="card-sub">Contrats les plus touchés</div></div></div><div class="chart-w" style="height:225px"><canvas id="cTovContrat"></canvas></div></div>
</div>
<div class="st"><span class="material-icons-round" style="color:#0EA5E9">insights</span>Synthèse mensuelle — Sorties · Formations</div>
<div class="card">
  <div class="card-head"><div><div class="card-title">Dynamique RH consolidée</div><div class="card-sub">Sorties / formations · Courbe tendance composite</div></div><span class="card-tag">Graphique combiné</span></div>
  <div class="chart-w" style="height:275px"><canvas id="cSyntheseRH"></canvas></div>
</div>
<script>
(function(){
  const C=Chart;C.defaults.font.family="'Inter',sans-serif";C.defaults.color='#7A8EAE';
  const grid='rgba(200,213,238,.4)';
  const tt={backgroundColor:'#fff',borderColor:'#E2E8F4',borderWidth:1,padding:12,titleColor:'#0B1D3A',bodyColor:'#1E3456',cornerRadius:10,titleFont:{weight:'700',size:12},bodyFont:{size:11.5}};
  const sc={x:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'}},y:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'},beginAtZero:true}};
  const pal=<?= $PAL_JS ?>;
  const tvM=<?= jj($evolution??[]) ?>;
  new C(document.getElementById('cTovMois'),{type:'bar',data:{labels:tvM.map(r=>r.mois_label||r.mois_num),datasets:[{label:'Sorties totales',data:tvM.map(r=>r.total_sorties),backgroundColor:'rgba(148,163,184,.3)',borderRadius:4},{label:'Démissions',data:tvM.map(r=>r.demissions),backgroundColor:'rgba(239,68,68,.8)',borderRadius:4},{label:'Fins contrat',data:tvM.map(r=>r.fins_contrat),backgroundColor:'rgba(249,115,22,.8)',borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:sc}});
  new C(document.getElementById('cTovMotifs'),{type:'doughnut',data:{labels:['Démissions','Fins contrat','Autres'],datasets:[{data:[<?= $dem ?>,<?= $fc ?>,<?= $autres ?>],backgroundColor:['#EF4444','#F97316','#CBD5E1'],borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:true,position:'right',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}}}});
  const dT=<?= jj($par_departement??[]) ?>;
  new C(document.getElementById('cTovDept'),{type:'bar',data:{labels:dT.map(r=>r.departement),datasets:[{data:dT.map(r=>r.total_sorties),backgroundColor:dT.map((_,i)=>pal[i%pal.length]),borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}});
  const cT=<?= jj($par_contrat??[]) ?>;
  new C(document.getElementById('cTovContrat'),{type:'doughnut',data:{labels:cT.map(r=>r.contrat),datasets:[{data:cT.map(r=>r.total_sorties),backgroundColor:pal.slice(0,cT.length),borderWidth:0,hoverOffset:8}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:true,position:'right',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}}}});
  const synth=<?= jj($synthese_mensuelle_rh??[]) ?>;
  new C(document.getElementById('cSyntheseRH'),{type:'bar',data:{labels:synth.map(r=>r.mois_label||r.mois_num),datasets:[{type:'bar',label:'Sorties',data:synth.map(r=>r.nb_sorties),backgroundColor:'rgba(239,68,68,.7)',borderRadius:5,order:2},{type:'bar',label:'Formations',data:synth.map(r=>r.nb_formations),backgroundColor:'rgba(14,165,233,.65)',borderRadius:5,order:2},{type:'line',label:'Tendance',data:synth.map(r=>r.tendance),borderColor:'#F97316',backgroundColor:'rgba(249,115,22,.06)',tension:.45,fill:true,pointRadius:4,pointBackgroundColor:'#F97316',pointBorderColor:'#fff',pointBorderWidth:2,borderWidth:2.5,order:1,yAxisID:'y2'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:{...sc,y2:{position:'right',grid:{display:false},ticks:{font:{size:10.5},color:'#7A8EAE'},beginAtZero:true}}}});
})();
</script>

<?php
/* ════════════════════════════
   FORMATIONS
════════════════════════════ */
elseif($action==='formations'):
  $kpi=$kpi??[];
  $totF=(int)($kpi['total_formations']??0);$empF=(int)($kpi['employes_formes']??0);
  $fdist=(int)($kpi['formations_distinctes']??0);$txCov=(float)($kpi['taux_couverture']??0);
  $fmNorm2=$par_mois??[];
  if(empty($fmNorm2)){
    $moisAbr=['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
    for($mi=1;$mi<=12;$mi++) $fmNorm2[]=['mois_num'=>$mi,'mois_label'=>$moisAbr[$mi-1],'nb_formations'=>0,'employes_formes'=>0];
  }
  $fmValsP=array_column($fmNorm2,'nb_formations');
  $fmMaxP=!empty($fmValsP)&&max($fmValsP)>0?max($fmValsP):1;
?>
<div class="alert <?= $txCov<50?'alert-warning':'alert-info' ?>">
  <span class="material-icons-round"><?= $txCov<50?'warning':'school' ?></span>
  <div><strong>Couverture formation : <?= pct($txCov) ?></strong>&nbsp;·&nbsp;<?= nbf($empF) ?> formés · <?= nbf($totF) ?> sessions · <?= nbf($fdist) ?> types.
  <?= $txCov<50?' Couverture insuffisante.':' Bonne dynamique.' ?></div>
</div>
<div class="kpi-strip">
<?php $fK=[
  ['lbl'=>'Sessions',   'v'=>nbf($totF), 's'=>'dispensées',           'ico'=>'library_books','c'=>'#0EA5E9','bg'=>'#EFF9FF','gr'=>'linear-gradient(135deg,#0EA5E9,#38BDF8)','tr'=>'','tc'=>''],
  ['lbl'=>'Formés',     'v'=>nbf($empF), 's'=>'collaborateurs',       'ico'=>'person',       'c'=>'#8B5CF6','bg'=>'#F5F3FF','gr'=>'linear-gradient(135deg,#8B5CF6,#A78BFA)','tr'=>'','tc'=>''],
  ['lbl'=>'Types',      'v'=>nbf($fdist),'s'=>'formations distinctes','ico'=>'category',     'c'=>'#F59E0B','bg'=>'#FFFBEB','gr'=>'linear-gradient(135deg,#F59E0B,#FCD34D)','tr'=>'','tc'=>''],
  ['lbl'=>'Couverture', 'v'=>pct($txCov),'s'=>'du personnel',         'ico'=>'pie_chart',    'c'=>$txCov<50?'#F97316':'#10B981','bg'=>$txCov<50?'#FFF7ED':'#ECFDF5','gr'=>$txCov<50?'linear-gradient(135deg,#F97316,#FB923C)':'linear-gradient(135deg,#10B981,#34D399)','tr'=>$txCov<50?'↓ Faible':'✓ Bonne','tc'=>$txCov<50?'badge-o':'badge-g'],
];
foreach($fK as $k): ?>
  <div class="kpi-card">
    <div class="kpi-top-bar" style="background:<?= $k['gr'] ?>"></div>
    <div class="kpi-body">
      <div class="kpi-ico-row">
        <div class="kpi-ico" style="background:<?= $k['bg'] ?>"><span class="material-icons-round" style="color:<?= $k['c'] ?>"><?= $k['ico'] ?></span></div>
        <?php if($k['tr']): ?><span class="badge <?= $k['tc'] ?>"><?= $k['tr'] ?></span><?php endif; ?>
      </div>
      <div class="kpi-v" style="color:<?= $k['c'] ?>"><?= $k['v'] ?></div>
      <div class="kpi-lbl"><?= $k['lbl'] ?></div>
      <div class="kpi-s2"><?= $k['s'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<div class="st"><span class="material-icons-round" style="color:#10B981">school</span>Formations par mois — <?= (int)$annee ?></div>
<div class="card" style="margin-bottom:14px">
  <div class="card-head">
    <div><div class="card-title">Heatmap & Tendance — <?= (int)$annee ?></div><div class="card-sub">Volume mensuel · Intensité colorée · Courbe tendance</div></div>
    <span class="badge badge-g"><?= nbf($totF) ?> sessions</span>
  </div>
  <div class="fm-heatrow">
    <?php foreach($fmNorm2 as $fm):
      $val=(int)($fm['nb_formations']??0);
      $ratio=$fmMaxP>0?$val/$fmMaxP:0;
      $cls=$ratio<.05?'fm-0':($ratio<.2?'fm-1':($ratio<.4?'fm-2':($ratio<.6?'fm-3':($ratio<.8?'fm-4':'fm-5'))));
    ?>
    <div class="fm-cell <?= $cls ?>" title="<?= safe($fm['mois_label']??'') ?> : <?= $val ?> formation(s)">
      <span class="fc-lbl"><?= safe($fm['mois_label']??'') ?></span>
      <?php if($val>0): ?><span class="fc-val"><?= $val ?></span><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="hm-legend">
    <span style="margin-right:3px">Intensité :</span>
    <div class="hm-swatch" style="background:#F0FDF4;border:1px solid #D1FAE5"></div>
    <div class="hm-swatch" style="background:#A7F3D0"></div>
    <div class="hm-swatch" style="background:#6EE7B7"></div>
    <div class="hm-swatch" style="background:#34D399"></div>
    <div class="hm-swatch" style="background:#10B981"></div>
    <span style="margin-left:4px">faible → élevé</span>
  </div>
  <div class="chart-w" style="height:190px"><canvas id="cFormHeat"></canvas></div>
</div>
<div class="row c2">
  <div class="card"><div class="card-head"><div><div class="card-title">Sessions & participants par mois</div><div class="card-sub">Volume mensuel réel</div></div></div><div class="chart-w" style="height:245px"><canvas id="cFormMois"></canvas></div></div>
  <div class="card"><div class="card-head"><div><div class="card-title">Par type de formation</div><div class="card-sub">Volume par catégorie</div></div></div><div class="chart-w" style="height:245px"><canvas id="cFormDept"></canvas></div></div>
</div>
<script>
(function(){
  const C=Chart;C.defaults.font.family="'Inter',sans-serif";C.defaults.color='#7A8EAE';
  const grid='rgba(200,213,238,.4)';
  const tt={backgroundColor:'#fff',borderColor:'#E2E8F4',borderWidth:1,padding:12,titleColor:'#0B1D3A',bodyColor:'#1E3456',cornerRadius:10,titleFont:{weight:'700',size:12},bodyFont:{size:11.5}};
  const sc={x:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'}},y:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'},beginAtZero:true}};
  const pal=<?= $PAL_JS ?>;
  const fmN=<?= jj($fmNorm2) ?>;
  const fmV=fmN.map(r=>+(r.nb_formations||0));
  const fmMaxJ=Math.max(...fmV,1);
  const fmT=fmV.map((v,i)=>i===0?v:+((v+fmV[i-1])/2).toFixed(1));
  new C(document.getElementById('cFormHeat'),{type:'bar',data:{labels:fmN.map(r=>r.mois_label||r.mois_num),datasets:[{type:'bar',label:'Formations',data:fmV,backgroundColor:fmV.map(v=>{const r=v/fmMaxJ;return r>=.8?'rgba(16,185,129,.88)':r>=.6?'rgba(16,185,129,.68)':r>=.4?'rgba(52,211,153,.6)':r>=.2?'rgba(110,231,183,.65)':r>0?'rgba(167,243,208,.7)':'rgba(209,250,229,.4)'}),borderWidth:1.5,borderRadius:8,borderSkipped:false,order:2},{type:'line',label:'Tendance',data:fmT,borderColor:'#10B981',backgroundColor:'rgba(16,185,129,.07)',tension:.45,fill:true,pointRadius:4,pointBackgroundColor:'#10B981',pointBorderColor:'#fff',pointBorderWidth:2,borderWidth:2.5,order:1}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:{...sc,y:{...sc.y,ticks:{...sc.y.ticks,stepSize:1}}}}});
  new C(document.getElementById('cFormMois'),{type:'bar',data:{labels:fmN.map(r=>r.mois_label||r.mois_num),datasets:[{label:'Sessions',data:fmV,backgroundColor:'rgba(14,165,233,.8)',borderRadius:5},{label:'Participants',data:fmN.map(r=>+(r.employes_formes||0)),backgroundColor:'rgba(139,92,246,.7)',borderRadius:5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:sc}});
  const fD=<?= jj($par_departement??[]) ?>;
  new C(document.getElementById('cFormDept'),{type:'bar',data:{labels:fD.map(r=>r.departement),datasets:[{data:fD.map(r=>r.nb_formations),backgroundColor:fD.map((_,i)=>pal[i%pal.length]),borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}});
})();
</script>

<?php
/* ════════════════════════════
   SALAIRES & FIDÉLITÉ
════════════════════════════ */
elseif($action==='salaires'):
?>
<div class="alert alert-info">
  <span class="material-icons-round">insights</span>
  <div><strong>Analyse Salaires & Fidélité</strong> — Écarts salariaux, employés fidèles, distribution ancienneté.</div>
</div>
<div class="st"><span class="material-icons-round" style="color:#F59E0B">workspace_premium</span>Top 5 — Piliers de l'entreprise</div>
<?php $top5=$top5_fideles??[];$medals=['🥇','🥈','🥉','④','⑤'];$mC=['#F97316','#64748B','#F59E0B','#8B5CF6','#0EA5E9']; ?>
<?php if(!empty($top5)): ?>
<div class="podium-wrap" style="margin-bottom:14px">
  <?php foreach($top5 as $i=>$f): ?>
  <div class="podium-card rk<?= $i+1 ?>" style="min-width:145px">
    <div class="pod-rank"><?= $medals[$i]??($i+1) ?></div>
    <div class="pod-anc" style="color:<?= $mC[$i]??'var(--txt2)' ?>"><?= number_format((float)($f['anciennete_ans']??0),1) ?><span style="font-size:11px;font-weight:400;color:var(--txt4)"> ans</span></div>
    <div class="pod-name"><?= safe(($f['nom']??'').' '.($f['prenom']??'')) ?></div>
    <div class="pod-dept"><?= safe($f['departement']??'') ?></div>
    <div style="font-size:10px;color:var(--txt4);margin-top:2px"><?= safe($f['fonction']??'') ?></div>
    <?php if(!empty($f['salaire_net'])): ?><div class="pod-sal"><?= mnt($f['salaire_net']) ?></div><?php endif; ?>
    <?php if(!empty($f['date_entree'])): ?><div style="font-size:9.5px;color:var(--txt4);margin-top:3px">Depuis <?= date('d/m/Y',strtotime($f['date_entree'])) ?></div><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="row c2">
  <div class="card"><div class="card-head"><div><div class="card-title">Salaire moyen par département</div><div class="card-sub">Min · Moyen · Max</div></div></div><div class="chart-w" style="height:265px"><canvas id="cSalDept"></canvas></div></div>
  <div class="card"><div class="card-head"><div><div class="card-title">Ancienneté par département</div><div class="card-sub">Années moyennes</div></div></div><div class="chart-w" style="height:265px"><canvas id="cAncDept"></canvas></div></div>
</div>
<div class="row c2">
  <div class="card">
    <div class="card-head"><div><div class="card-title">Distribution ancienneté</div><div class="card-sub">0–2 · 3–5 · 6–10 · 10+ ans</div></div><span class="card-tag">Fidélité</span></div>
    <div class="chart-w" style="height:225px"><canvas id="cDistAncFine"></canvas></div>
  </div>
  <div class="card">
    <div class="card-head"><div><div class="card-title">Top 10 — Employés les plus fidèles</div><div class="card-sub">Ancienneté décroissante</div></div><span class="badge badge-o">Fidélité</span></div>
    <?php $fideles=$top_fideles??[]; ?>
    <?php if(empty($fideles)): ?><div class="empty"><span class="material-icons-round">emoji_events</span>Aucune donnée</div>
    <?php else: ?>
    <table class="tbl">
      <thead><tr><th>#</th><th>Collaborateur</th><th>Département</th><th>Fonction</th><th>Ancienneté</th></tr></thead>
      <tbody>
      <?php foreach($fideles as $i=>$f): ?>
      <tr>
        <td><span class="pill pgy"><?= $i+1 ?></span></td>
        <td><strong><?= safe(($f['nom']??'').' '.($f['prenom']??'')) ?></strong></td>
        <td><?= safe($f['departement']??'') ?></td>
        <td style="color:var(--txt4);font-size:11.5px"><?= safe($f['fonction']??'') ?></td>
        <td><span class="pill pg"><?= number_format((float)($f['anciennete_ans']??0),1) ?> ans</span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<div class="card">
  <div class="card-head"><div><div class="card-title">Top 10 fonctions les plus représentées</div><div class="card-sub">Effectif par poste</div></div></div>
  <?php $fns=$top_fonctions??[];$mxFn=!empty($fns)?max(array_column($fns,'effectif')):1; ?>
  <?php foreach($fns as $i=>$fn): ?>
  <div class="rank-item">
    <div class="rank-num"><?= $i+1 ?></div>
    <div class="rank-label" title="<?= safe($fn['fonction']) ?>"><?= safe($fn['fonction']) ?></div>
    <div class="rank-bar-o"><div class="rank-bar-f" style="width:<?= round(($fn['effectif']/$mxFn)*100) ?>%;background:<?= $PAL[$i%count($PAL)] ?>"></div></div>
    <div class="rank-v"><?= (int)$fn['effectif'] ?></div>
  </div>
  <?php endforeach; ?>
</div>
<script>
(function(){
  const C=Chart;C.defaults.font.family="'Inter',sans-serif";C.defaults.color='#7A8EAE';
  const grid='rgba(200,213,238,.4)';
  const tt={backgroundColor:'#fff',borderColor:'#E2E8F4',borderWidth:1,padding:12,titleColor:'#0B1D3A',bodyColor:'#1E3456',cornerRadius:10,titleFont:{weight:'700',size:12},bodyFont:{size:11.5}};
  const sc={x:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'}},y:{grid:{color:grid},ticks:{font:{size:10.5,weight:'500'},color:'#7A8EAE'},beginAtZero:true}};
  const pal=<?= $PAL_JS ?>;
  const sD=<?= jj($salaires_dep??[]) ?>;
  new C(document.getElementById('cSalDept'),{type:'bar',data:{labels:sD.map(r=>r.departement),datasets:[{label:'Min',data:sD.map(r=>r.salaire_min),backgroundColor:'rgba(14,165,233,.22)',borderRadius:4},{label:'Moyen',data:sD.map(r=>r.salaire_moyen),backgroundColor:'rgba(14,165,233,.82)',borderRadius:4},{label:'Max',data:sD.map(r=>r.salaire_max),backgroundColor:'rgba(139,92,246,.6)',borderRadius:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,position:'bottom',labels:{font:{size:10.5},color:'#7A8EAE',boxWidth:9}},tooltip:{...tt}},scales:sc}});
  const aD=<?= jj($anciennete_dep??[]) ?>;
  new C(document.getElementById('cAncDept'),{type:'bar',data:{labels:aD.map(r=>r.departement),datasets:[{data:aD.map(r=>r.anciennete_moyenne),backgroundColor:aD.map((_,i)=>pal[i%pal.length]),borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,indexAxis:'y',plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}});
  const ancF=<?= jj($distribution_anc_fine??$distribution_anc??[]) ?>;
  new C(document.getElementById('cDistAncFine'),{type:'bar',data:{labels:ancF.map(r=>r.tranche),datasets:[{label:'Effectif',data:ancF.map(r=>r.effectif),backgroundColor:['rgba(249,115,22,.88)','rgba(139,92,246,.88)','rgba(14,165,233,.88)','rgba(16,185,129,.88)'],borderRadius:10,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{...tt}},scales:sc}});
})();
</script>

<?php
/* ════════════════════════════
   ANNUAIRE
════════════════════════════ */
elseif($action==='employes'):
  $employes=$employes??[];$departements=$departements??[];$contrats=$contrats??[];
  $filters=$filters??[];$page=$page??1;$total_pages=$total_pages??1;$total=$total??0;$per_page=$per_page??20;
?>
<div class="filters">
  <form method="GET" style="display:flex;gap:9px;flex-wrap:wrap;align-items:center;width:100%">
    <input type="hidden" name="action" value="reporting">
    <input type="hidden" name="reporting_view" value="employes">
    <input type="hidden" name="annee" value="<?= (int)$annee ?>">
    <span class="filter-lbl">Département</span>
    <select name="departement" class="filter-sel" onchange="this.form.submit()">
      <option value="">Tous</option>
      <?php foreach($departements as $d): ?>
        <option value="<?= safe($d['nom_departement']) ?>" <?= ($filters['departement']??'')===$d['nom_departement']?'selected':'' ?>><?= safe($d['nom_departement']) ?></option>
      <?php endforeach; ?>
    </select>
    <span class="filter-lbl">Contrat</span>
    <select name="type_contrat" class="filter-sel" onchange="this.form.submit()">
      <option value="">Tous</option>
      <?php foreach($contrats as $c): ?>
        <option value="<?= safe($c['type_contrat']) ?>" <?= ($filters['type_contrat']??'')===$c['type_contrat']?'selected':'' ?>><?= safe($c['type_contrat']) ?></option>
      <?php endforeach; ?>
    </select>
    <span class="filter-lbl">Genre</span>
    <select name="genre" class="filter-sel" onchange="this.form.submit()">
      <option value="">Tous</option>
      <option value="F" <?= ($filters['genre']??'')==='F'?'selected':'' ?>>♀ Femmes</option>
      <option value="M" <?= ($filters['genre']??'')==='M'?'selected':'' ?>>♂ Hommes</option>
    </select>
    <a href="?action=reporting&reporting_view=employes&annee=<?= (int)$annee ?>" class="btn btn-ghost" style="font-size:12px;padding:7px 12px">
      <span class="material-icons-round">restart_alt</span>Reset
    </a>
    <span class="filter-count"><?= nbf($total) ?> résultats</span>
  </form>
</div>
<div class="card">
  <div class="tbl-wrap">
    <?php if(empty($employes)): ?><div class="empty"><span class="material-icons-round">search_off</span>Aucun résultat</div>
    <?php else: ?>
    <table class="tbl">
      <thead><tr><th>Matricule</th><th>Collaborateur</th><th>Genre</th><th>Département</th><th>Fonction</th><th>Contrat</th><th>Salaire net</th><th>Ancienneté</th><th>Statut</th></tr></thead>
      <tbody>
      <?php foreach($employes as $e):
        $cpill=match($e['type_contrat']??''){'TITULAIRE'=>'pv','CDD'=>'po','APP'=>'pm','CIVP'=>'pg',default=>'pgy'}; ?>
      <tr>
        <td><span class="pill pgy" style="font-family:'JetBrains Mono',monospace;font-size:10px"><?= safe($e['matricule']) ?></span></td>
        <td><strong><?= safe(($e['nom']??'').' '.($e['prenom']??'')) ?></strong></td>
        <td><?= ($e['genre']??'')==='F'?'<span class="pill pm">♀ F</span>':'<span class="pill pb">♂ H</span>' ?></td>
        <td style="color:var(--txt2)"><?= safe($e['departement']??'') ?></td>
        <td style="color:var(--txt4);font-size:11.5px"><?= safe($e['fonction']??'') ?></td>
        <td><span class="pill <?= $cpill ?>"><?= safe($e['type_contrat']??'') ?></span></td>
        <td style="font-family:'JetBrains Mono',monospace;font-size:11.5px;font-weight:700;color:#10B981"><?= mnt($e['salaire_net']??0) ?></td>
        <td style="color:var(--txt3);font-family:'JetBrains Mono',monospace;font-size:11.5px"><?= number_format((float)($e['anciennete_ans']??0),1) ?> ans</td>
        <td><span class="pill <?= ($e['statut']??'')==='Actif'?'pg':'pr' ?>"><?= safe($e['statut']??'') ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
  <?php if($total_pages>1): $qB="action=reporting&reporting_view=employes&annee=".(int)$annee."&departement=".urlencode($filters['departement']??'')."&genre=".urlencode($filters['genre']??'')."&type_contrat=".urlencode($filters['type_contrat']??''); ?>
  <div class="pagination">
    <span><?= nbf(($page-1)*$per_page+1) ?>–<?= nbf(min($page*$per_page,$total)) ?> sur <?= nbf($total) ?></span>
    <div class="pag-links">
      <?php if($page>1): ?><a class="pag-btn" href="?<?= $qB ?>&page=<?= $page-1 ?>">‹</a><?php endif; ?>
      <?php for($pp=max(1,$page-2);$pp<=min($total_pages,$page+2);$pp++): ?>
        <a class="pag-btn <?= $pp===$page?'active':'' ?>" href="?<?= $qB ?>&page=<?= $pp ?>"><?= $pp ?></a>
      <?php endfor; ?>
      <?php if($page<$total_pages): ?><a class="pag-btn" href="?<?= $qB ?>&page=<?= $page+1 ?>">›</a><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php endif; ?>

  </div>
</div>
</div>
</body>
</html>
