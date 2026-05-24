<?php

class DashboardController {

    public function index(): void {
        requireLogin();

        // Rooms with container count and item count
        $rooms = db()->query('
            SELECT r.*,
                   COUNT(DISTINCT c.id)  AS container_count,
                   COUNT(DISTINCT i.id)  AS item_count
            FROM rooms r
            LEFT JOIN containers c ON c.room_id = r.id
            LEFT JOIN inventory  i ON i.room_id = r.id
            GROUP BY r.id
            ORDER BY r.name
        ')->fetchAll();

        // Expiring soon
        $expiringSoon = db()->prepare('
            SELECT inv.*, it.name AS item_name, r.name AS room_name,
                   c.name AS container_name
            FROM inventory inv
            JOIN items it ON it.id = inv.item_id
            JOIN rooms  r  ON r.id  = inv.room_id
            LEFT JOIN containers c ON c.id = inv.container_id
            WHERE inv.expiry_date IS NOT NULL
              AND inv.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY inv.expiry_date ASC
            LIMIT 10
        ');
        $expiringSoon->execute([EXPIRY_WARN_DAYS]);
        $expiringSoon = $expiringSoon->fetchAll();

        // Expired
        $expired = db()->query('
            SELECT inv.*, it.name AS item_name, r.name AS room_name
            FROM inventory inv
            JOIN items it ON it.id = inv.item_id
            JOIN rooms  r  ON r.id  = inv.room_id
            WHERE inv.expiry_date < CURDATE()
            ORDER BY inv.expiry_date ASC
            LIMIT 5
        ')->fetchAll();

        $hour     = (int) date('H');
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
        $greetEmoji = $hour < 12 ? '☀️' : ($hour < 17 ? '🌤️' : '🌙');

        $totalItems   = db()->query('SELECT COUNT(*) FROM inventory')->fetchColumn();
        $expiredCount = db()->query("SELECT COUNT(*) FROM inventory WHERE expiry_date < CURDATE()")->fetchColumn();
        $soonCount    = db()->query("SELECT COUNT(*) FROM inventory WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

        $pageTitle = 'Dashboard';
        ob_start();
        require __DIR__ . '/../Views/dashboard.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }
}
