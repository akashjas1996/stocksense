<?php

class ContainerController {

    public function createForm(): void {
        requireLogin();
        $roomId = $_GET['room_id'] ?? null;
        $rooms  = db()->query('SELECT id, name FROM rooms ORDER BY name')->fetchAll();
        $pageTitle = 'Add Container';
        ob_start(); require __DIR__ . '/../Views/containers/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function store(): void {
        requireLogin();
        $name   = trim($_POST['name'] ?? '');
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $type   = $_POST['type'] ?? 'other';

        if (!$name || !$roomId) {
            flash('error', 'Name and room are required.');
            redirect('/containers/create');
        }

        $stmt = db()->prepare('INSERT INTO containers (room_id, name, type, qr_code) VALUES (?, ?, ?, ?)');
        $stmt->execute([$roomId, $name, $type, generateUuid()]);
        flash('success', "Container '{$name}' created.");
        redirect("/rooms/{$roomId}");
    }

    public function show(string $id): void {
        requireLogin();
        $container = $this->findOr404($id);

        $items = db()->prepare('
            SELECT inv.*, it.name AS item_name
            FROM inventory inv
            JOIN items it ON it.id = inv.item_id
            WHERE inv.container_id = ?
            ORDER BY it.name
        ');
        $items->execute([$id]);
        $items = $items->fetchAll();

        $room = db()->prepare('SELECT * FROM rooms WHERE id = ?');
        $room->execute([$container['room_id']]);
        $room = $room->fetch();

        $pageTitle = e($container['name']);
        ob_start(); require __DIR__ . '/../Views/containers/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function editForm(string $id): void {
        requireLogin();
        $container = $this->findOr404($id);
        $rooms = db()->query('SELECT id, name FROM rooms ORDER BY name')->fetchAll();
        $pageTitle = 'Edit Container';
        ob_start(); require __DIR__ . '/../Views/containers/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function update(string $id): void {
        requireLogin();
        $container = $this->findOr404($id);
        $name   = trim($_POST['name'] ?? '');
        $roomId = (int) ($_POST['room_id'] ?? 0);
        $type   = $_POST['type'] ?? 'other';

        if (!$name || !$roomId) {
            flash('error', 'Name and room are required.');
            redirect("/containers/{$id}/edit");
        }

        db()->prepare('UPDATE containers SET name = ?, room_id = ?, type = ? WHERE id = ?')
             ->execute([$name, $roomId, $type, $id]);
        flash('success', 'Container updated.');
        redirect("/containers/{$id}");
    }

    public function delete(string $id): void {
        requireLogin();
        $container = $this->findOr404($id);
        $roomId = $container['room_id'];
        db()->prepare('DELETE FROM containers WHERE id = ?')->execute([$id]);
        flash('success', 'Container deleted.');
        redirect("/rooms/{$roomId}");
    }

    public function qr(string $id): void {
        requireLogin();
        $container = $this->findOr404($id);
        $qrPayload = APP_URL . '/location/' . $container['qr_code'];
        $pageTitle  = 'QR — ' . e($container['name']);
        ob_start(); require __DIR__ . '/../Views/containers/qr.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    private function findOr404(string $id): array {
        $stmt = db()->prepare('SELECT * FROM containers WHERE id = ?');
        $stmt->execute([$id]);
        $c = $stmt->fetch();
        if (!$c) { http_response_code(404); exit('Container not found.'); }
        return $c;
    }
}
