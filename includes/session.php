<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// KOHA MAX PA AKTIVITET (15 minuta)
$timeout = 15 * 60;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit;
}

// Update aktiviteti
$_SESSION['last_activity'] = time();