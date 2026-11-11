<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$user    = require_login();
$message = take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Site A &middot; Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <span class="brand">Site A &middot; Online Bank</span>
        <span class="who"><?= h($user['username']) ?> &middot; <a href="logout.php">Sign out</a></span>
    </header>

    <main class="page">
        <?php if ($message !== null): ?>
            <div class="flash-ok"><?= h($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <p class="balance-label">Available balance</p>
            <p class="balance"><?= h(money((int) $user['balance_cents'])) ?></p>
        </div>

        <div class="card">
            <h2>Transfer money</h2>
            <form method="post" action="transfer.php">
                <label for="to">Recipient username</label>
                <input id="to" name="to" type="text" placeholder="e.g. attacker" required>

                <label for="amount">Amount (USD)</label>
                <input id="amount" name="amount" type="number" step="0.01" min="0.01" placeholder="0.00" required>

                <button type="submit">Send money</button>
            </form>
        </div>
    </main>
</body>
</html>
