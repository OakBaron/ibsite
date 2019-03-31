<?php
  //This fixes the "headers already sent" issue noticed in the msg system
  ob_start();
  
  //error handling
  function errorHandler($n, $m, $f, $l) {
    if($n > 10){
      reportError("Level $n error: $m in file \"$f\" on line $l");
    }
  }
  set_error_handler('errorHandler');
    
  //msg handling (msg delivered in /res/top)
  if(isset($_COOKIE["IB_MSG"])){
    $msg = $_COOKIE["IB_MSG"];
    setcookie("IB_MSG", $_COOKIE["IB_MSG"], $_SERVER['REQUEST_TIME']-3600, "/");
  }else{
    $msg = NULL;
  }
  
  require "conn.php";
  //conn.php used to be part of this file, but I don't want to accidentally upload it to github
  
  //Verifying user with cookie
  if(isset($_COOKIE["IB_SESSION"])){
    $cookie = $_COOKIE["IB_SESSION"];
    conn();
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM login_tokens WHERE user_id = :id");
    $stmt->bindParam(":id", $_COOKIE["IB_ID"]);
    $stmt->execute();
    $row = $stmt->fetchAll();
    $GLOBALS["verified"] = $verified = false;
    for($i=0; $i<sizeof($row); $i++){
      $token = $row[$i]["token"];
      if(password_verify($cookie, $token)){
        $GLOBALS["verified"] = $verified = true;
        // both $current_user and $GLOBALS["current_user"]
        // are used later, so don't delete either one
        $current_user = $GLOBALS["current_user"] = $_COOKIE["IB_ID"];
      }
    }
  }else{
      header("Location: /user/login.php");
  }
  if(!$GLOBALS["verified"]){
      header("Location: /user/login.php");
  }
  
  function getUserInfo($id){
    conn();
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM users WHERE id = :id");
    $id = strval($id); //Just making sure
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if($result==NULL){
      return false;
    }else{
      return $result;
    }
  }
  
  function getUserInfoByName($name){
    conn();
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM users WHERE name = :name");
    $stmt->bindParam(":name", $name);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    if($result==NULL){
      return false;
    }else{
      return $result;
    }
  }
  
  //Making sure that the user isn't banned
  if(getUserInfo($current_user)->special == "banned" && $GLOBALS["page"] != "banned"){
    header("Location: /user/banned.php");
  }
  
  function strContains($haystack, $needle){
    if(strpos($haystack, $needle)!==false){
      return true;
    }else{
      return false;
    }
  }
  
  function verifyID($pid){
    conn();
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM forums WHERE post_id = :pid");
    $stmt->bindParam(":pid", $pid);
    $stmt->execute();
    $forums = $stmt->fetch(PDO::FETCH_OBJ)->post_id;
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM forums WHERE image = :pid");
    $stmt->bindParam(":pid", $pid);
    $stmt->execute();
    $images = $stmt->fetch(PDO::FETCH_OBJ)->image;
    return ($forums!=NULL||$images!=NULL)?false:true;
  }
  
  function randID($length = 7) {
    do{
      if(function_exists("random_bytes")){
        $bytes = random_bytes(ceil($length/2));
      }elseif(function_exists("openssl_random_pseudo_bytes")){
        $bytes = openssl_random_pseudo_bytes(ceil($length/2));
      }else{
        throw new Exception("No cryptographically secure random function available.");
      }
      $x = substr(bin2hex($bytes), 0, $length);
      $id = gmp_strval(gmp_init($x, 36), 62);
    } while(!verifyID($id));
    return $id;
  }
  
  function makeDate($date){
    $date = strtotime($date);
    $now = $_SERVER['REQUEST_TIME'];
    $datediff = $now - $date;
    $secs  = round($datediff);
    $mins  = round($datediff / (60));
    $hours = round($datediff / (60 * 60));
    $days  = round($datediff / (60 * 60 * 24));
    
    if($secs<60){
      if($secs == 1){
        $date = $secs." second ago";
      }else{
        $date = $secs." seconds ago";
      }
    }else if($mins<60){
      if($mins == 1){
        $date = $mins." minute ago";
      }else{
        $date = $mins." minutes ago";
      }
    }else if($days<1){
      if($hours == 1){
        $date = $hours." hour ago";
      }else{
        $date = $hours." hours ago";
      }
    }else if($days < 8){
      if($days == 1){
        $date = " yesterday";
      }else{
        $date = $days." days ago";
      }
    }else{
      $date = date("M j", $date);
    }
    
    return $date;
  }
  
  function verifyUser($level, $id = NULL){
      if($id == NULL){
        $id = $GLOBALS["current_user"] ;
      }
      
      $oldLevel = $level;
      
      switch($level){
        case "admin":
          $level = 1;
          break;
        case "owner";
          $level = 2;
          break;
        default:
          $level = 0;
          break;
      }
      
      conn();
      $stmt = $GLOBALS['conn']->prepare("SELECT special FROM users WHERE id = :id");
      $stmt->bindParam(":id", $id);
      $stmt->execute();
      $row = $stmt->fetch(PDO::FETCH_OBJ);
      $extra = $row->special;
      
      switch($extra){
        case "admin":
          $userLevel = 1;
          break;
        case "owner":
          $userLevel = 2;
          break;
        default:
          $userLevel = 0;
      }
      
      if($userLevel>=$level){
        echo "<!-- ".getUserInfo($id)->name." verified as an $oldLevel -->\n";
        return true;
      }else{
        return false;
      }
  }
  
  function restrictAccess($level){
    if(!verifyUser($level)){
      $current_user = $GLOBALS["current_user"];
      msg("You don't have access to that file");
      $username = getUserInfo($current_user)->name;
      $file = getcwd()."/".basename(__FILE__);
      reportError("User $current_user ($username) tried to access $file");
      header("Location: /");
    }
  }
  
  function alertDelete($id){
    conn();
    $stmt = $GLOBALS['conn']->prepare("DELETE FROM alerts WHERE id = :id");
    $stmt->bindValue(":id", $id);
    $stmt->execute();
    if(!$stmt){
      reportError("Error in globalFuncs:alertDelete - stmt failed");
      msg("Error removing mention from sidebar. This has been reported for you.");
    }
  }
  
  function alertInsert($er, $ee, $id){
    if($er == NULL){
        $er = $GLOBALS["current_user"];
    }
    conn();
    $stmt = $GLOBALS['conn']->prepare("SELECT id FROM comments ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);
    $maxID = intval($result->id);
    $maxID++;
    
    $stmt = $GLOBALS['conn']->prepare("INSERT INTO alerts (id, mentioner, mentionee, post_id) VALUES (:mid, :er, :ee, :id)");
    $stmt->bindValue(":mid", $maxID);
    $stmt->bindValue(":er", $er);
    $stmt->bindValue(":ee", $ee);
    $stmt->bindValue(":id", $id);
    $stmt->execute();
    if(!$stmt){
      reportError("Issue in globalFuncs:alertInsert - stmt failed");
      msg("Error in the mention system - ".getUserInfo($ee)->name." could not be alerted to your mention.");
    }
  }
  
  function hyperSearch($text){
    preg_match_all("#((((-|_){0,}\w)+\.([a-zA-Z]+){2,})|((((http)|(https)):\/\/){1}((-|_){0,}\w)+\.([a-zA-Z]+){2,}))((\w+|-|_|\/|\.)+){0,}#", $text, $match);
    if($match[0]==NULL){
      return false;
    }else{
      return $match[0];
    }
  }
  
  function hyperlink($text){
      $link = hyperSearch($text);
      if($link != false){
        foreach($link as $link){
          $content = "<a class=userRefLink target=_BLANK href=".$link.">".$link."</a>";
          $text = str_replace($link, $content, $text);
        }
      }
      return $text;
    }
  
  function linkSearch($text){
    if(strContains($text, "@")){
      preg_match_all("#(?<=@)(.?)(?:[\w\-_])+#", $text, $match);
      return $match[0];
    }else{
     return false;
    }
  }
  
  function encodeUserLink($info, $er = NULL, $id = NULL){
    // Searching for user links
    $link = linkSearch($info);
    if($link != false){
      $link = array_filter($link);
      foreach($link as $thing){
        $user = getUserInfoByName(strval($thing));
        if($user!=false){
          $info = str_replace($thing, $user->id, $info);
          //For the mention (alert) system
          alertInsert($er, $user->id, $id);
        }else if(getUserInfo($thing) != false){
          //For the mention (alert) system
          alertInsert($er, $thing, $id);
        }
      }
    }
    return $info;
  }
  
  function decodeUserLink($info){
      $link = linkSearch($info);
      if($link != false){
        $link = array_filter($link);
        foreach($link as $thing){
        $user = getUserInfo(strval($thing));
        if($user!=false){
          $content = "<a class=userRefLink href=/user/?user=".$user->id.">".$user->name."</a>";
          $info = str_replace($thing, $content, $info);
        }else if(!$user && getUserInfoByName(strval($thing))!=false){
          $user = getUserInfoByName(strval($thing));
          $content = "<a class=userRefLink href=/user/?user=".$user->id.">".$user->name."</a>";
          $info = str_replace($thing, $content, $info);
        }
      }
    }
    return $info;
  }
  
  function decodeUserNoLink($info){
    $link = linkSearch($info);
    if($link != false){
      $link = array_filter($link);
      foreach($link as $thing){
        $user = getUserInfo(strval($thing));
        if($user!=false){
          $content = $user->name;
          $info = str_replace($thing, $content, $info);
        }else if(!$user && getUserInfoByName(strval($thing))!=false){
          $user = getUserInfoByName(strval($thing));
          $content = $user->name;
          $info = str_replace($thing, $content, $info);
        }
      }
    }
    return $info;
  }
  
  function reportError($issue){
    $reporter = $_COOKIE["IB_ID"];
    $stmt = $GLOBALS['conn']->prepare("INSERT INTO issue_tracker (reporter, comment, auto) VALUES (:reporter, :issue, :auto)");
    $stmt->bindValue(":reporter", $reporter);
    $stmt->bindValue(":issue", $issue);
    $stmt->bindValue(":auto", true);
    $result = $stmt->execute();
  }
  
  function msg($text){
    // msg is "delivered" in /res/top
    setcookie("IB_MSG", $text, $_SERVER['REQUEST_TIME'] + 60, "/", NULL, true, true);
  }
  
  function getPostInfo($post){
    conn();
    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM forums WHERE post_id = :id");
    $stmt->bindParam(":id", $post);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_OBJ);
  }
  
  function makePost($post){
  if(gettype($post)=="object"){
    //converting object into array if needed, so it can be used here
    $post = json_decode(json_encode($post), True);
  }
 //$img = $post["image"]==NULL?"":"<img class=postPrevImg src=/forum/images/".substr($post["image"], 0, strpos($post["image"], ",")).">";
 //setting some defaults
 $img = $clp = $imgClass = NULL;
 
 $file = substr($post["image"], 0, strpos($post["image"], ","));
 $exType = substr($file, strpos($file, '.')+1);
 $docFiles = ["doc", "docx", "pdf"];
 $imgFiles = ["jpg", "jpeg", "png"];
 //image stuff
 if(in_array($exType, $docFiles)){
   $clp = "<img class='postPrevImg clip' src=/res/i/clip.png>";
 }else if(in_array($exType, $imgFiles)){
   $img = "<img class=postPrevImg src=/forum/images/$file>";
   $imgClass = " image";
 }
 
 
 $ellipsisT = strlen($post["title"])>75?"...":"";
 $ellipsisC = strlen($post["content"])>400?"...":"";
 $section = $post["section"]=="none"?"":ucwords($post["section"]);
 $type = $post["type"]=="other"?"":strtolower($post["type"]);
 $type = $section==""?ucwords($type):$type;
 $section = $section==$type?"No topic":$section;
 
  echo "\n<a href=/forum/post?post=".$post["post_id"]." class=forumLink>
        <div class='forum card".$imgClass."'>
          <div class=left>
            <div class=info>
              <p onclick=\"document.location.href = '/search?q=".strtolower($post["section"]).":+'; return false\" class=postType>".$section." ".$type."</p>
              <p>Posted ".makeDate($post["date"])." by <span onclick=\"document.location.href = '/user/?user=".$post["poster_id"]."'; return false\" class=userlink>".getUserInfo($post["poster_id"])->name."</span></p>
            </div>
            <div class=title>
              <h2>".substr($post["title"], 0, 75).$ellipsisT.$clp."</h2>
            </div>
            <div class=preview>
              <p>".decodeUserNoLink(substr(strip_tags($post["content"]), 0, 400)).$ellipsisC."</p>
            </div>
          </div>
          <div class=right>
            ".$img."
          </div>
        </div>
        </a>";
  }
?>