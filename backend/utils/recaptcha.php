<?php

$CAPTCHA = (isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '');
if (empty($CAPTCHA))
{
    die("This page requires captcha to be solved.");
}

// API Key
$KEY = "6Lfkh34eAAAAAKmUky_YJ7D9rlLIEL58kh-ZkSs1";

// Request
$URL = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($KEY) .  '&response=' . urlencode($CAPTCHA);
$RESPONSE = file_get_contents($URL);
$KEYS = json_decode($RESPONSE, true);

// Should return JSON with success as true
if ($KEYS["success"] == false) 
{
    die("Captcha failed.");
}

?>