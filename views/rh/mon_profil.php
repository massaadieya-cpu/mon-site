<?php
/*
 * Vue : mon_profil.php
 * Variables attendues depuis ProfileController :
 *   $user    — ligne de information_employe
 *   $message — ['type' => 'success'|'error', 'text' => '...'] ou null
 */

if (empty($user) || !is_array($user)) {
    header("Location: index.php");
    exit();
}

$initiales = strtoupper(
    substr($user['prenom'] ?? '?', 0, 1) .
    substr($user['nom']    ?? '?', 0, 1)
);

$dateEntree = '—';
if (!empty($user['date_entree'])) {
    try {
        $d = new DateTime($user['date_entree']);
        $mois = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
        $dateEntree = $d->format('d') . ' ' . $mois[(int)$d->format('m') - 1] . ' ' . $d->format('Y');
    } catch (Exception $e) {
        $dateEntree = htmlspecialchars($user['date_entree']);
    }
}

$anciennete = '—';
if (!empty($user['anciennete_ans'])) {
    $ans = (float)$user['anciennete_ans'];
    if ($ans < 1) $anciennete = 'Moins d\'un an';
    elseif ($ans < 2) $anciennete = '1 an';
    else $anciennete = round($ans) . ' ans';
}

$statutColor = match(strtolower($user['statut'] ?? '')) {
    'actif'   => ['bg' => '#d1fae5', 'color' => '#065f46', 'border' => 'rgba(6,95,70,0.2)', 'dot' => '#10b981'],
    'inactif' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => 'rgba(153,27,27,0.2)', 'dot' => '#ef4444'],
    default   => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => 'rgba(146,64,14,0.2)', 'dot' => '#f59e0b'],
};

