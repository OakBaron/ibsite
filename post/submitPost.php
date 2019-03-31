<?php

/**
 * Please note that files uploaded are placed in /forum/images (even non-image 
 * files) because this function used to only be for images. It has since been
 * updated to allow for doc files too.
 * 
**/

  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  
  $pid = randID();
  $uid = $current_user;
  $sbj = $_POST["section"];
  $typ = $_POST["type"];
  $ttl = $_POST["title"];
  $ctt = hyperLink($_POST["content"], $uid, $pid);
  $ctt = encodeUserLink($ctt, $uid, $pid);
  $file = $_FILES["images"];
  
  if($file["name"][0]!=NULL){
    for($i=0; $i<sizeof($file["name"]); $i++){
      $ext = explode('.', $file["name"][$i]);
      $ext = strtolower($ext[sizeof($ext)-1]);
      $allowedExt = array('jpg', 'jpeg', 'png', 'doc', 'docx', 'pdf');
      if(in_array($ext, $allowedExt)){
        if(!$file["error"][$i]){
          $imgDest = randID().".".$ext;
          $img .= $imgDest.",";
          $dest = $_SERVER['DOCUMENT_ROOT']."/forum/images/".$imgDest;
          move_uploaded_file($file["tmp_name"][$i], $dest);
        }else{
          echo "Error uploading file";
          exit();
        }
      }else{
        msg("Bad file type.");
        header("Location: /post");
        exit(); //this is needed for some reason
      }
    }
  }else{
    $img = NULL;
  }
  
  $stmt = $conn->prepare("INSERT INTO forums (post_id, poster_id, section, type, title, content, image) VALUES (:pid, :uid, :sbj, :typ, :ttl, :ctt, :img)");
  $stmt->bindParam(":pid", $pid);
  $stmt->bindParam(":uid", $uid);
  $stmt->bindParam(":sbj", $sbj);
  $stmt->bindParam(":typ", $typ);
  $stmt->bindParam(":ttl", $ttl);
  $stmt->bindParam(":ctt", $ctt);
  $stmt->bindParam(":img", $img);
  $stmt->execute();
  if($stmt){
    header("Location: /forum/post/?post=$pid");
  }else{
    msg("Couldn't submit post. This has been reported for you.");
    header("Location: /forum");
  }
?>