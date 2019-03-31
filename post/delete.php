<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  
  //Making sure that the user is allowed to delete the post
  if(getPostInfo($_GET["post"])->poster_id != $current_user && !verifyUser("admin")){
    msg("You are not the owner of that post.");
    header("Location: /forum/post?post=".$_GET["post"]);
    exit(); //needed for some reason
  }else{
    conn();
    
    //Moving images/documents
    $getfstmt = $conn->prepare("SELECT image FROM forums WHERE post_id = :pid");
    $getfstmt->bindParam(":pid", $_GET["post"]);
    $getfstmt->execute();
    $images = $getfstmt->fetch(PDO::FETCH_ASSOC);
    
    if(count($images)>0 && $images["image"] != NULL){
      $images = explode(",", substr($images["image"], 0, -1));
      $poster = getPostInfo($_GET["post"])->poster_id;
      $dir = $_SERVER['DOCUMENT_ROOT']."/deletedContent/$poster/";
      if(!is_dir($dir)){
        mkdir($dir);
      }
      mkdir($dir.$_GET["post"]);
      foreach($images as $file){
        $oldName = $_SERVER['DOCUMENT_ROOT']."/forum/images/".$file;
        $newName = $dir.$_GET["post"]."/".$file;
        rename($oldName, $newName);
        if(!file_exists($newName)){
          msg("Unable to delete post");
          header("Location: /forum/post/?post=".$_GET["post"]);
          exit(); //needed for some reason
        }
      }
    }
    
    //Putting post into "deleted" database
    $post = getPostInfo($_GET["post"])->post_id;
    $pstr = getPostInfo($_GET["post"])->poster_id;
    $sctn = getPostInfo($_GET["post"])->section;
    $type = getPostInfo($_GET["post"])->type;
    $date = getPostInfo($_GET["post"])->date;
    $tags = getPostInfo($_GET["post"])->tags;
    $titl = getPostInfo($_GET["post"])->title;
    $cont = getPostInfo($_GET["post"])->content;
    $imag = getPostInfo($_GET["post"])->image;
    $rprt = getPostInfo($_GET["post"])->reports;
    $mfstmt = $conn->prepare("INSERT INTO deletedForums (post_id, poster_id, section, type, date, tags, title, content, image, reports) VALUES (:post, :pstr, :sctn, :type, :date, :tags, :titl, :cont, :imag, :rprt)");
    $mfstmt->bindParam(":post", $post);
    $mfstmt->bindParam(":pstr", $pstr);
    $mfstmt->bindParam(":sctn", $sctn);
    $mfstmt->bindParam(":type", $type);
    $mfstmt->bindParam(":date", $date);
    $mfstmt->bindParam(":tags", $tags);
    $mfstmt->bindParam(":titl", $titl);
    $mfstmt->bindParam(":cont", $cont);
    $mfstmt->bindParam(":imag", $imag);
    $mfstmt->bindParam(":rprt", $rprt);
    $mfstmt->execute();
    
    //post pstr date text rpts
    $getcstmt = $conn->prepare("SELECT * FROM comments WHERE post_id = :pid");
    $getcstmt->bindParam(":pid", $_GET["post"]);
    $getcstmt->execute();
    $comments = $getcstmt->fetchAll();
    $mcstmt = $conn->prepare("INSERT INTO deletedComments (post_id, poster_id, date, text, reports) VALUES (:post, :pstr, :date, :text, :rpts)");
    foreach($comments as $comment){
      $mcstmt->bindParam(":post", $comment["post_id"]);
      $mcstmt->bindParam(":pstr", $comment["poster_id"]);
      $mcstmt->bindParam(":date", $comment["date"]);
      $mcstmt->bindParam(":text", $comment["text"]);
      $mcstmt->bindParam(":rpts", $comment["reports"]);
      $mcstmt->execute();
    }
    if(count($comments) == 0){
        $mcstmt = true;
    }
    
    //Making sure the post was moved correctly
    if(!$mfstmt || !$mcstmt){
        msg("Post could not be deleted.");
        header("Location: /forum/post/?post=".$_GET["post"]);
        exit(); //just in case
    }
    
    //Deleting content from the original databases
    $fstmt = $conn->prepare("DELETE FROM forums WHERE post_id = :pid");
    $fstmt->bindParam(":pid", $_GET["post"]);
    $fstmt->execute();

    $cstmt = $conn->prepare("DELETE FROM comments WHERE post_id = :pid");
    $cstmt->bindParam(":pid", $_GET["post"]);
    $cstmt->execute();
    
    $bstmt = $conn->prepare("DELETE FROM bookmarks WHERE post_id = :pid");
    $bstmt->bindParam(":pid", $_GET["post"]);
    $bstmt->execute();
    
    //Feedback msg and redirection
    if($fstmt && $cstmt && $bstmt){
      msg("Post deleted.");
      header("Location: /forum");
    }else{
      reportError("Error deleting post");
      msg("There was an error deleting your post. It has been reported.");
      header("Location: /forum/post?post=".$_GET["post"]);
    }
  }
?>