$genreLabel = match($user['genre'] ?? '') {
    'M' => 'Masculin', 'F' => 'Féminin', default => '—',
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Profil — Altutex RH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --ink:         #0d1117;
      --ink-2:       #3d4451;
      --ink-3:       #6b7280;
      --ink-4:       #9ca3af;
      --canvas:      #f8f9fc;
      --white:       #ffffff;
      --border:      #e5e7eb;
      --border-2:    #f0f1f4;
      --navy:        #0f2557;
      --navy-mid:    #1a3a7a;
      --navy-soft:   #eef2fb;
      --accent:      #2563eb;
      --accent-glow: rgba(37,99,235,0.12);
      --r:           14px;
      --r-lg:        20px;
      --shadow:      0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
      --shadow-card: 0 0 0 1px rgba(0,0,0,0.06), 0 4px 24px rgba(0,0,0,0.06);
    }

    body {
      font-family: 'Sora', sans-serif;
      background: var(--canvas);
      color: var(--ink);
      min-height: 100vh;
      line-height: 1.5;
    }

    /* ── TOPBAR ── */
    .topbar {
      background: var(--navy);
      height: 52px;
      display: flex;
      align-items: center;
      padding: 0 2rem;
      position: sticky;
      top: 0;
      z-index: 100;
      gap: 1rem;
    }
    .topbar::after {
      content: '';
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 1px;
      background: rgba(255,255,255,0.08);
    }
    .topbar-logo {
      font-family: 'JetBrains Mono', monospace;
      color: #fff;
      font-size: 14px;
      font-weight: 500;
      letter-spacing: 0.06em;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .topbar-logo-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #60a5fa;
    }
    .topbar-sep { flex: 1; }
    .topbar-back {
      display: flex;
      align-items: center;
      gap: 6px;
      color: rgba(255,255,255,0.55);
      text-decoration: none;
      font-size: 13px;
      font-weight: 400;
      transition: color 0.2s;
      padding: 6px 12px;
      border-radius: 8px;
      border: 1px solid transparent;
    }
    .topbar-back:hover {
      color: #fff;
      background: rgba(255,255,255,0.07);
      border-color: rgba(255,255,255,0.1);
    }

    /* ── PAGE ── */
    .page {
      max-width: 1020px;
      margin: 2.5rem auto;
      padding: 0 1.5rem 5rem;
    }

    .page-header {
      margin-bottom: 2rem;
    }
    .breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--ink-4);
      margin-bottom: 10px;
    }
    .breadcrumb span { color: var(--ink-3); }
    .page-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--ink);
      letter-spacing: -0.02em;
    }
    .page-sub {
      font-size: 14px;
      color: var(--ink-3);
      margin-top: 4px;
    }

    /* ── TABS ── */
    .tabs-wrap {
      margin-bottom: 2rem;
      border-bottom: 1.5px solid var(--border);
      display: flex;
      gap: 0;
    }
    .tab {
      padding: 11px 20px;
      font-size: 13px;
      font-weight: 500;
      font-family: 'Sora', sans-serif;
      cursor: pointer;
      color: var(--ink-3);
      border: none;
      background: none;
      display: flex;
      align-items: center;
      gap: 7px;
      border-bottom: 2px solid transparent;
      margin-bottom: -1.5px;
      transition: color 0.2s, border-color 0.2s;
    }
    .tab.active { color: var(--accent); border-bottom-color: var(--accent); }
    .tab:not(.active):hover { color: var(--ink-2); }
    .tab svg { opacity: 0.7; }
    .tab.active svg { opacity: 1; }

    /* ── LAYOUT ── */
    .layout { display: grid; grid-template-columns: 280px 1fr; gap: 1.5rem; align-items: start; }
    @media (max-width: 720px) { .layout { grid-template-columns: 1fr; } }

    /* ── CARD BASE ── */
    .card {
      background: var(--white);
      border-radius: var(--r-lg);
      box-shadow: var(--shadow-card);
      overflow: hidden;
    }

    /* ── IDENTITY CARD ── */
    .id-header {
      background: linear-gradient(160deg, var(--navy) 0%, var(--navy-mid) 100%);
      padding: 2rem 1.5rem 1.75rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .id-header::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 180px; height: 180px;
      border-radius: 50%;
      background: rgba(255,255,255,0.04);
      pointer-events: none;
    }
    .id-header::after {
      content: '';
      position: absolute;
      bottom: -40px; left: -40px;
      width: 120px; height: 120px;
      border-radius: 50%;
      background: rgba(255,255,255,0.03);
      pointer-events: none;
    }
    .avatar-ring {
      width: 78px; height: 78px;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      border: 2px solid rgba(255,255,255,0.22);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      position: relative;
      z-index: 1;
    }
    .avatar-initials {
      font-family: 'JetBrains Mono', monospace;
      font-size: 22px;
      font-weight: 500;
      color: #fff;
      letter-spacing: 0.06em;
    }
    .id-name {
      font-size: 17px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 3px;
      position: relative; z-index: 1;
    }
    .id-role {
      font-size: 12px;
      color: rgba(255,255,255,0.5);
      margin-bottom: 12px;
      position: relative; z-index: 1;
    }
    .matricule-tag {
      display: inline-block;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      color: rgba(255,255,255,0.7);
      background: rgba(255,255,255,0.09);
      border: 1px solid rgba(255,255,255,0.16);
      border-radius: 6px;
      padding: 3px 10px;
      letter-spacing: 0.06em;
      position: relative; z-index: 1;
    }
    .statut-pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 600;
      border-radius: 99px;
      padding: 4px 12px;
      margin-top: 10px;
      position: relative; z-index: 1;
    }
    .statut-dot-anim {
      width: 7px; height: 7px;
      border-radius: 50%;
    }

    /* ID BODY */
    .id-body { padding: 0; }
    .id-row {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 13px 1.25rem;
      border-bottom: 1px solid var(--border-2);
      transition: background 0.15s;
    }
    .id-row:last-child { border-bottom: none; }
    .id-row:hover { background: #fafbff; }
    .id-icon {
      width: 32px; height: 32px;
      border-radius: 9px;
      background: var(--navy-soft);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .id-label {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: var(--ink-4);
      margin-bottom: 2px;
    }
    .id-value {
      font-size: 13px;
      font-weight: 500;
      color: var(--ink);
    }
    .pill-dept {
      display: inline-block;
      padding: 2px 10px;
      background: var(--navy-soft);
      color: var(--navy-mid);
      font-size: 11px;
      font-weight: 600;
      border-radius: 99px;
    }
    .pill-contrat {
      display: inline-block;
      padding: 2px 10px;
      background: #fef9ec;
      color: #92400e;
      font-size: 11px;
      font-weight: 600;
      border-radius: 99px;
    }

    /* ── PANEL ── */
    .panel { display: none; }
    .panel.active { display: block; }

    .panel-title {
      display: flex;
      align-items: center;
      gap: 9px;
      padding: 1.25rem 1.5rem;
      border-bottom: 1px solid var(--border-2);
      font-size: 14px;
      font-weight: 600;
      color: var(--ink);
    }
    .panel-title-icon {
      width: 30px; height: 30px;
      border-radius: 8px;
      background: var(--navy-soft);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--accent);
    }

    /* ── INFO GRID ── */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
    }
    .info-cell {
      padding: 1.1rem 1.5rem;
      border-bottom: 1px solid var(--border-2);
      border-right: 1px solid var(--border-2);
      transition: background 0.15s;
    }
    .info-cell:hover { background: #fafbff; }
    .info-cell:nth-child(even) { border-right: none; }
    .info-cell.full { grid-column: 1 / -1; border-right: none; }
    .info-cell:last-child, .info-cell.no-border { border-bottom: none; }
    .ic-label {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.07em;
      color: var(--ink-4);
      margin-bottom: 5px;
    }
    .ic-value {
      font-size: 14px;
      font-weight: 500;
      color: var(--ink);
    }
    .ic-value.mono {
      font-family: 'JetBrains Mono', monospace;
      font-size: 13px;
      color: var(--accent);
    }
    @media (max-width: 480px) {
      .info-grid { grid-template-columns: 1fr; }
      .info-cell { border-right: none; }
    }

    /* ── ALERT ── */
    .alert {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 13px 1.5rem;
      font-size: 13px;
      border-bottom: 1px solid transparent;
    }
    .alert-success { background: #f0fdf4; color: #166534; border-color: rgba(22,101,52,0.15); }
    .alert-error   { background: #fef2f2; color: #991b1b; border-color: rgba(153,27,27,0.15); }
    .alert svg { flex-shrink: 0; }

    /* ── FORM ── */
    .form-body { padding: 1.5rem; }
    .form-section-label {
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      font-weight: 600;
      color: var(--ink-4);
      margin-bottom: 1rem;
    }
    .form-row { margin-bottom: 1.25rem; }
    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: var(--ink-2);
      margin-bottom: 8px;
    }
    .input-wrap {
      display: flex;
      align-items: center;
      background: var(--canvas);
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .input-wrap:focus-within {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-glow);
      background: #fff;
    }
    .input-wrap.is-valid  { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
    .input-wrap.is-error  { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }
    .input-icon {
      padding: 0 12px;
      color: var(--ink-4);
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }
    .input-wrap input {
      flex: 1;
      background: none;
      border: none;
      outline: none;
      padding: 12px 0;
      font-size: 14px;
      color: var(--ink);
      font-family: 'Sora', sans-serif;
    }
    .input-wrap input::placeholder { color: var(--ink-4); }
    .toggle-eye {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0 12px;
      color: var(--ink-4);
      display: flex;
      align-items: center;
      transition: color 0.2s;
    }
    .toggle-eye:hover { color: var(--ink-2); }
    .field-error {
      font-size: 12px;
      color: #dc2626;
      margin-top: 6px;
      display: none;
      align-items: center;
      gap: 5px;
    }
    .form-divider {
      border: none;
      border-top: 1px solid var(--border-2);
      margin: 1.5rem 0;
    }

    /* STRENGTH */
    .strength-bars {
      display: flex;
      gap: 5px;
      margin-top: 10px;
    }
    .strength-bar {
      height: 4px;
      flex: 1;
      border-radius: 99px;
      background: var(--border);
      transition: background 0.3s;
    }
    .strength-label {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 8px;
    }
    .strength-text {
      font-size: 12px;
      font-weight: 500;
      min-height: 16px;
    }
    .checklist {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 6px 12px;
      margin-top: 12px;
    }
    @media (max-width: 480px) { .checklist { grid-template-columns: 1fr; } }
    .chk {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 12px;
      color: var(--ink-4);
      transition: color 0.2s;
    }
    .chk.ok { color: #059669; }
    .chk-dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: var(--border);
      flex-shrink: 0;
      transition: background 0.2s;
    }
    .chk.ok .chk-dot { background: #10b981; }

    /* SUBMIT */
    .submit-btn {
      width: 100%;
      background: var(--accent);
      color: #fff;
      border: none;
      border-radius: var(--r);
      padding: 13px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 1.5rem;
      font-family: 'Sora', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 2px 8px rgba(37,99,235,0.25);
    }
    .submit-btn:hover:not(:disabled) {
      background: #1d4ed8;
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(37,99,235,0.3);
    }
    .submit-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; box-shadow: none; }

    /* ANIMATIONS */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(12px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .card { animation: fadeUp 0.4s ease both; }
    .layout > div:last-child .card { animation-delay: 0.05s; }
  </style>
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar">
  <div class="topbar-logo">
    <div class="topbar-logo-dot"></div>
    ALTUTEX · RH
  </div>
  <div class="topbar-sep"></div>
  <a href="index.php?action=dashboard" class="topbar-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Tableau de bord
  </a>
</nav>

<div class="page">

  <!-- PAGE HEADER -->
  <div class="page-header">
    <div class="breadcrumb">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
      Accueil
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Mon profil</span>
    </div>
    <h1 class="page-title">Mon profil</h1>
    <p class="page-sub">Bienvenue, <strong><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></strong> — consultez vos informations et gérez votre compte.</p>
  </div>

  <!-- TABS -->
  <div class="tabs-wrap">
    <button class="tab active" data-tab="informations">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Informations
    </button>
    <button class="tab" data-tab="securite">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Sécurité
    </button>
  </div>

  <div class="layout">

    <!-- IDENTITY CARD -->
    <div class="card">
      <div class="id-header">
        <div class="avatar-ring">
          <span class="avatar-initials"><?= htmlspecialchars($initiales) ?></span>
        </div>
        <div class="id-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
        <div class="id-role"><?= htmlspecialchars($user['fonction'] ?? '—') ?></div>
        <div class="matricule-tag"><?= htmlspecialchars($user['matricule'] ?? '—') ?></div>
        <br>
        <span class="statut-pill" style="background:<?= $statutColor['bg'] ?>;color:<?= $statutColor['color'] ?>;border:1px solid <?= $statutColor['border'] ?>">
          <span class="statut-dot-anim" style="background:<?= $statutColor['dot'] ?>"></span>
          <?= htmlspecialchars(ucfirst(strtolower($user['statut'] ?? 'Inconnu'))) ?>
        </span>
      </div>

      <div class="id-body">
        <div class="id-row">
          <div class="id-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a3a7a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
          </div>
          <div>
            <div class="id-label">Département</div>
            <div class="id-value"><span class="pill-dept"><?= htmlspecialchars($user['departement'] ?? '—') ?></span></div>
          </div>
        </div>
        <div class="id-row">
          <div class="id-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a3a7a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div>
            <div class="id-label">Type de contrat</div>
            <div class="id-value"><span class="pill-contrat"><?= htmlspecialchars($user['type_contrat'] ?? '—') ?></span></div>
          </div>
        </div>
        <div class="id-row">
          <div class="id-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a3a7a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div class="id-label">Date d'entrée</div>
            <div class="id-value"><?= $dateEntree ?></div>
          </div>
        </div>
        <div class="id-row">
          <div class="id-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1a3a7a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div>
            <div class="id-label">Ancienneté</div>
            <div class="id-value"><?= $anciennete ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT PANELS -->
    <div>

      <!-- PANEL: Informations -->
      <div class="panel active card" id="panel-informations">
        <div class="panel-title">
          <div class="panel-title-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </div>
          Informations personnelles
        </div>
        <div class="info-grid">
          <div class="info-cell">
            <div class="ic-label">Nom</div>
            <div class="ic-value"><?= htmlspecialchars($user['nom'] ?? '—') ?></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Prénom</div>
            <div class="ic-value"><?= htmlspecialchars($user['prenom'] ?? '—') ?></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Genre</div>
            <div class="ic-value"><?= $genreLabel ?></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Âge</div>
            <div class="ic-value"><?= !empty($user['age']) ? htmlspecialchars($user['age']) . ' ans' : '—' ?></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Département</div>
            <div class="ic-value"><span class="pill-dept"><?= htmlspecialchars($user['departement'] ?? '—') ?></span></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Fonction</div>
            <div class="ic-value"><?= htmlspecialchars($user['fonction'] ?? '—') ?></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Type de contrat</div>
            <div class="ic-value"><span class="pill-contrat"><?= htmlspecialchars($user['type_contrat'] ?? '—') ?></span></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Statut</div>
            <div class="ic-value">
              <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;background:<?= $statutColor['bg'] ?>;color:<?= $statutColor['color'] ?>;border:1px solid <?= $statutColor['border'] ?>;border-radius:99px;padding:3px 10px;">
                <span style="width:6px;height:6px;border-radius:50%;background:<?= $statutColor['dot'] ?>;"></span>
                <?= htmlspecialchars(ucfirst(strtolower($user['statut'] ?? '—'))) ?>
              </span>
            </div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Date d'entrée</div>
            <div class="ic-value"><?= $dateEntree ?></div>
          </div>
          <div class="info-cell">
            <div class="ic-label">Ancienneté</div>
            <div class="ic-value"><?= $anciennete ?></div>
          </div>
          <div class="info-cell full no-border">
            <div class="ic-label">Matricule</div>
            <div class="ic-value mono"><?= htmlspecialchars($user['matricule'] ?? '—') ?></div>
          </div>
        </div>
      </div>

      <!-- PANEL: Sécurité -->
      <div class="panel card" id="panel-securite">
        <div class="panel-title">
          <div class="panel-title-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          Changer le mot de passe
        </div>

        <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message['type']) ?>">
          <?php if ($message['type'] === 'success'): ?>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <?php else: ?>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php endif; ?>
          <?= htmlspecialchars($message['text']) ?>
        </div>
        <?php endif; ?>

        <form class="form-body" action="index.php?action=update_password" method="POST" id="secForm" autocomplete="off">

          <div class="form-section-label">Mot de passe actuel</div>
          <div class="form-row">
            <label class="form-label">Confirmez votre identité</label>
            <div class="input-wrap" id="wrap-old">
              <span class="input-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input type="password" name="old_password" id="old_password" placeholder="Votre mot de passe actuel" required autocomplete="current-password">
              <button type="button" class="toggle-eye" data-target="old_password" aria-label="Afficher/masquer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <hr class="form-divider">

          <div class="form-section-label">Nouveau mot de passe</div>
          <div class="form-row">
            <label class="form-label">Choisissez un nouveau mot de passe</label>
            <div class="input-wrap" id="wrap-new">
              <span class="input-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
              </span>
              <input type="password" name="new_password" id="new_password" placeholder="Min. 8 caractères" required autocomplete="new-password">
              <button type="button" class="toggle-eye" data-target="new_password" aria-label="Afficher/masquer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="strength-bars">
              <div class="strength-bar" id="s1"></div>
              <div class="strength-bar" id="s2"></div>
              <div class="strength-bar" id="s3"></div>
              <div class="strength-bar" id="s4"></div>
            </div>
            <div class="strength-label">
              <span class="strength-text" id="strength-txt"></span>
            </div>
            <div class="checklist">
              <div class="chk" id="chk-len"><span class="chk-dot"></span>8 caractères min.</div>
              <div class="chk" id="chk-up"><span class="chk-dot"></span>Majuscule</div>
              <div class="chk" id="chk-num"><span class="chk-dot"></span>Chiffre</div>
              <div class="chk" id="chk-sp"><span class="chk-dot"></span>Caractère spécial</div>
            </div>
          </div>

          <div class="form-row" style="margin-top:1.25rem;">
            <label class="form-label">Confirmation du mot de passe</label>
            <div class="input-wrap" id="wrap-confirm">
              <span class="input-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <input type="password" name="confirm_password" id="confirm_password" placeholder="Répéter le nouveau mot de passe" required autocomplete="new-password">
              <button type="button" class="toggle-eye" data-target="confirm_password" aria-label="Afficher/masquer">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="field-error" id="match-err" style="display:none;color:#dc2626;font-size:12px;margin-top:6px;">Les mots de passe ne correspondent pas.</div>
          </div>

          <button type="submit" class="submit-btn" id="submitBtn" disabled>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Sauvegarder les changements
          </button>

        </form>
      </div>

    </div>
  </div>
</div>

<script>
// Tabs
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
  });
});

