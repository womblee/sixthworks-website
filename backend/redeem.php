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
    $PASSWORD = (isset($_SESSION['password']) ? $_SESSION['password'] : '');
}
else
{
    $USERNAME = (isset($_POST['username']) ? $VALIDATION_MANAGER->test_input($_POST['username']) : '');
    $PASSWORD = (isset($_POST['password']) ? $VALIDATION_MANAGER->test_input($_POST['password']) : '');
}

// License key
$KEY = (isset($_POST['key']) ? $VALIDATION_MANAGER->test_input($_POST['key']) : '');

// Game
$GAME = (isset($_POST['game']) ? $VALIDATION_MANAGER->test_input($_POST['game']) : '');

// No data received?
if (empty($USERNAME) || empty($PASSWORD))
{
    die("Username/password are not specified.");
}

// No key? No redeem
if (empty($KEY))
{
    die("License key is not specified.");
}

// No game?
if (empty($GAME))
{
    die("Please specify the game that you are trying to redeem the key of.");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_redeem($USERNAME, $PASSWORD, $GAME, $KEY);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "You have successfully redeemed a key, redirecting you to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>
