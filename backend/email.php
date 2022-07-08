<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Verification code
$CODE = (isset($_GET['code']) ? $VALIDATION_MANAGER->test_input($_GET['code']) : '');

// No key?
if (empty($CODE))
{
    die("Unique identification code was not specified.");
}

// Attempt
$ATTEMPT = $ACCOUNT_MANAGER->attempt_verify($CODE);

if ($ATTEMPT['status'] == true)
{
    // Congratulations
    echo "You have successfully verified your account, redirecting you to the dashboard in 5 seconds.";

    // Redirect
    header("Refresh:5; url=/account");
}
else
{
    die($ATTEMPT['string']);
}

?>
