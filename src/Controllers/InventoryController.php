<?php

class InventoryController {

    public function createForm(): void {
        requireLogin();
        $rooms      = db()->query('SELECT id, name FROM rooms ORDER BY name')->fetchAll();
        $containers = db()->query('SELECT id, room_id, name FROM containers ORDER BY name')->fetchAll();
        $items      = db()->query('SELECT id, name FROM items ORDER BY name')->fetchAll();

        // Pre-select room/container from query string (when coming from scan)
        $preRoomId      = (int) ($_GET['room_id'] ?? 0);
        $preContainerId = (int) ($_GET['container_id'] ?? 0);
        $preItemName    = $_GET['item_name'] ?? '';

        $pageTitle = 'Add Item';
        ob_start(); require __DIR__ . '/../Views/inventory/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function store(): void {
        requireLogin();
        $itemName    = trim($_POST['item_name'] ?? '');
        $barcode     = trim($_POST['barcode'] ?? '') ?: null;
        $roomId      = (int) ($_POST['room_id'] ?? 0);
        $containerId = (int) ($_POST['container_id'] ?? 0) ?: null;
        $grams       = (int) ($_POST['quantity_grams'] ?? 0);
        $arrival     = $_POST['arrival_date'] ?? date('Y-m-d');
        $expiry      = trim($_POST['expiry_date'] ?? '') ?: null;
        $notes       = trim($_POST['notes'] ?? '') ?: null;

        if (!$itemName || !$roomId || $grams <= 0) {
            flash('error', 'Item name, room, and quantity are required.');
            redirect('/inventory/create');
        }

        // Upsert item into catalog
        $itemId = $this->findOrCreateItem($itemName, $barcode);

        $stmt = db()->prepare('
            INSERT INTO inventory (item_id, room_id, container_id, quantity_grams, arrival_date, expiry_date, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$itemId, $roomId, $containerId, $grams, $arrival, $expiry, $notes]);

        flash('success', "{$itemName} added to inventory.");

        if ($containerId) redirect("/containers/{$containerId}");
        redirect("/rooms/{$roomId}");
    }

    public function editForm(string $id): void {
        requireLogin();
        $entry      = $this->findOr404($id);
        $rooms      = db()->query('SELECT id, name FROM rooms ORDER BY name')->fetchAll();
        $containers = db()->query('SELECT id, room_id, name FROM containers ORDER BY name')->fetchAll();
        $pageTitle  = 'Edit Item';
        ob_start(); require __DIR__ . '/../Views/inventory/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function update(string $id): void {
        requireLogin();
        $entry       = $this->findOr404($id);
        $grams       = (int) ($_POST['quantity_grams'] ?? 0);
        $containerId = (int) ($_POST['container_id'] ?? 0) ?: null;
        $roomId      = (int) ($_POST['room_id'] ?? 0);
        $expiry      = trim($_POST['expiry_date'] ?? '') ?: null;
        $notes       = trim($_POST['notes'] ?? '') ?: null;

        db()->prepare('
            UPDATE inventory SET quantity_grams = ?, container_id = ?, room_id = ?,
                                 expiry_date = ?, notes = ?
            WHERE id = ?
        ')->execute([$grams, $containerId, $roomId, $expiry, $notes, $id]);

        flash('success', 'Item updated.');
        if ($containerId) redirect("/containers/{$containerId}");
        redirect("/rooms/{$roomId}");
    }

    public function delete(string $id): void {
        requireLogin();
        $entry = $this->findOr404($id);
        db()->prepare('DELETE FROM inventory WHERE id = ?')->execute([$id]);
        flash('success', 'Item removed.');
        if ($entry['container_id']) redirect("/containers/{$entry['container_id']}");
        redirect("/rooms/{$entry['room_id']}");
    }

    public function consumeForm(string $id): void {
        requireLogin();
        $entry = $this->findOr404($id);
        $item  = db()->prepare('SELECT * FROM items WHERE id = ?');
        $item->execute([$entry['item_id']]);
        $item = $item->fetch();

        $pageTitle = 'Use ' . e($item['name']);
        ob_start(); require __DIR__ . '/../Views/inventory/consume.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function consume(string $id): void {
        requireLogin();
        $entry = $this->findOr404($id);
        $use   = (int) ($_POST['use_grams'] ?? 0);

        if ($use <= 0 || $use > $entry['quantity_grams']) {
            flash('error', 'Invalid amount.');
            redirect("/inventory/{$id}/consume");
        }

        $newQty = $entry['quantity_grams'] - $use;

        // Fetch context for denormalized log (survives inventory deletion)
        $item = db()->prepare('SELECT name FROM items WHERE id = ?');
        $item->execute([$entry['item_id']]); $item = $item->fetch();
        $room = db()->prepare('SELECT name FROM rooms WHERE id = ?');
        $room->execute([$entry['room_id']]); $room = $room->fetch();
        $containerName = null;
        if ($entry['container_id']) {
            $con = db()->prepare('SELECT name FROM containers WHERE id = ?');
            $con->execute([$entry['container_id']]); $con = $con->fetch();
            $containerName = $con['name'] ?? null;
        }

        db()->prepare('
            INSERT INTO consumption_log
                (inventory_id, item_id, item_name, room_name, container_name, user_id, quantity_grams)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ')->execute([$id, $entry['item_id'], $item['name'], $room['name'], $containerName, $_SESSION['user_id'], $use]);

        if ($newQty === 0) {
            db()->prepare('DELETE FROM inventory WHERE id = ?')->execute([$id]);
            flash('success', 'Item fully used and removed.');
        } else {
            db()->prepare('UPDATE inventory SET quantity_grams = ? WHERE id = ?')
                 ->execute([$newQty, $id]);
            flash('success', formatWeight($use) . ' used. ' . formatWeight($newQty) . ' remaining.');
        }

        if ($entry['container_id']) redirect("/containers/{$entry['container_id']}");
        redirect("/rooms/{$entry['room_id']}");
    }

    private function findOrCreateItem(string $name, ?string $barcode): int {
        // Try barcode first
        if ($barcode) {
            $stmt = db()->prepare('SELECT id FROM items WHERE product_barcode = ?');
            $stmt->execute([$barcode]);
            $row = $stmt->fetch();
            if ($row) return $row['id'];
        }

        // Try name match
        $stmt = db()->prepare('SELECT id FROM items WHERE LOWER(name) = LOWER(?)');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        if ($row) return $row['id'];

        // Create new
        $stmt = db()->prepare('INSERT INTO items (name, product_barcode) VALUES (?, ?)');
        $stmt->execute([$name, $barcode]);
        return (int) db()->lastInsertId();
    }

    private function findOr404(string $id): array {
        $stmt = db()->prepare('SELECT * FROM inventory WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) { http_response_code(404); exit('Inventory entry not found.'); }
        return $row;
    }
}
