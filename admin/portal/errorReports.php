<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("owner");
  conn();
?>
<!DOCTYPE html>
<html>
  <?php
    include $_SERVER['DOCUMENT_ROOT']."/res/head";
  ?>
<body>
  <?php
    
    if(isset($_GET["del"])){
      conn();
      $del = $conn->prepare("DELETE FROM issue_tracker WHERE id = :id");
      $del->bindParam(":id", $_GET["del"]);
      $del->execute();
      if($del){
        msg("Good job :)");
        header("Location: /admin/portal/errorReports.php");
      }
    }
    
    include $_SERVER['DOCUMENT_ROOT']."/res/top";
    $stmt = $conn->prepare("SELECT * FROM issue_tracker ORDER BY date DESC");
    $stmt->execute();
    $stmt = $stmt->fetchAll();
  ?>
  <div id=monoContainer>
    <div class="card noHover center">
      <h2>REPORTED ISSUES</h2>
        <?php
          foreach($stmt as $report){
            echo "<a class=deletable href='?del=".$report["id"]."'>".$report['comment']."</a><br>";
            echo "<small>Reported by: ".$report["reporter"]." (".getUserInfo($report["reporter"])->name.") ".makeDate($report["date"])."</small><br><br>";
          }
          if($stmt == NULL){
            echo "<i>No issues have been reported :D</i><br>";
          }
        ?>
        <br>
    </div>
  </div>
</body>