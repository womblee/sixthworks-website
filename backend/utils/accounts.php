<?php
include $_SERVER['DOCUMENT_ROOT'] . '/backend/protected/mysql.php';
include $_SERVER['DOCUMENT_ROOT'] . '/backend/protected/mail_data.php';

// Mailer
require(dirname(__DIR__) . "/protected/mailer/PHPMailer.php");
require(dirname(__DIR__) . "/protected/mailer/SMTP.php");
require(dirname(__DIR__) . "/protected/mailer/Exception.php");

// Use
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Modules
include __DIR__ . '/validation.php';
include __DIR__ . '/other.php';
include __DIR__ . '/games.php';

class ACCOUNTS
{
    function valid_username($NAME)
    {
        // Row
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $NAME);

        // Return
        return $ROW != null;
    }

    function valid_email($EMAIL)
    {
        // Row
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE email=%s", $EMAIL);

        // Return
        return $ROW != null;
    }

    function has_verified_email($USERNAME)
    {
        // Row
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

        // Status
        $STATUS = $ROW['verified'];

        // Return
        return $STATUS === '1';
    }

    function has_old_unverified($ROW)
    {
        // Timestamp
        $DATE = $ROW['created'];

        // Difference
        $DIFFERENCE = time() - $DATE;

        // Return
        return $DIFFERENCE >= 86400;
    }

    function compare_passwords($ROW, $PASSWORD)
    {
        // Data
        $HASHED_PASSWORD = $ROW["hashed_password"];
        $ENCRYPTION_KEY = base64_decode($ROW["enc_key"]);
        $IV = base64_decode($ROW["iv"]);

        // Decrypt
        $DECRYPTED = openssl_decrypt($HASHED_PASSWORD, "aes-256-cbc", $ENCRYPTION_KEY, 0, $IV); 
        
        // Compare
        return $PASSWORD == $DECRYPTED;
    }

    function get_user_games($ROW)
    {
        // Import data
        global $MENU_DATA;
        global $GAMES_MANAGER;

        // List
        $LIST = $MENU_DATA['games'];
        
        // User games
        $RAW = $ROW['games'];
        $TABLE = json_decode($RAW, true);

        // New data
        $NEW = $GAMES_MANAGER->new_table_string($RAW);

        // Outdated in the database?
        foreach (array_keys($LIST) as $GAME)
        {
            if (array_key_exists($GAME, $TABLE) == false)
            {
                $UPDATE =
                [
                    'games' => $NEW,
                ];

                DB::update('accounts', $UPDATE, "username=%s", $ROW['username']);
            }
        }

        // Anyway, return the new array string
        return $NEW;
    }

    function has_game_version($ROW, $GAME)
    {
        // Import data
        global $MENU_DATA;
        global $GAMES_MANAGER;

        // User games
        $RAW = $this->get_user_games($ROW);
        $GAMES = json_decode($RAW, true);

        // Version
        $VALUE = $GAMES[$GAME];

        // Return based on the value  found in the versions haystack
        $VERSIONS = $MENU_DATA['games'][$GAME]['versions'];
        
        // Does he have any version?
        $STATUS = in_array($VALUE, $VERSIONS);
    
        // He has a better version to redeem?
        $POSITION = array_search($VALUE, $VERSIONS);
        $LAST_POSITION = array_search(end($VERSIONS), $VERSIONS);
    
        // Better?
        $BETTER = $POSITION < $LAST_POSITION;
          
        // Return
        return
        [
            "status" => $STATUS,
            "better" => $BETTER,
        ];
    }

    function log_ip($ROW)
    {
        // IP
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $IP = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $IP = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $IP = $_SERVER['REMOTE_ADDR'];
        }

        // Time
        $CURRENT_TIME = time();

        // User agent
        $USER_AGENT = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        // Element
        $ARRAY =
        [
            'ip' => $IP,
            'time' => $CURRENT_TIME,
            'user_agent' => $USER_AGENT,
        ];

        // Current logs
        $CURRENT = $ROW['ip_logs'];

        // Serve it as an element on the first time, not as an independent array
        $PUSHED = false;

        if (empty($CURRENT) == true || $CURRENT == "null")
        {
            // Push to the dummy
            $PLACEHOLDER = [];
            array_push($PLACEHOLDER, $ARRAY);

            // So that we do not push an array inside of an array.
            $PUSHED = true;

            // Assign
            $ARRAY = $PLACEHOLDER;
        }

        if ($PUSHED == false)
        {
            // Current one, should be already an array
            $DECODED = json_decode($CURRENT, true);
            
            // 500 logs per 1 user, that would be 500000 requests per 1000 users with length of each log being ~100 symbols, making it 50000000 bytes (~51 megabytes)
            if (count($DECODED) >= 500)
            {
                // Erase everything
                $DECODED = [];
            }

            // No need to mess with the decoded, rather create a variable instead.
            $DUMMY = $DECODED;

            // Is actually an array?
            if (is_array($DECODED))
            {
                // Push to dummy
                array_push($DUMMY, $ARRAY);

                // Assign to main variable
                $ARRAY = $DUMMY;
            }
        }

        // Encoded JSON
        $ENCODED = json_encode($ARRAY);
        
        // Update
        $UPDATE =
        [
            'ip_logs' => $ENCODED,
        ];

        // Update
        DB::update('accounts', $UPDATE, "username=%s", $ROW['username']);
    }

    function send_verification($USERNAME, $EMAIL)
    {
        // Import managers
        global $OTHER_MANAGER;

        // Import data
        global $SMTP_DATA;

        // Current time
        $CURRENT_TIME = time();

        // Validate
        $RESULTS = DB::query("SELECT * FROM email_verification WHERE email=%s", $EMAIL);
        {
            // Hits
            $HITS = 0;
    
            // Hits with cooldown
            $COOLDOWN_HITS = 0;
    
            foreach ($RESULTS as $RESULT)
            {
                // Time
                $TIME = $RESULT['time'];
    
                // Cooldown
                if ($CURRENT_TIME - $TIME >= 60)
                {
                    $COOLDOWN_HITS++;        
                }
    
                // Add
                $HITS++;
            }

            // Hits are older than 60 seconds?
            if ($HITS > 0 && $HITS != $COOLDOWN_HITS)
            {
                return false;
            }
        }

        // Verification
        $MAIL = new PHPMailer();
        
        try
        {
            // SMTP
            $MAIL->isSMTP();
            $MAIL->Host          = $SMTP_DATA['host'];
            $MAIL->SMTPAuth      = true;
            $MAIL->SMTPSecure    = $SMTP_DATA['protocol'];
            $MAIL->Port          = $SMTP_DATA['port'];
            $MAIL->Username      = $SMTP_DATA['username'];
            $MAIL->Password      = $SMTP_DATA['password'];
        
            // From => To
            $MAIL->setFrom("admin@sixthworks.com", "Sixthworks");
            $MAIL->addAddress($EMAIL, $USERNAME);
        
            // HTML
            $MAIL->isHTML(true);
            
            // Subject
            $MAIL->Subject = "Verification";

            // Generate
            $UNIQUE = $OTHER_MANAGER->random_string(18);
            
            // Link
            $SITE = $OTHER_MANAGER->get_url();
            $URL = "$SITE/email?code=$UNIQUE";

            // Content
            $MAIL->Body = "Hello, please verify that this email belongs to you: <a href='$URL'>$URL</a>";
        
            // Send
            $MAIL->send();

            // Row
            $INSERT =
            [
                'email' => $EMAIL,
                'code' => $UNIQUE,
                'time' => $CURRENT_TIME,
            ];
    
            // Insert
            $STATUS = DB::insert('email_verification', $INSERT);
    
            // Result
            $RESULT = $STATUS == true && DB::affectedRows() == 1;

            // Return
            return $RESULT;
        }
        catch (Exception $ERROR)
        {
            // Return
            return false;
        }
    }

    function attempt_registration($USERNAME, $PASSWORD, $EMAIL)
    {
        // Import managers
        global $VALIDATION_MANAGER;
        global $OTHER_MANAGER;
        global $GAMES_MANAGER;
        
        // Import data
        global $SMTP_DATA;

        // Username
        if ($VALIDATION_MANAGER->username($USERNAME) == false)
        {
            return
            [
                "status" => false,
                "string" => "Username must contain only latin letters, no special symbols except dashes, while having at least 6 out of 30 symbols.",
            ];
        }
        
        // Password
        if ($VALIDATION_MANAGER->password($PASSWORD) == false)
        {
            return
            [
                "status" => false,
                "string" => "Password must contain only latin letters, at least 1 uppercase letter, at least 1 lowercase letter, at least 1 number, while having at least 8 out of 128 symbols.",
            ];
        }

        // Email
        if ($VALIDATION_MANAGER->email($EMAIL) == false)
        {
            return
            [
                "status" => false,
                "string" => "Your email address has not been proven valid, pleace contact an administrator.",
            ];
        }

        // Does it exist?
        if ($this->valid_username($USERNAME) == true)
        {
            return
            [
                "status" => false,
                "string" => "Account with that username already exists.",
            ];
        }

        // Does someone use that email?
        if ($this->valid_email($EMAIL) == true)
        {
            return
            [
                "status" => false,
                "string" => "Someone is already using that email.",
            ];
        }

        // Get the array with all of the hashed data
        $HASHED_ARRAY = $OTHER_MANAGER->generate_encrypted($PASSWORD);
        $HASHED_PASSWORD = $HASHED_ARRAY['data'];

        // Base64
        $BASE64_ENC_KEY = base64_encode($HASHED_ARRAY['enc_key']);
        $BASE64_IV = base64_encode($HASHED_ARRAY['iv']);

        // Creation date
        $CREATED = time();

        // Game list
        $GAMES = $GAMES_MANAGER->table_string();

        // Row
        $INSERT =
        [
            'username' => $USERNAME,
            'email' => $EMAIL,
            'verified' => 0,
            'created' => $CREATED,
            'hashed_password' => $HASHED_PASSWORD,
            'enc_key' => $BASE64_ENC_KEY,
            'iv' => $BASE64_IV,
            'ip_logs' => 'null',
            'hwid_logs' => 'null',
            'hwid' => 'null',
            'hwid_update' => 0,
            'games' => $GAMES,
            'moderator' => 0,
        ];

        // Insert
        $STATUS = DB::insert('accounts', $INSERT);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        // Verification
        $VERIFICATION = $this->send_verification($USERNAME, $EMAIL);

        if ($VERIFICATION == false)
        {
            return 
            [
                "status" => false,
                "string" => "Failed in sending a verification letter, maybe you requested too much in a short period of time? In case you did not, please contact an administrator.",
            ];
        }

        // Return
        return 
        [
            "status" => $RESULT,
            "string" => "",
        ];
    }

    function attempt_resend($USERNAME, $PASSWORD)
    {
        // User
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);
        
        // User valid?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "This account is not valid.",
            ];
        }
        
        // Did the user specify the correct password?
        $COMPARISON = $this->compare_passwords($ROW, $PASSWORD);

        if ($COMPARISON == false)
        {
            return
            [
                "status" => false,
                "string" => "The entered password is incorrect.",
            ];
        }

        // User already verified?
        $VERIFIED = $ROW['verified'];

        if ($VERIFIED === 1)
        {
            return
            [
                "status" => false,
                "string" => "This account is already verified.",
            ];
        }

        // Email
        $USERNAME = $ROW['username'];
        $EMAIL = $ROW['email'];

        // Resend
        $VERIFICATION = $this->send_verification($USERNAME, $EMAIL);
        
        if ($VERIFICATION == false)
        {
            return 
            [
                "status" => false,
                "string" => "Failed resending a verification letter to your inbox, maybe you requested too much in a short period of time? In case you did not, please contact an administrator.",
            ];
        }

        // Return
        return 
        [
            "status" => true,
            "string" => "",
        ];
    }

    function attempt_verify($CODE)
    {
        // Code is unique for each user
        $ROW = DB::queryFirstRow("SELECT * FROM email_verification WHERE code=%s", $CODE);

        // Does it exist?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "Specified code is invalid.",
            ];
        }

        // Email
        $EMAIL = $ROW['email'];

        // Verification
        $UPDATE =
        [
            'verified' => 1,
        ];

        // Insert
        $STATUS = DB::update('accounts', $UPDATE, "email=%s", $EMAIL);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        // No problems?
        if ($RESULT == false)
        {
            return 
            [
                "status" => false,
                "string" => "Verification failed, please contact an administrator.",
            ];
        }

        // Delete
        $STATUS = DB::delete('email_verification', 'code=%s', $CODE);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        // Return
        return 
        [
            "status" => $RESULT,
            "string" => "",
        ];
    }

    function attempt_reset($EMAIL)
    {
        // Import managers
        global $VALIDATION_MANAGER;
        global $OTHER_MANAGER;

        // Import data
        global $SMTP_DATA;

        // User
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE email=%s", $EMAIL);

        // Valid?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "There are no accounts with that email address.",
            ];
        }

        // Current time
        $CURRENT_TIME = time();

        // Validate
        $RESULTS = DB::query("SELECT * FROM requests_reset WHERE email=%s", $EMAIL);
        {
            // Hits
            $HITS = 0;
    
            // Hits with cooldown
            $COOLDOWN_HITS = 0;
    
            foreach ($RESULTS as $RESULT)
            {
                // Time
                $TIME = $RESULT['time'];
    
                // Cooldown
                if ($CURRENT_TIME - $TIME >= 60)
                {
                    $COOLDOWN_HITS++;        
                }
    
                // Add
                $HITS++;
            }

            // Hits are older than 60 seconds?
            if ($HITS > 0 && $HITS != $COOLDOWN_HITS)
            {
                return
                [
                    "status" => false,
                    "string" => "You can reset your password only each 60 seconds.",
                ];
            }
        }

        // Verification
        $MAIL = new PHPMailer();

        try
        {
            // SMTP
            $MAIL->isSMTP();
            $MAIL->Host          = $SMTP_DATA['host'];
            $MAIL->SMTPAuth      = true;
            $MAIL->SMTPSecure    = $SMTP_DATA['protocol'];
            $MAIL->Port          = $SMTP_DATA['port'];
            $MAIL->Username      = $SMTP_DATA['username'];
            $MAIL->Password      = $SMTP_DATA['password'];
        
            // Username
            $USERNAME = $ROW['username'];

            // From => To
            $MAIL->setFrom("admin@sixthworks.com", "Sixthworks");
            $MAIL->addAddress($EMAIL, $USERNAME);
        
            // HTML
            $MAIL->isHTML(true);
            
            // Subject
            $MAIL->Subject = "Password reset";

            // Generate
            $UNIQUE = $OTHER_MANAGER->random_string(16);

            // Link
            $SITE = $OTHER_MANAGER->get_url();
            $URL = "$SITE/backend/password.php?code=$UNIQUE";

            // Content
            $MAIL->Body = "1. Visit this link if you want to reset your password: <a href=$URL>visit</a><br>2. After using the link above, this should be your new password for authentification: <i><u>$UNIQUE</u></i>";
            
            // Send
            $MAIL->send();

            // Request
            $INSERT =
            [
                'email' => $EMAIL,
                'code' => $UNIQUE,
                'time' => $CURRENT_TIME,
            ];
            
            // Insert
            $STATUS = DB::insert('requests_reset', $INSERT);

            // Result
            $RESULT = $STATUS == true && DB::affectedRows() == 1;
    
            return 
            [
                "status" => $RESULT,
                "string" => "",
            ];
        }
        catch (Exception $ERROR)
        {
            return
            [
                "status" => false,
                "string" => $ERROR,
            ];
        }
    }

    function reset_handle($CODE)
    {
        // Import managers
        global $OTHER_MANAGER;

        // Code is unique for each user
        $ROW = DB::queryFirstRow("SELECT * FROM requests_reset WHERE code=%s", $CODE);

        // Does it exist?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "Specified code is invalid.",
            ];
        }

        // Does the user from the code exist?
        $EMAIL = $ROW['email'];

        // Row from email
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE email=%s", $EMAIL);

        // Does it exist?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "Account with that email was found invalid in the reset process, please contact an administrator.",
            ];
        }

        // Validate
        $USERNAME = $ROW['username'];

        if ($this->valid_username($USERNAME) == false)
        {
            return
            [
                "status" => false,
                "string" => "Username was found invalid in the reset process, please contact an administrator.",
            ];
        }

        // Data
        $DATA = $OTHER_MANAGER->generate_encrypted($CODE);
        
        // Password
        $HASHED_PASSWORD = $DATA['data'];
            
        // Base64
        $BASE64_ENC_KEY = base64_encode($DATA['enc_key']);
        $BASE64_IV = base64_encode($DATA['iv']);

        // New
        $UPDATE =
        [
            'hashed_password' => $HASHED_PASSWORD,
            'enc_key' => $BASE64_ENC_KEY,
            'iv' => $BASE64_IV,
        ];

        // Update
        $STATUS = DB::update('accounts', $UPDATE, "username=%s", $USERNAME);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;
        
        // No problems?
        if ($RESULT == false)
        {
            return 
            [
                "status" => false,
                "string" => "Reset failed, please contact an administrator.",
            ];
        }
        
        // Delete
        $STATUS = DB::delete('requests_reset', 'code=%s', $CODE);
        
        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        return 
        [
            "status" => $RESULT,
            "string" => "",
        ];
    }

    function attempt_change($USERNAME, $PASSWORD, $NEW)
    {
        // Import managers
        global $VALIDATION_MANAGER;
        global $OTHER_MANAGER;

        // Same?
        if ($NEW == $PASSWORD)
        {
            return
            [
                "status" => false,
                "string" => "Changing to the same password is not available.",
            ];
        }

        // Proper new one?
        if ($VALIDATION_MANAGER->password($NEW) == false)
        {
            return
            [
                "status" => false,
                "string" => "Password must contain only latin letters, at least 1 uppercase letter, at least 1 lowercase letter, at least 1 number, while having at least 8 out of 128 symbols.",
            ];
        }

        // User
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

        // User valid?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "Provided account does not exist.",
            ];
        }

        // Did the user specify the correct password?
        $COMPARISON = $this->compare_passwords($ROW, $PASSWORD);

        if ($COMPARISON == false)
        {
            return
            [
                "status" => false,
                "string" => "The entered password is incorrect.",
            ];
        }

        // Get the array with all of the hashed data
        $HASHED_ARRAY = $OTHER_MANAGER->generate_encrypted($NEW);
        $HASHED_PASSWORD = $HASHED_ARRAY['data'];

        // Base64
        $BASE64_ENC_KEY = base64_encode($HASHED_ARRAY['enc_key']);
        $BASE64_IV = base64_encode($HASHED_ARRAY['iv']);

        // New data for row
        $UPDATE =
        [
            'hashed_password' => $HASHED_PASSWORD,
            'enc_key' => $BASE64_ENC_KEY,
            'iv' => $BASE64_IV,
        ];

        // Update
        $STATUS = DB::update('accounts', $UPDATE, "username=%s", $USERNAME);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        return 
        [
            "status" => $RESULT,
            "string" => "",
        ];
    }

    function has_hwid_cooldown($ROW)
    {
        $CURRENT_TIME = time();
        $COOLDOWN_TIME = 86400;
        
        return $CURRENT_TIME < $ROW['hwid_update'] + $COOLDOWN_TIME;
    }
    
    function log_hwid($ROW, $HWID)
    {
        // Time
        $CURRENT_TIME = time();

        // Element
        $ARRAY =
        [
            'hwid' => $HWID,
            'time' => $CURRENT_TIME,
        ];

        // Current logs
        $CURRENT = $ROW['hwid_logs'];

        // Serve it as an element on the first time, not as an independent array
        $PUSHED = false;

        if (empty($CURRENT) == true || $CURRENT == "null")
        {
            // Push to the dummy
            $PLACEHOLDER = [];
            array_push($PLACEHOLDER, $ARRAY);

            // So that we do not push an array inside of an array.
            $PUSHED = true;

            // Assign
            $ARRAY = $PLACEHOLDER;
        }

        if ($PUSHED == false)
        {
            // Current one, should be already an array
            $DECODED = json_decode($CURRENT, true);
            
            // Keep only 30, more will cause issues related to sharing suspicion, it's already a lot also
            if (count($DECODED) >= 30)
            {
                // Erase everything
                $DECODED = [];
            }

            // No need to mess with the decoded, rather create a variable instead.
            $DUMMY = $DECODED;

            // Is actually an array?
            if (is_array($DECODED))
            {
                // Push to dummy
                array_push($DUMMY, $ARRAY);

                // Assign to main variable
                $ARRAY = $DUMMY;
            }
        }

        // Encoded JSON
        $ENCODED = json_encode($ARRAY);
        
        // Update
        $UPDATE =
        [
            'hwid_logs' => $ENCODED,
        ];

        // Update
        DB::update('accounts', $UPDATE, "username=%s", $ROW['username']);
    }

    function has_sharing_suspicion($ROW)
    {
        // Logs
        $LOGS = $ROW['hwid_logs'];
        
        // Validate
        if (empty($LOGS) == true || $LOGS == "null")
        {
            // Return
            return false;
        }

        // Decoded
        $LOGS = json_decode($LOGS, true);

        // Countup of every log with same HWID
        $BUFFER = [];
        
        foreach ($LOGS as $LOG)
        {
            // HWID
            $HWID = $LOG['hwid'];
        
            // Validate
            if (array_key_exists($HWID, $BUFFER) == false)
            {
                $BUFFER[$HWID] = 0;
            }
        
            // Add to count
            $BUFFER[$HWID] = $BUFFER[$HWID] + 1;
        }
        
        // Logs that repeat 2 times
        $POSITIVE = [];
        
        foreach ($BUFFER as $HWID => $COUNT)
        {
            if ($COUNT >= 2)
            {
                array_push($POSITIVE, $HWID);
            }
        }
        
        // Suspicions moments
        $COUNT = 0;
        
        foreach ($POSITIVE as $HWID)
        {
            $COUNT++;
        }
        
        // Decision?
        $SUSPICOUS = $COUNT >= 3;
    
        // Return
        return $SUSPICOUS;
    }

    function attempt_hwid($USERNAME, $PASSWORD, $HWID)
    {       
        // Get the row here, no need to use the 'exists' function, since we will need to use that row later anyway.
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

        // Does it exist?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "The account that you are trying to reset the password of, does not exist.",
            ];
        }

        // Did the user specify the correct password?
        $COMPARISON = $this->compare_passwords($ROW, $PASSWORD);

        if ($COMPARISON == false)
        {
            return
            [
                "status" => false,
                "string" => "The entered password is incorrect.",
            ];
        }

        // Length
        if (strlen($HWID) > 64)
        {
            die("Specified HWID is too long.");
        }

        // Current HWID
        $CURRENT_HWID = $ROW['hwid'];
        
        // Resetting to current?
        if ($CURRENT_HWID == $HWID)
        {
            die("You can't change your HWID to your current one.");
        }

        // Cooldown / allow the user to instantly change the HWID after reset
        $AFTER_RESET = $CURRENT_HWID != "null" || empty($CURRENT_HWID) == true;

        if ($this->has_hwid_cooldown($ROW) && $AFTER_RESET)
        {
            die("You have a cooldown on resetting your HWID. The cooldown is 24 hours, so you better wait for that to end before proceeding.");
        }

        // New data for row
        $CURRENT_TIME = time();

        $UPDATE =
        [
            'hwid' => $HWID,
            'hwid_update' => $CURRENT_TIME,
        ];

        // Update
        $STATUS = DB::update('accounts', $UPDATE, "username=%s", $USERNAME);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        return 
        [
            "status" => $RESULT,
            "string" => "",
        ];
    }

    function attempt_request($METHOD, $USERNAME, $PASSWORD, $GAME, $WALLET)
    {
        // Get the row here, no need to use the 'exists' function, since we will need to use that row later anyway.
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

        // Does it exist?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "The account that you are trying to redeem the license on does not exist.",
            ];
        }

        // Did the user specify the correct password?
        $COMPARISON = $this->compare_passwords($ROW, $PASSWORD);

        if ($COMPARISON == false)
        {
            return
            [
                "status" => false,
                "string" => "The entered password is incorrect.",
            ];
        }
        
        // Monero
        if ($METHOD == "monero")
        {
            // Wallet length, should be 95 maximum.
            if (strlen($WALLET) > 95)
            {
                return
                [
                    "status" => false,
                    "string" => "Monero wallets are 95 symbols maximum, consider resolving the issue yourself or contact an administrator.",
                ];
            }

            // Is there a row with the same payment method from the same user?
            $PURCHASE_ROW = DB::queryFirstRow("SELECT * FROM requests_purchase WHERE username=%s AND method=%s AND wallet=%s", $USERNAME, $METHOD, $WALLET);
        
            if ($PURCHASE_ROW != null)
            {
                return
                [
                    "status" => false,
                    "string" => "There is already a pending request with the specified wallet.",
                ];
            }
            
            // Request
            $INSERT =
            [
                'username' => $USERNAME,
                'method' => $METHOD,
                'wallet' => $WALLET,
                'game' => $GAME,
            ];
    
            // Insert
            $STATUS = DB::insert('requests_purchase', $INSERT);

            // Result
            $RESULT = $STATUS == true && DB::affectedRows() == 1;
    
            return 
            [
                "status" => $RESULT,
                "string" => "",
            ];
        }
        else
        {
            return
            [
                "status" => false,
                "string" => "Specified payment method is not supported.",
            ];
        }
    }

    function attempt_redeem($USERNAME, $PASSWORD, $GAME, $KEY)
    {
        // Import data
        global $MENU_DATA;

        // Import managers
        global $GAMES_MANAGER;

        // Get the row here, no need to use the 'exists' function, since we will need to use that row later anyway.
        $ROW = DB::queryFirstRow("SELECT * FROM accounts WHERE username=%s", $USERNAME);

        // Does it exist?
        if ($ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "The account that you are trying to redeem the license on does not exist.",
            ];
        }

        // Did the user specify the correct password?
        $COMPARISON = $this->compare_passwords($ROW, $PASSWORD);

        if ($COMPARISON == false)
        {
            return
            [
                "status" => false,
                "string" => "The entered password is incorrect.",
            ];
        }

        // Does the game exist?
        $TABLE = $GAMES_MANAGER->table();

        if (array_key_exists($GAME, $TABLE) == false)
        {
            return
            [
                "status" => false,
                "string" => "The game, you are trying to use for redeemal, does not exist.",
            ];
        }

        // Does this license key exist?
        $REDEEM_ROW = DB::queryFirstRow("SELECT * FROM redeem_keys WHERE code=%s", $KEY); 

        if ($REDEEM_ROW == null)
        {
            return
            [
                "status" => false,
                "string" => "The specified license key is invalid.",
            ];
        }

        // Is this even the right game?
        $REDEEM_GAME = $REDEEM_ROW['game'];

        if ($GAME != $REDEEM_GAME)
        {
            return
            [
                "status" => false,
                "string" => "This license key is intended for another game.",
            ];
        }

        // Versions variable, decided to put it here
        $VERSIONS = $MENU_DATA['games'][$GAME]['versions'];

        // Is the version valid for this game?
        $REDEEM_VERSION = $REDEEM_ROW['version'];

        if (in_array($REDEEM_VERSION, $VERSIONS) == false)
        {
            return
            [
                "status" => false,
                "string" => "The version for this game is not valid, please contact an administrator to resolve this issue.",
            ];
        }

        // User games
        $RAW = $this->get_user_games($ROW);
        $TABLE = json_decode($RAW, true);

        // Is the user trying to reedem the thing he already has?
        $VERSION = $TABLE[$GAME];

        if ($VERSION == $REDEEM_VERSION)
        {
            return
            [
                "status" => false,
                "string" => "Reedeming something that you already have is not possible.",
            ];
        }

        // Is the user trying to go back to the worse version?
        $CURRENT_POSITION = array_search($TABLE[$GAME], $VERSIONS);
        $REDEEM_POSITION = array_search($REDEEM_VERSION, $VERSIONS);

        if ($REDEEM_POSITION < $CURRENT_POSITION)
        {
            return
            [
                "status" => false,
                "string" => "Redeeming a version that is worse is not possible, if you want to do that - please contact an administrator.",
            ];
        }

        // New games string
        $NEW_TABLE = $TABLE;

        // Give the version out to the user
        $NEW_TABLE[$GAME] = $REDEEM_VERSION;

        // Stringify
        $NEW_TABLE = json_encode($NEW_TABLE);

        // New data for row
        $UPDATE =
        [
            'games' => $NEW_TABLE,
        ];

        // Update
        $STATUS = DB::update('accounts', $UPDATE, "username=%s", $USERNAME);

        // Result
        $RESULT = $STATUS == true && DB::affectedRows() == 1;

        // Went successfull actually? Delete the key then
        if ($RESULT)
        {
            // Delete key
            $STATUS = DB::delete('redeem_keys', 'code=%s', $KEY);

            // Change result
            $RESULT = $STATUS == true && DB::affectedRows() == 1;
        }

        return
        [
            "status" => $RESULT,
            "string" => "",
        ];
    }
}

$ACCOUNT_MANAGER = new ACCOUNTS;

?>