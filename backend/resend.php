<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Necessary
$USERNAME = (isset($_GET['username']) ? $VALIDATION_MANAGER->test_input($_GET['username']) : '');
$PASSWORD = (isset($_GET['password']) ? $VALIDATION_MANAGER->test_input($_GET['password']) : '');

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_resend($USERNAME, $PASSWORD);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "Resent letter, redirecting you to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>
