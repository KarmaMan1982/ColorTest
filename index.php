<?php
declare(strict_types=1);

/**
 * Simple color picker with JSON persistence
 * - Validates hex color (#fff or #ffffff)
 * - Escapes output for HTML
 * - Uses LOCK_EX for atomic writes
 * - Shows user-friendly messages and accessible markup
 */

$defaultColor = '#ff0080';
$colorFile = __DIR__ . '/color.json';
$errors = [];
$success = false;
$TestColor = $defaultColor;

// Load saved color if file exists and is readable
if (is_readable($colorFile)) {
    $raw = file_get_contents($colorFile);
    $data = json_decode($raw, true);
    if (is_array($data) && !empty($data['TestColor']) && preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $data['TestColor'])) {
        $TestColor = $data['TestColor'];
    }
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw input and sanitize minimally (we validate with regex later)
    #$inputColor = filter_input(INPUT_POST, 'tbTestColor', FILTER_SANITIZE_STRING) ?? '';
    $inputColor = $_POST['tbTestColor'] ?? '';
    $inputColor = trim((string)$inputColor);

    // Validate hex color (#fff or #ffffff)
    if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $inputColor)) {
        $errors[] = 'Ungültiger Farbwert. Bitte ein Hex-Farbformat wie #ff0080 oder #f08 verwenden.';
    } else {
        $payload = ['TestColor' => $inputColor];
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $errors[] = 'Fehler beim Erstellen der JSON-Daten.';
        } else {
            $written = @file_put_contents($colorFile, $json, LOCK_EX);
            if ($written === false) {
                $errors[] = 'Fehler beim Speichern der Datei. Schreibrechte prüfen.';
            } else {
                $success = true;
                $TestColor = $inputColor;
            }
        }
    }
}

// Helper for safe HTML output
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <title>Color-Test</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        :root { --color: <?= h($TestColor) ?>; }
        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; padding: 1rem; }
        #fieldTestColor { background: var(--color); padding: 1rem; border-radius: 6px; max-width: 480px; }
        #lgTestColor { background: #000; color: #fff; padding: 0.25rem 0.5rem; display: inline-block; border-radius: 4px; }
        .controls { margin-top: 0.75rem; display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap; }
        .msg { margin-top:0.75rem; }
        .error { color: #b00020; }
        .success { color: #006400; }
        input[type="color"] { width: 3rem; height: 2.25rem; border: none; padding:0; background: transparent; }
        input[type="text"] { padding:0.35rem; border-radius:4px; border:1px solid #ccc; }
        button { padding:0.45rem 0.75rem; border-radius:4px; border:1px solid #333; background:#fff; cursor:pointer; }
    </style>
</head>
<body>
    <form action="" method="post" novalidate>
        <fieldset id="fieldTestColor" aria-describedby="status">
            <legend id="lgTestColor">Choose your color</legend>

            <div class="controls">
                <label for="tbTestColor">Farbauswahl:</label>
                <input id="tbTestColor" name="tbTestColor" type="color" value="<?= h($TestColor) ?>" aria-label="Farbwähler">
                <input id="tbTestColorText" name="tbTestColorText" type="text" value="<?= h($TestColor) ?>" pattern="#[A-Fa-f0-9]{3}([A-Fa-f0-9]{3})?" title="#rrggbb oder #rgb" aria-hidden="true" style="width:6.5rem;">
                <button type="submit">Farbe testen</button>
            </div>

            <div id="status" class="msg" aria-live="polite">
                <?php if ($success): ?>
                    <div class="success">Farbe erfolgreich gespeichert: <strong><?= h($TestColor) ?></strong></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <?php foreach ($errors as $err): ?>
                        <div class="error"><?= h($err) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </fieldset>
    </form>

    <script>
    // Keep the text input in sync with the color input for users who want the hex value
    (function(){
        const color = document.getElementById('tbTestColor');
        const text = document.getElementById('tbTestColorText');
        if (!color || !text) return;
        color.addEventListener('input', () => text.value = color.value);
        text.addEventListener('input', () => {
            // basic client-side validation for UX; server-side validation remains authoritative
            if (/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(text.value)) {
                color.value = text.value;
            }
        });
    })();
    </script>
</body>
</html>
