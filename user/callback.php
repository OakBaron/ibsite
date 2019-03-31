<?php
  require_once("config.php");
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  
  if($GLOBALS["verified"]){
    //No idea what this used to do, but I'm scared to get rid of it
    //header("Location: https://ib.lukeogburn.com/user/?user=".$_COOKIE["IBSITE_ID"]);
  }
  if(isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
  } else {
    reportError('callback.php: $_GET["code"] was not set!');
    msg("Internal error. It has been reported.");
    header("Location: /");
  }
  
  $oAuth = new Google_Service_Oauth2($client);
  $user = $oAuth->userinfo->get();
  
  //Adding cookie token thing
  conn();
  $stmt = $conn->prepare("INSERT INTO login_tokens (token, user_id) VALUES (:ac, :id)");
  $id = substr($user->email, 0, strlen("@students.hcps.us"));
  $access = password_hash($token["access_token"], PASSWORD_DEFAULT);
  $stmt->bindParam(':ac', $access);
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  
  //Updating last login timestamp
  $stmt = $conn->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = :id");
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  
  setcookie("IB_SESSION", $token["access_token"], time() + (60*60*24*14), "/", NULL, true, true);
  setcookie("IB_ID", substr($user->email, 0, strlen("@students.hcps.us")), time() + (60*60*24*14), "/", NULL, true, true);
  
  //Checking if user is in database
  $dbID = getUserInfo($id)->id; //$id from above used
  
  if(substr($user->email, -7) != "hcps.us"){
    header("Location: https://ib.lukeogburn.com/user/reqHcps.php");
  }else if($id!=$dbID){
    //putting user in database if they aren't already
    $stmt = $conn->prepare("INSERT INTO users (id, name, image_url, teacher) VALUES (:id, :nm, :im, :tc)");
    $stmt->bindParam(':id', $id);
    $name = str_replace(" ", "_", $user["name"]);
    $stmt->bindParam(':nm', $name);
    $stmt->bindParam(':im', $user["picture"]);
    $teacher = is_numeric($id)?NULL:true;
    $stmt->bindParam(':tc', $teacher);
    $stmt->execute();
    if(!$stmt){
      reportError("Error signing in (013)");
      msg("Error. Try again, maybe? This has been reported.");
      header("Location: /");
    }
    
    msg("You have been logged in");
    header("Location: https://ib.lukeogburn.com/forum/");
  }else{
    //updating the user's profile picture just in case they changed it in Google
    $stmt = $GLOBALS['conn']->prepare("UPDATE users SET image_url = :im WHERE id = :id");
    $stmt->bindParam(':im', $user->picture);
    $stmt->bindParam(':id', $id);
    $result = $stmt->execute();
    if(!$result){
      reportError("Error in callback - code 014");
      msg("Error. It has been reported. Try again, maybe?");
      header("Location: /");
    }
    
    msg("You have been logged in");
    header("Location: https://ib.lukeogburn.com/forum/");
  }
?>