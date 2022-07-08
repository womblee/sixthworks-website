<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Data
require __DIR__ . '/protected/menu_data.php';

// Dummy
$ARRAY = [];

// Game list
$GAMES = $MENU_DATA['games'];

// Fill the dummy array with needed data
foreach (array_keys($GAMES) as $GAME)
{
    $VALUE = $GAMES[$GAME];
    
    $ARRAY[$GAME] = 
    [
        "name" => $VALUE['full_name'],
        "last_update" => $VALUE['last_update'],
        "file_info" => $VALUE['file_info'],
    ];
}

// Stringify
$JSON = json_encode($ARRAY);

// Echo
echo $JSON;

?>