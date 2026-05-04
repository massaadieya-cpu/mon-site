<!DOCTYPE html> 
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --altutex-dark: #082149;
            --altutex-orange: #fd7e14;
            --success-green: #28a745;
            --danger-red: #8B1A1A;
            --logout-red: #f1556c;
        }

        body { background-color: #f4f7fe; padding: 20px; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        .planning-container { 
            background: white; 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            max-width: 1300px; 
            margin: auto;
        }

        /* Barre d'outils supérieure */
        .top-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-title { 
            color: var(--altutex-dark); 
            font-weight: 800; 
            font-size: 1.6rem; 
            display: flex; 
            align-items: center; 
            gap: 12px;
            margin: 0;
        }

        .btn-dashboard {
            background: white;
            color: var(--altutex-dark);
            border: 1px solid #e2e8f0;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-dashboard:hover { background: #f8fafc; border-color: var(--altutex-dark); }

        .btn-logout {
            background: var(--logout-red);
            color: white;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-logout:hover { background: var(--danger-red); color: white; box-shadow: 0 4px 12px rgba(241, 85, 108, 0.3); }

        /* Formulaire */
        .form-section {
            background: #f8fafc;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            margin-bottom: 30px;
        }

        .input-custom { 
            border: 1px solid #cbd5e1; 
            border-radius: 10px; 
            padding: 10px 15px; 
            outline: none; 
            transition: all 0.3s;
        }

        .input-custom:focus { border-color: var(--altutex-dark); box-shadow: 0 0 0 3px rgba(8, 33, 73, 0.1); }

        .btn-envoyer { 
            background: var(--danger-red); 
            color: white; 
            border: none; 
            padding: 10px 35px; 
            border-radius: 10px; 
            font-weight: 700; 
            transition: 0.3s; 
        }

        .btn-envoyer:hover { background: #6b1414; transform: translateY(-2px); }

        /* Navigation Semaine */
        .week-nav { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 25px;
            padding: 0 10px;
        }

        .week-info h5 { 
            margin: 0; 
            color: var(--altutex-dark); 
            font-weight: 800; 
            font-size: 1.2rem;
        }

        .btn-nav { 
            background: white; 
            border: 1px solid #e2e8f0; 
            color: #64748b; 
            border-radius: 12px; 
            padding: 8px 20px; 
            text-decoration: none; 
            font-weight: 600;
            transition: 0.2s; 
        }

        .btn-nav:hover { background: var(--altutex-dark); color: white; }

        /* Calendrier */
        .calendar-table { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 10px; table-layout: fixed; }
        
        .calendar-table th { 
            background: transparent; 
            color: var(--altutex-orange); 
            text-align: center; 
            padding: 10px; 
            font-weight: 800; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .calendar-table td { 
            height: 200px; 
            vertical-align: top; 
            padding: 12px; 
            border-radius: 15px; 
            background: #fff; 
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
            transition: 0.3s;
        }

        .calendar-table td:hover { border-color: #cbd5e1; transform: translateY(-3px); }

        .today-cell { background: #f0f7ff !important; border: 2px solid #3b82f6 !important; }

        .day-num { 
            display: flex; 
            justify-content: center; 
            align-items: center;
            width: 35px; 
            height: 35px;
            margin: 0 auto 15px;
            color: #64748b; 
            font-weight: 800; 
            font-size: 1.1rem; 
        }

        .today-num { background: #3b82f6; color: white; border-radius: 10px; }

        .event-card { 
            background: var(--success-green); 
            color: white; 
            padding: 10px; 
            border-radius: 12px; 
            font-size: 0.85rem; 
            text-align: center; 
            margin-bottom: 10px; 
            position: relative;
            overflow: hidden;
        }

        .event-time { 
            font-weight: 800; 
            display: block; 
            margin-bottom: 5px; 
            background: rgba(0,0,0,0.1);
            border-radius: 6px;
        }

        .event-actions { 
            display: flex; 
            justify-content: center; 
            gap: 12px; 
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }

        .event-actions i { cursor: pointer; transition: 0.2s; opacity: 0.8; }
        .event-actions i:hover { transform: scale(1.3); opacity: 1; }
    </style>
</head>
<body>

<div class="planning-container">
    <div class="top-toolbar">
        <a href="index.php?action=dashboard" class="btn-dashboard">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
        
        <div class="header-title">
            <i class="fas fa-graduation-cap text-primary"></i> 
            <span>Gestion des Formations Altutex</span>
        </div>

        <a href="index.php?action=logout" class="btn-logout">
            <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
        </a>
    </div>
    
    <div class="form-section">
        <form method="POST" action="index.php?action=planning" class="row g-3 align-items-center">
            <input type="hidden" name="id_formation" id="id_f" value="">
            
            <div class="col-lg-1 text-success fw-bold">
                <i class="fas fa-plus-circle"></i> <span id="label-mode">Ajouter</span>
            </div>
            
            <div class="col-lg-4">
                <input type="text" name="nom_formation" id="nom_f" class="form-control input-custom" placeholder="Nom de la formation..." required>
            </div>
            
            <div class="col-lg-2">
                <input type="date" name="date" id="date_f" class="form-control input-custom" value="<?= $targetDate ?>">
            </div>
            
            <div class="col-lg-1">
                <select name="heure" id="h_f" class="form-select input-custom">
                    <?php for($i=8;$i<=20;$i++) echo "<option value='".str_pad($i,2,'0',STR_PAD_LEFT)."'>{$i}h</option>"; ?>
                </select>
            </div>
            
            <div class="col-lg-1">
                <select name="minute" id="m_f" class="form-select input-custom">
                    <option value="00">00</option>
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="45">45</option>
                </select>
            </div>
            
            <div class="col-lg-3 d-flex gap-2">
                <button type="submit" name="envoyer" class="btn-envoyer flex-grow-1">ENVOYER</button>
                <button type="button" onclick="location.href='index.php?action=planning'" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="week-nav">
        <a href="index.php?action=planning&week=<?= $prevWeek ?>" class="btn-nav">
            <i class="fas fa-arrow-left me-2"></i> Précédent
        </a>
        
        <div class="week-info text-center">
            <h5>Semaine du <?= date('d/m', strtotime($monday)) ?> au <?= date('d/m/Y', strtotime($monday . ' + 6 days')) ?></h5>
            <a href="index.php?action=planning" class="badge bg-primary text-decoration-none mt-2 px-3 py-2">
                <i class="fas fa-calendar-day me-1"></i> Aujourd'hui
            </a>
        </div>

        <a href="index.php?action=planning&week=<?= $nextWeek ?>" class="btn-nav">
            Suivant <i class="fas fa-arrow-right ms-2"></i>
        </a>
    </div>

    <table class="calendar-table">
        <thead>
            <tr>
                <th>Lundi</th><th>Mardi</th><th>Mercredi</th><th>Jeudi</th><th>Vendredi</th><th>Samedi</th><th>Dimanche</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <?php
            for($i=0; $i<7; $i++) {
                $date_c = date('Y-m-d', strtotime("+$i days", strtotime($monday)));
                $isTodayNum = ($date_c == date('Y-m-d')) ? 'today-num' : '';
                $isTodayCell = ($date_c == date('Y-m-d')) ? 'today-cell' : '';
                
                echo "<td class='$isTodayCell'><span class='day-num $isTodayNum'>".date('d', strtotime($date_c))."</span>";
                
                if (isset($formationsByDate[$date_c])) {
                    foreach ($formationsByDate[$date_c] as $f) {
                        $t = explode(':', $f['heure_formation']);
                        echo "<div class='event-card shadow-sm'>";
                        echo "<span class='event-time'>{$t[0]}:{$t[1]}</span>";
                        echo "<div class='fw-bold mb-1'>".htmlspecialchars($f['nom_formation'])."</div>";
                        echo "<div class='event-actions'>";
                        echo "<i class='fas fa-edit' title='Modifier' onclick='editF(\"".$f['id']."\",\"".addslashes($f['nom_formation'])."\",\"".$f['date_formation']."\",\"".$t[0]."\",\"".$t[1]."\")'></i>";
                        echo "<i class='fas fa-trash-alt' title='Supprimer' onclick='if(confirm(\"Supprimer cette formation ?\")) window.location.href=\"index.php?action=planning&delete_id=".$f['id']."&week=".$targetDate."\"'></i>";
                        echo "</div></div>";
                    }
                }
                echo "</td>";
            } ?>
            </tr>
        </tbody>
    </table>
</div>

<script>
function editF(id, nom, date, h, m) {
    document.getElementById('id_f').value = id;
    document.getElementById('nom_f').value = nom;
    document.getElementById('date_f').value = date;
    document.getElementById('h_f').value = h;
    document.getElementById('m_f').value = m;
    document.getElementById('label-mode').innerText = "Modifier";
    document.getElementById('nom_f').focus();
    window.scrollTo({top: 0, behavior: 'smooth'});
}
</script>
</body>
</html>