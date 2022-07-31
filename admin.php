<!DOCTYPE html>
<html>
<head>
  <!-- Title bar -->
  <title>Sixthworks</title>
  <link rel="icon" href="media/sixthworks_transparent.ico" type="image/x-icon">

  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">

  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="css/main.css">

  <!-- JS -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    
<?php
include __DIR__ . '/backend/protected/mysql.php';
include __DIR__ . '/backend/protected/menu_data.php';

// Cookies
session_start();

// Data
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
{
    $USERNAME = (isset($_SESSION['username']) ? $_SESSION['username'] : '');
    $PASSWORD = (isset($_SESSION['password']) ? $_SESSION['password'] : '');
}
else
{
    $USERNAME = '';
    $PASSWORD = '';
}

// Necessary
$GOTO = (isset($_GET['u']) ? $_GET['u'] : '');
$OFFSET = (isset($_GET['o']) ? $_GET['o'] : '');

// 0 by default
if (empty($OFFSET))
{
    $OFFSET = 0;
}

// Error
$ERROR = "<div class='container-fluid mt-3'><img src='media/sixthworks_evil.png' class='mb-2'><p><strong>Oh no</strong>, you have encountered a secret page! Now, consider <strong>going away</strong> from here, dark place.</p></div>";

// Username/password not specified?
if (empty($USERNAME) || empty($PASSWORD))
{
    http_response_code(403);

    die($ERROR);
}

// Verify
$ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

// Invalid?
if ($ROW == null)
{
    http_response_code(403);

    die($ERROR);
}

// Moderator
$ADMIN = $ROW['moderator'];

// Is moderator?
if (empty($ADMIN) == true || $ADMIN == 0)
{
    http_response_code(403);

    die($ERROR);
}

?>

