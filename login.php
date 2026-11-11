<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// Already signed in? Nothing to do here.
if (current_user() !== null) {
    header('Location: dashboard.php');
    exit;
}

$error    = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if ($row !== false && password_verify($password, $row['password_hash'])) {
        // The session cookie is minted here. From now on the browser attaches
        // it to every request for this host -- including forged ones.
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $row['id'];

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site A &middot; Sign in</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-wrap">
        <div class="card auth-card">
            <h1>Site A &middot; Online Bank</h1>
            <p class="muted">Sign in to view your balance and send money.</p>

            <?php if ($error !== null): ?>
                <div class="flash-err"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <label for="username">Username</label>
                <input id="username" name="username" value="<?= h($username) ?>" autocomplete="username" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>

                <button type="submit" class="block">Sign in</button>
            </form>

            <p class="muted">Lab accounts: <code>victim</code> / <code>victim123</code>
            &middot; <code>attacker</code> / <code>attacker123</code></p>
        </div>
    </div>
</body>
</html>
