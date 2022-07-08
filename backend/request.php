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

// Method
$METHOD = (isset($_POST['method']) ? $VALIDATION_MANAGER->test_input($_POST['method']) : '');

// Game
$GAME = (isset($_POST['game']) ? $VALIDATION_MANAGER->test_input($_POST['game']) : '');

// Wallet
$WALLET = (isset($_POST['wallet']) ? $VALIDATION_MANAGER->test_input($_POST['wallet']) : '');

// No data received?
if (empty($USERNAME) || empty($PASSWORD))
{
    die("Username/password are not specified.");
}

// No game?
if (empty($GAME))
{
    die("Specify the game for which you want to buy our product.");
}

// Paying without wallet?
if (empty($WALLET))
{
    die("Please specify the wallet from which you will pay from.");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_request($METHOD, $USERNAME, $PASSWORD, $GAME, $WALLET);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "Request successfully sent, redirecting you to the purchase page in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/purchase");
}
else
{
    die($ATTEMPT['string']);
}

?>
