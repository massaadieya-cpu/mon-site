<!DOCTYPE html> 
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --altutex-blue: #003790;
            --altutex-orange: #fd7e14;
            --light-bg: #f8f9fc;
            --card-green: #28a745;
        }

        body { background-color: var(--light-bg); font-family: 'Inter', sans-serif; }
        
        /* Conteneur Principal avec bords arrondis prononcés */
        .main-card { 
            background: white; 
            border-radius: 25px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.03); 
            padding: 40px; 
            margin-top: 20px;
        }

        /* En-tête stylisé */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .title-group i { color: var(--altutex-blue); font-size: 2rem; margin-right: 15px; }
        .title-group h2 { color: var(--altutex-blue); font-weight: 800; margin: 0; }

        /* Navigation de la Semaine optimisée */
        .nav-week {
            background: #fff;
            border: 1px solid #e3e6f0;
            border-radius: 15px;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-nav {
            width: 45px; height: 45px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s ease;
            color: var(--altutex-blue);
            border: 1px solid #e3e6f0;
            text-decoration: none;
        }

        .btn-nav:hover { background: var(--altutex-blue); color: white; transform: translateY(-2px); }

        /* Calendrier */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 15px; }
        
        .day-column { text-align: center; }
        
        .day-header { 
            color: var(--altutex-orange); 
            font-weight: 800; 
            font-size: 0.9rem; 
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .day-box {
            background: #fff;
            border: 1px solid #edf2f7;
            border-radius: 20px;
            min-height: 220px;
            padding: 15px;
            transition: all 0.3s ease;
            position: relative;
        }

        .today-box { border: 2px solid #4e73df; background: #f0f4ff; }
        
        .date-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #b7beca;
            margin-bottom: 15px;
            display: block;
        }

        .today-box .date-number { color: #4e73df; }

        /* Carte de Formation Réelle */
        .formation-pill {
            background: var(--card-green);
            color: white;
            padding: 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            text-align: left;
            margin-bottom: 8px;
            border-left: 5px solid #1e7e34;
            animation: fadeIn 0.5s ease;
        }

        .time-tag {
            background: rgba(0,0,0,0.15);
            padding: 2px 8px;
            border-radius: 6px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 5px;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="main-card">
        <div class="header-section">
            <div class="title-group d-flex align-items-center">
                <i class="fas fa-user-graduate"></i>
                <h2>Mon Planning de Formation</h2>
            </div>
            
            <div class="nav-week shadow-sm">
                <a href="index.php?action=planning&week=<?= $prevWeek ?>" class="btn-nav">
                    <i class="fas fa-chevron-left"></i>
                </a>
                <span class="fw-bold text-secondary">
                    Semaine du <?= date('d/m', strtotime($monday)) ?> au <?= date('d/m', strtotime($monday . ' + 6 days')) ?>
                </span>
                <a href="index.php?action=planning&week=<?= $nextWeek ?>" class="btn-nav">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>

            <a href="index.php?action=dashboard" class="btn btn-outline-dark rounded-pill px-4 shadow-sm">
                <i class="fas fa-th-large me-2"></i>Dashboard
            </a>
        </div>

        <div class="calendar-grid">
            <?php 
            $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
            for($i=0; $i<7; $i++): 
                $date_c = date('Y-m-d', strtotime("+$i days", strtotime($monday)));
                $isToday = ($date_c == date('Y-m-d'));
            ?>
                <div class="day-column">
                    <div class="day-header"><?= $jours[$i] ?></div>
                    <div class="day-box <?= $isToday ? 'today-box shadow-sm' : '' ?>">
                        <span class="date-number"><?= date('d', strtotime($date_c)) ?></span>

                        <?php if (isset($formationsByDate[$date_c])): ?>
                            <?php foreach ($formationsByDate[$date_c] as $f): ?>
                                <div class="formation-pill shadow-sm">
                                    <span class="time-tag">
                                        <i class="far fa-clock me-1"></i> 
                                        <?= substr($f['heure_formation'], 0, 5) ?>
                                    </span>
                                    <div class="fw-bold mt-1"><?= htmlspecialchars($f['nom_formation']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

</body>
</html>