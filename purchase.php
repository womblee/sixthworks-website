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

<!-- Centered div -->
<div class="align-items-center">
  <div class="container">
    <img class="img-fluid mb-3" style="width:100%;min-height:100px;" src="media/sixthworks_bay.png">

    <blockquote class="blockquote">
      Better than ever cheat provider, best & stable code, fully working features for *very cheap*

      <figcaption class="blockquote-footer mb-0 font-italic">
        Tomas Curda
      </figcaption>
    </blockquote>

    <p>All our cheats is your go-to solution for professional cheating and messing in games. Since 2021 sixthworks has been developing a  stable service to meet needs for hacking in this game. It was a non-profit project made for fun, but expenses need to be covered. We might sell more cheats in the future (we have our infrastructure prepared for this). Sixthworks allows registration and usage using Tor and other privacy services (proxies, VPNs), you don't need an email to register. It is certain to stay forever until something bad happens, which is very unlikely.</p>

    <?php
    foreach (array_keys($MENU_DATA['games']) as $GAME)
    {
      // Validate
      if (array_key_exists($GAME, $RESELLERS_DATA))
      {
        $TABLE = $RESELLERS_DATA[$GAME];
      
        $NAME = $MENU_DATA['games'][$GAME]['full_name'];
        echo "<h3 class='mb-2'>$NAME</h3>";
  
        $i = 0;
        foreach (array_keys($TABLE) as $RESELLER)
        {
          echo '<div class="mb-3">';
  
          echo "<h5><i>$RESELLER</i></h5>";
  
          // Methods
          $METHODS = $TABLE[$RESELLER]['methods'];
  
          // Keys
          $KEYS = array_keys($METHODS);
  
          foreach ($KEYS as $METHOD)
          {
            $LINK = $METHODS[$METHOD]['url'];
            $PRICE = $METHODS[$METHOD]['price'];
  
            $STR = "<a href=$LINK>$METHOD</a>";
  
            $BADGE = empty($PRICE) ? "secondary" : "success";
            $BADGE_TEXT = empty($PRICE) ? "Unknown" : $PRICE;
  
            $STR = $STR . " ";
            $STR = $STR . "<span class='badge badge-$BADGE'>$BADGE_TEXT</span>";
            $STR = $STR . "<br>";
  
            echo $STR;
          }
          
          echo '</div>';
  
          $i++;
        }
  
        if ($i == 0)
        {
          echo "<h5>- No resellers available</h5>";
        }
      }
    }
    ?>

    <div class="alert alert-warning" role="alert">
      <p>These are resellers and have nothing to do with us, we are <strong>not responsible</strong> for their actions or any payments made. Only trusted people are listed above, although you should be aware that they are third-party/not official.</p>
      <p>If you are not happy with the price on these destinations, just don't pay them, instead buy the cheat <strong>officially</strong> from here. Don't make drama and be decent people! Having resellers is a very decent thing, we can work with all payments in the world and you need to appreciate what we do for you.</p>
    </div>

    <!-- Separator -->
    <hr>
    
    <!-- Monero image -->
    <img class="mb-3" src="media/monero.svg">

    <h4>Description.</h4>
    <p>Purchasing with <a href="https://en.wikipedia.org/wiki/Monero">monero</a> makes you avoid many reseller difficulties to go through, the purchase is automated and has no humans behind it. You will avoid human interaction or wasting useless time on talking with a person. When buying officially from us, many positive <a href="/tos">terms</a> apply and you can refund.</p>

    <?php
    if ($ALLOW)
    {
      if ($ACCOUNT_MANAGER->has_verified_email($USERNAME) == false)
      {
      ?>

      <div class="alert alert-info" role="alert">
        You must verify your email before purchasing.<br><br>We have sent you a verification letter, check your mail inbox for new messages. Use <a href='/resend?username=<?php echo $USERNAME?>&password=<?php echo $PASSWORD?>'>this page</a> to resend.
      </div>

      <?php
      }
      else
      {
    ?>

    <h4>Payment information.</h4>
    <p>Convert the product price to <a href="https://en.wikipedia.org/wiki/Monero">monero</a> on <a href="https://www.coingecko.com/en/coins/monero/usd">this page</a>, price is listed in every dropdown. Purchase will go through only if you send an equal/greater amount.</p>
    
    <p>If you send anything to our wallet <strong>before creating</strong> a purchase request, nothing will be sent to your email address. <strong>Always create</strong> a purchase request before paying anything to our wallet.</p>
    
    <p>Our wallet: <i><u>45HQ4tM6K5G3ndK6e7fWdXMtL89pYEWXm1tM957QyQtfKV9GPf59sQNixgJappduEGSSuv9BBEWRN7RKh7H3i58b6i6GcV7</u></i></p>

    <h4>Purchase request.</h4>
    <form class="form-basic mt-3" action="backend/request.php" method="post">
      <div class="form-group">
        <select class="form-control" name="game">
          <?php
            foreach (array_keys($MENU_DATA['games']) as $GAME)
            {
              $STR = $MENU_DATA['games'][$GAME]['full_name'] . " (" . $MENU_DATA['games'][$GAME]['cost'] . ")";
              echo "<option value='$GAME'>$STR</option>";

              $i++;
            }

            if ($i == 0)
            {
              echo "<option value='nope'>No games available</option>";
            }
          ?>
        </select>
      </div>
      
      <label for="input_wallet" class="sr-only">Payment wallet</label>
      <input name="wallet" type="wallet" id="input_wallet" class="form-control mb-2" placeholder="Payment wallet" required autofocus>
      
      <div class="g-recaptcha mb-2" data-sitekey="6Lfkh34eAAAAAI3fSfOaVIkZTFM0VChDaP-qfo7c"></div>

      <div class="alert alert-warning" role="alert">
        In the second field, enter the <strong>wallet</strong> from which the <strong>transaction</strong> will come. After <strong>requesting</strong> a purchase, send a required amount of monero to our <strong>wallet</strong>.
      </div>

      <button class="btn btn-default btn-md mb-3" type="submit">Request purchase</button>
    </form>
    
    <?php
      }
    }
    else
    {
    ?>

    <div class="alert alert-info" role="alert">
      You must be logged in before purchasing with Monero.
    </div>

    <?php
    }
    ?>

    <!-- Separator -->
    <hr>

    <h2>For resellers</h2>
    <p>To buy our products for reselling, contact the service administration. Purchasing in bulk is accepted only, while the minimum amount is 5 keys, and the maximum is 1000 keys. Key price is not listed on this page, therefore you need to ask the service administration about it, contacts are listed below.<br><a href="https://t.me/aaathats3aaas">Telegram</a></p>
    
    <p>Contact only if you are sure that you are going to purchase from us, since we do not want any silly question things going on. That will make things better for you, and for us.
  </div>
</div>

</body>
</html>