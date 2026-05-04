<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALTUTEX | Mes Documents Officiels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-grad-1: #0e2f4f;
            --sidebar-grad-2: #426189;
            --accent-yellow: #ffba52;
            --main-bg: #f8fafc;
            --text-dark: #022964;
            --glass-white: rgba(255, 255, 255, 0.03);
        }

        body { 
            background-color: var(--main-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 270px;
            background: linear-gradient(135deg, var(--sidebar-grad-1) 0%, var(--sidebar-grad-2) 100%);
            position: fixed;
            height: 100vh;
            padding: 40px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 10px 0 30px rgba(0,0,0,0.15);
            z-index: 1000;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-brand {
            font-size: 1.8rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 50px;
            letter-spacing: -1px;
            color: white;
            text-transform: uppercase;
        }

        .sidebar-nav {
            padding: 0 15px;
            flex-grow: 1;
        }

        .sidebar-nav .nav-link {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.95rem;
            font-weight: 600;
            padding: 14px 20px;
            border-radius: 15px;
            margin-bottom: 8px;
            transition: 0.3s all ease;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .sidebar-nav .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .sidebar-nav .nav-link.active i { color: var(--accent-yellow); }

        .sidebar-nav .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.08);
            color: white;
            transform: translateX(5px);
        }

        .logout-link {
            color: #ffb3b3 !important;
            background: rgba(255, 71, 71, 0.05) !important;
            margin-top: auto;
        }

        /* --- CONTENU --- */
        .main-content {
            margin-left: 270px;
            width: calc(100% - 270px);
            padding: 40px 5%;
        }

        .content-header {
            background: white;
            border-radius: 24px;
            padding: 25px 40px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.02);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .header-info h2 {
            font-weight: 800;
            color: var(--text-dark);
            margin: 0;
        }

        .session-badge {
            background: #f1f5f9;
            padding: 6px 14px;
            border-radius: 10px;
            color: var(--sidebar-grad-1);
            font-size: 0.85rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
        }

        .status-pill {
            background: #ecfdf5;
            color: #10b981;
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1px solid #d1fae5;
        }

        /* --- DOCUMENTS --- */
        .docs-container {
            background: white;
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.02);
            border: 1px solid #edf2f7;
        }

        .doc-list-item {
            background: #ffffff;
            border-radius: 20px;
            padding: 20px 30px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #f1f5f9;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .doc-list-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.06);
            border-color: var(--sidebar-grad-2);
        }

        .icon-box {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
        }

        .btn-view {
            background: var(--sidebar-grad-1);
            color: white;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 700;
            transition: 0.3s;
            text-decoration: none;
            border: none;
        }

        .btn-view:hover { background: var(--sidebar-grad-2); color: white; transform: scale(1.02); }

        .btn-download {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: white;
            color: var(--sidebar-grad-1);
            border: 1.5px solid #e2e8f0;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-download:hover { background: #f8fafc; color: #10b981; border-color: #10b981; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-brand">ALTUTEX</div>
        <nav class="sidebar-nav">
            <a href="index.php?action=dashboard" class="nav-link">
                <i class="bi bi-grid-1x2-fill me-3"></i> Dashboard
            </a>
            <a href="index.php?action=mes_documents" class="nav-link active">
                <i class="bi bi-folder-fill me-3"></i> Mes Documents
            </a>
            <div style="height: 1px; background: rgba(255,255,255,0.1); margin: 20px 25px;"></div>
            <a href="index.php?action=logout" class="nav-link logout-link">
                <i class="bi bi-power me-3"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="content-header">
            <div class="header-info">
                <h2>Espace Documentaire</h2>
                <div class="mt-1">
                    <span class="session-badge">
                        <i class="bi bi-person-badge me-2"></i> 
                        <?= htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? 'Employé')) ?> 
                        <small class="ms-2 opacity-50" style="font-weight: 500;">
                            [<?= htmlspecialchars($_SESSION['matricule'] ?? 'N/A') ?>]
                        </small>
                    </span>
                </div>
            </div>
            <div class="status-pill d-none d-md-block">
                <i class="bi bi-shield-lock-fill me-2"></i> Données confidentielles
            </div>
        </div>

        <div class="docs-container">
            <?php if (empty($documents)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-folder2-open display-1 text-light"></i>
                    </div>
                    <h3 class="fw-bold text-secondary">Aucun document disponible</h3>
                    <p class="text-muted">Vous recevrez une notification dès qu'un document vous sera adressé.</p>
                </div>
            <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                    <div class="doc-list-item">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-4">
                                <?php 
                                    $ext = strtolower($doc['type_fichier']);
                                    // Gestion dynamique des icônes selon l'extension
                                    if($ext == 'pdf') {
                                        echo '<i class="bi bi-file-earmark-pdf-fill text-danger fs-2"></i>';
                                    } elseif(in_array($ext, ['doc', 'docx'])) {
                                        echo '<i class="bi bi-file-earmark-word-fill text-primary fs-2"></i>';
                                    } elseif(in_array($ext, ['xls', 'xlsx'])) {
                                        echo '<i class="bi bi-file-earmark-excel-fill text-success fs-2"></i>';
                                    } else {
                                        echo '<i class="bi bi-file-earmark-fill text-secondary fs-2"></i>';
                                    }
                                ?>
                            </div>
                            <div>
                                <h5 class="m-0 fw-bold text-dark">
                                    <?= htmlspecialchars($doc['nom_affichage']) ?>
                                    <?php if (isset($doc['diffuse_a_tous']) && $doc['diffuse_a_tous']): ?>
                                        <span class="badge bg-info-subtle text-info ms-2" style="font-size: 0.6rem; vertical-align: middle;">
                                            <i class="bi bi-megaphone-fill"></i> PUBLIC
                                        </span>
                                    <?php endif; ?>
                                </h5>
                                <small class="text-muted">
                                    <i class="bi bi-calendar3 me-1"></i> Reçu le <?= date('d/m/Y', strtotime($doc['date_envoi'])) ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-3">
                            <a href="<?= $doc['chemin'] ?>" target="_blank" class="btn-view">
                                <i class="bi bi-eye-fill me-2"></i> Consulter
                            </a>
                            <a href="<?= $doc['chemin'] ?>" 
                               download="<?= htmlspecialchars($doc['nom_affichage']) ?>.<?= $doc['type_fichier'] ?>" 
                               class="btn-download" 
                               title="Télécharger">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>