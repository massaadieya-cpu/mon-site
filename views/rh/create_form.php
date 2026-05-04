<!DOCTYPE html>
<html lang="fr" id="mainHtml">
<head>
    <meta charset="UTF-8">
    <title>Créateur de Formulaire - Altutex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --v-purple: #673ab7; 
            --v-gradient: linear-gradient(135deg, #673ab7 0%, #9c27b0 100%);
            --yes-green: #2e7d32;
            --no-red: #d32f2f;
            --soft-bg: #f8f9fc;
        }
        
        body { background-color: var(--soft-bg); font-family: 'Poppins', sans-serif; padding-bottom: 80px; margin: 0; }

        /* Header Style */
        .altutex-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1001;
        }

        /* Logo Altutex 3D */
        .logo-3d {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--v-purple);
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            text-shadow: 
                0 1px 0 #ccc, 0 2px 0 #c9c9c9, 0 3px 0 #bbb, 
                0 4px 1px rgba(0,0,0,.1), 0 0 5px rgba(0,0,0,.1);
            transition: 0.3s;
        }
        .logo-3d:hover { color: #9c27b0; }

        .form-container { max-width: 850px; margin: 40px auto; position: relative; }
        
        .card-form { background: white; border-radius: 15px; padding: 30px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(103, 58, 183, 0.1); border: none; }
        .card-header-design { border-top: 12px solid var(--v-purple); }
        .question-card { border-left: 6px solid #e2e8f0; }
        .question-card:focus-within { border-left: 6px solid var(--v-purple); }

        /* Bouton d'ajout au bas */
        .add-question-area {
            border: 2px dashed #d1d5db;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.5);
            margin-bottom: 30px;
        }
        .add-question-area:hover {
            background: white;
            border-color: var(--v-purple);
            transform: translateY(-2px);
        }
        .add-icon-circle {
            background: var(--v-gradient);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(103, 58, 183, 0.3);
        }

        .input-google { border: none; border-bottom: 2px solid #e2e8f0; width: 100%; padding: 12px 0; outline: none; font-size: 18px; font-weight: 500; color: #333; background: transparent; }
        .input-google:focus { border-bottom: 2px solid var(--v-purple); }

        .btn-dash {
            border: 2px solid var(--v-purple);
            color: var(--v-purple);
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 20px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-dash:hover { background: var(--v-purple); color: white; }

        .yesno-container { background: #f3e8ff; border-radius: 12px; padding: 25px; text-align: center; border: 1px dashed var(--v-purple); }
        .yn-btn { padding: 10px 35px; border-radius: 30px; border: 2px solid #ccc; background: white; font-weight: bold; cursor: pointer; transition: 0.3s; margin: 0 10px; }
        
        .btn-yes-active { background-color: var(--yes-green) !important; color: white !important; border-color: var(--yes-green) !important; }
        .btn-no-active { background-color: var(--no-red) !important; color: white !important; border-color: var(--no-red) !important; }

        .rtl { direction: rtl; text-align: right; }
    </style>
</head>
<body>

<header class="altutex-header">
    <a href="#" class="logo-3d">Altutex</a>
    
    <div class="d-flex align-items-center gap-3">
        <div class="btn-group shadow-sm">
            <button class="btn btn-sm btn-white border" onclick="applyTranslation('fr')">FR</button>
            <button class="btn btn-sm btn-white border" onclick="applyTranslation('ar')">AR</button>
        </div>
        <a href="index.php?action=dashboard" class="btn-dash" id="btnDash">Dashboard</a>
    </div>
</header>

<div class="form-container">
    <form action="index.php?action=formulaire" method="POST">
        <div class="card-form card-header-design">
            <input type="text" name="form_title" class="input-google fs-2 fw-bold" placeholder="Titre du formulaire" id="fTitle">
            <input type="text" name="form_desc" class="input-google text-muted mt-2" placeholder="Description..." id="fDesc">
        </div>

        <div id="questions-zone"></div>

        <div class="add-question-area" onclick="addQuestion()">
            <div class="add-icon-circle">
                <i class="fas fa-plus fa-lg"></i>
            </div>
            <div class="fw-bold text-muted" id="addQuestionText">Ajouter une nouvelle section ou question</div>
        </div>

        <div class="d-flex justify-content-between mt-5 align-items-center">
            <a href="index.php?action=dashboard" class="text-muted fw-bold text-decoration-none" id="btnCancel">Annuler</a>
            <button type="submit" class="btn px-5 py-3 fw-bold shadow" style="background: var(--v-gradient); color: white; border-radius: 50px; border: none;" id="btnSubmit">
                DIFFUSER LE FORMULAIRE
            </button>
        </div>
    </form>
</div>

<script>
let questionCount = 0;
let lang = 'fr';

const dictionary = {
    fr: { title: "Titre du formulaire", desc: "Description...", q: "Votre question...", opt: "Option", add: "+ Ajouter une option", req: "Obligatoire", yes: "Oui", no: "Non", justif: "Justifiez votre réponse ici...", types: ["Choix unique", "Choix multiples", "Oui/Non + Justification", "Texte libre"], cancel: "Annuler", submit: "DIFFUSER LE FORMULAIRE", freeText: "L'employé écrira sa réponse ici...", addArea: "Ajouter une nouvelle section ou question", dash: "Dashboard" },
    ar: { title: "عنوان النموذج", desc: "وصف النموذج...", q: "اكتب سؤالك هنا...", opt: "خيار", add: "+ إضافة خيار جديد", req: "إجباري", yes: "نعم", no: "لا", justif: "برر إجابتك هنا...", types: ["اختيار واحد", "خيارات متعددة", "نعم/لا + تبرير", "نص حر"], cancel: "إلغاء", submit: "نشر النموذج", freeText: "سيكتب الموظف إجابته هنا...", addArea: "إضافة قسم أو سؤال جديد", dash: "لوحة القيادة" }
};

function addQuestion() {
    questionCount++;
    const t = dictionary[lang];
    const zone = document.getElementById('questions-zone');
    const div = document.createElement('div');
    div.className = 'card-form question-card';
    div.id = `q-container-${questionCount}`;
    
    div.innerHTML = `
        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-7"><input type="text" name="questions[${questionCount}][titre]" class="input-google" placeholder="${t.q}" required></div>
            <div class="col-md-5">
                <select name="questions[${questionCount}][type]" class="form-select border-0 shadow-sm fw-bold" style="color: var(--v-purple);" onchange="handleTypeChange(${questionCount}, this.value)">
                    <option value="radio">${t.types[0]}</option>
                    <option value="checkbox">${t.types[1]}</option>
                    <option value="yesno">${t.types[2]}</option>
                    <option value="text">${t.types[3]}</option>
                </select>
            </div>
        </div>
        <div id="dynamic-area-${questionCount}">
            <div class="d-flex align-items-center mb-3">
                <i class="far fa-circle me-3" style="color: var(--v-purple)"></i>
                <input type="text" name="questions[${questionCount}][options][]" class="input-google w-75" placeholder="${t.opt} 1">
            </div>
        </div>
        <div class="mt-2" id="add-opt-btn-${questionCount}">
            <span class="fw-bold" style="color: var(--v-purple); cursor:pointer" onclick="addOptionRow(${questionCount})">${t.add}</span>
        </div>
        <hr class="my-4">
        <div class="d-flex justify-content-between align-items-center">
            <i class="far fa-trash-alt text-danger fs-5" style="cursor:pointer" onclick="document.getElementById('q-container-${questionCount}').remove()"></i>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-muted small">${t.req}</span>
                <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="questions[${questionCount}][required]"></div>
            </div>
        </div>`;
    zone.appendChild(div);
    div.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function handleTypeChange(id, type) {
    const area = document.getElementById(`dynamic-area-${id}`);
    const btn = document.getElementById(`add-opt-btn-${id}`);
    const t = dictionary[lang];
    
    if (type === 'yesno') {
        area.innerHTML = `
            <div class="yesno-container">
                <div class="d-flex justify-content-center mb-3">
                    <button type="button" class="yn-btn" onclick="toggleYN(this, 'yes')">${t.yes}</button>
                    <button type="button" class="yn-btn" onclick="toggleYN(this, 'no')">${t.no}</button>
                </div>
                <textarea name="questions[${id}][placeholder]" class="form-control border-0 shadow-sm" rows="2" placeholder="${t.justif}"></textarea>
            </div>`;
        btn.style.display = 'none';
    } else if (type === 'text') {
        area.innerHTML = `
            <div class="p-3 bg-light rounded border-start border-4 border-primary">
                <textarea name="questions[${id}][placeholder]" class="form-control bg-white" rows="3" placeholder="${t.freeText}"></textarea>
            </div>`;
        btn.style.display = 'none';
    } else {
        const iconClass = (type === 'checkbox') ? 'fa-square' : 'fa-circle';
        area.innerHTML = `
            <div class="d-flex align-items-center mb-3">
                <i class="far ${iconClass} me-3" style="color: var(--v-purple)"></i>
                <input type="text" name="questions[${id}][options][]" class="input-google w-75" placeholder="${t.opt} 1">
            </div>`;
        btn.style.display = 'block';
    }
}

function toggleYN(btn, choice) {
    const parent = btn.parentElement;
    parent.querySelectorAll('.yn-btn').forEach(b => b.classList.remove('btn-yes-active', 'btn-no-active'));
    if(choice === 'yes') btn.classList.add('btn-yes-active');
    else btn.classList.add('btn-no-active');
}

function applyTranslation(targetLang) {
    lang = targetLang;
    const body = document.getElementById('mainHtml');
    const t = dictionary[lang];
    
    lang === 'ar' ? body.classList.add('rtl') : body.classList.remove('rtl');
    document.getElementById('fTitle').placeholder = t.title;
    document.getElementById('fDesc').placeholder = t.desc;
    document.getElementById('btnCancel').innerText = t.cancel;
    document.getElementById('btnSubmit').innerText = t.submit;
    document.getElementById('addQuestionText').innerText = t.addArea;
    document.getElementById('btnDash').innerText = t.dash;

    document.getElementById('questions-zone').innerHTML = "";
    addQuestion();
}

function addOptionRow(id) {
    const area = document.getElementById(`dynamic-area-${id}`);
    const select = document.querySelector(`#q-container-${id} select`);
    const iconClass = (select.value === 'checkbox') ? 'fa-square' : 'fa-circle';
    
    const div = document.createElement('div');
    div.className = 'd-flex align-items-center mb-3';
    div.innerHTML = `
        <i class="far ${iconClass} me-3" style="color: var(--v-purple)"></i>
        <input type="text" name="questions[${id}][options][]" class="input-google w-75" placeholder="${dictionary[lang].opt}">
        <i class="fas fa-times ms-3 text-muted" onclick="this.parentElement.remove()" style="cursor:pointer"></i>`;
    area.appendChild(div);
}

addQuestion();
</script>
</body>
</html>