<?php

class ExploreController {

    public function index(): void {
        requireLogin();

        $rows = db()->query('
            SELECT
                r.id   AS room_id,   r.name AS room_name,
                c.id   AS cid,       c.name AS cname,  c.type AS ctype,
                inv.id AS inv_id,    inv.quantity_grams, inv.expiry_date, inv.notes,
                it.id  AS item_id,   it.name AS item_name,
                it.name_en,          it.image_url
            FROM rooms r
            LEFT JOIN inventory inv ON inv.room_id = r.id
            LEFT JOIN items     it  ON it.id = inv.item_id
            LEFT JOIN containers c  ON c.id  = inv.container_id
            ORDER BY r.name, (c.name IS NULL) DESC, c.name, it.name
        ')->fetchAll();

        // Build nested structure: room → section (container or loose) → items
        $mall = [];
        foreach ($rows as $row) {
            $rid = $row['room_id'];
            if (!isset($mall[$rid])) {
                $mall[$rid] = ['name' => $row['room_name'], 'sections' => []];
            }
            if ($row['inv_id'] === null) continue;
            $skey = $row['cid'] ?? '__loose__';
            if (!isset($mall[$rid]['sections'][$skey])) {
                $mall[$rid]['sections'][$skey] = [
                    'name'  => $row['cname'],
                    'type'  => $row['ctype'],
                    'items' => [],
                ];
            }
            $mall[$rid]['sections'][$skey]['items'][] = $row;
        }

        $totalItems  = db()->query('SELECT COUNT(*) FROM inventory')->fetchColumn();
        $totalWeight = db()->query('SELECT SUM(quantity_grams) FROM inventory')->fetchColumn();
        $roomCount   = count($mall);

        $pageTitle = 'Explore';
        ob_start();
        require __DIR__ . '/../Views/explore.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }
}
