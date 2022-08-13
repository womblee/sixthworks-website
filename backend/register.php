<?php
// Security
require __DIR__ . '/utils/rate_limit.php';
require __DIR__ . '/utils/recaptcha.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Necessary
$USERNAME = (isset($_POST['username']) ? $VALIDATION_MANAGER->test_input($_POST['username']) : '');
$PASSWORD = (isset($_POST['password']) ? $VALIDATION_MANAGER->test_input($_POST['password']) : '');

// Email
$EMAIL = (isset($_POST['email']) ? $VALIDATION_MANAGER->test_input($_POST['email']) : '');

// No data received?
if (empty($USERNAME) || empty($PASSWORD))
{
    die("Username/password are not specified.");
}

// No email?
if (empty($EMAIL))
{
    die("Email was not specified, specify the email and try registering again.");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_registration($USERNAME, $PASSWORD, $EMAIL);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "You have successfully registered an account, look into your email inbox for an account verification link, redirecting to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>