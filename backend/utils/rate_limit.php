<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/protected/mysql.php';

// IP
if (!empty($_SERVER['HTTP_CLIENT_IP']))
{
    $IP = $_SERVER['HTTP_CLIENT_IP'];
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
{
    $IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
else
{
    $IP = $_SERVER['REMOTE_ADDR'];
}

// Time
$CURRENT_TIME = time();

// Delete outdated
$RESULTS = DB::query("SELECT * FROM requests WHERE ip=%s", $IP);

foreach ($RESULTS as $RESULT)
{
    $TIME_DIFFERENCE = $CURRENT_TIME - 250;

    if ($RESULT['time'] < $TIME_DIFFERENCE)
    {
        DB::query("DELETE FROM requests WHERE ip=%s", $IP);
    }
}

// User agent
$USER_AGENT = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

// Insert
$TRUE = DB::insert('requests', 
[
    'ip' => $IP,
    'time' => $CURRENT_TIME,
    'agent' => $USER_AGENT
]);

// Rate limited
$MESSAGE = "Unfortunately, you are rate limited. That means that you will have to wait some time before accessing the page again.";

if ($TRUE === true || DB::affectedRows() === 1)
{
    foreach ($RESULTS as $RESULT)
    {
        $HITS = 0;
        foreach ($RESULTS as $RESULT_1)
        {
            if ($RESULT_1["ip"] == $RESULT["ip"])
            {
                $HITS++;
            }
        }

        if ($HITS > 25)
        {
            die($MESSAGE);
        }
    }
}
else
{
    die("An error has occured while trying to update the rate-limiting data, please contact the administrator.");
}

?>
