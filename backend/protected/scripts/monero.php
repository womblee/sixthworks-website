<?php
// Mailer
require(dirname(__DIR__) . "/mailer/PHPMailer.php");
require(dirname(__DIR__) . "/mailer/SMTP.php");
require(dirname(__DIR__) . "/mailer/Exception.php");

// SMTP Config
require(dirname(__DIR__) . "/smtp_data.php");

// Use
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Minimum amount to receive
$MINIMUM = 0.005;

// RPC url
$RPC_URL = "http://127.0.0.1:28088/json_rpc";

// Get address without manually specifying it
$AUTOMATIC_ADDRESS = true; 

// It's longer than bitcoin which means it's better
$MANUAL_ADRESS = "45HQ4tM6K5G3ndK6e7fWdXMtL89pYEWXm1tM957QyQtfKV9GPf59sQNixgJappduEGSSuv9BBEWRN7RKh7H3i58b6i6GcV7";

// Wallet manager
class WALLET
{
    public $ADDRESS;
}

$WALLET_MANAGER = new WALLET();

// Transfer manager
class TRANSFER
{
    public $PAYMENT_ID;
    public $PAY_ID;
    public $AMOUNT;
}

$TRANSFER_MANAGER = new TRANSFER();

// Get address
if ($AUTOMATIC_ADDRESS)
{
    // CURL request
    $CURL = curl_init();
    
    curl_setopt($CURL, CURLOPT_URL, $RPC_URL);
    curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($CURL, CURLINFO_HEADER_OUT, true);  
    
    // Payload
    $PAYLOAD =
    [
        "jsonrpc" => "2.0",
        "id" => "0",
        "method" => "get_address"
    ];
    
    $ENCODED = json_encode($PAYLOAD);
    
    // Post
    curl_setopt($CURL, CURLOPT_POST, true);
    curl_setopt($CURL, CURLOPT_POSTFIELDS, $ENCODED);
    
    // Headers
    $HEADERS =
    [
        "Content-Type: application/json",
        "Accept: application/json",
    ];
    
    curl_setopt($CURL, CURLOPT_HTTPHEADER, $HEADERS);
    
    // Result
    $RESULT = curl_exec($CURL);
    $DECODED = json_decode($RESULT, true);

    // Manager
    $WALLET_MANAGER->ADDRESS = $DECODED['result']['address']; // Wallet address
    
    // Close connection
    curl_close($CURL); 
}
else
{
    // Manual method
    $WALLET_MANAGER->ADDRESS = $MANUAL_ADRESS;
}

// CURL request
$CURL = curl_init();
    
curl_setopt($CURL, CURLOPT_URL, $RPC_URL);
curl_setopt($CURL, CURLOPT_RETURNTRANSFER, true);
curl_setopt($CURL, CURLINFO_HEADER_OUT, true);  

// Payload
$PAYLOAD =
[
    "jsonrpc" => "2.0",
    "id" => "0",
    "method" => "get_transfers",
    "params" =>
    [
        "in" => true,
        "pool" => true,
        "account_index" => 0
    ]
];

$ENCODED = json_encode($PAYLOAD);

// Post
curl_setopt($CURL, CURLOPT_POST, true);
curl_setopt($CURL, CURLOPT_POSTFIELDS, $ENCODED);

// Headers
$HEADERS =
[
    "Content-Type: application/json",
    "Accept: application/json",
];

curl_setopt($CURL, CURLOPT_HTTPHEADER, $HEADERS);

// Result
$RESULT = curl_exec($CURL);
$DECODED = json_decode($RESULT, true);

// Manager
$TRANSFER_MANAGER->ADDRESS

// Close connection
curl_close($CURL); 

?>