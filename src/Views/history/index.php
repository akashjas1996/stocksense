<div class="page-head">
    <h1>History</h1>
    <span style="background:var(--accent-l);color:var(--accent);font-weight:700;font-size:.78rem;padding:5px 10px;border-radius:20px;"><?= formatWeight((int)$thisMonth) ?> this month</span>
</div>

<!-- Daily bar chart -->
<div class="card" style="margin-bottom:12px;padding:16px;">
    <div style="font-size:.78rem;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Last 14 days</div>
    <canvas id="dailyChart" height="130"></canvas>
</div>

<!-- Top items doughnut -->
<?php if (!empty($topItems)): ?>
<div class="card" style="margin-bottom:12px;padding:16px;">
    <div style="font-size:.78rem;color:var(--text-3);font-weight:700;text-transform:uppercase;letter-spacing:.05em;margin-bottom:12px;">Top consumed (all time)</div>
    <div style="display:flex;align-items:center;gap:16px;">
        <canvas id="topChart" width="130" height="130" style="flex-shrink:0;"></canvas>
        <ul style="list-style:none;margin:0;padding:0;flex:1;font-size:.82rem;">
            <?php foreach ($topItems as $it): ?>
            <li style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border-s);">
                <a href="<?= APP_URL ?>/history/item/<?= e($it['item_id'] ?? '') ?>"
                   style="color:var(--text);text-decoration:none;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:120px;">
                    <?= e($it['item_name']) ?>
                </a>
                <span style="color:var(--text-2);flex-shrink:0;"><?= formatWeight((int)$it['total_grams']) ?></span>
            </li>
            <?php endforeach ?>
        </ul>
    </div>
</div>
<?php endif ?>

<!-- Activity feed -->
<div class="sec-label" style="margin-top:20px;margin-bottom:8px;">Recent activity</div>
<?php if (empty($feed)): ?>
<div class="empty-state"><i class="bi bi-clock-history"></i><p>No usage logged yet.</p></div>
<?php else: ?>
<?php
$currentDate = null;
foreach ($feed as $log):
    $logDate = date('d M Y', strtotime($log['consumed_at']));
    if ($logDate !== $currentDate):
        $currentDate = $logDate;
?>
<div class="sec-label" style="margin-top:16px;margin-bottom:6px;"><?= $logDate ?></div>
<?php endif ?>
<div style="background:var(--card);border:1px solid var(--border-s);border-radius:var(--r-s);padding:12px 14px;margin-bottom:6px;display:flex;align-items:center;gap:10px;">
    <div style="flex:1;">
        <div style="font-weight:700;font-size:.9rem;"><?= e($log['item_name'] ?? '—') ?></div>
        <div style="font-size:.78rem;color:var(--text-2);margin-top:2px;">
            <?= formatWeight((int)$log['quantity_grams']) ?> used
            &middot; <?= e($log['room_name'] ?? '') ?>
            <?= $log['container_name'] ? '/ ' . e($log['container_name']) : '' ?>
        </div>
    </div>
    <div style="font-size:.75rem;color:var(--text-3);flex-shrink:0;">
        <?= date('H:i', strtotime($log['consumed_at'])) ?>
    </div>
</div>
<?php endforeach ?>
<?php endif ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const dailyLabels = <?= json_encode($dailyLabels) ?>;
const dailyData   = <?= json_encode($dailyData) ?>;

new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'grams used',
            data: dailyData,
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
            x: { ticks: { font: { size: 10 } }, grid: { display: false } }
        }
    }
});

<?php if (!empty($topItems)): ?>
const topLabels = <?= json_encode(array_column($topItems, 'item_name')) ?>;
const topData   = <?= json_encode(array_map(fn($r) => (int)$r['total_grams'], $topItems)) ?>;
const colors    = ['#D97706','#B45309','#92400E','#78716C','#A16207','#CA8A04','#D97706','#F59E0B'];

new Chart(document.getElementById('topChart'), {
    type: 'doughnut',
    data: {
        labels: topLabels,
        datasets: [{ data: topData, backgroundColor: colors, borderWidth: 0 }]
    },
    options: { plugins: { legend: { display: false } }, cutout: '68%' }
});
<?php endif ?>
</script>
