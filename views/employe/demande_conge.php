<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ALTUTEX - Demande de Congé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --altutex-blue-dark: #00185f;
            --altutex-blue-main: #37558b;
            --altutex-accent: #0d6efd;
            --bg-soft: #f8f9fc;
        }

        body { 
            background-color: var(--bg-soft); 
            font-family: 'Inter', 'Segoe UI', Roboto, sans-serif; 
            color: #333;
        }
        
        .card-custom {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 24, 95, 0.06);
        }

        .form-header {
            background: linear-gradient(135deg, var(--altutex-blue-dark) 0%, var(--altutex-blue-main) 100%);
            color: white;
            padding: 25px 35px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            color: #6c757d;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            margin-bottom: 15px;
        }
        .btn-back:hover { color: var(--altutex-accent); }

        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            background-color: #fdfdfd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            border-color: var(--altutex-accent);
            background-color: #fff;
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-radius: 8px 0 0 8px;
            color: var(--altutex-blue-main);
            border-color: #e0e0e0;
        }

        .btn-submit {
            background: var(--altutex-blue-main);
            border: none;
            padding: 12px 45px;
            border-radius: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            transition: all 0.3s;
            color: white;
        }

        .btn-submit:hover {
            background: var(--altutex-blue-dark);
            box-shadow: 0 8px 20px rgba(0, 24, 95, 0.2);
            transform: translateY(-2px);
            color: white;
        }

        .section-title {
            font-size: 1rem;
            color: var(--altutex-blue-main);
            font-weight: 800;
            position: relative;
            padding-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--altutex-accent);
            border-radius: 3px;
        }

        .doc-ref-badge {
            background: rgba(255, 255, 255, 0.15);
            font-size: 0.7rem;
            padding: 4px 12px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            
            <a href="index.php?action=dashboard" class="btn-back">
                <i class="fa-solid fa-chevron-left me-2"></i> Dashboard
            </a>
            
            <div class="card card-custom">
                <div class="form-header d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0 fw-bold h4">ALTUTEX <small class="fw-light opacity-75 ms-1">HR Connect</small></h2>
                        <p class="mb-0 small opacity-75"><i class="fa-solid fa-calendar-check me-1"></i> Formulaire de Demande de Congé</p>
                    </div>
                    <div class="text-end d-none d-sm-block">
                        <div class="doc-ref-badge mb-1">Réf: FOR-GRH-17</div>
                        <div class="doc-ref-badge small opacity-75">Ver: 0.2</div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5 bg-white">
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 text-center">
                            <i class="fas fa-check-circle me-2"></i> 
                            <strong>Succès !</strong> Votre demande de congé a été transmise au département RH.
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php 
                                switch($_GET['error']) {
                                    case 'duree_max': echo "La durée du congé ne peut pas dépasser 14 jours."; break;
                                    case 'quota_annuel': echo "Quota annuel de 2 demandes atteint."; break;
                                    case 'date_passee': echo "La date de début ne peut pas être dans le passé."; break;
                                    default: echo "Une erreur est survenue.";
                                }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="index.php?action=submit_conge" method="POST">
                        <div class="row mb-5">
                            <div class="col-12">
                                <h5 class="section-title mb-4">IDENTITÉ DE L'EMPLOYÉ</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nom Complet</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                                    <input type="text" name="nom" class="form-control" placeholder="Ex: Ben Soltan Emna" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Numéro CIN</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-regular fa-id-card"></i></span>
                                    <input type="text" name="cin" class="form-control" placeholder="00000000" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Poste Occupé</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-briefcase"></i></span>
                                    <input type="text" name="fonction" class="form-control" placeholder="Ex: Ingénieur Data" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Adresse</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa-solid fa-house-user"></i></span>
                                    <input type="text" name="adresse" class="form-control" placeholder="Adresse actuelle">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="section-title mb-4">PÉRIODE & MOTIF</h5>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de début</label>
                                <input type="date" name="date_debut" id="date_debut" class="form-control" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de fin</label>
                                <input type="date" name="date_fin" id="date_fin" class="form-control" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cause du congé <span class="text-danger">*</span></label>
                                <textarea name="motif" class="form-control" rows="3" required placeholder="Expliquez la raison de votre absence..."></textarea>
                            </div>
                        </div>
                        
                        <div class="text-center pt-4">
                            <button type="submit" class="btn btn-submit">
                                ENVOYER LA DEMANDE 
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex justify-content-center align-items-center mt-4 text-muted small">
                <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                Validation sous 24h à 48h par le département RH.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Logique pour synchroniser les dates
    document.addEventListener('DOMContentLoaded', function() {
        const inputDebut = document.getElementById('date_debut');
        const inputFin = document.getElementById('date_fin');

        inputDebut.addEventListener('change', function() {
            // La date de fin ne peut pas être avant la date de début choisie
            inputFin.min = this.value;
            if (inputFin.value && inputFin.value < this.value) {
                inputFin.value = this.value;
            }
        });
    });
</script>

</body>
</html>