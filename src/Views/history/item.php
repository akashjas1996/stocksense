<div class="page-head">
    <a href="<?= APP_URL ?>/history" class="back-btn"><i class="bi bi-arrow-left"></i></a>
    <h1><?= e($item['name']) ?></h1>
</div>

<!-- Current stock -->
<div class="card" style="padding:16px;margin-bottom:12px;">
    <div style="font-size:.78rem;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Current Stock</div>
    <?php if (empty($stock)): ?>
    <p style="color:var(--text-2);font-size:.85rem;margin:0;">None in stock.</p>
    <?php else: ?>
    <?php foreach ($stock as $s): ?>
    <div style="display:flex;justify-content:space-between;font-size:.85rem;padding:5px 0;border-bottom:1px solid var(--border-s);">
        <span style="color:var(--text-2);"><?= e($s['room_name']) ?><?= $s['container_name'] ? ' / ' . e($s['container_name']) : '' ?></span>
        <span style="font-weight:700;<?= expiryStatus($s['expiry_date']) === 'expired' ? 'color:var(--danger)' : '' ?>">
            <?= formatWeight($s['quantity_grams']) ?>
        </span>
    </div>
    <?php endforeach ?>
    <div style="display:flex;justify-content:space-between;font-size:.85rem;padding:6px 0 0;font-weight:800;">
        <span>Total</span>
        <span style="color:var(--accent);"><?= formatWeight(array_sum(array_column($stock, 'quantity_grams'))) ?></span>
    </div>
    <?php endif ?>
</div>

<!-- All-time stats -->
<div class="card" style="padding:16px;margin-bottom:12px;">
    <div style="font-size:.78rem;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">All-time consumed</div>
    <div style="font-size:2rem;font-weight:900;color:var(--accent);"><?= formatWeight($totalConsumed) ?></div>
    <div style="font-size:.82rem;color:var(--text-2);"><?= count($log) ?> usage event<?= count($log) !== 1 ? 's' : '' ?></div>
</div>

<!-- 30-day chart -->
<div class="card" style="padding:16px;margin-bottom:16px;">
    <div style="font-size:.78rem;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Last 30 days</div>
    <canvas id="itemChart" height="120"></canvas>
</div>

<!-- Usage log -->
<div class="sec-label" style="margin-bottom:8px;">Usage log</div>
<?php if (empty($log)): ?>
<div class="empty-state"><i class="bi bi-clock-history"></i><p>No usage recorded yet.</p></div>
<?php else: ?>
<?php foreach ($log as $entry): ?>
<div style="background:var(--card);border:1px solid var(--border-s);border-radius:var(--r-s);padding:12px 14px;margin-bottom:6px;">
    <div style="font-size:.75rem;color:var(--text-3);margin-bottom:3px;"><?= date('d M Y, H:i', strtotime($entry['consumed_at'])) ?></div>
    <div style="font-weight:700;"><?= formatWeight((int)$entry['quantity_grams']) ?></div>
    <div style="font-size:.78rem;color:var(--text-2);margin-top:2px;">
        <?= e($entry['room_name'] ?? '') ?>
        <?= $entry['container_name'] ? '/ ' . e($entry['container_name']) : '' ?>
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
            backgroundColor: '#D97706',
            borderRadius: 6,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: { callback: v => v >= 1000 ? (v/1000)+'kg' : v+'g', font: { size: 10 } },
                grid: { color: '#EDE5DA' }
            },
            x: { ticks: { font: { size: 9 } }, grid: { display: false } }
        }
    }
});
</script>
