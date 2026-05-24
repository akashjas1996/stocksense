<?php

class LocationController {

    public function show(string $qr): void {
        // Resolve QR → room or container (no login required)
        $stmt = db()->prepare('SELECT *, "room" AS type FROM rooms WHERE qr_code = ?');
        $stmt->execute([$qr]);
        $location = $stmt->fetch();

        if (!$location) {
            $stmt = db()->prepare('
                SELECT c.*, "container" AS type, r.name AS room_name, r.id AS room_id_ref
                FROM containers c
                JOIN rooms r ON r.id = c.room_id
                WHERE c.qr_code = ?
            ');
            $stmt->execute([$qr]);
            $location = $stmt->fetch();
        }

        if (!$location) {
            http_response_code(404);
            $pageTitle = 'Not Found';
            $content = '<div class="text-center py-5 text-muted"><i class="bi bi-qr-code display-4"></i><p class="mt-3">QR code not recognised.</p></div>';
            require __DIR__ . '/../Views/layouts/public.php';
            return;
        }

        // Fetch items
        if ($location['type'] === 'room') {
            $stmt = db()->prepare('
                SELECT inv.*, it.name AS item_name,
                       c.name AS container_name
                FROM inventory inv
                JOIN items it ON it.id = inv.item_id
                LEFT JOIN containers c ON c.id = inv.container_id
                WHERE inv.room_id = ?
                ORDER BY c.name ASC, it.name ASC
            ');
            $stmt->execute([$location['id']]);
        } else {
            $stmt = db()->prepare('
                SELECT inv.*, it.name AS item_name, NULL AS container_name
                FROM inventory inv
                JOIN items it ON it.id = inv.item_id
                WHERE inv.container_id = ?
                ORDER BY it.name ASC
            ');
            $stmt->execute([$location['id']]);
        }
        $items = $stmt->fetchAll();

        // Low stock threshold: items with < 500g
        $lowStock  = array_filter($items, fn($i) => $i['quantity_grams'] < 500);
        $expired   = array_filter($items, fn($i) => $i['expiry_date'] && expiryStatus($i['expiry_date']) === 'expired');
        $expiringSoon = array_filter($items, fn($i) => $i['expiry_date'] && expiryStatus($i['expiry_date']) === 'expiring-soon');

        $pageTitle = e($location['name']);
        ob_start();
        require __DIR__ . '/../Views/location/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/public.php';
    }
}
