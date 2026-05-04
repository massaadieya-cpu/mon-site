<!DOCTYPE html>
<html lang="fr" id="mainHtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Altutex - Espace Engagement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --v-purple: #833cff; 
            --v-gradient: linear-gradient(135deg, #9743ff 0%, #a29bfe 100%);
            /* Dégradé exact du bouton violet de l'image */
            --v-done-gradient: linear-gradient(90deg, #9436ff 0%, #a367dc 100%);
            --soft-bg: #f0f3ff;
            --text-dark: #2d3436;
            --glass-white: rgba(255, 255, 255, 0.8);
        }
        
        body { 
            background: linear-gradient(120deg, #f0f3ff 0%, #e0e7ff 100%);
            font-family: 'Poppins', sans-serif; 
            color: #444; 
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-altutex {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo-text { font-size: 1.5rem; font-weight: 800; color: var(--v-purple); text-decoration: none; }

        /* Bouton Retour */
        .btn-back {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #e8c1ff;
            padding: 10px 18px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            transition: 0.3s;
        }

        /* Header */
        .header-section h2 { font-weight: 800; color: var(--text-dark); font-size: 2.2rem; }
        .line-accent { width: 50px; height: 4px; background: var(--v-purple); border-radius: 10px; margin-bottom: 35px; }

        /* Card Modern Design */
        .enquete-card { 
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            border-radius: 25px; 
            padding: 30px; 
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 15px 35px rgba(0,0,0,0.03);
            height: 240px; /* Hauteur fixe pour éviter l'effet trop grand */
            max-width: 450px; /* Largeur max pour coller à la photo */
            display: flex;
            flex-direction: column;
            transition: 0.3s ease;
            position: relative;
        }
        .enquete-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(108, 92, 231, 0.1); }

        .status-badge {
            background: #1e7e34; /* Vert exact de l'image */
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 5px 15px;
            border-radius: 8px;
            text-transform: uppercase;
        }
        
        .card-title { font-weight: 700; color: #1e293b; margin-top: 20px; font-size: 1.3rem; }
        .card-desc { color: #64748b; font-size: 0.9rem; margin: 10px 0 25px 0; flex-grow: 1; }

        /* Style du bouton "Déjà envoyé" identique à l'image */
        .btn-done {
            background: var(--v-done-gradient);
            color: white;
            border-radius: 14px;
            padding: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: default;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(108, 92, 231, 0.2);
        }

        /* L'icône de check vert dans le cercle blanc */
        .check-circle-icon {
            background: #4cd137;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            border: 1.5px solid rgba(255,255,255,0.8);
        }

        .btn-participate { 
            background: #f1f5f9; 
            color: var(--v-purple); 
            border-radius: 14px; 
            padding: 12px; 
            font-weight: 700; 
            text-decoration: none; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .lang-toggle { cursor: pointer; padding: 5px 15px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; }
        .lang-toggle.active { background: var(--v-purple); color: white; }
    </style>
</head>
<body>

<nav class="navbar-altutex mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="logo-text">ALTUTEX</a>
        <div class="d-flex gap-2">
            <div id="btn-fr" class="lang-toggle active" onclick="setLanguage('fr')">FR</div>
            <div id="btn-ar" class="lang-toggle" onclick="setLanguage('ar')">AR</div>
        </div>
    </div>
</nav>

<div class="container">
    <a href="index.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> <span id="txt-back">Retour au Dashboard</span>
    </a>

    <div class="header-section">
        <h2 id="main-title">Espace Collaboratif & Feedback</h2>
        <p class="text-secondary" id="main-sub">Votre voix est le moteur de notre évolution au sein d'ALTUTEX.</p>
        <div class="line-accent"></div>
    </div>

    <div class="row g-4 mb-5">
        <?php foreach ($enquetes as $e): 
            $stmt_check = $pdo->prepare("SELECT id FROM enquete_participations WHERE enquete_id = ? AND utilisateur_id = ?");
            $stmt_check->execute([$e['id'], $_SESSION['user_id']]);
            $dejaRepondu = $stmt_check->fetch();
        ?>
        <div class="col-md-5">
            <div class="enquete-card">
                <div class="d-flex justify-content-between align-items-center">
                    <?php if ($dejaRepondu): ?>
                        <span class="status-badge" id="badge-done">SOUMIS</span>
                    <?php else: ?>
                        <span class="status-badge" id="badge-new" style="background: #6c5ce7;">NOUVEAU</span>
                    <?php endif; ?>
                    <div class="text-muted small">
                        <i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($e['date_creation'])) ?>
                    </div>
                </div>
                
                <h4 class="card-title"><?= htmlspecialchars($e['titre']) ?></h4>
                <p class="card-desc"><?= htmlspecialchars(substr($e['description'], 0, 100)) ?>...</p>
                
                <?php if ($dejaRepondu): ?>
                    <div class="btn-done">
                        <span class="btn-txt-done">Déjà envoyé</span> 
                        <div class="check-circle-icon"><i class="fas fa-check"></i></div>
                    </div>
                <?php else: ?>
                    <a href="index.php?action=repondre_enquete&id=<?= $e['id'] ?>" class="btn-participate">
                        <span class="btn-txt">Participer à l'enquête</span> <i class="fas fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function setLanguage(lang) {
        const html = document.getElementById('mainHtml');
        if (lang === 'ar') {
            html.setAttribute('dir', 'rtl');
            document.getElementById('txt-back').innerText = "العودة إلى لوحة القيادة";
            document.getElementById('main-title').innerText = "فضاء المشاركة والآراء";
            document.querySelectorAll('.btn-txt-done').forEach(el => el.innerText = "تم الإرسال");
            document.querySelectorAll('#badge-done').forEach(el => el.innerText = "مُرسل");
        } else {
            html.setAttribute('dir', 'ltr');
            document.getElementById('txt-back').innerText = "Retour au Dashboard";
            document.getElementById('main-title').innerText = "Espace Collaboratif & Feedback";
            document.querySelectorAll('.btn-txt-done').forEach(el => el.innerText = "Déjà envoyé");
            document.querySelectorAll('#badge-done').forEach(el => el.innerText = "SOUMIS");
        }
        document.getElementById('btn-fr').classList.toggle('active', lang === 'fr');
        document.getElementById('btn-ar').classList.toggle('active', lang === 'ar');
    }
</script>

</body>
</html>