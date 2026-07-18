<?php

session_set_cookie_params([
    'lifetime' => 7 * 24 * 60 * 60,
    'path' => '/',
    'domain' => '',
    'secure' => false, // true only in HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();