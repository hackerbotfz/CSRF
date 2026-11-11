<?php
declare(strict_types=1);

/*
 * Site A bootstrap: session handling + database handle.
 * Included by every page in Site A.
 */

ini_set('display_errors', '1');
error_reporting(E_ALL);

// Lab convenience: stops PHP adopting an attacker-supplied session ID.
// Nothing to do with CSRF; the missing token is the bug.
ini_set('session.use_strict_mode', '1');

/*
 * SameSite=Lax is what browsers apply when the attribute is omitted. It is
 * spelled out because it decides whether this lab works: the two servers are
 * different origins but the SAME site (same registrable domain), so Lax still
 * attaches this cookie on the cross-origin form POST.
 * Put Site A on 127.0.0.1 and the attack is blocked by the browser.
 */
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require __DIR__ . '/db.php';
