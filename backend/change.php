<?php
// Security
require __DIR__ . '/utils/rate_limit.php';
require __DIR__ . '/utils/recaptcha.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Cookies
session_start();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
{
    $USERNAME = (isset($_SESSION['username']) ? $_SESSION['username'] : '');
}
else
{
    $USERNAME = (isset($_POST['username']) ? $VALIDATION_MANAGER->test_input($_POST['username']) : '');
}

// Manual password input
$PASSWORD = (isset($_POST['password']) ? $VALIDATION_MANAGER->test_input($_POST['password']) : '');

// New password
$NEW = (isset($_POST['new']) ? $VALIDATION_MANAGER->test_input($_POST['new']) : '');

// No username?
if (empty($USERNAME))
{
    die("Username/password are not specified.");
}

// No password?
if (empty($USERNAME))
{
    die("Password was not specified.");
}

// How should we reset without the new variant?
if (empty($NEW))
{
    die("New password was not specified.");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_change($USERNAME, $PASSWORD, $NEW);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "Password successfully changed, redirecting you to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>
