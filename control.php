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

  <!-- Recaptcha -->
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>

<?php

// Security
require __DIR__ . '/backend/utils/rate_limit.php';

// Account manager
include __DIR__ . '/backend/utils/accounts.php';

// Cookies
session_start();

// Globals
$ALLOW = false;
$ROW = null;

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'])
{
  // Username and password
  $USERNAME = (isset($_SESSION['username']) ? $VALIDATION_MANAGER->test_input($_SESSION['username']) : '');
  $PASSWORD = (isset($_SESSION['password']) ? $VALIDATION_MANAGER->test_input($_SESSION['password']) : '');

  // Try to get the row based on the cookie information
  $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

  if ($ROW != null)
  {
    // Passwords match?
    $COMPARISON = $ACCOUNT_MANAGER->compare_passwords($ROW, $PASSWORD);
    $ALLOW = $COMPARISON;
  }
}

if ($ALLOW)
{
?>

<!-- Navbar -->
<div class="d-flex flex-column flex-md-row align-items-center p-2 px-md-4 mb-3 bg-dark border-bottom box-shadow">
  <h5 class="my-0 mr-md-auto font-weight-normal"><a class="text-white" href="<?php echo $OTHER_MANAGER->get_url() ?>">Sixthworks</a></h5>
  <nav class="my-2 my-md-0 mr-md-3">
    <a class="p-2 text-white" href="https://discord.gg/Rxm28E8jGa">Discord</a>
    <a class="p-2 text-white" href="/tos">Terms of service</a>
  </nav>

  <a class="btn btn-outline-success" href="/logout">Sign out</a>
</div>

<div class="container-fluid pb-3">
  <h2>Dashboard</h2>

  <br>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link active" href="#main">Main</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#hwid">HWID</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#info">Information</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#account">Account</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" href="#changelog">Release notes</a>
    </li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div id="main" class="container-fluid tab-pane fade show active"><br>
      <h3 class="mb-3">Redeem license</h3>

      <form class="form-basic" action="backend/redeem.php" method="post">
        <label for="input_key" class="sr-only">License key</label>
        <input name="key" type="key" id="input_key" class="form-control mb-2" placeholder="License key" required autofocus>
        
        <?php
        $i = 0;
        ?>

        <div class="form-group">
          <label for="sel1">Redeem for:</label>
          <select class="form-control" name="game" id="sel1">
            <?php           
              foreach (array_keys($MENU_DATA['games']) as $GAME)
              {
                $STATUS = $ACCOUNT_MANAGER->has_game_version($ROW, $GAME);

                if ($STATUS['status'] == false || $STATUS['better'] == true)
                {
                  $STR = $MENU_DATA['games'][$GAME]['full_name'];
                  echo "<option value='$GAME'>$STR</option>";

                  $i++;
                }
              }

              if ($i == 0)
              {
                echo "<option value='nope'>None</option>";
              }
            ?>
          </select>
        </div> 

        <div class="g-recaptcha mb-2" data-sitekey="6Lfkh34eAAAAAI3fSfOaVIkZTFM0VChDaP-qfo7c"></div>

        <p>Read our terms of service on <a href="/tos">this page</a>.</p>

        <?php

        if ($i > 0)
        {
          ?>

          <button class="btn btn-dark btn-lg mb-3" type="submit">
            Redeem
          </button>

          <?php
        }
        else
        {
          ?>

          <button class="btn btn-dark btn-lg mb-3" type="submit" disabled="disabled">
            Redeem
          </button>

          <?php
        }

        ?>
      </form>
    </div>

    <div id="hwid" class="container-fluid tab-pane fade"><br>
      <h3 class="mb-2">Actions</h3>

      <?php
      if ($ACCOUNT_MANAGER->has_hwid_cooldown($ROW))
      {
        ?>

        <button type="button" class="btn btn-dark btn-lg mb-3" disabled="disabled">
          Reset HWID
        </button>

        <?php
      }
      else
      {
      ?>
      
      <form class="form-basic" action="backend/hwid.php?hwid=null" method="post">
        <button type="submit" class="btn btn-dark btn-lg mb-3">
          Reset HWID
        </button>
      </form>

      <?php
      }
      ?>

      <h3>Current</h3>
      <?php

      // Default HWID
      $LAST = $ROW['hwid'];
      $STR = $LAST;

      // None
      if ($STR == "null" || empty($STR) == true)
      {
        $STR = "None";
      }

      echo "<p>$STR</p>";
      ?>

      <h3>Last update</h3>
      <?php

      // Date
      $LAST = $ROW['hwid_update'];

      if ($LAST != 0 && empty($LAST) == false)
      {
        $CLASS = new DateTime("@$LAST");
        $FORMATTED = $CLASS->format('Y-m-d H:i:s');

        echo "<p>$FORMATTED</p>";
      }
      else
      {
        echo "<p>None</p>";
      }
      
      ?>
    </div>

    <div id="info" class="container-fluid tab-pane fade"><br>
      <h3>Username</h3>
      <p><?php echo $ROW['username']?></p>

      <h3>Registration date</h3>
      <p><?php echo $ROW['created']?></p>

      <h3>Purchased cheats</h3>
      
      <p>
        <?php
        $i = 0;

        foreach (array_keys($MENU_DATA['games']) as $GAME)
        {
          // Game hack owned?
          $STATUS = $ACCOUNT_MANAGER->has_game_version($ROW, $GAME);

          if ($STATUS['status'] == true)
          {
            // Panel
            echo "<div class='card' style='max-width: 400px'>";
            
            // Body
            echo "<div class='card-body' style='padding-left: 15px'>";
            
            // Title
            $STR = "<h5 class='card-title'>" . $MENU_DATA['games'][$GAME]['full_name'] . "</h5>";

            echo $STR;
          
            // Version
            $GAMES = $ACCOUNT_MANAGER->get_user_games($ROW); // No point, but we must not rely on other functions
            $DECODED = json_decode($GAMES, true);

            // Default
            $VERSION = $DECODED[$GAME];

            // Pretty
            if (array_key_exists($VERSION, $VERSION_DATA))
            {
              $VERSION = $VERSION_DATA[$VERSION]['full_name'];
            }

            // Footer
            $STR = "<p class='card-text text-success'>$VERSION</p>";

            echo $STR;

            // Body end
            echo "</div>";

            // End
            echo "</div>";

            $i++;
          }
        }

        if ($i == 0)
        {
          echo "<a href='/purchase'>Where to purchase?</a>";
        }
        ?>
      </p>
    </div>

    <div id="account" class="container-fluid tab-pane fade"><br>
      <h3 class="mb-3">Password change</h3>

      <form class="form-basic" action="backend/change.php" method="post">
        <label for="input_password" class="sr-only">Current password</label>
        <input name="password" type="password" id="input_password" class="form-control mb-2" placeholder="Current password" required autofocus>

        <label for="input_new_password" class="sr-only">New password</label>
        <input name="new" type="password" id="input_new_password" class="form-control mb-2" placeholder="New password" required>

        <div class="g-recaptcha mt-3 mb-3" data-sitekey="6Lfkh34eAAAAAI3fSfOaVIkZTFM0VChDaP-qfo7c"></div>

        <button class="btn btn-dark btn-lg mb-3" type="submit">Change</button>
      </form>
    </div>

    <div id="changelog" class="container-fluid tab-pane fade"><br>
      <?php
      $i = 0;

      foreach (array_keys($MENU_DATA['games']) as $GAME)
      {
        // Container start
        echo "<div>";

        // Header
        $NAME = $MENU_DATA['games'][$GAME]['full_name'];

        echo "<h3 class='text-light'>$NAME</h3>";

        // Validate
        if (array_key_exists($GAME, $CHANGELOG_DATA))
        {
          // History
          $PRESENT = false;
          
          // Collapsed
          $COLLAPSED = false;
          
          // Iterations
          $r_i = 0;

          // Ton of text
          foreach (array_keys($CHANGELOG_DATA[$GAME]) as $DATE)
          {
            // For spacing purposes
            echo "<div class='mb-2'>";

            // Date
            $CLASS = new DateTime("@$DATE");
            $FORMATTED = $CLASS->format('d.m.Y');
  
            // Decide whether to use collapsible or not
            $COLLAPSIBLE = $r_i > 1;
            
            if ($COLLAPSIBLE)
            {
              if ($r_i > 1 && $PRESENT == false)
              {
                echo "<h5 class='mb-3'>Previous release notes</h5>";

                $PRESENT = true;
              }

              echo "<button class='btn btn-dark mb-2' type='button' data-toggle='collapse' data-target='#collapse_$r_i' aria-expanded='false' aria-controls='collapse_$r_i'>Notes $FORMATTED</button>";
            }
            else
            {
              echo "<h5 class='mb-2'>$FORMATTED</h5>";
            }
            
            // Changes
            $CHANGES = $CHANGELOG_DATA[$GAME][$DATE];
  
            if ($COLLAPSIBLE)
            {
              echo "<div class='collapse' id='collapse_$r_i'>";
              echo "<div class='card card-body'>";
            }

            // Info
            if (count($CHANGES) >= 1)
            {
              echo "<ul style='padding-left: 15px; margin-bottom: unset;'>";

              foreach ($CHANGELOG_DATA[$GAME][$DATE] as $CHANGE)
              {
                echo "<li>$CHANGE</li>";
              }

              echo "</ul>";
            }
            else
            {
              echo "<p>No data</p>"; 
            }
  
            // Collapsible
            if ($COLLAPSIBLE)
            {
              echo "</div>";
              echo "</div>";
            }

            // Main
            echo "</div>";

            $r_i++;
          }
        }
        else
        {
          echo "<p>No release notes available</p>"; 
        }
        
        // Container end
        echo "<div>";

        $i++;
      }

      if ($i == 0)
      {
        echo "<h3>No games available</h3>";
      }
      ?>
    </div>
  </div>
</div>

<script>
  $(document).ready(function()
  {
    $(".nav-tabs a").click(function()
    {
      $(this).tab('show');
    });
  });
</script>

<?php
}
else
{
  $REGISTER = isset($_GET['register']);
  if ($REGISTER !== true)
  {
?>

<!-- Navbar -->
<div class="d-flex flex-column flex-md-row align-items-center py-3 px-md-4 mb-3 bg-dark border-bottom box-shadow">
  <h5 class="my-0 mr-md-auto font-weight-normal"><a class="text-white" href="<?php echo $OTHER_MANAGER->get_url() ?>">Sixthworks</a></h5>
</div>

<div class="container">
  <form class="form-signin" action="/login" method="post">
    <h1 class="h3 mb-3 font-weight-normal">Login</h1>

    <label for="input_login" class="sr-only">Username / Email</label>
    <input name="login" type="login" id="input_login" class="form-control mb-2" placeholder="Username / Email" required autofocus>

    <label for="input_password" class="sr-only">Password</label>
    <input name="password" type="password" id="input_password" class="form-control mb-2" placeholder="Password" aria-labelledby="forgot" required>
    
    <div class="form-helper mb-2">Forgot password? Reset <a href="/reset">here</a>.</div>

    <div class="g-recaptcha mb-2" data-sitekey="6Lfkh34eAAAAAI3fSfOaVIkZTFM0VChDaP-qfo7c"></div>
    
    <p>Not a member? <a href="?register">Sign up</a>.</p>

    <button class="btn btn-outline-primary btn-lg mb-3" type="submit">Login</button>
  </form>
</div>

<?php
  }
  else
  {
?>

<!-- Navbar -->
<div class="d-flex flex-column flex-md-row align-items-center py-3 px-md-4 mb-3 bg-dark border-bottom box-shadow">
  <h5 class="my-0 mr-md-auto font-weight-normal"><a class="text-white" href="<?php echo $OTHER_MANAGER->get_url() ?>">Sixthworks</a></h5>
</div>

<div class="container">
  <form class="form-signin" action="backend/register.php" method="post">
    <h1 class="h3 mb-3 font-weight-normal">Registration</h1>

    <label for="input_user" class="sr-only">Username</label>
    <input name="username" type="username" id="input_user" class="form-control mb-2" placeholder="Username" required autofocus>
    
    <label for="input_email" class="sr-only">Email</label>
    <input name="email" type="email" id="input_email" class="form-control mb-2" placeholder="Email" required>

    <label for="input_password" class="sr-only">Password</label>
    <input name="password" type="password" id="input_password" class="form-control mb-2" placeholder="Password" required>

    <div class="g-recaptcha mb-2" data-sitekey="6Lfkh34eAAAAAI3fSfOaVIkZTFM0VChDaP-qfo7c"></div>

    <p>Already have an account? Sign in <a href="/account">here</a>.</p>

    <button class="btn btn-outline-primary btn-lg mb-3" type="submit">Register</button>
  </form>
</div>

<?php
  }
}
?>

</body>
</html>