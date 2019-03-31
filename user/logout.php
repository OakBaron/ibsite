<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  $stmt = $conn->prepare("DELETE FROM login_tokens WHERE user_id = :tk");
  $stmt->bindParam(":tk", $_COOKIE["IB_ID"]);
  $stmt->execute();
  
  setcookie("IB_ID", $_COOKIE["IB_ID"], time()-3600, "/");
  setcookie("IB_SESSION", $_COOKIE["IB_SESSION"], time()-3600, "/");
  
  header("Location: finishLogout.php");
  //Without this, PHP can't tell the cookie was deleted. It's dumb but it works.
?>