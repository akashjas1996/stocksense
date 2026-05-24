<?php

class AskController {

    public function index(): void {
        requireLogin();
        if (!isset($_SESSION['ask_history'])) {
            $_SESSION['ask_history'] = [];
        }
        $history   = $_SESSION['ask_history'];
        $bodyClass = 'ask-page';
        $pageTitle = 'Ask AI';
        ob_start(); require __DIR__ . '/../Views/ask.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function query(): void {
        requireLogin();
        header('Content-Type: application/json');

        $question = trim($_POST['question'] ?? '');
        if (!$question) {
            echo json_encode(['error' => 'No question provided.']); return;
        }

        if (!defined('GEMINI_API_KEY') || !GEMINI_API_KEY) {
            echo json_encode(['error' => 'AI not configured. Add GEMINI_API_KEY to config/config.php.']); return;
        }

        $inventory = $this->buildInventoryContext();

        if (!isset($_SESSION['ask_history'])) {
            $_SESSION['ask_history'] = [];
        }

        $systemPrompt =
            "You are a helpful kitchen and pantry assistant for a family home inventory app called StockSense. " .
            "You have real-time access to the family's complete inventory listed below. " .
            "Help with: recipe ideas using what's on hand, shopping priorities, locating items, and expiry management. " .
            "Be concise, warm, and practical. Prefer items they already have. " .
            "Flag expired or expiring-soon items as urgent. Respond in plain English without unnecessary preamble.\n\n" .
            "CURRENT INVENTORY:\n" . $inventory;

        // Gemini alternates user/model — seed with a model acknowledgement
        $contents = [
            ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
            ['role' => 'model', 'parts' => [['text' => 'Got it! I can see the full inventory. What would you like to know?']]],
        ];

        foreach ($_SESSION['ask_history'] as $turn) {
            $contents[] = ['role' => 'user',  'parts' => [['text' => $turn['q']]]];
            $contents[] = ['role' => 'model', 'parts' => [['text' => $turn['a']]]];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $question]]];

        $body = json_encode([
            'contents'         => $contents,
            'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1024],
        ]);

        $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;
        $resp = $this->curlPost($url, $body);
        if ($resp === false) {
            echo json_encode(['error' => 'Could not reach the AI service. Ensure the server can make outbound HTTPS requests (cURL).']); return;
        }

        $data   = json_decode($resp, true);
        $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$answer) {
            $errMsg = $data['error']['message'] ?? 'Unexpected response from AI.';
            echo json_encode(['error' => $errMsg]); return;
        }

        $_SESSION['ask_history'][] = ['q' => $question, 'a' => $answer];
        if (count($_SESSION['ask_history']) > 10) {
            array_shift($_SESSION['ask_history']);
        }

        echo json_encode(['answer' => $answer]);
    }

    public function clearHistory(): void {
        requireLogin();
        $_SESSION['ask_history'] = [];
        redirect('/ask');
    }

    private function curlPost(string $url, string $body): string|false {
        if (!function_exists('curl_init')) return false;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    private function buildInventoryContext(): string {
        $rows = db()->query('
            SELECT
                i.name,
                i.name_en,
                inv.quantity_grams,
                inv.expiry_date,
                r.name  AS room_name,
                c.name  AS container_name
            FROM inventory inv
            JOIN items      i ON i.id  = inv.item_id
            JOIN rooms      r ON r.id  = inv.room_id
            LEFT JOIN containers c ON c.id = inv.container_id
            WHERE inv.quantity_grams > 0
            ORDER BY r.name, c.name, i.name
        ')->fetchAll();

        if (!$rows) return 'No items currently in inventory.';

        $today = date('Y-m-d');
        $warn  = date('Y-m-d', strtotime('+' . EXPIRY_WARN_DAYS . ' days'));
        $lines = [];

        foreach ($rows as $row) {
            $name = $row['name'];
            if ($row['name_en']) $name .= " ({$row['name_en']})";

            $loc = $row['room_name'];
            if ($row['container_name']) $loc .= ' > ' . $row['container_name'];

            $qty = formatWeight((int) $row['quantity_grams']);

            $expiry = '';
            if ($row['expiry_date']) {
                if ($row['expiry_date'] < $today) {
                    $expiry = " | EXPIRED ({$row['expiry_date']})";
                } elseif ($row['expiry_date'] <= $warn) {
                    $expiry = " | Expiring soon ({$row['expiry_date']})";
                } else {
                    $expiry = " | Expires {$row['expiry_date']}";
                }
            }

            $lines[] = "- {$name} | {$loc} | {$qty}{$expiry}";
        }

        return implode("\n", $lines);
    }
}
