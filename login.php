<?php
// Security
require __DIR__ . '/backend/utils/rate_limit.php';
require __DIR__ . '/backend/utils/recaptcha.php';

// Account manager
include __DIR__ . '/backend/utils/accounts.php';

// Cookies
session_start();

// Already?
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
{
    // Redirect
    header('Location: /account');
}

// Necessary
$LOGIN = (isset($_POST['login']) ? $VALIDATION_MANAGER->test_input($_POST['login']) : '');
$PASSWORD = (isset($_POST['password']) ? $VALIDATION_MANAGER->test_input($_POST['password']) : '');

// No login?
if (empty($LOGIN))
{
    die("Login was not specified, please enter your username or email.");
}

// No password?
if (empty($PASSWORD))
{
    die("Password was not specified, please enter the password of your account.");
}

// Email?
$EMAIL = $VALIDATION_MANAGER->email($LOGIN);

// Choice
$CHOICE = $EMAIL ? 'email' : 'username';

// Row
$ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE $CHOICE=%s", $LOGIN);

if ($ROW == null)
{
    die("Unfortunately, the account that you are trying to login into is not valid.");
}

// Did the user specify the correct password?
$COMPARISON = $ACCOUNT_MANAGER->compare_passwords($ROW, $PASSWORD);

if ($COMPARISON == false)
{
    die("The entered password is incorrect.");
}

// Log IP
$ACCOUNT_MANAGER->log_ip($ROW);

// Cookies
$_SESSION['logged_in'] = true;
$_SESSION['username'] = $ROW['username'];
$_SESSION['password'] = $PASSWORD;

// Don't remember
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect
header('Location: /account');

?>