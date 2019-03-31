<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("owner");
  $user = getUserInfo($_GET["user"]);
  if($user == false){
    msg("That user doesn't exist.");
    header("Location: /forum");
  }
  
  //Getting the number of posts
  $stmt = $conn->prepare("SELECT * FROM forums WHERE poster_id = :usr");
  $stmt->bindparam(":usr", $user->id);
  $stmt->execute();
  $posts = $stmt->rowCount();
  
  //Getting the number of comments
  $stmt = $conn->prepare("SELECT * FROM comments WHERE poster_id = :usr");
  $stmt->bindparam(":usr", $user->id);
  $stmt->execute();
  $comments = $stmt->rowCount();
  
  //Getting the number of reported posts made by user
  $stmt = $conn->prepare("SELECT * FROM forums WHERE poster_id = :usr AND reports IS NOT NULL");
  $stmt->bindparam(":usr", $user->id);
  $stmt->execute();
  $reportedPosts = $stmt->rowCount();
  
  //Getting the number of reported comments made by user
  $stmt = $conn->prepare("SELECT * FROM comments WHERE poster_id = :usr AND reports IS NOT NULL");
  $stmt->bindparam(":usr", $user->id);
  $stmt->execute();
  $reportedComments = $stmt->rowCount();
?>
<!DOCTYPE html>
<html>
    <?php 
      $css = "/admin/portal/admin";
      include "../../res/head";
    ?>
  <body>
    <?php 
      include "../../res/top";
    ?>
    <div id=monoContainer>
      <div class="card noHover center">
        <h2><?=$user->name?></h2>
        <?php
          $name = $user->name;
          $id = $user->id;
          $login = makeDate($user->last_login);
          if($posts == 0){
            $reportedPostsPercent = 0;
          }else{
            $reportedPostsPercent = round($reportedPosts/$posts);
          }
          if($comments == 0){
            $reportedCommentsPercent = 0;
          }else{
            $reportedCommentsPercent = round($reportedComments/$comments);
          }
          
          echo "
            <p>Last login: $login</p>
            <p>Posts made: $posts</p>
            <p>Reported posts made: $reportedPosts</p>
            <p>Percent of posts reported: $reportedPostsPercent%</p>
            <p>Comments made: $comments</p>
            <p>Reported comments made: $reportedComments</p>
            <p>Percent of comments reported: $reportedCommentsPercent%</p>
            <p><a class=noStyle href=/user/?user=$id>User's page</a></p>
          ";
        ?>
    </div>
  </body>
</html>