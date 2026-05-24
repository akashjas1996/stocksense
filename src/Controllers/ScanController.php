<?php

class ScanController {

    public function index(): void {
        requireLogin();
        $pageTitle = 'Scan';
        ob_start(); require __DIR__ . '/../Views/scan/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    /**
     * Called when a location QR is scanned.
     * ?qr=UUID → resolves to room or container and redirects.
     */
    public function location(): void {
        $qr = trim($_GET['qr'] ?? '');
        if (!$qr) redirect('/scan');

        // Redirect to the public location landing page
        header('Location: ' . APP_URL . '/location/' . urlencode($qr));
        exit;
    }

    /**
     * Called via AJAX when a product barcode is scanned.
     * ?barcode=EAN → returns JSON with item info (from DB or Open Food Facts).
     */
    public function product(): void {
        requireLogin();
        header('Content-Type: application/json');
        $barcode = trim($_GET['barcode'] ?? '');
        if (!$barcode) { echo json_encode(['error' => 'No barcode']); exit; }

        // Check local DB first
        $stmt = db()->prepare('SELECT * FROM items WHERE product_barcode = ?');
        $stmt->execute([$barcode]);
        $item = $stmt->fetch();
        if ($item) {
            echo json_encode(['source' => 'local', 'name' => $item['name'], 'barcode' => $barcode]);
            exit;
        }

        // Open Food Facts API
        $url      = "https://world.openfoodfacts.org/api/v0/product/{$barcode}.json";
        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if (($data['status'] ?? 0) === 1) {
                $name = $data['product']['product_name'] ?? $data['product']['product_name_en'] ?? '';
                if ($name) {
                    echo json_encode(['source' => 'openfoodfacts', 'name' => $name, 'barcode' => $barcode]);
                    exit;
                }
            }
        }

        echo json_encode(['source' => 'unknown', 'name' => '', 'barcode' => $barcode]);
    }
}
