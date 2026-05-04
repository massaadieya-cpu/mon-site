<?php
// Simuler les données utilisateur (à remplacer par vos vraies données)
$user = [
    'nom'        => 'Dupont',
    'prenom'     => 'Mohamed',
    'email'      => 'mohamed.dupont@altutex.com',
    'poste'      => 'Responsable RH',
    'departement'=> 'Ressources Humaines',
    'matricule'  => 'ALT-2024-0412',
    'avatar'     => '', // URL d'une photo ou vide pour les initiales
    'date_creation' => '12 Jan 2024',
];

// Traitement du formulaire
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['old_password'])) {
    $old     = trim($_POST['old_password'] ?? '');
    $new     = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (empty($old) || empty($new) || empty($confirm)) {
        $message = ['type' => 'error', 'text' => 'Tous les champs sont obligatoires.'];
    } elseif ($new !== $confirm) {
        $message = ['type' => 'error', 'text' => 'Les nouveaux mots de passe ne correspondent pas.'];
    } elseif (strlen($new) < 8) {
        $message = ['type' => 'error', 'text' => 'Le mot de passe doit contenir au moins 8 caractères.'];
    } else {
        // TODO: Vérifier l'ancien mot de passe en base et mettre à jour
        // password_verify($old, $hash_from_db) && password_hash($new, PASSWORD_BCRYPT)
        $message = ['type' => 'success', 'text' => 'Mot de passe mis à jour avec succès.'];
    }
}

