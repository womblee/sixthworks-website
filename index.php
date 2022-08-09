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

?>

<!-- Navbar -->
<div class="d-flex flex-column flex-md-row align-items-center p-2 px-md-4 mb-3 bg-dark border-bottom box-shadow">
  <h5 class="my-0 mr-md-auto font-weight-normal"><a class="text-white" href="<?php echo $OTHER_MANAGER->get_url() ?>">Sixthworks</a></h5>
  <nav class="my-2 my-md-0 mr-md-3">
    <a class="p-2 text-white" href="https://discord.gg/Rxm28E8jGa">Discord</a>
    <a class="p-2 text-white" href="/tos">Terms of service</a>
  </nav>

  <?php
  if ($ALLOW)
  {
  ?>

  <a class="btn btn-outline-success" href="/account">Dashboard</a>
  
  <?php
  }
  else
  {
  ?>

  <a class="btn btn-outline-primary" href="/account">Sign in</a> 
  
  <?php
  }
  ?>
</div>

<!-- Centered div -->
<div class="align-items-center">
  <div class="container mb-4">

    <!-- Main -->
    <img class="img-fluid mb-3" style="width:100%;min-height:100px;" src="media/sixthworks_large.png">

    <h3 class="display-5"><strong>Instant access</strong> to our premium cheats starting at <strong>$20 USD</strong>. Pay once, keep forever.</p>
    <a class="btn btn-outline-success btn-lg mb-4" href="/purchase">
      Buy now
    </a>

    <!-- Software -->  
    <h3 class="mb-3">Software</h3>

    <div class="row">
      <?php
      foreach (array_keys($MENU_DATA['games']) as $GAME)
      {
        if (array_key_exists($GAME, $CARD_DATA))
        {
          // Start #1
          echo "<div class='col-sm-4'><div class='card mb-3'>";
  
          // Necessary
          $NAME = $CARD_DATA[$GAME]['title'];
          $TEXT = $CARD_DATA[$GAME]['content'];
          $PICTURE = $CARD_DATA[$GAME]['img'];

          // Price
          $PRICE = $MENU_DATA['games'][$GAME]['cost'];

          // Image
          echo "<img class='card-img-fluid' src='media/$PICTURE' alt='$NAME'>";

          // Start #2
          echo "<div class='card-body'>";

          echo "<h4 class='card-title'>$NAME</h4>";
          echo "<h5 class='card-title pricing-card-title'>$PRICE <small class='text-muted'>/ lifetime</small></h5>";
          echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#modal_$GAME'>Learn more</button>";

          // End #2
          echo "</div>";

          // End #1
          echo "</div></div>";
          
          ?>

          <!-- Modal -->
          <div class="modal fade" id=<?php echo "modal_$GAME" ?> tabindex="-1" role="dialog" aria-labelledby=<?php echo "modal_$GAME-label" ?> aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
              <div class="modal-content">
                <!-- Header -->
                <div class="modal-header">
                  <h5 class="modal-title" id=<?php echo "modal_$GAME-label" ?>><?php echo $NAME ?></h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>

                <!-- Content -->
                <div class="modal-body" style="font-size: 18px">
                  <?php echo $TEXT ?>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

          <?php
        }
      }
      ?>
    </div>
  </div>
</div>

</body>
</html>