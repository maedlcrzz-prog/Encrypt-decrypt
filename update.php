<?php
require_once "conn.php";

$message = "";

/* LOAD KEYS */
$stmt = $pdo->query("SELECT * FROM dynamic_keys ORDER BY original ASC");
$keys = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* UPDATE / RESET */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* RESET ALL KEYS */
    if (isset($_POST['reset'])) {
        $pdo->query("UPDATE dynamic_keys SET mapped = original");
        $message = "All keys have been reset.";
    }

    /* UPDATE SINGLE KEY */
    else {
        $orig = $_POST['original'];
        $new  = $_POST['mapped'];

        /* VALIDATE: must be exactly 1 char */
        if (strlen($new) !== 1) {
            $message = "Mapped value must be exactly ONE character.";
        }

        /* VALIDATE: cannot map to itself */
        elseif ($orig === $new) {
            $message = "Mapped value cannot be the SAME as the original.";
        }

        /* VALIDATE: mapped value must not exist already */
        else {
            $check = $pdo->prepare("SELECT COUNT(*) FROM dynamic_keys WHERE mapped = :m");
            $check->execute([':m' => $new]);
            $exists = $check->fetchColumn();

            if ($exists > 0) {
                $message = "The value '$new' is already used. Please select another.";
            } else {

                // UPDATE the key
                $stmt = $pdo->prepare("
                    UPDATE dynamic_keys 
                    SET mapped = :m 
                    WHERE original = :o
                ");
                $stmt->execute([
                    ':m' => $new,
                    ':o' => $orig
                ]);

                $message = "Updated $orig → $new";
            }
        }
    }

    // reload table
    $stmt = $pdo->query("SELECT * FROM dynamic_keys ORDER BY original ASC");
    $keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dynamic Key</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="card">

    <h2>Dynamic Key</h2>

    <form method="post">

        <label>Select Key to Update:</label>
        <select name="original" class="input">
            <?php foreach ($keys as $k): ?>
                <option value="<?= $k['original'] ?>">
                    <?= $k['original'] ?> → <?= $k['mapped'] ?>
                </option>
            <?php endforeach; ?>
        </select> <br><br>

        <label>New Assigned Value:</label><br><br>
        <input type="text" name="mapped" maxlength="1" placeholder="Enter 1 character">

        <button class="btn">Update</button><br>
    </form>

    <form method="post">
        <button name="reset" class="btn reset-btn">Reset All Keys</button><br>
    </form>

    <?php if ($message): ?>
        <p class="success"><?= $message ?></p>
    <?php endif; ?>

    <hr>

    <h3>Current Mappings</h3>
    <p class="muted">
        <?php foreach ($keys as $k): ?>
            <?= $k['original'] ?> → <?= $k['mapped'] ?><br>
        <?php endforeach; ?>
    </p>

    <a href="index.php" class="landing-btn">← Back</a>

</div>

</body>
</html>