$initiales = strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Profil — Altutex RH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:        #1e3c72;
      --navy-mid:    #2a5298;
      --navy-light:  #e8eef8;
      --surface:     #f5f6fa;
      --card:        #ffffff;
      --border:      rgba(30,60,114,0.12);
      --border-soft: rgba(30,60,114,0.06);
      --text-1:      #0f1c35;
      --text-2:      #4a5878;
      --text-3:      #8a96ae;
      --success:     #0f6e56;
      --success-bg:  #e1f5ee;
      --error:       #a32d2d;
      --error-bg:    #fcebeb;
      --warn:        #854f0b;
      --warn-bg:     #faeeda;
      --radius:      12px;
      --radius-lg:   18px;
      --radius-xl:   24px;
      --shadow-sm:   0 1px 3px rgba(30,60,114,0.08), 0 1px 2px rgba(30,60,114,0.05);
      --shadow-md:   0 4px 16px rgba(30,60,114,0.10), 0 2px 6px rgba(30,60,114,0.06);
    }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--surface);
      color: var(--text-1);
      min-height: 100vh;
    }

    /* ─── TOP NAV ─── */
    .topbar {
      background: var(--navy);
      height: 56px;
      display: flex;
      align-items: center;
      padding: 0 2rem;
      gap: 1rem;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .topbar-logo {
      font-family: 'DM Mono', monospace;
      color: #fff;
      font-size: 15px;
      font-weight: 500;
      letter-spacing: 0.04em;
      opacity: 0.95;
    }
    .topbar-sep { flex: 1; }
    .topbar-back {
      display: flex;
      align-items: center;
      gap: 6px;
      color: rgba(255,255,255,0.65);
      text-decoration: none;
      font-size: 13px;
      transition: color 0.2s;
    }
    .topbar-back:hover { color: #fff; }

    /* ─── PAGE LAYOUT ─── */
    .page {
      max-width: 960px;
      margin: 2.5rem auto;
      padding: 0 1.5rem 4rem;
    }

    .page-title {
      font-size: 22px;
      font-weight: 600;
      color: var(--text-1);
      margin-bottom: 0.25rem;
    }
    .page-subtitle {
      font-size: 14px;
      color: var(--text-3);
      margin-bottom: 2rem;
    }

    /* ─── TAB NAV ─── */
    .tabs {
      display: flex;
      gap: 4px;
      background: var(--card);
      border: 0.5px solid var(--border);
      border-radius: var(--radius);
      padding: 4px;
      margin-bottom: 2rem;
      width: fit-content;
      box-shadow: var(--shadow-sm);
    }
    .tab {
      padding: 8px 18px;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      color: var(--text-3);
      border: none;
      background: none;
      font-family: inherit;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 7px;
    }
    .tab.active {
      background: var(--navy);
      color: #fff;
      box-shadow: var(--shadow-sm);
    }
    .tab:not(.active):hover { color: var(--text-1); background: var(--surface); }

    /* ─── GRID ─── */
    .grid {
      display: grid;
      grid-template-columns: 280px 1fr;
      gap: 1.5rem;
      align-items: start;
    }
    @media (max-width: 680px) {
      .grid { grid-template-columns: 1fr; }
    }

    /* ─── CARD BASE ─── */
    .card {
      background: var(--card);
      border: 0.5px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }

    /* ─── PROFILE CARD ─── */
    .profile-header {
      background: var(--navy);
      padding: 2rem 1.5rem 1.5rem;
      text-align: center;
    }
    .avatar {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: rgba(255,255,255,0.18);
      border: 2.5px solid rgba(255,255,255,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'DM Mono', monospace;
      font-size: 22px;
      font-weight: 500;
      color: #fff;
      margin: 0 auto 1rem;
      letter-spacing: 0.04em;
    }
    .profile-name {
      font-size: 16px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 3px;
    }
    .profile-poste {
      font-size: 12px;
      color: rgba(255,255,255,0.6);
    }
    .profile-body { padding: 1.25rem 1.5rem; }
    .info-row {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 10px 0;
      border-bottom: 0.5px solid var(--border-soft);
    }
    .info-row:last-child { border-bottom: none; }
    .info-icon {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: var(--navy-light);
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      margin-top: 1px;
    }
    .info-content { flex: 1; min-width: 0; }
    .info-label { font-size: 11px; color: var(--text-3); margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.05em; }
    .info-value { font-size: 13px; color: var(--text-1); font-weight: 500; word-break: break-all; }
    .badge-dept {
      display: inline-block;
      padding: 3px 10px;
      background: var(--navy-light);
      color: var(--navy);
      font-size: 11px;
      font-weight: 600;
      border-radius: 99px;
      margin-top: 4px;
    }

    /* ─── PANEL SECTION ─── */
    .panel { display: none; }
    .panel.active { display: block; }

    .section-title {
      font-size: 15px;
      font-weight: 600;
      color: var(--text-1);
      padding: 1.25rem 1.5rem;
      border-bottom: 0.5px solid var(--border-soft);
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .section-title svg { color: var(--text-3); }

    /* ─── ALERT ─── */
    .alert {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 1.5rem;
      font-size: 13px;
      border-bottom: 0.5px solid transparent;
    }
    .alert-success { background: var(--success-bg); color: var(--success); border-color: rgba(15,110,86,0.2); }
    .alert-error   { background: var(--error-bg);   color: var(--error);   border-color: rgba(163,45,45,0.2); }

    /* ─── FORM ─── */
    .form-body { padding: 1.5rem; }
    .form-row { margin-bottom: 1.25rem; }
    .form-label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: var(--text-3);
      margin-bottom: 7px;
    }
    .input-wrap {
      display: flex;
      align-items: center;
      background: var(--surface);
      border: 0.5px solid var(--border);
      border-radius: var(--radius);
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .input-wrap:focus-within {
      border-color: var(--navy-mid);
      box-shadow: 0 0 0 3px rgba(42,82,152,0.10);
      background: #fff;
    }
    .input-wrap.is-valid  { border-color: var(--success); }
    .input-wrap.is-error  { border-color: var(--error); box-shadow: 0 0 0 3px rgba(163,45,45,0.08); }
    .input-icon {
      padding: 0 11px;
      color: var(--text-3);
      display: flex;
      align-items: center;
      flex-shrink: 0;
    }
    .input-wrap input {
      flex: 1;
      background: none;
      border: none;
      outline: none;
      padding: 11px 0;
      font-size: 14px;
      color: var(--text-1);
      font-family: 'DM Sans', sans-serif;
    }
    .input-wrap input::placeholder { color: var(--text-3); }
    .toggle-eye {
      background: none;
      border: none;
      cursor: pointer;
      padding: 0 11px;
      color: var(--text-3);
      display: flex;
      align-items: center;
      transition: color 0.2s;
    }
    .toggle-eye:hover { color: var(--text-2); }

    .form-divider { border: none; border-top: 0.5px solid var(--border-soft); margin: 1.5rem 0; }

    /* strength bar */
    .strength-bars { display: flex; gap: 4px; margin-top: 8px; }
    .strength-bar {
      height: 3px;
      flex: 1;
      border-radius: 99px;
      background: var(--border);
      transition: background 0.3s;
    }
    .strength-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 6px;
    }
    .strength-text { font-size: 12px; font-weight: 500; min-height: 16px; }
    .checklist {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 5px 16px;
      margin-top: 10px;
    }
    .chk {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: var(--text-3);
      transition: color 0.2s;
    }
    .chk.ok { color: var(--success); }
    .chk-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--border);
      flex-shrink: 0;
      transition: background 0.2s;
    }
    .chk.ok .chk-dot { background: var(--success); }

    .field-error { font-size: 12px; color: var(--error); margin-top: 5px; display: none; }

    /* submit */
    .submit-btn {
      width: 100%;
      background: var(--navy);
      color: #fff;
      border: none;
      border-radius: var(--radius);
      padding: 13px;
      font-size: 15px;
      font-weight: 500;
      cursor: pointer;
      margin-top: 1.5rem;
      font-family: 'DM Sans', sans-serif;
      transition: background 0.2s, transform 0.15s, opacity 0.2s;
      letter-spacing: 0.01em;
    }
    .submit-btn:hover:not(:disabled) { background: var(--navy-mid); transform: translateY(-1px); }
    .submit-btn:active:not(:disabled) { transform: scale(0.99); }
    .submit-btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }

    /* ─── INFO PANEL ─── */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0;
    }
    .info-cell {
      padding: 1.1rem 1.5rem;
      border-bottom: 0.5px solid var(--border-soft);
      border-right: 0.5px solid var(--border-soft);
    }
    .info-cell:nth-child(even) { border-right: none; }
    .info-cell:nth-last-child(-n+2) { border-bottom: none; }
    .info-cell-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-3); margin-bottom: 5px; }
    .info-cell-value { font-size: 14px; font-weight: 500; color: var(--text-1); }
    .info-cell-value.mono { font-family: 'DM Mono', monospace; font-size: 13px; }

    @media (max-width: 480px) {
      .info-grid { grid-template-columns: 1fr; }
      .info-cell { border-right: none; }
      .info-cell:nth-last-child(1) { border-bottom: none; }
      .checklist { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- TOP NAV -->
<nav class="topbar">
  <span class="topbar-logo">ALTUTEX · RH</span>
  <span class="topbar-sep"></span>
  <a href="index.php?action=dashboard" class="topbar-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Tableau de bord
  </a>
</nav>

<!-- PAGE -->
<div class="page">

  <h1 class="page-title">Mon profil</h1>
  <p class="page-subtitle">Gérez vos informations personnelles et la sécurité de votre compte</p>

  <!-- TABS -->
  <div class="tabs">
    <button class="tab active" data-tab="informations">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
      Informations
    </button>
    <button class="tab" data-tab="securite">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      Sécurité
    </button>
  </div>

  <div class="grid">

    <!-- COLONNE GAUCHE : carte identité -->
    <div class="card">
      <div class="profile-header">
        <div class="avatar"><?= htmlspecialchars($initiales) ?></div>
        <div class="profile-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
        <div class="profile-poste"><?= htmlspecialchars($user['poste']) ?></div>
      </div>
      <div class="profile-body">
        <div class="info-row">
          <div class="info-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div class="info-content">
            <div class="info-label">Email</div>
            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
          </div>
        </div>
        <div class="info-row">
          <div class="info-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
          </div>
          <div class="info-content">
            <div class="info-label">Département</div>
            <div class="info-value"><span class="badge-dept"><?= htmlspecialchars($user['departement']) ?></span></div>
          </div>
        </div>
        <div class="info-row">
          <div class="info-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div class="info-content">
            <div class="info-label">Membre depuis</div>
            <div class="info-value"><?= htmlspecialchars($user['date_creation']) ?></div>
          </div>
        </div>
        <div class="info-row">
          <div class="info-icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#1e3c72" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          </div>
          <div class="info-content">
            <div class="info-label">Matricule</div>
            <div class="info-value" style="font-family:'DM Mono',monospace;font-size:12px;"><?= htmlspecialchars($user['matricule']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- COLONNE DROITE : panneaux -->
    <div>

      <!-- PANEL : Informations -->
      <div class="panel active card" id="panel-informations">
        <div class="section-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          Informations du compte
        </div>
        <div class="info-grid">
          <div class="info-cell">
            <div class="info-cell-label">Prénom</div>
            <div class="info-cell-value"><?= htmlspecialchars($user['prenom']) ?></div>
          </div>
          <div class="info-cell">
            <div class="info-cell-label">Nom</div>
            <div class="info-cell-value"><?= htmlspecialchars($user['nom']) ?></div>
          </div>
          <div class="info-cell">
            <div class="info-cell-label">Adresse email</div>
            <div class="info-cell-value"><?= htmlspecialchars($user['email']) ?></div>
          </div>
          <div class="info-cell">
            <div class="info-cell-label">Poste</div>
            <div class="info-cell-value"><?= htmlspecialchars($user['poste']) ?></div>
          </div>
          <div class="info-cell">
            <div class="info-cell-label">Département</div>
            <div class="info-cell-value"><?= htmlspecialchars($user['departement']) ?></div>
          </div>
          <div class="info-cell">
            <div class="info-cell-label">Matricule</div>
            <div class="info-cell-value mono"><?= htmlspecialchars($user['matricule']) ?></div>
          </div>
        </div>
      </div>

      <!-- PANEL : Sécurité -->
      <div class="panel card" id="panel-securite">
        <div class="section-title">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Changer le mot de passe
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?>">
          <?php if ($message['type'] === 'success'): ?>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <?php else: ?>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <?php endif; ?>
          <?= htmlspecialchars($message['text']) ?>
        </div>
        <?php endif; ?>

        <form class="form-body" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?action=update_password" method="POST" id="secForm" autocomplete="off">

          <div class="form-row">
            <label class="form-label">Mot de passe actuel</label>
            <div class="input-wrap" id="wrap-old">
              <span class="input-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              </span>
              <input type="password" name="old_password" id="old_password" placeholder="Votre mot de passe actuel" required autocomplete="current-password">
              <button type="button" class="toggle-eye" data-target="old_password">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>

          <hr class="form-divider">

          <div class="form-row">
            <label class="form-label">Nouveau mot de passe</label>
            <div class="input-wrap" id="wrap-new">
              <span class="input-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
              </span>
              <input type="password" name="new_password" id="new_password" placeholder="Min. 8 caractères" required autocomplete="new-password">
              <button type="button" class="toggle-eye" data-target="new_password">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="strength-bars">
              <div class="strength-bar" id="s1"></div>
              <div class="strength-bar" id="s2"></div>
              <div class="strength-bar" id="s3"></div>
              <div class="strength-bar" id="s4"></div>
            </div>
            <div class="strength-meta">
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
            <label class="form-label">Confirmation</label>
            <div class="input-wrap" id="wrap-confirm">
              <span class="input-icon">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <input type="password" name="confirm_password" id="confirm_password" placeholder="Répéter le nouveau mot de passe" required autocomplete="new-password">
              <button type="button" class="toggle-eye" data-target="confirm_password">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="field-error" id="match-err">Les mots de passe ne correspondent pas.</div>
          </div>

          <button type="submit" class="submit-btn" id="submitBtn" disabled>
            Sauvegarder les changements
          </button>

        </form>
      </div>

    </div>
  </div>
</div>

<script>
// ── Tabs ──
document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById('panel-' + tab.dataset.tab).classList.add('active');
  });
});

