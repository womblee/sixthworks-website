<!DOCTYPE html>

<html lang="en">
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

// Account manager
include __DIR__ . '/backend/utils/accounts.php';

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
$ERROR = "<div class='container-fluid mt-3'><img src='media/sixthworks_evil.png' class='mb-2'><p><strong>Oh no</strong>, you have encountered a secret page! Now, consider <strong>going away</strong> from here, dark place.<br><br>For real, please go away. This is an administration panel for <strong>managing</strong> this service...</p></div>";

// Username/password not specified?
if (empty($USERNAME) || empty($PASSWORD))
{
    die($ERROR);
}

// Verify
$ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

// Invalid?
if ($ROW == null)
{
    die($ERROR);
}

// Moderator
$ADMIN = $ROW['moderator'];

// Is moderator?
if (empty($ADMIN) === true || $ADMIN == 0)
{
    die($ERROR);
}

?>

<div class="container-fluid pt-3">
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
                      <th scope="col">Email</th>
                      <th scope="col">Created</th>
                      <th scope="col">Verified</th>
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
                        {
                            // Fancy
                            $STR = $ACCOUNT_MANAGER->has_sharing_suspicion($ROW_RESULTS) ? "<span class='text-warning'>$USERNAME</span>" : "$USERNAME";
    
                            // Print
                            echo "<td>$STR</td>";
                        }

                        // Username
                        $EMAIL = $ROW_RESULTS['email'];
                        {
                            // Print
                            echo "<td>$EMAIL</td>";
                        }

                        // Created
                        $DATE = $ROW_RESULTS['created'];
                        {
                            // Print
                            echo "<td>$DATE</td>";
                        }

                        // Verified
                        $VERIFIED = $ROW_RESULTS['verified'] === '1' ? 'Yes' : 'No';
                        {
                            // Fancy
                            $STR = $ACCOUNT_MANAGER->has_old_unverified($ROW_RESULTS) && $VERIFIED == false ? "<span class='text-danger'>Expiration</span>" : "$VERIFIED";
                            
                            // Print
                            echo "<td>$STR</td>";
                        }
                        
                        // Privelege
                        $ADMIN = $ROW_RESULTS['moderator'] > 0 ? 'moderator' : 'user';
                        {
                            // Print
                            echo "<td>$ADMIN</td>";
                        }

                        // View
                        $STR = "<a href='?u=$USERNAME'>View</a>";
                        {
                            // Print
                            echo "<td>$STR</td>";
                        }

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
            echo "<div class='mb-2'>";

            // HWID Logs
            echo "<h2 class='mb-3'>HWID logs</h2>";
            {
                // List
                $LIST = $ROW['hwid_logs'];
    
                // Decoded
                $LIST = json_decode($LIST, true);
        
                if ($LIST != null)
                {
                    // Sorting by date
                    $SORTED = [];
        
                    foreach ($LIST as $LOG)
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
                        foreach ($LIST as $LOG)
                        {
                            // Data
                            $DECODED_HWID = $LOG['hwid'];
                            $DECODED_DATE = $LOG['time'];
        
                            // Push log
                            if ($DATE == $DECODED_DATE)
                            {
                                $MINI = array($DECODED_HWID, $DECODED_DATE);
        
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
                        $HWID = $LOG[0];
                        $DATE = $LOG[1];
            
                        // Format
                        $CLASS = new DateTime("@$DATE");
                        $FORMATTED = $CLASS->format('Y-m-d H:i:s');
            
                        echo "$FORMATTED / <strong>$HWID</strong><br>";
                    }
                }
                else
                {
                    echo "<p>Not available</p>";
                }
            }

            // Div end
            echo "</div>";

            // Div begin
            echo "<div class='mb-2'>";

            // IP Logs
            echo "<h2 class='mb-3'>IP logs</h2>";
            {
                // List
                $LIST = $ROW['ip_logs'];
    
                // Decoded
                $LIST = json_decode($LIST, true);
        
                if ($LIST != null)
                {
                    // Sorting by date
                    $SORTED = [];
        
                    foreach ($LIST as $LOG)
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
                        foreach ($LIST as $LOG)
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
            
                        echo "$FORMATTED / <strong>$IP</strong> / $USER_AGENT / <a href='http://ip-api.com/json/$IP?fields=192319'>view</a><br>";
                    }
                }
                else
                {
                    echo "<p>Not available</p>";
                }
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