<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Wanted
$WANTED = (isset($_GET['wanted']) ? $VALIDATION_MANAGER->test_input($_GET['wanted']) : '');

// Data
if (array_key_exists($WANTED, $CRC_DATA))
{
	// Stringify
    $JSON = json_encode($CRC_DATA[$WANTED]);

    // Echo
    echo $JSON;
}

?>