<div class="container-fluid">
    <!-- Title -->
    <h1 class="pt-3 mb-4">Monitoring utility.</h1>

    <?php

    // All users
    if (empty($GOTO))
    {
        // 25 rows per page.
        $MAXIMUM_AMOUNT = 25;
    
        if ($OFFSET >= 0)
        {
            // User count
            $COUNT = 0;

            // Maximum
            $AMOUNT = $OFFSET + $MAXIMUM_AMOUNT;
    
            // Range
            $RESULTS = DB::query("SELECT username FROM accounts ORDER BY created DESC LIMIT %d, %d", $OFFSET, $AMOUNT - 1);

            if ($RESULTS != null)
            {
                // Tables
                ?>

                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col">Username</th>
                      <th scope="col">Created</th>
                      <th scope="col">Privelege</th>
                      <th scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody>    
                    <?php

                    foreach ($RESULTS as $RESULT)
                    {
                        // Row
                        $ROW_RESULTS = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $RESULT['username']);
        
                        // Start
                        echo "<tr>";

                        // Count
                        $READABLE = $COUNT + 1;

                        echo "<th scope='row'>$READABLE</th>";

                        // Username
                        $USERNAME = $ROW_RESULTS['username'];

                        echo "<td>$USERNAME</td>";

                        // Created
                        $DATE = $ROW_RESULTS['created'];

                        echo "<td>$DATE</td>";

                        // Privelege
                        $ADMIN = $ROW_RESULTS['moderator'] > 0 ? 'moderator' : 'user';

                        echo "<td>$ADMIN</td>";

                        // View
                        $STR = "<a href='?u=$USERNAME'>View</a>";

                        echo "<td>$STR</td>";

                        // End
                        echo "</tr>";

                        $COUNT++;
                    }
    
                    ?>
                  </tbody>
                </table>

                <?php
            }

            // Next page
            if ($COUNT == 0)
            {
                echo "<p>No users here, unfortunately.</p>";
            }
            else
            {
                echo "<a href='?o=$AMOUNT' class='btn btn-primary' role='button'>Next page</a>";
            }
        }
    }
    else
    {
        // Row of selected user
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $GOTO);
    
        if ($ROW != null)
        {
            // Div begin
            echo "<div>";

            // List
            $GAMES = $ROW['games'];
    
            // Form begin
            echo "<form class='form-basic' method='post'>";

            // Games input
            echo "<label for='games' class='sr-only'>User games</label>";
            echo "<input type='text' id='games' class='form-control mb-2' placeholder='User games' value='$GAMES' name='games' required autofocus>";
    
            // Current HWID
            $HWID = $ROW['hwid'];
    
            // HWID input
            echo "<label for='hwid' class='sr-only'>HWID</label>";
            echo "<input type='text' id='hwid' class='form-control mb-2' placeholder='HWID' value='$HWID' name='hwid'>";
    
            // Current cooldown
            $HWID_UPDATE = $ROW['hwid_update'];
    
            // Cooldown input
            echo "<label for='hwid_cooldown' class='sr-only'>HWID cooldown</label>";
            echo "<input type='text' id='hwid_cooldown' class='form-control mb-2' placeholder='HWID cooldown' value='$HWID_UPDATE' name='hwid_cooldown'>";
    
            // Warning
            echo "<div class='alert alert-warning' role='alert'>It is <strong>not recommended</strong> to change anything here, unless you <strong>know</strong> how to properly change every field and know what you're doing.</div>";

            // Apply
            echo "<button type='submit' class='btn btn-primary mb-3'>Apply all</button>";
            
            // Form end
            echo "</form>";
    
            // Div end
            echo "</div>";

            // Inputs
            $INPUT_GAMES = (isset($_POST['games']) ? $_POST['games'] : '');
            $INPUT_HWID = (isset($_POST['hwid']) ? $_POST['hwid'] : '');
            $INPUT_COOLDOWN = (isset($_POST['hwid_cooldown']) ? $_POST['hwid_cooldown'] : '');
    
            // Array
            $ARRAY = [];
    
            // Games
            if (strlen($INPUT_COOLDOWN) >= 1)
            {
                $ARRAY['games'] = $INPUT_GAMES;
            }
    
            // HWID
            if (strlen($INPUT_COOLDOWN) >= 1)
            {
                $ARRAY['hwid'] = $INPUT_HWID;
            }
    
            // HWID cooldown
            if (strlen($INPUT_COOLDOWN) >= 1)
            {
                // Integer
                $INTEGER = intval($INPUT_COOLDOWN);
    
                // Insert integer
                $ARRAY['hwid_update'] = $INTEGER;
            }
    
            // MySQL
            if (count($ARRAY) >= 1)
            {
                DB::update('accounts', $ARRAY, "username=%s", $ROW['username']);
    
                // Refresh
                header("Refresh: 0.5");
            }

            // Separator
            echo "<hr>";
            
            // Div begin
            echo "<div>";
    
            // Title
            echo "<h2 class='mb-3'>IP logs</h2>";

            // List
            $LIST = $ROW['ip_logs'];
            $DECODED = json_decode($LIST, true);
    
            if ($DECODED != null)
            {
                // Sorting by date
                $SORTED = [];
    
                foreach ($DECODED as $LOG)
                {
                    // Date
                    $DATE = $LOG['time'];
    
                    array_push($SORTED, $DATE);
                }
    
                // Sort in descending order
                rsort($SORTED);
    
                // Dummy
                $ARRAY = [];
    
                foreach ($SORTED as $DATE)
                {
                    foreach ($DECODED as $LOG)
                    {
                        // Data
                        $DECODED_USER_AGENT = $LOG['user_agent'];
                        $DECODED_DATE = $LOG['time'];
                        $DECODED_IP = $LOG['ip'];
    
                        // Push log
                        if ($DATE == $DECODED_DATE)
                        {
                            $MINI = array($DECODED_DATE, $DECODED_IP, $DECODED_USER_AGENT);
    
                            array_push($ARRAY, $MINI);
                        }
                    }
                }
    
                // Replace sorted with dummy
                $SORTED = $ARRAY;
    
                // Erase dummy from memory
                $ARRAY = null;
                
                foreach ($SORTED as $LOG)
                {
                    // Data
                    $DATE = $LOG[0];
                    $IP = $LOG[1];
                    $USER_AGENT = empty($LOG[2]) ? "Unknown" : $LOG[2];
        
                    // Format
                    $CLASS = new DateTime("@$DATE");
                    $FORMATTED = $CLASS->format('Y-m-d H:i:s');
        
                    echo "$FORMATTED / <strong>$IP</strong> / <em>$USER_AGENT</em> / <a href='http://ip-api.com/json/$IP?fields=192319'>view</a><br>";
                }
            }
            else
            {
                echo "<p>Not available</p>";
            }

            // Div end
            echo "</div>";
        }
        else
        {
            echo "<p>User not available.</p>";
        }
    }
    
    ?>
</div>

</body>
</html> 