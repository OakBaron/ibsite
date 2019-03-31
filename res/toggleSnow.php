<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  
  if(getUserInfo($current_user)->snow){
    $stmt = $conn->prepare("UPDATE users SET snow = 0 WHERE id = :usr");
    $stmt->bindParam(":usr", $current_user);
    $stmt->execute();
  }else{
    $stmt = $conn->prepare("UPDATE users SET snow = 1 WHERE id = :usr");
    $stmt->bindParam(":usr", $current_user);
    $stmt->execute();
  }
  
  if($stmt){
    msg("Snow setting changed.");
  }else{
    msg("Error changing snow.");
  }
  
  header("Location: /user/settings.php");
?>