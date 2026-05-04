<?php
// On récupère les données avec sécurité
$recommandations = $donneesIA['recommandations'] ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALTUTEX | Analytics RH</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #223b7f; /* Bleu Marine Profond */
            --accent-blue: #3b82f6;  /* Bleu Analytics */
            --bg-soft: #f8fafc;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
        }

        body { 
            background-color: var(--bg-soft); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--text-main); 
            margin: 0;
        }

        /* Header Style Business Intelligence */
        .analytics-header {
            background-color: var(--primary-blue);
            color: white;
            padding: 35px 0;
            border-bottom: 4px solid var(--accent-blue);
        }

        .header-title { font-weight: 800; letter-spacing: -0.5px; margin: 0; }
        .header-subtitle { opacity: 0.8; font-size: 0.95rem; font-weight: 400; }

        /* Dashboard Cards */
        .card-analytics {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            height: 100%;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary-blue);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Items de risque (Data Warning) */
        .data-alert-item {
            background: #fdf2f2;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid #fee2e2;
        }

        .data-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #b91c1c;
            background: #fecaca;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Items de solution (Decision Support) */
        .decision-item {
            background: #f0f9ff;
            border-left: 4px solid var(--accent-blue);
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 0 8px 8px 0;
        }

        /* Confidence Bar */
        .confidence-score {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary-blue);
        }

        .progress-tiny {
            height: 4px;
            background-color: #cbd5e1;
            border-radius: 2px;
            margin-top: 8px;
        }

        .btn-back {
            color: var(--primary-blue);
            border: 1.5px solid var(--primary-blue);
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 25px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-back:hover {
            background: var(--primary-blue);
            color: white;
        }
    </style>
</head>
<body>

<header class="analytics-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="header-title"><i class="fas fa-chart-line me-2"></i> DATA ANALYTICS RH</h2>
                <p class="header-subtitle mb-0">Traitement sémantique et analyse des vecteurs de satisfaction</p>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="d-inline-block text-start bg-white bg-opacity-10 p-2 rounded px-3">
                    <small class="d-block opacity-75 small">Modèle d'analyse :</small>
                    <span class="fw-bold">Multilingual Transformer L12</span>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container mt-5 pb-5">
    <div class="row g-4">
        
        <div class="col-lg-6">
            <div class="card-analytics p-4">
                <h5 class="section-title"><i class="fas fa-search-plus"></i> Signaux Critiques Détectés</h5>
                
                <?php if(!empty($recommandations)): ?>
                    <?php foreach($recommandations as $item): ?>
                        <div class="data-alert-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="data-label"><?= htmlspecialchars($item['type']) ?></span>
                                <span class="confidence-score"><?= $item['confiance'] ?>% de corrélation</span>
                            </div>
                            <p class="mb-2 fw-bold text-dark" style="font-size: 0.95rem;">
                                <?= htmlspecialchars($item['probleme']) ?>
                            </p>
                            <div class="progress-tiny">
                                <div class="progress-bar bg-primary" style="width: <?= $item['confiance'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-database text-muted fa-3x mb-3 opacity-25"></i>
                        <p class="text-muted small">Aucune anomalie sémantique détectée dans le jeu de données.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card-analytics p-4">
                <h5 class="section-title"><i class="fas fa-bullseye"></i> Aide à la Décision Stratégique</h5>
                
                <?php 
                $unique_sols = array_unique(array_column($recommandations, 'solution'));
                if(!empty($unique_sols)):
                    foreach($unique_sols as $sol): ?>
                        <div class="decision-item">
                            <div class="d-flex gap-3">
                                <div class="text-primary mt-1">
                                    <i class="fas fa-arrow-right-to-bracket"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark">Action recommandée :</h6>
                                    <p class="mb-0 text-muted small"><?= htmlspecialchars($sol) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <p class="text-muted small">En attente de traitement...</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <div class="text-center mt-5">
        <hr class="mb-4 opacity-10">
        <a href="index.php?action=stats" class="btn-back">
            <i class="fas fa-chevron-left me-2"></i> Retour au reporting global
        </a>
    </div>
</div>

</body>
</html>