<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  
  //Deleting comments
  if(isset($_GET["delc"])){
    $getcstmt = $conn->prepare("SELECT * FROM comments WHERE id = :cid");
    $getcstmt->bindParam(":cid", $_GET["delc"]);
    $getcstmt->execute();
    $comment = $getcstmt->fetch(PDO::FETCH_ASSOC);
    //Putting comment into "deleted" datebase
    $mcstmt = $conn->prepare("INSERT INTO deletedComments (post_id, poster_id, date, text, reports) VALUES (:post, :pstr, :date, :text, :rpts)");
    $mcstmt->bindParam(":post", $comment["post_id"]);
    $mcstmt->bindParam(":pstr", $comment["poster_id"]);
    $mcstmt->bindParam(":date", $comment["date"]);
    $mcstmt->bindParam(":text", $comment["text"]);
    $mcstmt->bindParam(":rpts", $comment["reports"]);
    $mcstmt->execute();
    //Removing the comment from the normal database
    $cstmt = $conn->prepare("DELETE FROM comments WHERE id = :cid");
    $cstmt->bindParam(":cid", $_GET["delc"]);
    $cstmt->execute();
    //Giving feedback and redirecting
    if(!$mcstmt || !$cstmt){
      reportError("A comment couldn't be deleted in /forum/post/index.php");
      msg("Couldn't delete comment. It has been reported for you");
    }else{
      msg("Comment deleted");
    }
    header("Location: /forum/post/?post=".$_GET["post"]);
  }
  //reporting comment
  if(isset($_GET["repc"])){
    $stmt = $conn->prepare("SELECT * FROM comments WHERE id = :id");
    $id = $_GET["repc"];
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    $delc = $stmt->fetch(PDO::FETCH_OBJ);
    $rep = $delc->reports;
    if(strContains($rep, $current_user)){
      $prevRep = true;
    }else{
      $prevRep = false;
      $rep .= $current_user.",";
    }
    $stmt = $conn->prepare("UPDATE comments SET reports = :rep WHERE id = :id");
    $id = $_GET["repc"];
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":rep", $rep);
    $stmt->execute();
    if($stmt){
      msg($prevRep==true?"You already reported that comment.":"Comment reported");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }else{
      reportError("Error reporting comment in /forum/post/index.php - a");
      msg("Error reporting comment. This error has been reported.");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }
  }
  
  //Getting and storing the post info to echo later
  $post = getPostInfo($_GET["post"]);
  $poster_id = $post->poster_id;
  $title = $post->title;
  $content = hyperlink($post->content);
  $content = decodeUserLink($post->content);
  $section = $post->section=="none"?"":ucwords(str_replace("_", " ", $post->section));
  $type = $post->type=="other"?"":strtolower($post->type);
  $type = $section==""?ucwords($type):$type;
  $section = $section==$type?"No topic":$section;
  $date = $post->date;
  $images = $post->image;
  $poster = getUserInfo($poster_id)->name;
  
  //Checking if the user has this post bookmarked for un/bookmarking
  $stmt = $conn->prepare("SELECT * FROM bookmarks WHERE post_id = :pid AND user_id = :uid");
  $stmt->bindParam(":pid", $_GET["post"]);
  $stmt->bindParam(":uid", $current_user);
  $stmt->execute();
  $res = $stmt->fetch(PDO::FETCH_OBJ);
  $bk = $res->user_id==$current_user?true:false;
    
  //Bookmarking the post
  if(isset($_GET["bkmk"]) && $_GET["bkmk"] =="t" && !$bk){
    $stmt = $conn->prepare("INSERT INTO bookmarks (post_id, user_id) VALUES (:pid, :uid)");
    $stmt->bindParam(":pid", $_GET["post"]);
    $stmt->bindParam(":uid", $current_user);
    $stmt->execute();
    if($stmt){
      msg("Post saved. You can find it on your account page.");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }else{
      reportError("Error while saving post in /forum/post/index.php - b");
      msg("Error! It has been reported automatically.");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }
  }
  
  //Unbookmarking the post
  if(isset($_GET["bkmk"]) && $_GET["bkmk"] =="f"&&$bk){
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE post_id = :pid AND user_id = :uid");
    $stmt->bindParam(":pid", $_GET["post"]);
    $stmt->bindParam(":uid", $current_user);
    $stmt->execute();
    if($stmt){
      msg("Post unsaved.");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }else{
      reportError("Error unsaving post in /forum/post/index.php - c");
      msg("Error! It has already been reported for you.");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }
  }
  
  //Checking if the user has this post bookmarked
  $stmt = $conn->prepare("SELECT * FROM bookmarks WHERE post_id = :pid AND user_id = :uid");
  $stmt->bindParam(":pid", $_GET["post"]);
  $stmt->bindParam(":uid", $current_user);
  $stmt->execute();
  $res = $stmt->fetch(PDO::FETCH_OBJ);
  $bk = $res->user_id==$current_user?true:false;
  
  //Reporting the post
  if(isset($_GET["reportPost"])){
    $post = getPostInfo($_GET["post"]);
    $current = $post->reports;
    if(strpos($current, $current_user) === false){
      $new = $current.$current_user.",";
      $stmt = $conn->prepare("UPDATE forums SET reports = :new WHERE post_id = :id");
      $stmt->bindParam(":new", $new);
      $stmt->bindParam(":id", $post->post_id);
      $stmt->execute();
      if($stmt){
        msg("Post reported.");
      header("Location: /forum/post/?post=".$_GET["post"]);
      }else{
        reportError("Error reporting post in /forum/post/index.php - d");
        msg("Error reporting post. This error has been reported.");
      header("Location: /forum/post/?post=".$_GET["post"]);
      }
    }else{
      msg("You already reported this post.");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }
  }
