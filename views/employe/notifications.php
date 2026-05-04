<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root { 
        --primary-alt: #02075d; 
        --accent-blue: #3b82f6;
        --bg-body: #f9fafb; 
    }

    body { background-color: var(--bg-body); font-family: 'Plus Jakarta Sans', sans-serif; color: #1f2937; }

    /* Header & Search */
    .altutex-header { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(0,0,0,0.05); padding: 12px 0; position: sticky; top: 0; z-index: 1000; }
    .logo-box { background: var(--primary-alt); color: #fff; width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; }
    
    .search-wrapper { position: relative; }
    .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
    .search-input { background: #f3f4f6; border: 1px solid transparent; border-radius: 12px; padding: 8px 15px 8px 40px; width: 280px; font-size: 0.85rem; transition: 0.3s; }
    .search-input:focus { background: #fff; border-color: var(--accent-blue); box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); outline: none; }

    /* Back Button */
    .btn-back { 
        display: inline-flex; 
        align-items: center; 
        color: #64748b; 
        text-decoration: none; 
        font-size: 0.85rem; 
        font-weight: 600; 
        margin-bottom: 15px; 
        transition: 0.2s;
    }
    .btn-back:hover { color: var(--primary-alt); transform: translateX(-3px); }

    /* Cards */
    .notif-card { background: #fff; border-radius: 20px; border: 1px solid rgba(0,0,0,0.04); transition: all 0.3s ease; margin-bottom: 12px; }
    .notif-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,0.04); }
    .notif-card.unread { border-left: 4px solid var(--accent-blue); }
    .notif-card.read { border-left: 4px solid #e5e7eb; opacity: 0.8; }
    
    .icon-wrapper { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #eff6ff; color: var(--accent-blue); }
    .type-badge { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; padding: 4px 10px; background: #f3f4f6; border-radius: 8px; color: #6b7280; }

    /* Buttons */
    .filter-btn { border: none; background: transparent; padding: 6px 15px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; color: #6b7280; transition: 0.2s; }
    .filter-btn.active { background: #fff; color: var(--primary-alt); box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .btn-open { background: #f8fafc; color: var(--primary-alt); border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 18px; text-decoration: none; font-weight: 600; font-size: 0.8rem; }
    .btn-open:hover { background: var(--primary-alt); color: #fff; }

    .hidden { display: none !important; }
</style>

<div class="main-content">
    <header class="altutex-header mb-4">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="logo-box me-3">A</div>
                <h5 class="mb-0 fw-bold">ALTUTEX</h5>
            </div>
            <div class="d-flex align-items-center">
                <div class="search-wrapper me-3 d-none d-md-block">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher une alerte...">
                </div>
                <div class="avatar-circle rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width:38px; height:38px; font-size: 12px; font-weight: bold;">EMP</div>
            </div>
        </div>
    </header>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back">
            <i class="bi bi-arrow-left me-2"></i> Retour au Dashboard
        </a>

        <div class="row mb-4 align-items-end">
            <div class="col-md-7">
                <h2 class="fw-bold m-0 text-dark">Centre de Notifications 
                    <span id="globalBadge" class="badge rounded-pill bg-danger ms-2" style="font-size: 0.35em; vertical-align: middle;">
                        <?= count(array_filter($notifications, fn($n) => !$n['is_read'])) ?>
                    </span>
                </h2>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="d-inline-flex bg-light p-1 rounded-3 shadow-sm border">
                    <button class="filter-btn active" onclick="filterNotifs('all', this)">Tout</button>
                    <button class="filter-btn" onclick="filterNotifs('unread', this)">Non lus</button>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-xl-10" id="notifContainer">
                <?php if (empty($notifications)): ?>
                    <div id="emptyState" class="text-center py-5 bg-white rounded-4 shadow-sm border">
                        <i class="bi bi-bell-slash display-2 text-light mb-3 d-block"></i>
                        <h5 class="fw-bold">Aucune alerte</h5>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="notif-card p-3 shadow-sm <?= $n['is_read'] ? 'read' : 'unread' ?>" 
                             data-status="<?= $n['is_read'] ? 'read' : 'unread' ?>"
                             data-message="<?= strtolower(htmlspecialchars($n['message'])) ?>">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper me-3">
                                    <i class="bi bi-mortarboard-fill fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="type-badge"><?= htmlspecialchars($n['type'] ?? 'Formation') ?></span>
                                        <small class="text-muted"><?= date('H:i', strtotime($n['created_at'])) ?></small>
                                    </div>
                                    <h6 class="fw-bold mb-1 title-text text-dark"><?= htmlspecialchars($n['message']) ?></h6>
                                    <p class="small text-muted mb-0">Action requise : Cliquez pour consulter les détails.</p>
                                </div>
                                <div class="ms-4">
                                    <a href="index.php?action=notif_open&id=<?= $n['id'] ?>&url=<?= urlencode($n['url']) ?>" class="btn-open">
                                        Ouvrir <i class="bi bi-chevron-right ms-1 small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div id="noResults" class="text-center py-5 hidden">
                    <p class="text-muted">Aucune notification ne correspond à votre recherche.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const searchInput = document.getElementById('searchInput');
    const notifCards = document.querySelectorAll('.notif-card');
    const noResults = document.getElementById('noResults');
    let currentFilter = 'all';

    function updateDisplay() {
        const query = searchInput.value.toLowerCase();
        let visibleCount = 0;

        notifCards.forEach(card => {
            const matchesSearch = card.getAttribute('data-message').includes(query);
            const matchesFilter = (currentFilter === 'all' || card.getAttribute('data-status') === 'unread');

            if (matchesSearch && matchesFilter) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        noResults.classList.toggle('hidden', visibleCount > 0);
    }

    searchInput.addEventListener('input', updateDisplay);

    function filterNotifs(type, btn) {
        currentFilter = type;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        updateDisplay();
    }
</script>