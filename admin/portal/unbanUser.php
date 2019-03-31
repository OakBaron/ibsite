<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("admin");

  //Unbanning the user
  if(isset($_GET["user"])){
    conn();
    $person = $_GET["user"];
    $stmt = $conn->prepare("SELECT special FROM users WHERE id=:id");
    $stmt->bindParam(":id", $person);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res["special"]=="banned"){
      $person = $_GET["user"];
      $stmt = $conn->prepare("UPDATE users SET special=null, ban_reason=null WHERE id=:id");
      $stmt->bindParam(":id", $person);
      $stmt->execute();
      if($stmt){
        $person = getUserInfo($person)->name;
        msg("$person has been unbanned");
        header("Location: /admin/portal/unbanUser.php");
      }else{
        msg("Error unbanning $person");
        reportError("Error unbanning $person in /admin/portal/manage.php");
        header("Location: /admin/portal/unbanUser.php");
      }
    }else{
      msg("$person was never banned");
      header("Location: /admin/portal/unbanUser.php");
    }
  }
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
        <h2>UNBAN USER</h2>
        <p><?php
          conn();
          $stmt = $conn->prepare("SELECT * FROM users WHERE special='banned'");
          $stmt->execute();
          $row = $stmt->fetchAll();
          if(sizeof($row)==0){
            echo "<i>No banned users :D</i>";
          }
          foreach($row as $person){
              echo "<a class=deletable href=/admin/portal/unbanUser.php?user=".$person["id"].">".$person["name"]."</a><br>";
          }
        ?></p>
      </div>
    </div>
  </body>
</html>