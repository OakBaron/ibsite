<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  
  //Getting class settings
  $i=0;//for the comma
  if(isset($_POST['classes'])){
    foreach($_POST['classes'] as $class){
      if($i==1){//for the comma
          $classes .= ",";
      }else{
          $i=1;
      }
      $classes .= $class;
    }
  }else{
    $classes = "";
  }
  $id = $current_user;
  
  //Making sure username is legal
  if(strlen($_POST["name"])>20){
    msg("You cannot have a name with more than 20 characters");
    header("Location: /user/updateInfo.php");
    exit();
  }else if(strContains($_POST["name"], " ")){
    msg("You cannot have a space in your username");
    header("Location: /user/updateInfo.php");
    exit();
  }else if(preg_match('#[^a-zA-Z0-9\-_]+#', $_POST["name"])){
    preg_match_all('#[^a-zA-Z0-9\-_]#', $_POST["name"], $match);
    for($i=0; $i<sizeof($match[0]);$i++){
      $char .= $match[0][$i].", ";
    }
    $char = substr($char, 0, -2);
    msg("Please only use -, _, and alphanumeric characters (don't use $char)");
    header("Location: /user/settings.php");
    exit();
  }
  //Making sure the username isn't taken
  if(getUserInfoByName($_POST["name"])->name != "" && getUserInfoByName($_POST["name"])->id != $id){
    msg("That name is already taken");
    header("Location: /user/updateInfo.php");
    exit();
  }
  
  //Actually putting the info in the database
  conn();
  $stmt = $GLOBALS['conn']->prepare("UPDATE users SET name = :nm, grade = :gd, classes = :cs, dark_theme = :dt, snow = :sw WHERE id = :id");
  $stmt->bindParam(":nm", $_POST["name"]);
  $stmt->bindParam(":gd", $_POST["grade"]);
  $stmt->bindParam(":cs", $classes);
  $stmt->bindParam(":dt", $_POST["dark_theme"]);
  $stmt->bindParam(":sw", $_POST["snow"]);
  $stmt->bindParam(":id", $id);
  $stmt->execute();
  
  if($stmt){
    msg("Information updated");
  }else{
    reportError("Error given at end of /user/updateFunc.php");
    msg("Error updating. It has been reported.");
  }
  header("Location: https://ib.lukeogburn.com/user/?user=".$id);
?>