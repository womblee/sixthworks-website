<?php

class INPUT_VALIDATION
{
  function test_input($DATA)
  {
    $DATA = trim($DATA);
    $DATA = stripslashes($DATA);
    $DATA = htmlspecialchars($DATA);
  
    return $DATA;
  }

  function username($STR)
  {
    $REGEX = "/^[a-z0-9_-]{6,30}$/";
  
    return preg_match($REGEX, $STR) === 1 ? true : false;
  }
    
  function password($STR)
  {
    $REGEX = "/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{8,128}$/"; 
  
    return preg_match($REGEX, $STR) === 1 ? true : false;
  }

  function email($STR)
  {
    $REGEX = "/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/";

    return preg_match($REGEX, $STR) === 1 ? true : false;
  }
}

$VALIDATION_MANAGER = new INPUT_VALIDATION;

?>