<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

/*
 * State-changing endpoint, intentionally vulnerable to CSRF.
 *
 * What it checks:     a valid session (is anyone signed in?).
 * What it does NOT check:
 *   - a per-session anti-CSRF token
 *   - the Origin / Referer header of the request
 *
 * Any page on any origin can therefore make a signed-in browser POST here,
 * and the browser will attach the Site A session cookie automatically.
 */

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$to        = trim((string) ($_POST['to'] ?? ''));
$amountRaw = trim((string) ($_POST['amount'] ?? ''));

if ($to === '' || $amountRaw === '' || !is_numeric($amountRaw)) {
    flash('Enter a recipient and a numeric amount.');
    header('Location: dashboard.php');
    exit;
}

$amountCents = (int) round((float) $amountRaw * 100);

if ($amountCents <= 0) {
    flash('The amount must be greater than zero.');
    header('Location: dashboard.php');
    exit;
}

if (strcasecmp($to, (string) $user['username']) === 0) {
    flash('You cannot transfer money to yourself.');
    header('Location: dashboard.php');
    exit;
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Re-read the sender inside the transaction so a stale balance can't be spent twice.
    $stmt = $pdo->prepare('SELECT balance_cents FROM users WHERE id = ?');
    $stmt->execute([(int) $user['id']]);
    $senderBalance = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
    $stmt->execute([$to]);
    $recipientId = $stmt->fetchColumn();

    if ($recipientId === false) {
        throw new RuntimeException('No such recipient: ' . $to);
    }

    if ($senderBalance < $amountCents) {
        throw new RuntimeException('Insufficient funds.');
    }

    $stmt = $pdo->prepare('UPDATE users SET balance_cents = balance_cents - ? WHERE id = ?');
    $stmt->execute([$amountCents, (int) $user['id']]);

    $stmt = $pdo->prepare('UPDATE users SET balance_cents = balance_cents + ? WHERE id = ?');
    $stmt->execute([$amountCents, (int) $recipientId]);

    $pdo->commit();

    flash('Sent ' . money($amountCents) . ' to ' . $to . '.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    flash('Transfer failed: ' . $e->getMessage());
}

header('Location: dashboard.php');
exit;
