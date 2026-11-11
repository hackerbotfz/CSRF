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

$seed = [
    ['alice',   'alice123',   100000], // $1,000.00
    ['bob', 'bob@123',      0], // $0.00
];

$stmt = $pdo->prepare(
    'INSERT INTO users (username, password_hash, balance_cents) VALUES (?, ?, ?)'
);

foreach ($seed as [$username, $password, $balance]) {
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $balance]);
}

$users = $pdo->query('SELECT username, balance_cents FROM users ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Site A &middot; Setup</title>
    <style>
        body { font: 15px/1.6 system-ui, sans-serif; max-width: 34rem; margin: 4rem auto; padding: 0 1rem; color: #1c1c1e; }
        code { background: #f2f2f4; padding: .1rem .3rem; border-radius: 3px; }
        table { border-collapse: collapse; margin: 1rem 0; width: 100%; }
        th, td { text-align: left; padding: .35rem .5rem; border-bottom: 1px solid #e2e2e6; }
        th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #666; }
        .ok { border-left: 3px solid #2e7d32; padding: .6rem 0 .6rem 1rem; background: #f3faf3; }
        p.warn { color: #8a4b00; font-size: 14px; }
        a { color: #0b57d0; }
    </style>
</head>
<body>
    <h1>Database ready</h1>

    <div class="ok">
        <p><code>users</code> table created at <code><?= h(DB_PATH) ?></code></p>
    </div>

    <table>
        <thead>
            <tr><th>Username</th><th>Password</th><th>Balance</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= h($u['username']) ?></td>
                <td><code><?= h($u['username'] === 'victim' ? 'victim123' : 'attacker123') ?></code></td>
                <td><?= h(money((int) $u['balance_cents'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <p class="warn">Re-running this page wipes both balances and restores the original
    amounts &mdash; use it to reset the lab between attack runs.</p>

    <p><a href="login.php">Go to the login page &rarr;</a></p>
</body>
</html>
