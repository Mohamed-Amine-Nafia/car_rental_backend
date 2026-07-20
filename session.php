<?php

session_set_cookie_params([
    'lifetime' => 7 * 24 * 60 * 60,
    'path' => '/',
    'domain' => '',
    'secure' => true, // true only in HTTPS
    'httponly' => true,
    'samesite' => 'None'
]);

session_start();
