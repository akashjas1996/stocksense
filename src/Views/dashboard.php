<h5 class="fw-bold mb-3">Dashboard</h5>

<!-- Search bar -->
<div class="mb-3 position-relative">
    <div class="input-group input-group-lg shadow-sm">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search text-muted"></i>
        </span>
        <input type="text" id="search-input" class="form-control border-start-0 ps-0"
               placeholder="Find any item…" autocomplete="off">
        <button class="btn btn-outline-secondary d-none" id="search-clear" onclick="clearSearch()">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <!-- Results dropdown -->
    <div id="search-results" class="position-absolute w-100 bg-white rounded-3 shadow mt-1 z-3"
         style="display:none; max-height:70vh; overflow-y:auto; top:100%; left:0;"></div>
</div>

<?php if ($expired): ?>
<div class="alert alert-danger py-2">
    <strong><i class="bi bi-exclamation-triangle-fill"></i> Expired items</strong>
    <ul class="mb-0 mt-1 small">
        <?php foreach ($expired as $e): ?>
        <li><?= e($e['item_name']) ?> — <?= e($e['room_name']) ?> (<?= e($e['expiry_date']) ?>)</li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>

<?php if ($expiringSoon): ?>
<div class="alert alert-warning py-2">
    <strong><i class="bi bi-clock-fill"></i> Expiring soon</strong>
    <ul class="mb-0 mt-1 small">
        <?php foreach ($expiringSoon as $e): ?>
        <li><?= e($e['item_name']) ?> — <?= e($e['room_name']) ?> (<?= e($e['expiry_date']) ?>)</li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0 text-muted">Rooms</h6>
    <a href="<?= APP_URL ?>/rooms/create" class="btn btn-sm btn-outline-dark">
        <i class="bi bi-plus"></i> Add Room
    </a>
</div>

<?php if (empty($rooms)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-door-open display-4"></i>
    <p class="mt-2">No rooms yet. <a href="<?= APP_URL ?>/rooms/create">Add your first room.</a></p>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($rooms as $room): ?>
    <div class="col-6">
        <a href="<?= APP_URL ?>/rooms/<?= $room['id'] ?>" class="text-decoration-none">
            <div class="card location-card h-100 text-center p-3">
                <i class="bi bi-door-open-fill display-5 text-secondary"></i>
                <div class="fw-semibold mt-1"><?= e($room['name']) ?></div>
                <div class="text-muted small">
                    <?= $room['container_count'] ?> containers<br>
                    <?= $room['item_count'] ?> items
                </div>
            </div>
        </a>
    </div>
    <?php endforeach ?>
</div>
<?php endif ?>

<script>
const BASE = '<?= APP_URL ?>';
let debounce;

document.getElementById('search-input').addEventListener('input', function () {
    const q = this.value.trim();
    document.getElementById('search-clear').classList.toggle('d-none', q.length === 0);
    clearTimeout(debounce);
    if (q.length < 2) { hideResults(); return; }
    debounce = setTimeout(() => fetchResults(q), 280);
});

// Close on outside click
document.addEventListener('click', e => {
    if (!e.target.closest('#search-input') && !e.target.closest('#search-results')) hideResults();
});
document.getElementById('search-input').addEventListener('focus', function () {
    if (this.value.trim().length >= 2) fetchResults(this.value.trim());
});

function clearSearch() {
    document.getElementById('search-input').value = '';
    document.getElementById('search-clear').classList.add('d-none');
    hideResults();
}

function hideResults() {
    document.getElementById('search-results').style.display = 'none';
}

function fetchResults(q) {
    fetch(`${BASE}/search?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => renderResults(data, q))
        .catch(() => {});
}

function renderResults(items, q) {
    const box = document.getElementById('search-results');
    if (!items.length) {
        box.innerHTML = `<div class="p-3 text-muted small text-center">No items found for "<strong>${esc(q)}</strong>"</div>`;
        box.style.display = 'block';
        return;
    }

    let html = '';
    items.forEach(item => {
        const multi = item.locations.length > 1;
        html += `<div class="px-3 pt-3 pb-1">`;
        html += `<div class="d-flex justify-content-between align-items-baseline mb-1">
            <div>
                <span class="fw-bold">${highlight(item.item_name, q)}</span>
                ${item.item_name_en ? `<span class="text-muted small ms-1">${highlight(item.item_name_en, q)}</span>` : ''}
            </div>
            <span class="badge bg-dark ms-2 text-nowrap">${item.formatted_total} total</span>
        </div>`;

        item.locations.forEach(loc => {
            const crumb = loc.container_name
                ? `${esc(loc.room_name)} <i class="bi bi-chevron-right small"></i> ${esc(loc.container_name)}`
                : `${esc(loc.room_name)}`;

            const expBadge = loc.expiry_status === 'expired'
                ? `<span class="badge badge-expired ms-1">Expired</span>`
                : loc.expiry_status === 'expiring-soon'
                ? `<span class="badge badge-expiring-soon ms-1">Exp: ${esc(loc.expiry_date)}</span>`
                : '';

            const lowBadge = loc.quantity_grams < 500
                ? `<span class="badge bg-warning text-dark ms-1">Low</span>` : '';

            const notes = loc.notes ? `<span class="text-muted fst-italic small ms-1">${esc(loc.notes)}</span>` : '';

            const target = loc.container_id
                ? `${BASE}/containers/${loc.container_id}`
                : `${BASE}/rooms/${loc.room_id}`;

            html += `<a href="${target}" class="text-decoration-none d-block">
                <div class="d-flex align-items-center justify-content-between py-2 border-top">
                    <div class="small">
                        <span class="text-muted">${crumb}</span>
                        <div>${expBadge}${lowBadge}${notes}</div>
                    </div>
                    <span class="fw-semibold text-dark ms-3 text-nowrap">${esc(loc.formatted_qty)}</span>
                </div>
            </a>`;
        });

        html += `</div>`;
    });

    box.innerHTML = html;
    box.style.display = 'block';
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function highlight(text, q) {
    const safe = esc(text);
    const re = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&')})`, 'gi');
    return safe.replace(re, '<mark class="px-0 bg-warning rounded">$1</mark>');
}
</script>
