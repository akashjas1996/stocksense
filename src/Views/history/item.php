<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= APP_URL ?>/history" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0"><?= e($item['name']) ?></h5>
</div>

<!-- Current stock summary -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="text-muted small fw-semibold mb-2">Current stock</div>
        <?php if (empty($stock)): ?>
        <p class="text-muted small mb-0">None in stock.</p>
        <?php else: ?>
        <?php foreach ($stock as $s): ?>
        <div class="d-flex justify-content-between small border-bottom py-1">
            <span><?= e($s['room_name']) ?><?= $s['container_name'] ? ' / ' . e($s['container_name']) : '' ?></span>
            <span class="fw-semibold <?= expiryStatus($s['expiry_date']) === 'expired' ? 'text-danger' : '' ?>">
                <?= formatWeight($s['quantity_grams']) ?>
            </span>
        </div>
        <?php endforeach ?>
        <div class="d-flex justify-content-between small pt-1 fw-bold">
            <span>Total</span>
            <span><?= formatWeight(array_sum(array_column($stock, 'quantity_grams'))) ?></span>
        </div>
        <?php endif ?>
    </div>
</div>

<!-- Consumption stats -->
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="text-muted small fw-semibold mb-2">All-time consumed</div>
        <div class="fs-4 fw-bold text-success"><?= formatWeight($totalConsumed) ?></div>
        <div class="text-muted small"><?= count($log) ?> usage event<?= count($log) !== 1 ? 's' : '' ?></div>
    </div>
</div>

<!-- 30-day chart -->
<div class="card shadow-sm mb-3">
    <div class="card-body pb-2">
        <div class="text-muted small fw-semibold mb-2">Last 30 days</div>
        <canvas id="itemChart" height="120"></canvas>
    </div>
</div>

<!-- Usage log -->
<h6 class="text-muted mb-2">Usage log</h6>
<?php if (empty($log)): ?>
<div class="text-center py-4 text-muted small">No usage recorded yet.</div>
<?php else: ?>
<?php foreach ($log as $entry): ?>
<div class="item-pill d-flex align-items-center gap-2 mb-2">
    <div class="flex-grow-1">
        <div class="small text-muted"><?= date('d M Y, H:i', strtotime($entry['consumed_at'])) ?></div>
        <div class="fw-semibold"><?= formatWeight((int)$entry['quantity_grams']) ?></div>
        <div class="text-muted small">
            <?= e($entry['room_name'] ?? '') ?>
            <?= $entry['container_name'] ? '/ ' . e($entry['container_name']) : '' ?>
        </div>
    </div>
</div>
<?php endforeach ?>
<?php endif ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('itemChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($dailyLabels) ?>,
        datasets: [{
            data: <?= json_encode($dailyData) ?>,
            backgroundColor: '#0d6efd',
            borderRadius: 4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => v >= 1000 ? (v/1000)+'kg' : v+'g' } },
            x: { ticks: { font: { size: 9 } } }
        }
    }
});
</script>
