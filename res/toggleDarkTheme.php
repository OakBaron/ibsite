<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  
  if(getUserInfo($current_user)->dark_theme){
    $stmt = $conn->prepare("UPDATE users SET dark_theme = 0 WHERE id = :usr");
    $stmt->bindParam(":usr", $current_user);
    $stmt->execute();
  }else{
    $stmt = $conn->prepare("UPDATE users SET dark_theme = 1 WHERE id = :usr");
    $stmt->bindParam(":usr", $current_user);
    $stmt->execute();
  }
  
  if($stmt){
    msg("Theme updated.");
  }else{
    msg("Error updating theme.");
  }
  
  header("Location: /user/settings.php");
?>