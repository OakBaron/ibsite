<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  $pid = $_GET["post"];
  $uid = $current_user;
  $cmt = $_POST["comment"];
  $cmt = encodeUserLink($cmt, $uid, $pid);
  
  $stmt = $conn->prepare("INSERT INTO comments (post_id, poster_id, text) VALUES (:pid, :uid, :cmt)");
  $stmt->bindParam(":pid", $pid);
  $stmt->bindParam(":uid", $uid);
  $stmt->bindParam(":cmt", $cmt);
  $result = $stmt->execute();
  
  header("Location: /forum/post/?post=$pid#bottomOfComments");
?>