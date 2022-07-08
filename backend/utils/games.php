<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/protected/menu_data.php';

class GAMES
{
    // This function gets the latest game list for version column
    function table()
    {
        // Import data
        global $MENU_DATA;

        $GAMES = $MENU_DATA['games'];
        $OBJ = [];
    
        foreach (array_keys($GAMES) as $GAME)
        {
            $OBJ[$GAME] = "none";
        } 

        return $OBJ;
    }

    // Same as 'table', but returns it in a string
    function table_string()
    {
        // Get the latest game table
        $GAMES = $this->table();

        // Stringify
        $JSON = json_encode($GAMES);

        return $JSON;
    }

    // This function is in case if a new game gets added, and you need to refresh the user's column without affecting his subscriptions.
    function new_table_string($CURRENT)
    {
        // Get the latest game table
        $GAMES = $this->table();

        // Convert the string into an object
        $PARSED = json_decode($CURRENT, true);
        
        // Get the key list of the two
        $ACTUAL_KEYS = array_keys($GAMES);
        $PARSED_KEYS = array_keys($PARSED);

        // Dummy object to store the differences between the two
        $DUMMY = $PARSED;

        // Check if each key of actual exists in the parsed
        foreach ($ACTUAL_KEYS as $KEY)
        {
            // If this key does not exist in the parsed one, add it to the dummy
            if (in_array($KEY, $PARSED_KEYS) == false)
            {
                $DUMMY[$KEY] = "none";
            }
        }

        // Stringify
        $JSON = json_encode($DUMMY);

        return $JSON;
    }
}

$GAMES_MANAGER = new GAMES;

?>