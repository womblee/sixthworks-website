<?php
// Security
require __DIR__ . '/utils/rate_limit.php';
require __DIR__ . '/utils/recaptcha.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Cookies
session_start();

// Email
$EMAIL = (isset($_POST['email']) ? $VALIDATION_MANAGER->test_input($_POST['email']) : '');

// No data received?
if (empty($EMAIL))
{
    die("Please specify the email, reset can't be done without it..");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_reset($EMAIL);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "Reset request has been successfully sent to your email, redirecting you to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>
