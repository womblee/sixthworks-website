<?php

class OTHER
{
    function get_url()
    {
        // HTTPS or HTTP?
        $LINK = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
          
        // Here append the common URL characters.
        $LINK .= "://";
          
        // Append the host(domain name, ip) to the URL.
        $LINK .= $_SERVER['HTTP_HOST'];
          
        // Return
        return $LINK;
    }

    function generate_encrypted($STRING)
    {
        // Generates a hashed AES-256-CBC version of the specified password
        $ENC_KEY = openssl_random_pseudo_bytes(32); 
        $IV_SIZE = openssl_cipher_iv_length("aes-256-cbc"); 
        $IV = openssl_random_pseudo_bytes($IV_SIZE);
        $HASHED_STRING = openssl_encrypt($STRING, "aes-256-cbc", $ENC_KEY, 0, $IV);

        // All the needed information
        $ARRAY =
        [
           "data" => $HASHED_STRING,
           "iv" => $IV,
           "enc_key" => $ENC_KEY,
        ];

        return $ARRAY;
    }

    function random_string($LENGTH)
    {
        $CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $RANDOM_STRING = '';
      
        for ($i = 0; $i < $LENGTH; $i++)
        {
            $INDEX = rand(0, strlen($CHARACTERS) - 1);
            $RANDOM_STRING .= $CHARACTERS[$INDEX];
        }
      
        return $RANDOM_STRING;
    }
}

$OTHER_MANAGER = new OTHER;

?>