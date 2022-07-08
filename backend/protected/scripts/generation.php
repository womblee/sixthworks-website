<?php
require(dirname(__DIR__, 1) . "/mysql.php");
require(dirname(__DIR__, 2) . "/utils/other.php");

function random_string($LENGTH = 10) 
{
    $CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $CHARACTERS_LENGTH = strlen($CHARACTERS);
    $STR = '';

    for ($i = 0; $i < $LENGTH; $i++)
    {
        $STR .= $CHARACTERS[rand(0, $CHARACTERS_LENGTH - 1)];
    }

    return $STR;
}

$VERSION = $argv[1];
$GAME = $argv[2];
$AMOUNT = $argv[3];

if (empty($VERSION) || empty($GAME))
{
    die();
}

if (empty($AMOUNT))
{
    $AMOUNT = 25;
}

for ($i = 0; $i < $AMOUNT; $i++)
{
    // Key
    $STR = strtoupper($GAME) . "_" . random_string(18);

    // Print
    $COUNT = $i + 1;
    
    echo "#$COUNT - $STR\n";

    // Insert
    $INSERT =
    [
        'code' => $STR,
        'version' => $VERSION,
        'game' => $GAME,
    ];

    $TRUE = DB::insert('redeem_keys', $INSERT);
}

echo "Generated with success";