// Toggle visibility
document.querySelectorAll('.toggle-eye').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.target);
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.querySelector('.eye-svg').innerHTML = isPass
      ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>'
      : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  });
});

// Password strength
const newP  = document.getElementById('new_password');
const confP = document.getElementById('confirm_password');
const oldP  = document.getElementById('old_password');
const submitBtn = document.getElementById('submitBtn');
const segs  = [1,2,3,4].map(i => document.getElementById('s'+i));
const colors = ['','#ef4444','#f59e0b','#10b981','#059669'];
const labels = ['','Faible','Moyen','Fort','Très fort'];
const rules  = {
  len: v => v.length >= 8,
  up:  v => /[A-Z]/.test(v),
  num: v => /[0-9]/.test(v),
  sp:  v => /[^A-Za-z0-9]/.test(v),
};

function score(v) { return Object.values(rules).filter(fn => fn(v)).length; }

function updateStrength(v) {
  const s = v ? score(v) : 0;
  segs.forEach((seg, i) => { seg.style.background = i < s ? colors[s] : 'var(--border)'; });
  const txt = document.getElementById('strength-txt');
  txt.textContent = v ? labels[s] : '';
  txt.style.color = colors[s] || 'var(--ink-4)';
  for (const key of ['len','up','num','sp']) {
    const el = document.getElementById('chk-'+key);
    if (rules[key](v)) el.classList.add('ok'); else el.classList.remove('ok');
  }
}

function validate() {
  const np = newP.value, cp = confP.value, op = oldP.value;
  const match = np === cp;
  const wrapC = document.getElementById('wrap-confirm');
  const matchErr = document.getElementById('match-err');
  if (cp.length > 0 && !match) {
    matchErr.style.display = 'block';
    wrapC.classList.add('is-error'); wrapC.classList.remove('is-valid');
  } else if (cp.length > 0 && match) {
    matchErr.style.display = 'none';
    wrapC.classList.remove('is-error'); wrapC.classList.add('is-valid');
  } else {
    matchErr.style.display = 'none';
    wrapC.classList.remove('is-error','is-valid');
  }
  submitBtn.disabled = !(op.length > 0 && score(np) >= 2 && match && cp.length > 0);
}

newP.addEventListener('input',  () => { updateStrength(newP.value); validate(); });
confP.addEventListener('input', validate);
oldP.addEventListener('input',  validate);

document.getElementById('secForm').addEventListener('submit', () => {
  submitBtn.innerHTML = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.18-3.36"/></svg> Sauvegarde en cours…';
  submitBtn.disabled = true;
});

<?php if (!empty($message)): ?>
document.querySelector('[data-tab="securite"]').click();
<?php endif; ?>
</script>

</body>
</html>