<?php
// Account manager
include __DIR__ . '/backend/utils/accounts.php';

// Cookies
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
{
    $_SESSION['logged_in'] = null;
    $_SESSION['username'] = null;
    $_SESSION['password'] = null;

    // Log IP
    $ACCOUNT_MANAGER->log_ip($ROW);
    
    // Redirect
    header('Location: /account');
}
else
{
    die("You are not logged into your account.");
}

?>