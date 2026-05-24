<?php

class RoomController {

    public function index(): void {
        requireLogin();
        $rooms = db()->query('
            SELECT r.*, COUNT(DISTINCT c.id) AS container_count,
                   COALESCE(SUM(inv.quantity_grams), 0) AS total_grams
            FROM rooms r
            LEFT JOIN containers c ON c.room_id = r.id
            LEFT JOIN inventory  inv ON inv.room_id = r.id
            GROUP BY r.id ORDER BY r.name
        ')->fetchAll();

        $pageTitle = 'Rooms';
        ob_start(); require __DIR__ . '/../Views/rooms/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function createForm(): void {
        requireLogin();
        $pageTitle = 'Add Room';
        ob_start(); require __DIR__ . '/../Views/rooms/create.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function store(): void {
        requireLogin();
        $name = trim($_POST['name'] ?? '');
        if (!$name) { flash('error', 'Room name is required.'); redirect('/rooms/create'); }

        $stmt = db()->prepare('INSERT INTO rooms (name, qr_code) VALUES (?, ?)');
        $stmt->execute([$name, generateUuid()]);
        flash('success', "Room '{$name}' created.");
        redirect('/rooms');
    }

    public function show(string $id): void {
        requireLogin();
        $room = $this->findOr404($id);

        $containers = db()->prepare('
            SELECT c.*, COALESCE(SUM(inv.quantity_grams), 0) AS total_grams,
                   COUNT(inv.id) AS item_count
            FROM containers c
            LEFT JOIN inventory inv ON inv.container_id = c.id
            WHERE c.room_id = ?
            GROUP BY c.id ORDER BY c.name
        ');
        $containers->execute([$id]);
        $containers = $containers->fetchAll();

        // Items placed directly in the room (no container)
        $looseItems = db()->prepare('
            SELECT inv.*, it.name AS item_name
            FROM inventory inv
            JOIN items it ON it.id = inv.item_id
            WHERE inv.room_id = ? AND inv.container_id IS NULL
            ORDER BY it.name
        ');
        $looseItems->execute([$id]);
        $looseItems = $looseItems->fetchAll();

        $pageTitle = e($room['name']);
        ob_start(); require __DIR__ . '/../Views/rooms/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function editForm(string $id): void {
        requireLogin();
        $room = $this->findOr404($id);
        $pageTitle = 'Edit Room';
        ob_start(); require __DIR__ . '/../Views/rooms/edit.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function update(string $id): void {
        requireLogin();
        $room = $this->findOr404($id);
        $name = trim($_POST['name'] ?? '');
        if (!$name) { flash('error', 'Room name is required.'); redirect("/rooms/{$id}/edit"); }

        db()->prepare('UPDATE rooms SET name = ? WHERE id = ?')->execute([$name, $id]);
        flash('success', 'Room updated.');
        redirect("/rooms/{$id}");
    }

    public function delete(string $id): void {
        requireLogin();
        $this->findOr404($id);
        db()->prepare('DELETE FROM rooms WHERE id = ?')->execute([$id]);
        flash('success', 'Room deleted.');
        redirect('/rooms');
    }

    public function qr(string $id): void {
        requireLogin();
        $room = $this->findOr404($id);
        // The QR payload is the scan URL; JS on the scan page handles the redirect
        $qrPayload = APP_URL . '/location/' . $room['qr_code'];
        $pageTitle  = 'QR — ' . e($room['name']);
        ob_start(); require __DIR__ . '/../Views/rooms/qr.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    private function findOr404(string $id): array {
        $stmt = db()->prepare('SELECT * FROM rooms WHERE id = ?');
        $stmt->execute([$id]);
        $room = $stmt->fetch();
        if (!$room) { http_response_code(404); exit('Room not found.'); }
        return $room;
    }
}
