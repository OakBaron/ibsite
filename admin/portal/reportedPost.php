<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("admin");
  conn();
  
  //Getting and storing the post info to echo later
  $post = getPostInfo($_GET["post"]);
  $poster_id = $post->poster_id;
  $title = $post->title;
  $content = decodeUserLink($post->content);
  $section = $post->section=="math"?"HL Math":ucwords($post->section);
  $section = $post->section=="none"?"":ucwords($post->section);
  $type = $post->type=="other"?"":strtolower($post->type);
  $type = $section==""?ucwords($type):$type;
  $section = $section==$type?"No topic":$section;
  $date = $post->date;
  $images = $post->image;
  $poster = getUserInfo($poster_id)->name;
  
  //Clearing post of reports
  if($_GET["clearPost"]){
    $stmt = $conn->prepare("UPDATE forums SET reports=null WHERE post_id=:id");
    $stmt->bindParam(":id", $_GET["post"]);
    $stmt->execute();
    if($stmt){
      msg("Post cleared of all reports");
      header("Location: /forum/post/?post=".$_GET["post"]);
    }
  }
?>
<!DOCTYPE html>
<html>
  <?php
    $css2 = 'post';
    include "../../res/head";
  ?>
  <body>
    <?php
      include "../../res/top";
    ?>
    
  <div class="container center"> 
      <h2>What should happen to the below post?</h2>
      <p>Should this post be <a class=color href=/post/delete.php?post=<?=$_GET["post"]?>>deleted</a> or <a class=color href=?clearPost=1&post=<?=$_GET["post"]?>>cleared of reports</a>?</p>
  </div>
  
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
  </div>
  </body>
</html>