// ── Toggle password visibility ──
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

// ── Strength logic ──
const newP = document.getElementById('new_password');
const confP = document.getElementById('confirm_password');
const oldP = document.getElementById('old_password');
const matchErr = document.getElementById('match-err');
const submitBtn = document.getElementById('submitBtn');
const segs = [1,2,3,4].map(i => document.getElementById('s'+i));
const colors = ['','#e24b4a','#ef9f27','#1d9e75','#0f6e56'];
const labels = ['','Faible','Moyen','Fort','Très fort'];

const rules = {
  len:   v => v.length >= 8,
  up:    v => /[A-Z]/.test(v),
  num:   v => /[0-9]/.test(v),
  sp:    v => /[^A-Za-z0-9]/.test(v),
};

function score(v) {
  return Object.values(rules).filter(fn => fn(v)).length;
}

function updateStrength(v) {
  const s = v ? score(v) : 0;
  segs.forEach((seg, i) => {
    seg.style.background = i < s ? colors[s] : 'rgba(30,60,114,0.10)';
  });
  const txt = document.getElementById('strength-txt');
  txt.textContent = v ? labels[s] : '';
  txt.style.color = colors[s] || 'var(--text-3)';
  for (const key of ['len','up','num','sp']) {
    const el = document.getElementById('chk-'+key);
    if (rules[key](v)) el.classList.add('ok'); else el.classList.remove('ok');
  }
}

