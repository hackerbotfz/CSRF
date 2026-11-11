<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$dataDir = __DIR__ . '/data';

if (!is_dir($dataDir) && !mkdir($dataDir, 0775, true) && !is_dir($dataDir)) {
    http_response_code(500);
    exit('Could not create the data directory: ' . h($dataDir));
}

$pdo = db();

// Clean slate: re-running this page resets the lab between attempts.
$pdo->exec('DROP TABLE IF EXISTS users');
$pdo->exec(
    'CREATE TABLE users (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        username      TEXT    NOT NULL UNIQUE,
        password_hash TEXT    NOT NULL,
        balance_cents INTEGER NOT NULL DEFAULT 0
    )'
);

// Plaintext passwords live here only so this page can display them below.
$seed = [
    ['victim',   'victim123',   100000], // $1,000.00
    ['attacker', 'attacker123', 0],      // $0.00
];

$stmt = $pdo->prepare(
    'INSERT INTO users (username, password_hash, balance_cents) VALUES (?, ?, ?)'
);

foreach ($seed as [$username, $password, $balance]) {
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $balance]);
}

$passwords = array_column($seed, 1, 0);
$users = $pdo->query('SELECT username, balance_cents FROM users ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site A &middot; Setup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <span class="brand">Site A &middot; Setup</span>
        <a href="login.php">Sign in &rarr;</a>
    </header>

    <main class="page">
        <div class="card">
            <h1>Database ready</h1>
            <p class="muted"><code>users</code> table created at <code><?= h(DB_PATH) ?></code></p>

            <table class="data">
                <thead>
                    <tr><th>Username</th><th>Password</th><th>Balance</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= h($u['username']) ?></td>
                        <td><code><?= h($passwords[$u['username']] ?? '?') ?></code></td>
                        <td><?= h(money((int) $u['balance_cents'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <p class="muted">Re-running this page wipes the balances and restores the
            seeded amounts; use it to reset the lab between attack runs.</p>
        </div>
    </main>
</body>
</html>
