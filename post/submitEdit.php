<?php
  
/**
 * Please note that files uploaded are placed in /forum/images (even non-image 
 * files) because this function used to only be for images. It has since been
 * updated to allow for doc files too.
 * 
**/
  
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  
  $pid = $_POST["pid"];
  $sbj = $_POST["section"];
  $typ = $_POST["type"];
  $ttl = $_POST["title"];
  $ctt = encodeUserLink($_POST["content"]);
  $file = $_FILES["images"];
  $oldImg = $conn->prepare("SELECT image FROM forums WHERE post_id = :pid");
  $oldImg->bindParam(":pid", $pid);
  $oldImg->execute();
  $oldImg = $oldImg->fetch(PDO::FETCH_ASSOC);
  //$oimg = $_POST["orderedImgs"]==""?NULL:$_POST["orderedImgs"];
  //if($oimg != NULL){
    //$oimg = str_replace("-", ",", $oimg).",";
    //$oimg = str_replace("jpg", ".jpg", $oimg);
    //$oimg = str_replace("jpeg", ".jpeg", $oimg);
    //$oimg = str_replace("png", ".png", $oimg);
  //}
  
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
        header("Location: /post/edit.php?post=$pid");
        exit(); //this is needed for some reason
      }
    }
  }else{
    $img = NULL;
  }
  
  $oimg = implode(",", $oldImg).$img;
  echo $oImg;
  //exit();
  
  $stmt = $conn->prepare("UPDATE forums SET section = :scn, type = :typ, title = :ttl, content = :ctt, image = :img WHERE post_id = :pid");
  $stmt->bindParam(":scn", $sbj);
  $stmt->bindParam(":typ", $typ);
  $stmt->bindParam(":ttl", $ttl);
  $stmt->bindParam(":ctt", $ctt);
  $stmt->bindParam(":img", $oimg);
  $stmt->bindParam(":pid", $pid);
  $stmt->execute();
  if($stmt){
    msg("Edits saved.");
    header("Location: /forum/post/?post=$pid");
  }else{
    reportError("Error submitting post edit");
    msg("Something's broken. It has been reported.");
    header("Location: /forum/post/?post=$pid");
  }
?>