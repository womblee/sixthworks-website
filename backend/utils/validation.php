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
  
  function validate($REGEX, $STR)
  {
    return preg_match($REGEX, $STR) === 1 ? true : false;
  }

  function username($STR)
  {
    return $this->validate("/^[a-z0-9_-]{6,30}$/", $STR);
  }
    
  function password($STR)
  {
    return $this->validate("/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{8,128}$/", $STR);
  }

  function email($STR)
  {
    return $this->validate("/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-z]{2,}$/", $STR);
  }
}

$VALIDATION_MANAGER = new INPUT_VALIDATION;

?>