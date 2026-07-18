<?php

require_once "cors.php";
require_once "session.php";

header("Content-Type: application/json");

/**
 * 1. Clear session data
 */
$_SESSION = [];

/**
 * 2. Delete session cookie (browser side)
 */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/**
 * 3. Destroy server session
 */
session_destroy();

/**
 * 4. Force PHP to stop using old session ID in this request
 */
session_write_close();

/**
 * 5. Return explicit state (don’t be vague)
 */
echo json_encode([
    "authenticated" => false,
    "message" => "Logged out successfully"
]);