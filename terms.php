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

  <!-- Style -->
  <style>
    ul {
      padding-left: 20px;
    }
  </style>
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
    <a class="p-2 text-white" href="https://t.me/aaathats3aaas">Support</a>
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

<div class="container pb-2">
  <div class="text-left">
    <h3>Usage Policy</h3>
    <ul>
      <li>
        You agree not to exchange accounts to real currency and its equialents, using any forms of interaction.
      </li>

      <li>
        You agree to tell the administration about all important problems, which may disrupt the operation or gain unfair benefit.
        <br>
        <span class="text-secondary">Owning and not disclose such information is punishable, as well as transmission to other persons.</span>
      </li>

      <li>
        We reserve the right to scan temporary and permanent memory, devices and network of users during game in order to forbidden software or devices.
      </li>
    </ul>
    
    <h3>Privacy Policy</h3>
    <ul>
      <li>
        We store your unique HWID, which tells us absolutely nothing about the computer.
        <br>
        <span class="text-secondary">From a technical standpoint it is possible to know what computer parts you have, but not from the practical one.</span>
      </li>

      <li>
        We responsibly store only the encrypted version of the password, only the account owner can know the password.
        <br>
        <span class="text-secondary">Passwords are encrypted using AES with a 256 bits key size, even 128 bits key size is practically impossible to crack open.</span>
      </li>
    </ul>

    <h3>Refund Policy</h3>
    <ul>
      <li>
        If a technical issue has caused you not receive the product, no refund in any manner will be provided.
        <br>
        <span class="text-secondary">Contact our support and explain the situation so we can manually deliver the product to you</span>
      </li>
      
      <li>
        If you have purchased the software by mistake or purchased the wrong license, unfortunately there will be no refund.
      </li>

      <li>
        For refund requests email us to <a href="mailto:admin@sixthworks.com">admin@sixthworks.com</a>.
        <br>
        <span class="text-secondary">This only applies to purchases made directly from us, and not from any third-parties.</span>
      </li>
    </ul>

    <h3>Suspension Policy</h3>
    <ul>
      <li>
        We can terminate your agreement with us at any time, for any reason without explanations, notifications or warnings.
      </li>
      
      <li>
        In the event of TOS termination, account access and beyond will be blocked or lost, compensation will not be done in full or partial manner.
      </li>
    </ul>
  </div>
</div>

</body>
</html>