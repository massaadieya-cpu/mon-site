<?php
// On suppose que $pdo, $user_id, $deja_declares, $monday, $saturday, $current_date
// sont déjà préparés par le Controller.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ALTUTEX RH | Déclaration d'Absence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-dark: #140459; 
            --brand-primary: #025d6b; 
            --brand-accent: #ffc815; 
            --bg-body: #f8fafc;
            --danger: #ef4444;
        }

        body { background-color: var(--bg-body); font-family: 'Inter', sans-serif; color: var(--brand-dark); }
        
        .btn-back {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 8px 15px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: 0.3s;
            margin-right: 20px;
        }
        .btn-back:hover { background: var(--brand-accent); color: var(--brand-dark); }

        .premium-header { 
            background: var(--brand-dark); color: white; padding: 20px 40px;
            border-bottom: 3px solid var(--brand-accent); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .main-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; padding: 40px; }
        .glass-card { background: white; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; }

        .calendar-grid { display: grid; grid-template-columns: repeat(6, 1fr); background: #e2e8f0; gap: 1px; }
        .day-col { background: white; padding: 25px 15px; min-height: 650px; position: relative; transition: 0.3s; }
        .day-col.today { background: #fffdf5; }
        .day-col.today::before { content: "AUJOURD'HUI"; position: absolute; top: 0; left: 0; width: 100%; background: var(--brand-accent); color: var(--brand-dark); font-size: 0.6rem; font-weight: 900; text-align: center; padding: 2px 0; }
        
        /* État bloqué pour les jours déjà déclarés */
        .day-col.locked { background: #fef2f2; pointer-events: none; }
        .day-col.locked .day-date, .day-col.locked .day-name { opacity: 0.5; }
        .day-col.locked::after {
            content: "DÉJÀ DÉCLARÉ"; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-15deg);
            background: white; color: var(--danger);
            padding: 8px 12px; border: 3px solid var(--danger); 
            font-weight: 900; font-size: 0.8rem; border-radius: 12px;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.2);
            white-space: nowrap; z-index: 10;
        }

        .day-title { text-align: center; margin-bottom: 25px; }
        .day-name { font-weight: 700; font-size: 0.75rem; color: var(--brand-primary); text-transform: uppercase; letter-spacing: 1px; }
        .day-date { font-size: 2rem; font-weight: 900; color: var(--brand-dark); line-height: 1; }

        .time-slot { 
            display: block; width: 100%; padding: 12px 5px; margin-bottom: 10px;
            border: 1.5px solid #edf2f7; border-radius: 12px; background: white;
            font-size: 0.75rem; color: #64748b; font-weight: 700; text-align: center; 
            cursor: pointer; transition: all 0.2s ease;
        }
        .time-slot:hover { border-color: var(--brand-primary); transform: scale(1.02); color: var(--brand-primary); }
        .time-slot.selected { background: var(--brand-primary) !important; color: white !important; border-color: var(--brand-primary); box-shadow: 0 5px 15px rgba(2, 93, 107, 0.3); }

        .past-slot { background: #f8fafc !important; color: #cbd5e1 !important; cursor: not-allowed !important; opacity: 0.5; border-style: dashed; }

        .sidebar-panel { padding: 30px; position: sticky; top: 40px; height: fit-content; }
        #summary-view { background: #fcfdfe; border: 2px dashed #e2e8f0; max-height: 200px; overflow-y: auto; scrollbar-width: thin; }
        
        .btn-submit-flash { 
            background: var(--brand-dark); color: white; border: 2px solid var(--brand-accent);
            border-radius: 18px; padding: 18px; width: 100%; font-weight: 800; 
            text-transform: uppercase; transition: 0.3s; box-shadow: 0 10px 20px rgba(20, 4, 89, 0.2);
        }
        .btn-submit-flash:hover { background: var(--brand-accent); color: var(--brand-dark); transform: translateY(-3px); }
        .btn-submit-flash:active { transform: translateY(0); }
        
        .arabic { direction: rtl; display: block; font-size: 0.85rem; opacity: 0.8; font-weight: 400; }
        .pause-line { font-weight: 800; letter-spacing: 2px; color: #cbd5e1; font-size: 0.65rem; }
    </style>
</head>
<body>

<header class="premium-header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <a href="index.php?action=dashboard" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i> Dashboard
        </a>
        <div>
            <h2 class="m-0 fw-bold">ALTUTEX <span style="color: var(--brand-accent)">RH</span></h2>
            <span class="small fw-semibold">Déclaration d'absence / تصريح بالغياب</span>
        </div>
    </div>
    <div class="text-end">
        <div class="user-id-top mb-1"><i class="fas fa-user-circle me-2"></i>ID: <span class="fw-bold"><?php echo htmlspecialchars($user_id); ?></span></div>
        <div class="small fw-bold" style="color:var(--brand-accent)">Semaine du <?php echo $monday->format('d/m'); ?> au <?php echo $saturday->format('d/m/Y'); ?></div>
    </div>
</header>

<div class="main-grid">
    <div class="glass-card">
        <!-- ALERTES DE STATUT -->
        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] === 'success'): ?>
                <div class="alert alert-success m-4 rounded-4 border-0 shadow-sm d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div><strong>Succès !</strong> Votre absence a été déclarée et le RH a été notifié.</div>
                </div>
            <?php elseif($_GET['status'] === 'exists'): ?>
                <div class="alert alert-danger m-4 rounded-4 border-0 shadow-sm d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div><strong>Déjà déclaré !</strong> Vous avez sélectionné un jour déjà enregistré dans le système.</div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="calendar-grid">
            <?php 
            $jours_fr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
            $matin = ["06:00-07:30", "07:30-09:00", "09:00-10:30", "10:30-11:30"];
            $aprem = ["12:00-13:30", "13:30-15:00", "15:00-16:00"];

            for($i=0; $i<6; $i++): 
                $loop_date = clone $monday; $loop_date->modify("+$i days");
                $date_str = $loop_date->format('Y-m-d');
                $is_today = ($date_str == $current_date);
                $is_past = ($date_str < $current_date);
                // On vérifie si la date est dans le tableau des absences déjà déclarées
                $is_locked = in_array($date_str, $deja_declares); 
            ?>
            <div class="day-col <?php echo $is_today ? 'today' : ''; ?> <?php echo $is_locked ? 'locked' : ''; ?>" data-date="<?php echo $date_str; ?>">
                <div class="day-title text-center">
                    <div class="day-name"><?php echo $jours_fr[$i]; ?></div>
                    <div class="day-date"><?php echo $loop_date->format('d'); ?></div>
                </div>

                <div class="toggle-all mb-3">
                    <div class="form-check form-switch small fw-bold d-flex justify-content-between align-items-center">
                        <label>Journée <span class="arabic">يوم</span></label>
                        <input class="form-check-input" type="checkbox" <?php echo ($is_past || $is_locked) ? 'disabled' : ''; ?> onclick="toggleFullDay('<?php echo $date_str; ?>', this)">
                    </div>
                </div>

                <?php foreach($matin as $s): 
                    $class = ($is_past || $is_locked) ? 'time-slot past-slot' : 'time-slot';
                    $click = ($is_past || $is_locked) ? '' : "onclick=\"selectSlot('$date_str', '$s', this)\"";
                ?>
                    <div class="<?php echo $class; ?>" data-time="<?php echo $s; ?>" <?php echo $click; ?>><?php echo $s; ?></div>
                <?php endforeach; ?>

                <div class="pause-line text-center my-3">— PAUSE —</div>

                <?php 
                $slots_aprem = ($i == 5) ? ["12:00-13:00"] : $aprem; 
                foreach($slots_aprem as $s): 
                    $class = ($is_past || $is_locked) ? 'time-slot past-slot' : 'time-slot';
                    $click = ($is_past || $is_locked) ? '' : "onclick=\"selectSlot('$date_str', '$s', this)\"";
                ?>
                    <div class="<?php echo $class; ?>" data-time="<?php echo $s; ?>" <?php echo $click; ?>><?php echo $s; ?></div>
                <?php endforeach; ?>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="glass-card sidebar-panel">
        <h6 class="fw-bold mb-4 border-bottom pb-2 text-uppercase" style="letter-spacing: 1px;">
            <i class="fas fa-list-ul me-2 text-primary"></i>Résumé / <span class="arabic">الملخص</span>
        </h6>
        
        <div id="summary-view" class="p-3 rounded-4 mb-4 shadow-inner">
            <span class="text-muted small">Aucune séance sélectionnée.</span>
        </div>

        <form action="index.php?action=submit_absence" method="POST" id="absence-form">
            <input type="hidden" name="selected_data" id="final-input">
            
            <div class="mb-3">
                <label class="fw-bold small text-secondary mb-2">Motif / <span class="arabic">سبب الغياب</span></label>
                <select name="motif" class="form-select border-0 bg-light p-3 shadow-sm" style="border-radius: 12px;" required>
                    <option value="Maladie">Maladie / مرض</option>
                    <option value="Administratif">Administratif / إداري</option>
                    <option value="Urgence">Urgence / طارئ</option>
                    <option value="Autre">Autre / آخر</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="fw-bold small text-secondary mb-2">Justification / <span class="arabic">توضيحات</span></label>
                <textarea name="remarque" class="form-control border-0 bg-light p-3 shadow-sm" style="border-radius: 12px;" rows="3" placeholder="Ajoutez un message ici..."></textarea>
            </div>

            <button type="submit" class="btn-submit-flash" onclick="return validateSubmission()">
                Envoyer la demande <br>
                <span style="font-size: 0.75rem; opacity: 0.9; font-weight: 400;">إرسال طلب التصريح</span>
            </button>
        </form>
    </div>
</div>

<script>
let selections = [];

function selectSlot(date, time, el) {
    const id = date + "|" + time;
    const index = selections.indexOf(id);
    if (index > -1) { 
        selections.splice(index, 1); 
        el.classList.remove('selected'); 
    } else { 
        selections.push(id); 
        el.classList.add('selected'); 
    }
    updateUI();
}

function toggleFullDay(date, check) {
    const slots = document.querySelectorAll(`.day-col[data-date="${date}"] .time-slot:not(.past-slot)`);
    slots.forEach(s => {
        const id = date + "|" + s.dataset.time;
        if (check.checked) { 
            if (!selections.includes(id)) { selections.push(id); s.classList.add('selected'); } 
        } else { 
            selections = selections.filter(item => item !== id); s.classList.remove('selected'); 
        }
    });
    updateUI();
}

function updateUI() {
    const view = document.getElementById('summary-view');
    const input = document.getElementById('final-input');
    if (selections.length === 0) { 
        view.innerHTML = '<span class="text-muted small">Aucune séance sélectionnée.</span>'; 
    } else {
        // Tri des sélections par date pour un résumé propre
        selections.sort();
        view.innerHTML = selections.map(s => {
            const p = s.split('|');
            const dateObj = new Date(p[0]);
            const formattedDate = dateObj.toLocaleDateString('fr-FR', {day: 'numeric', month: 'short'});
            return `<div class="mb-2 p-2 bg-white rounded-3 shadow-sm border-start border-primary border-4" style="font-size:0.75rem;">
                        <span class="text-dark fw-bold">${formattedDate}</span> : <span class="text-primary">${p[1]}</span>
                    </div>`;
        }).join('');
    }
    input.value = JSON.stringify(selections);
}

function validateSubmission() {
    if (selections.length === 0) { 
        alert("Veuillez sélectionner au moins une séance avant d'envoyer."); 
        return false; 
    }
    return true;
}
</script>
</body>
</html>