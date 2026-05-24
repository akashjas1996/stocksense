<?php

class HistoryController {

    public function index(): void {
        requireLogin();

        // Last 14 days — daily consumption
        $daily = db()->query('
            SELECT DATE(consumed_at) AS day,
                   SUM(quantity_grams) AS total_grams
            FROM consumption_log
            WHERE consumed_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
            GROUP BY DATE(consumed_at)
            ORDER BY day ASC
        ')->fetchAll();

        // Fill in missing days with 0
        $dailyMap = array_column($daily, 'total_grams', 'day');
        $dailyLabels = [];
        $dailyData   = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyData[]   = (int)($dailyMap[$d] ?? 0);
        }

        // Top 8 consumed items (all time)
        $topItems = db()->query('
            SELECT item_id, item_name, SUM(quantity_grams) AS total_grams
            FROM consumption_log
            WHERE item_name IS NOT NULL
            GROUP BY item_id, item_name
            ORDER BY total_grams DESC
            LIMIT 8
        ')->fetchAll();

        // Recent feed — last 50 entries
        $feed = db()->query('
            SELECT cl.*, u.name AS user_name
            FROM consumption_log cl
            LEFT JOIN users u ON u.id = cl.user_id
            ORDER BY cl.consumed_at DESC
            LIMIT 50
        ')->fetchAll();

        // Total consumed this month
        $thisMonth = db()->query('
            SELECT COALESCE(SUM(quantity_grams), 0) AS total
            FROM consumption_log
            WHERE MONTH(consumed_at) = MONTH(CURDATE())
              AND YEAR(consumed_at)  = YEAR(CURDATE())
        ')->fetchColumn();

        $pageTitle = 'Consumption History';
        ob_start();
        require __DIR__ . '/../Views/history/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function item(string $itemId): void {
        requireLogin();

        $item = db()->prepare('SELECT * FROM items WHERE id = ?');
        $item->execute([$itemId]);
        $item = $item->fetch();
        if (!$item) { http_response_code(404); exit('Item not found.'); }

        // Current stock across all locations
        $stock = db()->prepare('
            SELECT inv.quantity_grams, r.name AS room_name, c.name AS container_name, inv.expiry_date
            FROM inventory inv
            JOIN rooms r ON r.id = inv.room_id
            LEFT JOIN containers c ON c.id = inv.container_id
            WHERE inv.item_id = ?
        ');
        $stock->execute([$itemId]);
        $stock = $stock->fetchAll();

        // Per-day consumption chart (last 30 days)
        $daily = db()->prepare('
            SELECT DATE(consumed_at) AS day, SUM(quantity_grams) AS total_grams
            FROM consumption_log
            WHERE item_id = ?
              AND consumed_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
            GROUP BY DATE(consumed_at)
            ORDER BY day ASC
        ');
        $daily->execute([$itemId]);
        $daily = $daily->fetchAll();

        $dailyMap = array_column($daily, 'total_grams', 'day');
        $dailyLabels = [];
        $dailyData   = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dailyLabels[] = date('d M', strtotime($d));
            $dailyData[]   = (int)($dailyMap[$d] ?? 0);
        }

        // Full log for this item
        $log = db()->prepare('
            SELECT cl.*, u.name AS user_name
            FROM consumption_log cl
            LEFT JOIN users u ON u.id = cl.user_id
            WHERE cl.item_id = ?
            ORDER BY cl.consumed_at DESC
            LIMIT 100
        ');
        $log->execute([$itemId]);
        $log = $log->fetchAll();

        $totalConsumed = array_sum(array_column($log, 'quantity_grams'));

        $pageTitle = e($item['name']) . ' — History';
        ob_start();
        require __DIR__ . '/../Views/history/item.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }
}
