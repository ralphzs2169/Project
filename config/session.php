<?php

// 🔐 Secure session settings
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);


session_set_cookie_params([
    'lifetime' => 1800, // 30 minutes
    'domain' => 'localhost',
    'path' => '/',
    'secure' => true, // set to true in production with HTTPS
    'httponly' => true
]);

// ✅ Start session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session regeneration logic (basic and secure)
$interval = 60 * 30; // 30 minutes

if (!isset($_SESSION['last_regeneration'])) {
    regenerate_session_id();
} elseif (time() - $_SESSION['last_regeneration'] >= $interval) {
    regenerate_session_id();
}

function regenerate_session_id()
{
    session_regenerate_id(true); // safely replace session ID
    $_SESSION['last_regeneration'] = time();
}

// function regenerate_session_id()
// {
//     $backup = $_SESSION;                        // 👈 backup current session data
//     session_regenerate_id(true);                // regenerate and delete old session
//     $_SESSION = $backup;                        // 👈 restore data
//     $_SESSION['last_regeneration'] = time();    // update regeneration timestamp
// }
