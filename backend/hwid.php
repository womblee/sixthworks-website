<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Cookies
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
{
    $USERNAME = (isset($_SESSION['username']) ? $_SESSION['username'] : '');
    $PASSWORD = (isset($_SESSION['password']) ? $_SESSION['password'] : '');
}
else
{
    $USERNAME = (isset($_GET['username']) ? $VALIDATION_MANAGER->test_input($_GET['username']) : '');
    $PASSWORD = (isset($_GET['password']) ? $VALIDATION_MANAGER->test_input($_GET['password']) : '');
}

// HWID
$HWID = (isset($_GET['hwid']) ? $VALIDATION_MANAGER->test_input($_GET['hwid']) : '');

// No data received?
if (empty($USERNAME) || empty($PASSWORD))
{
    die("Username/password are not specified.");
}

// No HWID?
if (empty($HWID))
{
    die("HWID was not specified, can't do anything without it.");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_hwid($USERNAME, $PASSWORD, $HWID);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "HWID successfully reset, redirecting you to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>
