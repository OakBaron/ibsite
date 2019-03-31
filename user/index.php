<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
  $row = getUserInfo($_GET["user"]);
  $id = $row->id;
  $name = $row->name;
  $classes = $row->classes;
  $grade = $row->grade;
  $image = $row->image_url;
  $teacher = $row->teacher;
  
  if($_COOKIE["IB_ID"]==$_GET['user']){
    $accountOwner = true;
  }else{
    $accountOwner = false;
  }
  
  if($id == NULL){
    msg("User doesn't exist.");
    header('Location: https://ib.lukeogburn.com/forum/');
  }
?>
<!DOCTYPE html>
<html>
  <?php
    $css = 'user';
    include $_SERVER['DOCUMENT_ROOT']."/res/head";
  ?>
  <body>
    <?php
      include $_SERVER['DOCUMENT_ROOT']."/res/top"; 
      function a($type){
        $check = $_GET['type']=="" ? "forum" : $_GET['type'];
        echo $type == $check ? "active" : "";
      }
    ?>
    <div id=userTopWrapper>
      <div id=userTop>
        <a class=userTopSel <?php a("forum"); ?> href=<?php echo "?user=".$_GET["user"]; ?>&type=forum>POSTS</a>
        <a class=userTopSel <?php a("saved"); ?> href=<?php echo "?user=".$_GET["user"]; ?>&type=saved>SAVED</a>
      </div>
    </div>
    
    <!-- Begin mobile-only part -->
    <div id=right class=mobileOnly>
      <div id=userInfo class="card noHover">
        <div id=userInfoTop>
          <img id=userImg src=<?php echo $image; ?>>
            <div class=infoDump>
              <h2><?php echo $name; ?></h2>
              <p><?php 
                $grade = $teacher?"Teacher":$grade;
                echo $grade==null?"Grade level unknown":ucwords($grade);
                echo verifyUser("admin", $_GET["user"])?" | Admin":"";
              ?></p>
              <p><?=$_GET["user"]?>@hcps.us</p>
              <p><?=$numOfPosts;?></p>
            </div>
          </div>
      </div>
      <?php
        if(!$teacher){
          echo "<div id=userClassInfo class='card noHover'>
                  <div id=userClassInfoTop class=infoDump>
                    <h2>Classes</h2>\n";
          if($classes==NULL){
            echo "<p>Unknown</p>";
          }else{
            $classesArray= explode(",", $classes);
            foreach($classesArray as $class){
              $class = ucwords(str_replace("_", " ", $class));
              echo "<p>$class</p>";
            }
          }
          echo "</div>
          </div>\n";
        }
        if($accountOwner){
          if(verifyUser("admin")){
            $admin = "<p><a href=/admin/portal/>Admin Portal</a></p>";
          }
          echo "<div id=userActionsWrapper class='card noHover'>
                  <div id=userActions>
                    <p><a href=/user/logout.php>Logout</a></p>
                    <p><a href=/user/settings.php>Account Settings</a></p>
                    <p><a href=/report.php>Report Site Issue</a></p>
                    $admin
                  </div>
                </div>";
          }
        ?>
      </div>
    <!-- End mobile-only part -->
    
  <div id=container>
    <div id=left>
      <?php
        $limit = 20;
        $page = is_numeric($_GET["page"])&&$_GET["page"]>0?$_GET["page"]:1;
        $start = $limit * ($page - 1);
        //setting amount of posts allowed on page
    
        if($_GET["type"]=="saved"){
          //Getting the saved posts
          conn();
          $stop = $limit+1;
          $stmt = $conn->prepare("SELECT * FROM bookmarks WHERE user_id = :uid ORDER BY unused_id DESC LIMIT $start,$stop");
          $stmt->bindParam(":uid", $_GET["user"]);
          $stmt->execute();
          $row = $stmt->fetchAll();
          $count = $stmt->rowCount();
          $row = array_slice($row, 0, $limit);
          foreach($row as $thing){
            $stmt = $conn->prepare("SELECT * FROM forums WHERE post_id = :pid");
            $stmt->bindParam(":pid", $thing["post_id"]);
            $stmt->execute();
            $post = $stmt->fetchAll();
            makePost($post[0]);
          }
          if($count == 0){
            $referer = $accountOwner?"your":getUserInfo($_GET["user"])->name."'s";
            echo "<h3 class='center noSelect' style=color:#888;font-style:italic;margin-top:10vh;>This is where ".$referer." saved posts would be</h3>";
          }
        }else{
          //Getting user's posts
          conn();
          $stop = $limit+1;
          $stmt = $conn->prepare("SELECT * FROM forums WHERE poster_id = :pid ORDER BY date DESC LIMIT $start,$stop");
          $stmt->bindParam(":pid", $_GET["user"]);
          $stmt->execute();
          $row = $stmt->fetchAll();
          $count = $stmt->rowCount();
          $row = array_slice($row, 0, $limit);
          foreach($row as $post){
            makePost($post);
          }
          
        }
        
        //Getting how many posts the user has made
        $stmt = $conn->prepare("SELECT COUNT(*) FROM forums WHERE poster_id = :id");
        $stmt->bindParam(":id", $_GET["user"]);
        $stmt->execute(); 
        $numOfPosts = $postCount = $stmt->fetchColumn(0);
        $numOfPosts = $numOfPosts==1?"$numOfPosts Post":"$numOfPosts Posts";
        
        if($postCount == 0 && $_GET["type"]!="saved"){
            $referer = $accountOwner?"your":getUserInfo($_GET["user"])->name."'s";
            echo "<h3 class='center noSelect' style=color:#888;font-style:italic;margin-top:10vh;>This is where ".$referer." posts would be</h3>";
        }
        
        //Page arrows
        echo "<div id=pages>";
        $user = $_GET["user"];
        $type = $_GET["type"];
        echo $page!=1?"<div id=prevPage><a href=/user/?user=$user&type=$type&page=".($page-1).">&larr;</a></div>":"<div></div>";
        echo $count>$limit?"<div id=nextPage><a href=/user/?user=$user&type=$type&page=".($page+1).">&rarr;</a></div>":"<div></div>";
        echo "</div>";
      ?>
    </div>
    <div id=right>
      <div id=userInfo class="card noHover">
        <div id=userInfoTop>
          <img id=userImg src=<?php echo $image; ?>>
            <div class=infoDump>
              <h2><?php echo $name; ?></h2>
              <p><?php 
                $grade = $teacher?"Teacher":$grade;
                echo $grade==null?"Grade level unknown":ucwords($grade);
                echo verifyUser("admin", $_GET["user"])?" | Admin":"";
              ?></p>
              <p><?=$_GET["user"]?>@hcps.us</p>
              <p><?=$numOfPosts;?></p>
            </div>
          </div>
      </div>
      <?php
        if(!$teacher){
          echo "<div id=userClassInfo class='card noHover'>
                  <div id=userClassInfoTop class=infoDump>
                    <h2>Classes</h2>\n";
          if($classes==NULL){
            echo "<p>Unknown</p>";
          }else{
            $classesArray= explode(",", $classes);
            foreach($classesArray as $class){
              $class = ucwords(str_replace("_", " ", $class));
              echo "<p>$class</p>";
            }
          }
          echo "</div>
          </div>\n";
        }
        if($accountOwner){
          if(verifyUser("admin")){
            $admin = "<p><a href=/admin/portal/>Admin Portal</a></p>";
          }
          echo "<div id=userActionsWrapper class='card noHover'>
                  <div id=userActions>
                    <p><a href=/user/logout.php>Logout</a></p>
                    <p><a href=/user/settings.php>Account Settings</a></p>
                    <p><a href=/report.php>Report Site Issue</a></p>
                    $admin
                  </div>
                </div>";
          }
        ?>
      </div>
    </div>
  </body>
</html>