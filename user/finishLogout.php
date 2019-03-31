<?php
  if(!isset($_COOKIE['IB_ID']) && !isset($_COOKIE['IB_SESSION'])){
    header("Location: /");
  }else{
    reportError("Error in /user/finishLogout.php");
    msg("There was an error logging you out. It has been reported.");
    header("Location: /");
  }
?>