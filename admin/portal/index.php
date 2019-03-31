<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("admin");
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
        <h2>NOTIFICATIONS</h2>
        <?php
          conn();
          $stmt = $conn->prepare("SELECT * FROM forums WHERE reports IS NOT NULL");
          $stmt->execute();
          $res = $stmt->fetchAll();
          foreach($res as $post){
            $times = substr_count($post["reports"], ",");
            $times = $times==1?"1 time":"$times times";
            echo "<p><a class=noStyle href=reportedPost.php?post=".$post['post_id'].">Post ".$post["post_id"]." has been reported $times</a></p>";
          }
          if(count($res)==0){
            echo "<p><i>Nothing has been reported.</i></p>";
          }
        ?>
      </div>
      <div class="card noHover center">
        <h2>ADMIN ACTIONS</h2>
        <p><a class=noStyle href=banUser.php>Ban a user</a></p>
        <p><a class=noStyle href=unbanUser.php>Unban a user</a></p>
        <?php
          if(verifyUser("owner")){
            echo "<p><a class=noStyle href=errorReports.php>Error reports</a></p>";
            echo "<p><a class=noStyle href=manage.php>Manage admins</a></p>";
            echo "<p><a class=noStyle href=siteVisitors.php>Site visitors</a></p>";
            echo "<p><a class=noStyle href=editClasses.php>Edit classes</a></p>";
          }
        ?>
      </div>
    </div>
  </body>
</html>