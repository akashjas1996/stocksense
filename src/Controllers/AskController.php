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

        if (!isset($_SESSION['ask_history'])) {
            $_SESSION['ask_history'] = [];
        }

        $systemPrompt = $this->buildSystemPrompt();

        $contents = [
            ['role' => 'user',  'parts' => [['text' => $systemPrompt]]],
            ['role' => 'model', 'parts' => [['text' => 'Got it! I can see your inventory and locations. How can I help?']]],
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

        $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . GEMINI_API_KEY;
        $resp = $this->curlPost($url, $body);
        if ($resp === false) {
            echo json_encode(['error' => 'Could not reach the AI service. Ensure the server can make outbound HTTPS requests (cURL).']); return;
        }

        $data   = json_decode($resp, true);
        $rawAnswer = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawAnswer) {
            $errMsg = $data['error']['message'] ?? 'Unexpected response from AI.';
            echo json_encode(['error' => $errMsg]); return;
        }

        // Parse and execute any <!--ADD:{...}--> action embedded by the AI
        $action = null;
        $answer = preg_replace_callback(
            '/<!--ADD:(.*?)-->/s',
            function (array $m) use (&$action): string {
                $payload = json_decode(trim($m[1]), true);
                if (is_array($payload)) {
                    $action = $this->executeAddItem($payload);
                }
                return '';   // strip the marker from the displayed text
            },
            $rawAnswer
        );
        $answer = trim($answer);

        // Store raw answer (with marker stripped) in session history
        $_SESSION['ask_history'][] = ['q' => $question, 'a' => $answer];
        if (count($_SESSION['ask_history']) > 10) {
            array_shift($_SESSION['ask_history']);
        }

        $result = ['answer' => $answer];
        if ($action) $result['action'] = $action;
        echo json_encode($result);
    }

    public function clearHistory(): void {
        requireLogin();
        $_SESSION['ask_history'] = [];
        redirect('/ask');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function buildSystemPrompt(): string {
        $inventory = $this->buildInventoryContext();
        $locations = $this->buildLocationsContext();

        return
            "You are a helpful kitchen and pantry assistant for a family home inventory app called StockSense. " .
            "Help with: recipe ideas, shopping priorities, item locations, expiry management, and adding new stock. " .
            "Be concise, warm, and practical. Flag expired or expiring-soon items as urgent.\n\n" .

            "CURRENT INVENTORY:\n{$inventory}\n\n" .

            "AVAILABLE LOCATIONS (use these exact names when adding items):\n{$locations}\n\n" .

            "ADDING ITEMS:\n" .
            "When the user wants to add an item, collect these details step by step — do not ask for everything at once:\n" .
            "  1. Item name (required)\n" .
            "  2. Quantity (required) — accept '2 kg', '500 g', '1.5 litres', '3 pieces' etc.; convert to grams in JSON (1 kg = 1000 g, 1 litre ≈ 1000 g, 1 ml = 1 g, 1 piece = 100 g)\n" .
            "  3. Room (required) — must match one of the room names above exactly\n" .
            "  4. Container (optional) — if the room has containers, offer the list and ask which one; accept 'none' or 'directly in room'\n" .
            "  5. Expiry date (optional) — ask only for perishable items; format YYYY-MM-DD\n\n" .
            "Once you have all required info (name + quantity + room), confirm in one sentence what you are saving, " .
            "then on the very LAST line of your response include this marker exactly:\n" .
            "<!--ADD:{\"item_name\":\"...\",\"room_name\":\"...\",\"container_name\":null,\"quantity_grams\":2000,\"expiry_date\":null}-->\n" .
            "Use JSON null (not the string \"null\") for optional fields not provided. " .
            "Do NOT include the marker until you have all required information.";
    }

    private function executeAddItem(array $p): array {
        $itemName      = trim($p['item_name']      ?? '');
        $roomName      = trim($p['room_name']       ?? '');
        $containerName = trim($p['container_name']  ?? '');
        $grams         = $this->parseGrams($p['quantity_grams'] ?? 0);
        $expiry        = $p['expiry_date'] ?? null;
        if ($expiry && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry)) $expiry = null;

        if (!$itemName) return ['status' => 'error', 'message' => 'Item name is missing.'];
        if (!$roomName) return ['status' => 'error', 'message' => 'Room name is missing.'];
        if ($grams <= 0) return ['status' => 'error', 'message' => 'Quantity could not be parsed.'];

        // Find room
        $stmt = db()->prepare('SELECT id FROM rooms WHERE LOWER(name) = LOWER(?)');
        $stmt->execute([$roomName]);
        $room = $stmt->fetch();
        if (!$room) return ['status' => 'error', 'message' => "Room \"{$roomName}\" not found. Check the available locations."];

        // Find container (optional)
        $containerId = null;
        $containerLabel = null;
        if ($containerName && !in_array(strtolower($containerName), ['null', 'none', '', 'directly in room', 'no container'])) {
            $stmt = db()->prepare('SELECT id, name FROM containers WHERE LOWER(name) = LOWER(?) AND room_id = ?');
            $stmt->execute([$containerName, $room['id']]);
            $c = $stmt->fetch();
            if ($c) {
                $containerId  = $c['id'];
                $containerLabel = $c['name'];
            }
        }

        // Find or create item in catalog
        $itemId = $this->findOrCreateItem($itemName);

        // Insert inventory entry
        db()->prepare('
            INSERT INTO inventory (item_id, room_id, container_id, quantity_grams, arrival_date, expiry_date)
            VALUES (?, ?, ?, ?, CURDATE(), ?)
        ')->execute([$itemId, $room['id'], $containerId, $grams, $expiry]);

        $location = $roomName . ($containerLabel ? " › {$containerLabel}" : '');

        return [
            'status'   => 'success',
            'item'     => $itemName,
            'qty'      => formatWeight($grams),
            'location' => $location,
            'expiry'   => $expiry,
        ];
    }

    private function findOrCreateItem(string $name): int {
        $stmt = db()->prepare('SELECT id FROM items WHERE LOWER(name) = LOWER(?)');
        $stmt->execute([$name]);
        $row = $stmt->fetch();
        if ($row) return (int) $row['id'];

        $stmt = db()->prepare('INSERT INTO items (name) VALUES (?)');
        $stmt->execute([$name]);
        return (int) db()->lastInsertId();
    }

    private function parseGrams(mixed $val): int {
        if (is_numeric($val)) return max(0, (int) round((float) $val));
        $s = strtolower(trim((string) $val));
        if (preg_match('/([\d.]+)\s*kg/',      $s, $m)) return (int) round((float)$m[1] * 1000);
        if (preg_match('/([\d.]+)\s*g\b/',     $s, $m)) return (int) round((float)$m[1]);
        if (preg_match('/([\d.]+)\s*litre?s?/',$s, $m)) return (int) round((float)$m[1] * 1000);
        if (preg_match('/([\d.]+)\s*l\b/',     $s, $m)) return (int) round((float)$m[1] * 1000);
        if (preg_match('/([\d.]+)\s*ml\b/',    $s, $m)) return (int) round((float)$m[1]);
        if (preg_match('/([\d.]+)\s*piece?s?/',$s, $m)) return (int) round((float)$m[1] * 100);
        if (preg_match('/^[\d.]+$/',            $s, $m)) return (int) round((float)$s);
        return 0;
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

    private function buildLocationsContext(): string {
        $rooms = db()->query('SELECT id, name FROM rooms ORDER BY name')->fetchAll();
        if (!$rooms) return 'No rooms set up yet.';

        $containers = db()->query('
            SELECT id, room_id, name FROM containers ORDER BY name
        ')->fetchAll();

        $byRoom = [];
        foreach ($containers as $c) {
            $byRoom[$c['room_id']][] = $c['name'];
        }

        $lines = [];
        foreach ($rooms as $r) {
            $line = "- {$r['name']} [room]";
            if (!empty($byRoom[$r['id']])) {
                $line .= ' → containers: ' . implode(', ', $byRoom[$r['id']]);
            } else {
                $line .= ' → no containers';
            }
            $lines[] = $line;
        }
        return implode("\n", $lines);
    }

    private function buildInventoryContext(): string {
        $rows = db()->query('
            SELECT
                i.name, i.name_en,
                inv.quantity_grams, inv.expiry_date,
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
            $loc  = $row['room_name'];
            if ($row['container_name']) $loc .= ' > ' . $row['container_name'];
            $qty  = formatWeight((int) $row['quantity_grams']);

            $expiry = '';
            if ($row['expiry_date']) {
                if      ($row['expiry_date'] < $today) $expiry = " | EXPIRED ({$row['expiry_date']})";
                elseif  ($row['expiry_date'] <= $warn)  $expiry = " | Expiring soon ({$row['expiry_date']})";
                else                                    $expiry = " | Expires {$row['expiry_date']}";
            }

            $lines[] = "- {$name} | {$loc} | {$qty}{$expiry}";
        }

        return implode("\n", $lines);
    }
}
