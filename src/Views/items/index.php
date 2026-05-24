<div class="page-head">
    <h1>Item Catalog</h1>
</div>

<!-- Status bar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
    <div style="font-size:.82rem;color:var(--text-2);font-weight:600;">
        <?= count($items) ?> items &middot;
        <span id="missing-count" style="color:<?= $missingCount > 0 ? 'var(--warn)' : 'var(--good)' ?>">
            <?= $missingCount ?> without image
        </span>
    </div>
    <?php if ($missingCount > 0): ?>
    <button id="fetch-all-btn" onclick="fetchAllMissing()" class="btn-accent" style="padding:8px 14px;font-size:.8rem;">
        <i class="bi bi-images"></i> Fetch all missing
    </button>
    <?php endif ?>
</div>

<div id="progress-bar-wrap" style="display:none;margin-bottom:14px;">
    <div style="font-size:.78rem;color:var(--text-2);font-weight:600;margin-bottom:6px;" id="progress-label">Fetching…</div>
    <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
        <div id="progress-fill" style="height:100%;background:var(--accent);border-radius:3px;width:0%;transition:width .3s;"></div>
    </div>
</div>

<!-- Grid -->
<div class="catalog-grid" id="catalog-grid">
    <?php foreach ($items as $item): ?>
    <div class="catalog-card" id="card-<?= $item['id'] ?>" <?= empty($item['image_url']) ? 'data-no-image="1"' : '' ?> data-id="<?= $item['id'] ?>">
        <div class="catalog-img" id="img-<?= $item['id'] ?>">
            <?php if (!empty($item['image_url'])): ?>
            <img src="<?= e($item['image_url']) ?>" alt="" onerror="this.style.display='none';this.nextSibling.style.display='flex'">
            <span style="display:none;align-items:center;justify-content:center;width:100%;height:100%;font-size:2rem;"><?= itemEmoji($item['name'], $item['name_en'] ?? '') ?></span>
            <?php else: ?>
            <span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:2rem;"><?= itemEmoji($item['name'], $item['name_en'] ?? '') ?></span>
            <?php endif ?>
        </div>
        <div class="catalog-name"><?= e($item['name']) ?></div>
        <?php if (!empty($item['name_en'])): ?>
        <div class="catalog-sub"><?= e($item['name_en']) ?></div>
        <?php endif ?>
        <button class="catalog-fetch-btn" id="btn-<?= $item['id'] ?>"
                onclick="fetchOne(<?= $item['id'] ?>, this)"
                title="<?= empty($item['image_url']) ? 'Fetch image' : 'Re-fetch image' ?>">
            <?php if (empty($item['image_url'])): ?>
            <i class="bi bi-image"></i>
            <?php else: ?>
            <i class="bi bi-arrow-clockwise" style="opacity:.45;font-size:.75rem;"></i>
            <?php endif ?>
        </button>
    </div>
    <?php endforeach ?>
</div>

<script>
const BASE = '<?= APP_URL ?>';

async function fetchOne(id, btn) {
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-hourglass-split" style="animation:spin .8s linear infinite"></i>';
    btn.disabled = true;

    try {
        const r    = await fetch(`${BASE}/items/${id}/fetch-image`, { method: 'POST' });
        const data = await r.json();
        if (data.success) {
            const imgWrap = document.getElementById(`img-${id}`);
            imgWrap.innerHTML = `<img src="${data.image_url}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
            const card = document.getElementById(`card-${id}`);
            card.removeAttribute('data-no-image');
            btn.innerHTML = '<i class="bi bi-arrow-clockwise" style="opacity:.45;font-size:.75rem;"></i>';
            updateMissingCount();
        } else {
            btn.innerHTML = '<i class="bi bi-x" style="color:var(--danger)"></i>';
            setTimeout(() => { btn.innerHTML = orig; btn.disabled = false; }, 2000);
            return;
        }
    } catch(e) {
        btn.innerHTML = orig;
    }
    btn.disabled = false;
}

async function fetchAllMissing() {
    const cards = [...document.querySelectorAll('[data-no-image]')];
    if (!cards.length) return;

    document.getElementById('fetch-all-btn').disabled = true;
    document.getElementById('progress-bar-wrap').style.display = 'block';

    let done = 0;
    const total = cards.length;

    for (const card of cards) {
        const id  = card.dataset.id;
        const btn = document.getElementById(`btn-${id}`);
        document.getElementById('progress-label').textContent = `Fetching ${done + 1} of ${total}…`;
        document.getElementById('progress-fill').style.width = `${Math.round(done / total * 100)}%`;

        await fetchOne(id, btn);
        done++;
        await new Promise(r => setTimeout(r, 350)); // be polite to OFF servers
    }

    document.getElementById('progress-fill').style.width = '100%';
    document.getElementById('progress-label').textContent = `Done — ${done} images fetched.`;
    document.getElementById('fetch-all-btn').disabled = false;
}

function updateMissingCount() {
    const n    = document.querySelectorAll('[data-no-image]').length;
    const span = document.getElementById('missing-count');
    span.textContent = `${n} without image`;
    span.style.color = n > 0 ? 'var(--warn)' : 'var(--good)';
    if (!n) document.getElementById('fetch-all-btn')?.remove();
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.catalog-card {
    background: var(--card);
    border: 1.5px solid var(--border-s);
    border-radius: var(--r);
    padding: 12px 8px 8px;
    display: flex; flex-direction: column; align-items: center;
    position: relative;
}
.catalog-img {
    width: 64px; height: 64px;
    border-radius: 12px;
    background: var(--accent-l);
    overflow: hidden;
    margin-bottom: 8px;
    flex-shrink: 0;
}
.catalog-img img { width: 100%; height: 100%; object-fit: cover; }
.catalog-name {
    font-size: .78rem; font-weight: 800; color: var(--text);
    text-align: center; width: 100%;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.catalog-sub {
    font-size: .65rem; color: var(--text-3); text-align: center;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    width: 100%; margin-top: 1px;
}
.catalog-fetch-btn {
    position: absolute; top: 6px; right: 6px;
    width: 26px; height: 26px; border-radius: 8px;
    background: var(--bg); border: 1.5px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .85rem; color: var(--accent);
    padding: 0;
}
.catalog-fetch-btn:disabled { cursor: default; }
</style>
