<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Répondre à l'enquête - ALTUTEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --v-purple: #673ab7; 
            --v-gradient: linear-gradient(135deg, #673ab7 0%, #9c27b0 100%); 
            --yes-green: #2e7d32; 
            --no-red: #d32f2f; 
        }
        body { background-color: #f8f9fc; font-family: 'Poppins', sans-serif; padding-bottom: 50px; }
        
        /* Dimensions demandées : 850px */
        .form-container { max-width: 850px; margin: 40px auto; }
        
        .card-form { background: white; border-radius: 15px; padding: 35px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(103, 58, 183, 0.1); border: 2px solid transparent; transition: 0.3s; }
        .card-header-design { border-top: 12px solid var(--v-purple); }
        
        .title-display { font-size: 2.2rem; font-weight: 800; color: #333; margin-bottom: 10px; }
        .question-text { font-size: 1.25rem; font-weight: 600; color: #444; margin-bottom: 20px; }
        
        .yesno-container { background: #f3e8ff; border-radius: 12px; padding: 30px; text-align: center; border: 1px dashed var(--v-purple); }
        .yn-btn-ui { padding: 12px 40px; border-radius: 30px; border: 2px solid #ccc; background: white; font-weight: bold; cursor: pointer; transition: 0.3s; margin: 0 10px; display: inline-block; }
        
        .btn-check:checked + .btn-yes { background-color: var(--yes-green) !important; color: white !important; border-color: var(--yes-green) !important; box-shadow: 0 4px 10px rgba(46, 125, 50, 0.2); }
        .btn-check:checked + .btn-no { background-color: var(--no-red) !important; color: white !important; border-color: var(--no-red) !important; box-shadow: 0 4px 10px rgba(211, 47, 47, 0.2); }
        
        .input-google-style { border: none; border-bottom: 2px solid #e2e8f0; width: 100%; padding: 10px 0; outline: none; background: transparent; transition: 0.3s; }
        .input-google-style:focus { border-bottom: 2px solid var(--v-purple); }
        
        .required-star { color: var(--no-red); margin-left: 5px; }

        /* Style d'erreur avec animation de secousse */
        .error-card { 
            border: 2px solid var(--no-red) !important; 
            background-color: #fff8f8; 
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        /* Alerte personnalisée flottante */
        #customAlert {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            background: var(--no-red);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>

<div id="customAlert"><i class="fas fa-exclamation-triangle me-2"></i> Merci de remplir toutes les questions obligatoires.</div>

<div class="form-container">
    <form action="index.php?action=submit_reponse_anonyme" method="POST" id="surveyForm" novalidate>
        <input type="hidden" name="enquete_id" value="<?= $enquete['id'] ?>">

        <div class="card-form card-header-design">
            <div class="title-display"><?= htmlspecialchars($enquete['titre']) ?></div>
            <div class="text-muted fs-5"><?= nl2br(htmlspecialchars($enquete['description'])) ?></div>
            <p class="text-muted small mt-3"><span class="text-danger">*</span> Indique une question obligatoire</p>
        </div>

        <?php 
        $questions = json_decode($enquete['structure_json'], true);
        foreach ($questions as $id => $q): 
            $is_required = (isset($q['required']) && ($q['required'] === 'true' || $q['required'] === 'on' || $q['required'] === true));
        ?>
            <div class="card-form question-card" data-required="<?= $is_required ? 'true' : 'false' ?>" data-type="<?= $q['type'] ?>">
                <div class="question-text">
                    <?= htmlspecialchars($q['titre'] ?? $q['label']) ?>
                    <?php if ($is_required): ?>
                        <span class="required-star">*</span>
                    <?php endif; ?>
                </div>
                
                <div class="dynamic-area">
                    <?php if ($q['type'] === 'yesno'): ?>
                        <div class="yesno-container">
                            <div class="d-flex justify-content-center mb-4">
                                <input type="radio" class="btn-check" name="reponses[<?= $id ?>][val]" id="y<?= $id ?>" value="Oui">
                                <label class="yn-btn-ui btn-yes" for="y<?= $id ?>">Oui</label>

                                <input type="radio" class="btn-check" name="reponses[<?= $id ?>][val]" id="n<?= $id ?>" value="Non">
                                <label class="yn-btn-ui btn-no" for="n<?= $id ?>">Non</label>
                            </div>
                            <textarea name="reponses[<?= $id ?>][justif]" class="form-control border-0 shadow-sm" rows="3" style="border-radius:10px;" placeholder="Justifiez votre réponse (optionnel)..."></textarea>
                        </div>

                    <?php elseif ($q['type'] === 'radio' || $q['type'] === 'checkbox'): ?>
                        <?php $is_checkbox = ($q['type'] === 'checkbox'); ?>
                        <div class="option-group">
                            <?php foreach ($q['options'] as $idx => $opt): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <input type="<?= $is_checkbox ? 'checkbox' : 'radio' ?>" 
                                           name="reponses[<?= $id ?>]<?= $is_checkbox ? '[]' : '' ?>" 
                                           value="<?= htmlspecialchars($opt) ?>" 
                                           id="opt_<?= $id ?>_<?= $idx ?>"
                                           class="form-check-input me-3"
                                           style="width: 22px; height: 22px; border-color: var(--v-purple);">
                                    <label for="opt_<?= $id ?>_<?= $idx ?>" class="fs-5" style="cursor:pointer;"><?= htmlspecialchars($opt) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif ($q['type'] === 'text'): ?>
                        <textarea name="reponses[<?= $id ?>]" class="form-control input-google-style" rows="3" placeholder="Tapez votre réponse ici..."></textarea>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="d-flex justify-content-between align-items-center mt-5">
            <a href="index.php?action=enquetes" class="text-muted fw-bold text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Annuler
            </a>
            <button type="submit" class="btn px-5 py-3 fw-bold shadow text-white" style="background: var(--v-gradient); border-radius: 50px; border: none;">
                ENVOYER MES RÉPONSES <i class="fas fa-paper-plane ms-2"></i>
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('surveyForm').addEventListener('submit', function(e) {
        let isValid = true;
        let firstError = null;
        const cards = document.querySelectorAll('.question-card');

        cards.forEach(card => {
            const isRequired = card.getAttribute('data-required') === 'true';
            if (!isRequired) return;

            const type = card.getAttribute('data-type');
            let answered = false;

            if (type === 'yesno' || type === 'radio') {
                answered = card.querySelector('input[type="radio"]:checked') !== null;
            } else if (type === 'checkbox') {
                answered = card.querySelector('input[type="checkbox"]:checked') !== null;
            } else if (type === 'text') {
                answered = card.querySelector('textarea').value.trim() !== "";
            }

            if (!answered) {
                isValid = false;
                card.classList.add('error-card');
                if (!firstError) firstError = card;
            } else {
                card.classList.remove('error-card');
            }
        });

        if (!isValid) {
            e.preventDefault();
            
            // Alerte visuelle
            const alertBox = document.getElementById('customAlert');
            alertBox.style.display = 'block';
            setTimeout(() => { alertBox.style.display = 'none'; }, 3500);

            // Scroll fluide
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Enlever le rouge dès que l'utilisateur commence à répondre
    document.querySelectorAll('.question-card input, .question-card textarea').forEach(elem => {
        elem.addEventListener('change', function() {
            this.closest('.question-card').classList.remove('error-card');
        });
    });
</script>
</body>
</html>