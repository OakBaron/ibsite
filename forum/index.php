<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
?>
<!DOCTYPE html>
<html>
  <?php 
    include "../res/head";
  ?>
  <body>
    <?php include "../res/top"; ?>
    <div id=container>
        
  <div id=left>
  <?php
    $limit = 10;
    $page = is_numeric($_GET["page"])&&$_GET["page"]>0?$_GET["page"]:1;
    $start = $limit * ($page - 1);
    $stmt = $conn->prepare("SELECT * FROM forums ORDER BY date DESC LIMIT $start,$limit");
    $stmt->execute();
    foreach($stmt->fetchAll() as $post){
      makePost($post);
    }
    
    //checking if there would be results on the next page
    $row = $start+$limit;
    $stmt = $conn->prepare("SELECT * FROM forums ORDER BY date DESC LIMIT $row,1");
    $stmt->execute();
    $moreResults = $stmt->rowCount();
  ?>
  <div id=pages>
    <?php
      echo $page!=1?"<div id=prevPage><a href=/forum/?page=".($page-1).">&larr;</a></div>":"<div></div>";
      echo $moreResults?"<div id=nextPage><a href=/forum/?page=".($page+1).">&rarr;</a></div>":"<div></div>";
    ?>
  </div>
  </div>
  <?php include $_SERVER['DOCUMENT_ROOT']."/res/notifs"; ?>
  </div>
  </body>
</html>