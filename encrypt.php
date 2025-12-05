<?php
require_once "conn.php";

function load_keymap($pdo) {
    $stmt = $pdo->query("SELECT original, mapped FROM dynamic_keys");
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['original']] = $row['mapped'];
    }
    return $map;
}

function encrypt_text($text, $map) {
    $output = "";
    foreach (str_split($text) as $ch) {
        $output .= $map[$ch] ?? $ch;
    }
    return $output;
}

$result = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $text = $_POST["text"];
    $map = load_keymap($pdo);
    $result = encrypt_text($text, $map);

    $stmt = $pdo->prepare("
        INSERT INTO encryption_logs (encrypted_text)
        VALUES (:encrypted)
    ");
    $stmt->execute([
        ':encrypted' => $result
    ]);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Encrypt</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">
<h2>Encrypt</h2>

<form method="post">
<label>Input Text</label>
<input type="text" name="text" required>
<button class="btn">Encrypt</button>
</form>

<?php if ($result): ?>
<hr>
<textarea rows="4"><?= htmlspecialchars($result) ?></textarea>
<?php endif; ?>

<a href="index.php">Back</a>
</div>

</body>
</html>
