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

<div class="container pb-3">
  <img class="img-fluid mb-4" style="width:100%;min-height:100px;" src="media/sixthworks_reset.png">

  <h2 class="mb-3">Information</h2>
  <p>After sending a reset request, you will be sent an <strong>email</strong> containing a <strong>link</strong> to reset your password with a new <strong>random generated</strong> one. This random generated password can be used to <strong>log into</strong> your account.</p>

  <p>Remember that there is a <strong>password change</strong> option in the dashboard, in case you don't like random generated ones.</p>

  <h2 class="mb-3">Request</h2>
  <form class="form-basic mt-3" action="backend/forgot.php" method="post">
  <label for="input_email" class="sr-only">Email</label>
    <input name="email" type="email" id="input_email" class="form-control mb-2" placeholder="Email" required autofocus>
    
    <div class="g-recaptcha mb-2" data-sitekey="6Lfkh34eAAAAAI3fSfOaVIkZTFM0VChDaP-qfo7c"></div>  
    
    <div class="alert alert-info" role="alert">
      You can send a request each <strong>60 seconds</strong>.
    </div>

    <button class="btn btn-dark btn-lg mb-3" type="submit">Reset password</button>
  </form>
</div>


</body>
</html>