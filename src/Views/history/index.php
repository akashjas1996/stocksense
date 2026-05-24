<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Consumption History</h5>
    <span class="badge bg-dark"><?= formatWeight((int)$thisMonth) ?> this month</span>
</div>

<!-- Daily bar chart -->
<div class="card shadow-sm mb-3">
    <div class="card-body pb-2">
        <div class="text-muted small fw-semibold mb-2">Last 14 days (grams used)</div>
        <canvas id="dailyChart" height="130"></canvas>
    </div>
</div>

<!-- Top items doughnut -->
<?php if (!empty($topItems)): ?>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="text-muted small fw-semibold mb-2">Top consumed items (all time)</div>
        <div class="row align-items-center">
            <div class="col-5">
                <canvas id="topChart" height="160"></canvas>
            </div>
            <div class="col-7">
                <ul class="list-unstyled mb-0 small">
                    <?php foreach ($topItems as $it): ?>
                    <li class="d-flex justify-content-between border-bottom py-1">
                        <a href="<?= APP_URL ?>/history/item/<?= e($it['item_id'] ?? '') ?>"
                           class="text-truncate text-dark text-decoration-none" style="max-width:120px">
                            <?= e($it['item_name']) ?>
                        </a>
                        <span class="text-muted"><?= formatWeight((int)$it['total_grams']) ?></span>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif ?>

<!-- Activity feed -->
<h6 class="text-muted mb-2">Recent activity</h6>
<?php if (empty($feed)): ?>
<div class="text-center py-4 text-muted small">No usage logged yet.</div>
<?php else: ?>
<?php
$currentDate = null;
foreach ($feed as $log):
    $logDate = date('d M Y', strtotime($log['consumed_at']));
    if ($logDate !== $currentDate):
        $currentDate = $logDate;
?>
<div class="text-muted small fw-semibold mt-3 mb-1"><?= $logDate ?></div>
<?php endif ?>
<div class="item-pill d-flex align-items-center gap-2 mb-2">
    <div class="flex-grow-1">
        <div class="fw-semibold"><?= e($log['item_name'] ?? '—') ?></div>
        <div class="text-muted small">
            <?= formatWeight((int)$log['quantity_grams']) ?> used
            &middot; <?= e($log['room_name'] ?? '') ?>
            <?= $log['container_name'] ? '/ ' . e($log['container_name']) : '' ?>
        </div>
    </div>
    <div class="text-muted small text-end">
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
            backgroundColor: '#198754',
            borderRadius: 4,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                ticks: {
                    callback: v => v >= 1000 ? (v/1000) + ' kg' : v + ' g'
                }
            },
            x: { ticks: { font: { size: 10 } } }
        }
    }
});

<?php if (!empty($topItems)): ?>
const topLabels = <?= json_encode(array_column($topItems, 'item_name')) ?>;
const topData   = <?= json_encode(array_map(fn($r) => (int)$r['total_grams'], $topItems)) ?>;
const colors    = ['#198754','#0d6efd','#fd7e14','#dc3545','#6610f2','#20c997','#ffc107','#6c757d'];

new Chart(document.getElementById('topChart'), {
    type: 'doughnut',
    data: {
        labels: topLabels,
        datasets: [{ data: topData, backgroundColor: colors, borderWidth: 1 }]
    },
    options: { plugins: { legend: { display: false } }, cutout: '65%' }
});
<?php endif ?>
</script>
