<?php
// Security
require __DIR__ . '/utils/rate_limit.php';

// Account manager
include __DIR__ . '/utils/accounts.php';

// Data
require __DIR__ . '/protected/menu_data.php';

// Credentials
$USERNAME = (isset($_GET['username']) ? $VALIDATION_MANAGER->test_input($_GET['username']) : '');
$PASSWORD = (isset($_GET['password']) ? $VALIDATION_MANAGER->test_input($_GET['password']) : '');

// Game
$GAME = (isset($_GET['game']) ? $VALIDATION_MANAGER->test_input($_GET['game']) : '');

// This will be given to the client
$ARRAY =
[
    "status" => "fail",
    "secret" => "none",
    "error" => "",
];

// Little & comfortable function
function json_die()
{
    global $ARRAY;
    
    $JSON = json_encode($ARRAY);

    die($JSON);
}

// No data received?
if (empty($USERNAME) || empty($PASSWORD))
{
    $ARRAY['error'] = "Username/password are not specified.";
    json_die();
}

// Can we do it without the game?
if (empty($GAME))
{
    $ARRAY['error'] = "Game was not specified.";
    json_die();
}

// Row
$ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

// Valid?
if ($ROW == null)
{
    $ARRAY['error'] = "Provided account does not exist.";
    json_die();
}

// Log IP
$ACCOUNT_MANAGER->log_ip($ROW);

// Did the user specify the correct password?
$COMPARISON = $ACCOUNT_MANAGER->compare_passwords($ROW, $PASSWORD);
if ($COMPARISON == false)
{
    $ARRAY['error'] = "Provided password is incorrect.";
    json_die();
}

// Games
$GAMES = $MENU_DATA['games'];

if (array_key_exists($GAME, $GAMES))
{
    // Subscriptions
    $HAS = $ACCOUNT_MANAGER->has_game_version($ROW, $GAME);

    // Has one?
    if ($HAS['status'] == true)
    {
        // Gift a user some random key thingy
        $KEY = "none";
        {
            // Current time
            $NOW = time();
    
            // Size for generation
            $SIZE = 36;
    
            // Unique secret
            $KEY = strtoupper($OTHER_MANAGER->random_string($SIZE));
    
            // Validate
            while (true)
            {
                // Rows
                $QUERY = DB::query("SELECT * FROM secrets WHERE secret=%s", $KEY);
    
                // Generate again
                if (count($QUERY) > 0)
                {
                    $KEY = strtoupper($OTHER_MANAGER->random_string($SIZE));
                }
                else
                {
                    break;
                }
            }
    
            // Row
            $INSERT =
            [
                'secret' => $KEY,
                'user' => $USERNAME,
                'game' => $GAME,
                'time' => $NOW,
            ];
        
            // Insert
            $STATUS = DB::insert('secrets', $INSERT);
        }

        // Give out
        $ARRAY['status'] = "success";
        $ARRAY['secret'] = $KEY;
        $ARRAY['error'] = "";
    }
    else
    {
        $ARRAY['error'] = "Your account does not have any subscriptions for this game.";
        json_die();
    }
}
else
{
    $ARRAY['error'] = "This game does not exist.";
    json_die();
}

// Final death
json_die();

?>
