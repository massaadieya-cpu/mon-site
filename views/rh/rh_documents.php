<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALTUTEX | Flux Documentaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #a2d2ff;
            --deep-navy: #1d3557;
            --accent-orange: #ffba52;
            --glass-white: rgba(255, 255, 255, 0.9);
        }

        body { 
            background: radial-gradient(circle at top left, #fdfcfb 0%, #e2d1c3 100%);
            background-attachment: fixed;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--deep-navy);
            min-height: 100vh;
        }

        .main-card {
            background: var(--glass-white);
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(15px);
            padding: 3.5rem;
        }

        .page-title {
            font-weight: 800;
            background: linear-gradient(90deg, #1d3557, #457b9d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .icon-badge {
            width: 64px;
            height: 64px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 15px rgba(162, 210, 255, 0.3);
            color: var(--primary-blue);
            font-size: 1.8rem;
        }

        .form-control, .form-select, .ts-control {
            border-radius: 18px !important;
            border: 2px solid #f0f3f7 !important;
            padding: 12px 20px !important;
        }

        .btn-send {
            background: linear-gradient(135deg, #74a5ff 0%, #1d3557 100%);
            border: none; border-radius: 30px; padding: 18px;
            font-weight: 700; color: white; transition: 0.4s;
        }

        .btn-paie {
            background: linear-gradient(135deg, #1d3557 0%, #457b9d 100%);
            border: none; border-radius: 30px; padding: 15px;
            font-weight: 700; color: white; transition: 0.4s;
        }

        .btn-send:hover, .btn-paie:hover { transform: translateY(-4px); opacity: 0.9; }

        .alert-modern { 
            border-radius: 20px; 
            border: none; 
            font-size: 0.9rem;
        }
        .alert-success-modern { background: #e7f9ed; color: #1f7a33; border-left: 5px solid #28a745 !important; }
        .alert-warning-modern { 
            background: #fff8ee; 
            color: #856404; 
            border-left: 5px solid #ffba52 !important; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-badge me-3"><i class="bi bi-send-plus-fill"></i></div>
            <div>
                <h1 class="page-title mb-1 h2">Flux Documentaire</h1>
                <p class="text-muted small mb-0">Gestion des transmissions ALTUTEX</p>
            </div>
        </div>
        <a href="index.php?action=dashboard" class="btn btn-light rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-grid-1x2-fill me-2"></i>Dashboard
        </a>
    </div>

    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="alert alert-modern alert-success-modern p-3 shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i> Document envoyé avec succès !
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['error']) && $_GET['error'] == 'confirm_duplicate'): ?>
                <div class="alert alert-modern alert-warning-modern p-3 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-circle-fill fs-5 me-2"></i>
                            <span>Le fichier <b><?= htmlspecialchars($_GET['filename']) ?></b> existe déjà (envoyé le <?= htmlspecialchars($_GET['date']) ?>).</span>
                        </div>
                        <a href="index.php?action=documents" class="btn-close ms-2"></a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="main-card">
                <form action="index.php?action=documents&do=upload" method="POST" enctype="multipart/form-data">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold small text-uppercase opacity-75">Nom du document</label>
                            <input type="text" name="nom_document" class="form-control" placeholder="Ex: Contrat de travail..." required>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-uppercase opacity-75">Destinataire</label>
                            <select name="target_user_id" class="form-select" id="userSelect" required>
                                <option value="">Sélectionnez un employé...</option>
                                <?php if(isset($employes)): foreach ($employes as $e): ?>
                                    <option value="<?= $e['id'] ?>"><?= $e['matricule'] ?> — <?= strtoupper($e['nom']) ?> <?= $e['prenom'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-uppercase opacity-75">Fichier</label>
                            <input type="file" name="fichier_upload" class="form-control" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-4 border">
                                <input class="form-check-input ms-0 me-3" type="checkbox" name="diffuse_all" id="diffuseAll">
                                <label class="form-check-label fw-bold" for="diffuseAll">DIFFUSER À TOUT LE MONDE</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-send w-100 mt-4 fs-5 shadow">DÉPLOYER LE DOCUMENT</button>
                </form>

                <hr class="my-5 opacity-25">
                

                <div class="p-4 rounded-4" style="background: rgba(162, 210, 255, 0.1); border: 1px dashed var(--primary-blue);">
                    <h3 class="h6 mb-3 fw-bold text-uppercase opacity-75">Fiches de Paie Automatiques</h3>
                    <form action="index.php?action=documents&do=upload" method="POST">
                        <input type="hidden" name="action_paie_auto" value="1">
                        <button type="submit" class="btn btn-paie w-100 shadow-sm" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Traitement...';">
                           LANCER L'ENVOI GROUPÉ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    var control = new TomSelect('#userSelect', { create: false });
    document.getElementById('diffuseAll').addEventListener('change', function() {
        if(this.checked) {
            control.disable();
            document.getElementById('userSelect').required = false;
        } else {
            control.enable();
            document.getElementById('userSelect').required = true;
        }
    });
</script>
</body>
</html>