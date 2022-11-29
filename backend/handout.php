<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Data
require __DIR__ . '/protected/menu_data.php';

// Only data we have
$SECRET = (isset($_GET['secret']) ? $VALIDATION_MANAGER->test_input($_GET['secret']) : '');

// Needed game
$GAME = (isset($_GET['game']) ? $VALIDATION_MANAGER->test_input($_GET['game']) : '');

// This will be given to the client
$ARRAY =
[
    "status" => "fail",
    "hwid" => "",
];

// Little & comfortable function
function json_die()
{
    global $ARRAY;
    
    $JSON = json_encode($ARRAY);

    die($JSON);
}

// No data received?
if (empty($SECRET))
{
    $ARRAY['error'] = "Secret was not specified.";
    json_die();
}

// Ugh we still need a damn game
if (empty($GAME))
{
    $ARRAY['error'] = "Game was not specified.";
    json_die();
}

// Row
$ROW = DB::queryFirstRow("SELECT * FROM secrets WHERE secret=%s AND game=%s", $SECRET, $GAME);

// Valid?
if ($ROW == null)
{
    $ARRAY['error'] = "Provided secret is not valid for this game.";
    json_die();
}

// Keys are not permanent
$CURRENT_TIME = time();

if ($CURRENT_TIME - $ROW['time'] > 180) // 3 minutes is enough for the user, I think
{
    // Erase the key for not wasting space on useless things
    DB::query("DELETE FROM secrets WHERE secret=%s", $SECRET);

    // Give out error
    $ARRAY['error'] = "This secret is no longer valid.";
    json_die();
}

// Games
$GAMES = $MENU_DATA['games'];

if (array_key_exists($GAME, $GAMES))
{
    // Getting his god damn row
    $ACCOUNT = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $ROW['user']);

    // Subscriptions
    $HAS = $ACCOUNT_MANAGER->has_game_version($ACCOUNT, $GAME);

    // Has one?
    if ($HAS['status'] == true)
    {
        // Cool result
        $ARRAY['status'] = "success";
        $ARRAY['hwid'] = $ACCOUNT['hwid'];

        // Now get rid of that
        DB::query("DELETE FROM secrets WHERE secret=%s", $SECRET);
    }
    else
    {
        $ARRAY['error'] = "Your account does not have any subscriptions for this game.";
        json_die();
    }
}
else
{
    $ARRAY['error'] = "This game is not present in our database.";
    json_die();
}

// Final death
json_die();

?>
