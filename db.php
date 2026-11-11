<?php
declare(strict_types=1);

const DB_PATH = __DIR__ . '/data/app.db';

/**
 * Single shared PDO connection to the SQLite file.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        // Serialise writers so the balance check and the update can't interleave.
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }

    return $pdo;
}

/**
 * The logged-in user row, or null when there is no session.
 */
function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT id, username, balance_cents FROM users WHERE id = ?');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $row = $stmt->fetch();

    return $row === false ? null : $row;
}

/**
 * Gate a page behind the session cookie. This is the only check protecting
 * the authenticated pages and, by design, the only one on transfer.php.
 */
function require_login(): array
{
    $user = current_user();

    if ($user === null) {
        header('Location: login.php');
        exit;
    }

    return $user;
}

/**
 * Format integer cents as dollars.
 */
function money(int $cents): string
{
    return '$' . number_format($cents / 100, 2);
}

/**
 * HTML-escape helper.
 */
function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * One-shot flash message stored in the session.
 */
function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function take_flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return $message === null ? null : (string) $message;
}