?>
<!DOCTYPE html>
<html>
  <?php
    $css = '/comments';
    $css2 = 'post';
    include "../../res/head";
  ?>
  <body>
    <?php
      include "../../res/top";
    ?>
  <div class="container card noHover">
  <div id=post>
    <div class="forum card noShadow">
      <div class=info>
        <p onclick="document.location.href = '/search/?q=<?=$section;?>:+'; return false" class=postType><?=$section." ".$type; ?></p>
        <p>Posted <?=makeDate($date);?> by <span onclick="document.location.href = '/user/?user=<?=$poster_id;?>'; return false" class=userlink><?=$poster;?></span></p>
      </div>
      <div class=title>
        <h2><?=$title;?></h2>
      </div>
      <div class=content>
        <p>
          <?=$content;?>
          <?php
            if($images != NULL){
              echo "<input type=checkbox id=hideImgs>
              <label for=hideImgs id=hide class=noSelect>HIDE ATTACHMENTS</label>
              <label for=hideImgs id=show class=noSelect>SHOW ATTACHMENTS</label>";
              foreach(explode(",", substr($images, 0, -1)) as $file){
                //substr gets rid of the last comma, explode makes the array
                $exType = substr($file, strpos($file, '.')+1);
                $docFiles = ["doc", "docx", "pdf"];
                $imgFiles = ["jpg", "jpeg", "png"];
                //image stuff
                if(in_array($exType, $docFiles)){
                  echo "<iframe class='postDocPreview toggleView' src=https://docs.google.com/gview?url=http://ib.lukeogburn.com/forum/images/$file&embedded=true></iframe>";
                  //<embed src="file_name.pdf" width="800px" height="2100px" />
                }else if(in_array($exType, $imgFiles)){
                  echo "<img class='postImage toggleView' src=/forum/images/$file>";
                }
              }
            }
          ?>
        </p>
      </div>
    </div>
  </div>
      <div class=postBottom>
        <?php
          if(getUserInfoByName($poster)->id == $current_user){
            $datediff = time() - strtotime($date);
            $mins  = round($datediff / (60));
            if($mins <= 5){
              $editable = " | <a id=editPost href=/post/edit.php?post=".$_GET['post'].">edit</a>";
            }
            echo "
            <p class=postActions><a id=deletePost href=/post/delete.php?post=".$_GET['post'].">delete</a>$editable</p>";
          }else{
            echo "<p><a href=?post=".$_GET['post']."&reportPost=true class=postReport>report</a></p>";
          }
        ?>
          <i class=material-icons><a class="postSave" href=?post=<?=$_GET['post'];?>&bkmk=<?=$bk?"f":"t";?>><?=$bk?"bookmark":"bookmark_outline";?></a></i>
        </div>
  </div>
  
  <?php include "../../res/comments"; ?>
  
  </body>
</html>