function validate() {
  const np = newP.value, cp = confP.value, op = oldP.value;
  const s = score(np);
  const match = np === cp;
  if (cp.length > 0 && !match) {
    matchErr.style.display = 'block';
    document.getElementById('wrap-confirm').classList.add('is-error');
    document.getElementById('wrap-confirm').classList.remove('is-valid');
  } else if (cp.length > 0 && match) {
    matchErr.style.display = 'none';
    document.getElementById('wrap-confirm').classList.remove('is-error');
    document.getElementById('wrap-confirm').classList.add('is-valid');
  } else {
    matchErr.style.display = 'none';
    document.getElementById('wrap-confirm').classList.remove('is-error','is-valid');
  }
  submitBtn.disabled = !(op.length > 0 && s >= 2 && match && cp.length > 0);
}

newP.addEventListener('input', () => { updateStrength(newP.value); validate(); });
confP.addEventListener('input', validate);
oldP.addEventListener('input', validate);

// ── Submit feedback ──
document.getElementById('secForm').addEventListener('submit', () => {
  submitBtn.textContent = 'Sauvegarde en cours...';
  submitBtn.disabled = true;
});

// ── Auto-open sécurité si succès ──
<?php if ($message && $message['type'] === 'success'): ?>
document.querySelector('[data-tab="securite"]').click();
<?php elseif ($message && $message['type'] === 'error'): ?>
document.querySelector('[data-tab="securite"]').click();
<?php endif; ?>
</script>

</body>
</html>