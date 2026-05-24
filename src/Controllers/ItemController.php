<?php

class ItemController {

    public function index(): void {
        requireLogin();

        $items = db()->query('
            SELECT id, name, name_en, name_hi, image_url, product_barcode
            FROM items ORDER BY name
        ')->fetchAll();

        $missingCount = count(array_filter($items, fn($i) => empty($i['image_url'])));

        $pageTitle = 'Item Catalog';
        ob_start(); require __DIR__ . '/../Views/items/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function fetchImage(string $id): void {
        requireLogin();
        header('Content-Type: application/json');

        $item = db()->prepare('SELECT id, name, name_en FROM items WHERE id = ?');
        $item->execute([$id]);
        $item = $item->fetch();
        if (!$item) { echo json_encode(['success' => false, 'error' => 'Not found']); return; }

        // Build the best search term for Open Food Facts
        $term = $this->bestSearchTerm($item);
        $url  = 'https://world.openfoodfacts.org/cgi/search.pl?' . http_build_query([
            'search_terms'  => $term,
            'search_simple' => 1,
            'action'        => 'process',
            'json'          => 1,
            'page_size'     => 5,
            'fields'        => 'product_name,image_front_url',
            'sort_by'       => 'unique_scans_n',  // most-scanned = most recognisable
        ]);

        $ctx  = stream_context_create(['http' => ['timeout' => 8, 'user_agent' => 'StockSense/1.0 (family inventory app)']]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) { echo json_encode(['success' => false, 'error' => 'Fetch failed']); return; }

        $data     = json_decode($resp, true);
        $imageUrl = null;
        foreach ($data['products'] ?? [] as $p) {
            if (!empty($p['image_front_url'])) { $imageUrl = $p['image_front_url']; break; }
        }

        if (!$imageUrl) { echo json_encode(['success' => false, 'error' => 'No image found on Open Food Facts']); return; }

        db()->prepare('UPDATE items SET image_url = ? WHERE id = ?')->execute([$imageUrl, $id]);
        echo json_encode(['success' => true, 'image_url' => $imageUrl]);
    }

    private function bestSearchTerm(array $item): string {
        $en   = trim($item['name_en'] ?? '');
        $name = trim($item['name']);

        // If no English name, use the stored name as-is
        if (!$en) return $name;

        // "Pigeon Pea / Toor Dal" → prefer the part after "/" (usually more common)
        // "Chickpea Flour / Gram Flour" → same logic
        if (str_contains($en, '/')) {
            $parts = array_map('trim', explode('/', $en));
            // Pick the shortest part — tends to be the more common single-word name
            usort($parts, fn($a, $b) => strlen($a) <=> strlen($b));
            return $parts[0];
        }

        // Strip parenthetical notes: "Groundnut Oil (Peanut Oil)" → "Groundnut Oil"
        $en = preg_replace('/\s*\(.*?\)/', '', $en);

        return $en ?: $name;
    }

    public function editForm(string $id): void {
        requireLogin();
        $item = db()->prepare('SELECT * FROM items WHERE id = ?');
        $item->execute([$id]);
        $item = $item->fetch();
        if (!$item) { http_response_code(404); exit('Item not found.'); }

        $pageTitle = 'Edit ' . $item['name'];
        ob_start(); require __DIR__ . '/../Views/items/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function update(string $id): void {
        requireLogin();
        $item = db()->prepare('SELECT id FROM items WHERE id = ?');
        $item->execute([$id]);
        if (!$item->fetch()) { http_response_code(404); exit('Item not found.'); }

        $name     = trim($_POST['name']     ?? '');
        $nameEn   = trim($_POST['name_en']  ?? '') ?: null;
        $nameHi   = trim($_POST['name_hi']  ?? '') ?: null;
        $imageUrl = trim($_POST['image_url'] ?? '') ?: null;

        if (!$name) { flash('error', 'Name is required.'); redirect("/items/{$id}/edit"); }

        db()->prepare('
            UPDATE items SET name = ?, name_en = ?, name_hi = ?, image_url = ? WHERE id = ?
        ')->execute([$name, $nameEn, $nameHi, $imageUrl, $id]);

        flash('success', 'Item updated.');
        redirect('/items');
    }

    public function lookup(): void {
        header('Content-Type: application/json');
        $name = trim($_GET['name'] ?? '');
        if (strlen($name) < 2) { echo json_encode(null); return; }

        $stmt = db()->prepare('
            SELECT id, name, image_url FROM items
            WHERE LOWER(name) = LOWER(?)
               OR LOWER(name_en) = LOWER(?)
               OR LOWER(name_hi) = LOWER(?)
            LIMIT 1
        ');
        $stmt->execute([$name, $name, $name]);
        echo json_encode($stmt->fetch() ?: null);
    }
}
