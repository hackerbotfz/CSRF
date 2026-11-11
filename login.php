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
    <title>Site A &middot; Sign in</title>
    <style>
        body { font: 15px/1.6 system-ui, sans-serif; max-width: 24rem; margin: 6rem auto; padding: 0 1rem; color: #1c1c1e; }
        label { display: block; margin: 1rem 0 .25rem; font-weight: 600; }
        input { width: 100%; box-sizing: border-box; padding: .5rem; border: 1px solid #c8c8cc; border-radius: 4px; font: inherit; }
        button { margin-top: 1.25rem; width: 100%; padding: .6rem; border: 0; border-radius: 4px; background: #0b57d0; color: #fff; font: inherit; cursor: pointer; }
        .err { background: #fdecec; border-left: 3px solid #c62828; padding: .5rem .75rem; margin-bottom: 1rem; }
        .hint { color: #555; font-size: 13px; margin-top: 1.5rem; }
        code { background: #f2f2f4; padding: .1rem .3rem; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Site A &middot; Online Bank</h1>
    <p>Sign in to view your balance and send money.</p>

    <?php if ($error !== null): ?>
        <div class="err"><?= h($error) ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input id="username" name="username" value="<?= h($username) ?>" autocomplete="username" autofocus>

        <label for="password">Password</label>
        <input id="password" name="password" type="password" autocomplete="current-password">

        <button type="submit">Sign in</button>
    </form>

    <p class="hint">Lab accounts: <code>victim</code> / <code>victim123</code>
    &middot; <code>attacker</code> / <code>attacker123</code></p>
</body>
</html>
