<?php

class SearchController {

    public function index(): void {
        requireLogin();
        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode([]);
            exit;
        }

        // Find all inventory entries matching the query, grouped by item
        $stmt = db()->prepare('
            SELECT
                it.id         AS item_id,
                it.name       AS item_name,
                inv.id        AS inv_id,
                inv.quantity_grams,
                inv.expiry_date,
                inv.notes,
                r.id          AS room_id,
                r.name        AS room_name,
                c.id          AS container_id,
                c.name        AS container_name
            FROM inventory inv
            JOIN items it      ON it.id  = inv.item_id
            JOIN rooms r       ON r.id   = inv.room_id
            LEFT JOIN containers c ON c.id = inv.container_id
            WHERE it.name LIKE ?
            ORDER BY it.name ASC, r.name ASC, c.name ASC
            LIMIT 60
        ');
        $stmt->execute(['%' . $q . '%']);
        $rows = $stmt->fetchAll();

        // Group by item, collect all locations under it
        $grouped = [];
        foreach ($rows as $row) {
            $key = $row['item_id'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'item_id'   => $row['item_id'],
                    'item_name' => $row['item_name'],
                    'total_grams' => 0,
                    'locations' => [],
                ];
            }
            $grouped[$key]['total_grams'] += $row['quantity_grams'];
            $grouped[$key]['locations'][] = [
                'inv_id'         => $row['inv_id'],
                'room_id'        => $row['room_id'],
                'room_name'      => $row['room_name'],
                'container_id'   => $row['container_id'],
                'container_name' => $row['container_name'],
                'quantity_grams' => $row['quantity_grams'],
                'expiry_date'    => $row['expiry_date'],
                'notes'          => $row['notes'],
                'expiry_status'  => expiryStatus($row['expiry_date']),
                'formatted_qty'  => formatWeight($row['quantity_grams']),
            ];
        }

        // Add formatted total to each group
        foreach ($grouped as &$g) {
            $g['formatted_total'] = formatWeight($g['total_grams']);
        }

        echo json_encode(array_values($grouped));
    }
}
