<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Data
include __DIR__ . '/protected/menu_data.php';

// Echo
echo $MENU_DATA['launcher_version'];

?>
