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
    <title>Site A &middot; Dashboard</title>
    <style>
        body { font: 15px/1.6 system-ui, sans-serif; max-width: 34rem; margin: 4rem auto; padding: 0 1rem; color: #1c1c1e; }
        header { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 1px solid #e2e2e6; padding-bottom: .75rem; }
        h1 { font-size: 1.25rem; margin: 0; }
        h2 { font-size: .95rem; margin: 2rem 0 .5rem; }
        .who { color: #555; font-size: 13px; }
        .balance { font-size: 2rem; font-weight: 600; margin: .25rem 0 0; }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #666; }
        label { display: block; margin: 1rem 0 .25rem; font-weight: 600; }
        input { width: 100%; box-sizing: border-box; padding: .5rem; border: 1px solid #c8c8cc; border-radius: 4px; font: inherit; }
        button { margin-top: 1rem; padding: .55rem 1.1rem; border: 0; border-radius: 4px; background: #0b57d0; color: #fff; font: inherit; cursor: pointer; }
        .ok { background: #f3faf3; border-left: 3px solid #2e7d32; padding: .5rem .75rem; margin: 1rem 0; }
        .note { background: #fff8e6; border-left: 3px solid #b8860b; padding: .6rem .9rem; font-size: 13px; margin-top: 2rem; }
        a { color: #0b57d0; }
        code { background: #f2f2f4; padding: .1rem .3rem; border-radius: 3px; }
    </style>
</head>
<body>
    <header>
        <h1>Site A &middot; Online Bank</h1>
        <span class="who"><?= h($user['username']) ?> &middot; <a href="logout.php">Sign out</a></span>
    </header>

    <?php if ($message !== null): ?>
        <div class="ok"><?= h($message) ?></div>
    <?php endif; ?>

    <p class="label">Available balance</p>
    <p class="balance"><?= h(money((int) $user['balance_cents'])) ?></p>

    <h2>Transfer money</h2>
    <form method="post" action="transfer.php">
        <label for="to">Recipient username</label>
        <input id="to" name="to" type="text" placeholder="e.g. attacker" required>

        <label for="amount">Amount (USD)</label>
        <input id="amount" name="amount" type="number" step="0.01" min="0.01" placeholder="0.00" required>

        <button type="submit">Send money</button>
    </form>
</body>
</html>
