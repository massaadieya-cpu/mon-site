<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications RH - ALTUTEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #2C4A7A;
            --accent-blue: #3B82F6;
            --dark-red: #EF4444;
            --bg-gray: #F3F4F6;
        }

        body {
            background-color: var(--bg-gray);
            font-family: 'Inter', sans-serif;
            color: #1F2937;
        }

        /* Nouveau bouton Dashboard */
        .btn-back {
            background: white;
            color: var(--primary-blue);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-back:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateX(-5px);
        }

        .notif-header {
            padding: 20px 0 40px 0;
        }

        .notif-header h1 {
            font-weight: 800;
            color: var(--primary-blue);
            letter-spacing: -1px;
            margin-top: 10px;
        }

        .card-custom {
            border: none;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            background: white;
            height: 100%;
        }

        .card-custom:hover {
            transform: translateY(-5px);
        }

        .card-header-custom {
            padding: 25px;
            border-bottom: 1px solid #F3F4F6;
            display: flex;
            align-items: center;
            gap: 15px;
            background: transparent;
        }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .bg-red-gradient { background: linear-gradient(135deg, #FF6B6B, #EF4444); }
        .bg-blue-gradient { background: linear-gradient(135deg, #60A5FA, #2C4A7A); }

        .notif-item {
            border-radius: 15px;
            background-color: #F9FAFB;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .notif-item:hover {
            background-color: white;
            border-color: #E5E7EB;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .avatar-red { background: #FEE2E2; color: #EF4444; }
        .avatar-blue { background: #DBEAFE; color: #2C4A7A; }

        .matricule-tag {
            font-size: 11px;
            background: #EEE;
            padding: 2px 8px;
            border-radius: 5px;
            color: #666;
        }

        .btn-action {
            border-radius: 10px;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 20px;
            text-transform: uppercase;
            transition: 0.3s;
        }

        .btn-outline-red {
            border: 2px solid #FEE2E2;
            color: #EF4444;
            background: transparent;
            text-decoration: none;
        }

        .btn-outline-red:hover {
            background: #EF4444;
            color: white;
        }

        .btn-outline-blue {
            border: 2px solid #DBEAFE;
            color: var(--primary-blue);
            background: transparent;
            text-decoration: none;
        }

        .btn-outline-blue:hover {
            background: var(--primary-blue);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #9CA3AF;
        }
    </style>
</head>
<body>

<div class="container notif-container">
    <a href="index.php?action=dashboard" class="btn-back">
        <i class="fas fa-arrow-left"></i> Retour au Dashboard
    </a>

    <header class="notif-header text-center">
        <h1>Centre de Notifications RH</h1>
        <p class="text-muted">Gérez les flux d'absences et demandes de congés - <strong>ALTUTEX</strong></p>
    </header>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <div class="icon-box bg-red-gradient">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Absences Déclarées</h5>
                        <small class="text-muted"><?= count($absences) ?> notification(s)</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($absences)): ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle fa-3x mb-3 opacity-25"></i>
                            <p>Aucune absence à traiter.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($absences as $a): ?>
                            <div class="notif-item">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar-circle avatar-red me-3">
                                        <?= strtoupper(substr($a['nom'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($a['nom'] . ' ' . $a['prenom']) ?></h6>
                                        <span class="matricule-tag">Matricule: <?= $a['matricule_user'] ?></span>
                                    </div>
                                    <small class="text-muted">Récents</small>
                                </div>
                                <p class="small text-secondary mb-3"><?= htmlspecialchars($a['message']) ?></p>
                                <div class="text-end">
                                    <a href="index.php?action=autorisation" class="btn btn-action btn-outline-red">Réviser</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <div class="icon-box bg-blue-gradient">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Demandes de Congés</h5>
                        <small class="text-muted"><?= count($conges) ?> nouvelle(s) demande(s)</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($conges)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check fa-3x mb-3 opacity-25"></i>
                            <p>Aucun congé en attente.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conges as $c): ?>
                            <div class="notif-item">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="avatar-circle avatar-blue me-3">
                                        <?= strtoupper(substr($c['nom'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></h6>
                                        <span class="matricule-tag">Matricule: <?= $c['matricule_user'] ?></span>
                                    </div>
                                    <small class="text-muted">Nouveau</small>
                                </div>
                                <p class="small text-secondary mb-3"><?= htmlspecialchars($c['message']) ?></p>
                                <div class="text-end">
                                    <a href="index.php?action=autorisation&tab=conges" class="btn btn-action btn-outline-blue">Confirmer</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>