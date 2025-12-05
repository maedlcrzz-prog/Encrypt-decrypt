<?php
require_once "conn.php";

function load_reverse_keymap($pdo) {
    $stmt = $pdo->query("SELECT original, mapped FROM dynamic_keys");
    $map = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $map[$row['mapped']] = $row['original'];
    }
    return $map;
}

function decrypt_text($text, $map) {
    $output = "";
    foreach (str_split($text) as $ch) {
        $output .= $map[$ch] ?? $ch;
    }
    return $output;
}

$result = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = $_POST["input"];
    $map = load_reverse_keymap($pdo);
    $result = decrypt_text($input, $map);
    
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Decrypt</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="card">
<h2>Decrypt</h2>

<form method="post">
<label>Encrypted Text</label>
<textarea name="input" rows="3"></textarea>
<button class="btn">Decrypt</button>
</form>

<?php if ($result !== ""): ?>
<hr>
<textarea rows="4"><?= htmlspecialchars($result) ?></textarea>
<?php endif; ?>

<a href="welcomepage.php">Back</a>
</div>

</body>
</html>

