<?php $user = currentUser(); ?>

<!-- Greeting -->
<div class="greet-card">
    <div class="greet-emoji"><?= $greetEmoji ?></div>
    <h2><?= $greeting ?>, <?= e(explode(' ', $user['name'])[0]) ?></h2>
    <p>You have <?= $totalItems ?> items across <?= count($rooms) ?> rooms.</p>
</div>

<!-- Stats strip -->
<div style="display:flex;gap:10px;margin-bottom:16px;">
    <div class="stat-chip">
        <div class="val"><?= $totalItems ?></div>
        <div class="lbl">Total items</div>
    </div>
    <div class="stat-chip <?= $soonCount > 0 ? 'accent-chip' : '' ?>">
        <div class="val"><?= $soonCount ?></div>
        <div class="lbl">Expiring soon</div>
    </div>
    <div class="stat-chip <?= $expiredCount > 0 ? '' : '' ?>" style="<?= $expiredCount > 0 ? 'background:var(--danger-l);border-color:#FECACA' : '' ?>">
        <div class="val" style="<?= $expiredCount > 0 ? 'color:var(--danger)' : '' ?>"><?= $expiredCount ?></div>
        <div class="lbl">Expired</div>
    </div>
</div>

<!-- Alerts -->
<?php if ($expired): ?>
<div class="alert-strip danger" style="margin-bottom:10px;">
    <i class="bi bi-exclamation-triangle-fill" style="flex-shrink:0"></i>
    <div>
        <div>Expired items</div>
        <ul>
            <?php foreach ($expired as $e): ?>
            <li><?= e($e['item_name']) ?> — <?= e($e['room_name']) ?></li>
            <?php endforeach ?>
        </ul>
    </div>
</div>
<?php endif ?>
<?php if ($expiringSoon): ?>
<div class="alert-strip warn" style="margin-bottom:10px;">
    <i class="bi bi-clock-fill" style="flex-shrink:0"></i>
    <div>
        <div>Expiring within <?= EXPIRY_WARN_DAYS ?> days</div>
        <ul>
            <?php foreach ($expiringSoon as $e): ?>
            <li><?= e($e['item_name']) ?> — <?= e($e['room_name']) ?> (<?= e($e['expiry_date']) ?>)</li>
            <?php endforeach ?>
        </ul>
    </div>
</div>
<?php endif ?>

<!-- Search -->
<div class="search-wrap" style="margin-bottom:20px;">
    <div class="search-input-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="search-input" placeholder="Search any item…" autocomplete="off">
        <button class="clear-btn" id="search-clear" onclick="clearSearch()" style="display:none">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div class="search-results" id="search-results"></div>
</div>

<!-- Rooms -->
<div class="sec-head">
    <span class="sec-label">Rooms</span>
    <a href="<?= APP_URL ?>/rooms/create" class="btn-outline" style="padding:6px 12px;font-size:.78rem;">
        <i class="bi bi-plus"></i> Add room
    </a>
</div>

<?php if (empty($rooms)): ?>
<div class="empty-state">
    <i class="bi bi-door-open"></i>
    <p>No rooms yet. <a href="<?= APP_URL ?>/rooms/create">Add your first room.</a></p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:10px;">
    <?php foreach ($rooms as $room): ?>
    <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="loc-card">
        <div class="loc-icon"><?= roomEmoji($room['name']) ?></div>
        <div class="loc-info">
            <div class="loc-name"><?= e($room['name']) ?></div>
            <div class="loc-meta">
                <?= $room['container_count'] ?> containers &middot; <?= $room['item_count'] ?> items
            </div>
        </div>
        <i class="bi bi-chevron-right loc-chevron"></i>
    </a>
    <?php endforeach ?>
</div>
<?php endif ?>

<script>
const BASE = '<?= APP_URL ?>';
let debounce;

document.getElementById('search-input').addEventListener('input', function () {
    const q = this.value.trim();
    document.getElementById('search-clear').style.display = q ? 'block' : 'none';
    clearTimeout(debounce);
    if (q.length < 2) { hideResults(); return; }
    debounce = setTimeout(() => fetchResults(q), 280);
});
document.getElementById('search-input').addEventListener('focus', function () {
    if (this.value.trim().length >= 2) fetchResults(this.value.trim());
});
document.addEventListener('click', e => {
    if (!e.target.closest('.search-wrap')) hideResults();
});

function clearSearch() {
    document.getElementById('search-input').value = '';
    document.getElementById('search-clear').style.display = 'none';
    hideResults();
    document.getElementById('search-input').focus();
}
function hideResults() { document.getElementById('search-results').style.display = 'none'; }

function fetchResults(q) {
    fetch(`${BASE}/search?q=${encodeURIComponent(q)}`)
        .then(r => r.json()).then(data => renderResults(data, q)).catch(() => {});
}

function renderResults(items, q) {
    const box = document.getElementById('search-results');
    if (!items.length) {
        box.innerHTML = `<div style="padding:20px;text-align:center;color:var(--text-3);font-size:.85rem;font-weight:600;">No items found for "<strong>${esc(q)}</strong>"</div>`;
        box.style.display = 'block'; return;
    }
    let html = '';
    items.forEach(item => {
        html += `<div class="search-item-group">
            <div class="search-item-title">
                <div>
                    <strong>${hi(item.item_name, q)}</strong>
                    ${item.item_name_en ? `<span style="font-size:.75rem;color:var(--text-3);margin-left:6px;font-weight:600;">${hi(item.item_name_en, q)}</span>` : ''}
                </div>
                <span class="qty-pill">${item.formatted_total}</span>
            </div>`;
        item.locations.forEach(loc => {
            const crumb = loc.container_name
                ? `${esc(loc.room_name)} <i class="bi bi-chevron-right" style="font-size:.65rem"></i> ${esc(loc.container_name)}`
                : esc(loc.room_name);
            const exp = loc.expiry_status === 'expired' ? `<span class="tag expired">Expired</span>`
                      : loc.expiry_status === 'expiring-soon' ? `<span class="tag soon">Exp ${esc(loc.expiry_date)}</span>` : '';
            const low = loc.quantity_grams < 500 ? `<span class="tag" style="background:var(--warn-l);color:var(--accent-d)">Low</span>` : '';
            const target = loc.container_id ? `${BASE}/containers/${loc.container_id}` : `${BASE}/rooms/${loc.room_id}`;
            html += `<a class="search-loc-row" href="${target}">
                <div>
                    <div class="crumb">${crumb}</div>
                    ${loc.notes ? `<div style="font-size:.72rem;color:var(--text-3)">${esc(loc.notes)}</div>` : ''}
                    <div style="display:flex;gap:4px;margin-top:3px">${exp}${low}</div>
                </div>
                <span class="qty">${esc(loc.formatted_qty)}</span>
            </a>`;
        });
        html += '</div>';
    });
    box.innerHTML = html; box.style.display = 'block';
}

function esc(s) { return s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''; }
function hi(t, q) {
    const s = esc(t);
    return s.replace(new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi'), '<mark>$1</mark>');
}
</script>
