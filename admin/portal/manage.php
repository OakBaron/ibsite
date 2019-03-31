<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("owner");
    
  //Adding admins
  if(isset($_POST["person"]) && strval(getUserInfo($_POST["person"])->name) != ""){
    $person = $_POST["person"];
    unset($_POST);
    conn();
    $stmt = $conn->prepare("UPDATE users SET special='admin' WHERE id=:id");
    $stmt->bindParam(":id", $person);
    $stmt->execute();
    if($stmt){
      $user = getUserInfo($person)->name;
      msg("$user added as admin");
      header("Location: /admin/portal/manage.php");
    }
  }else if(isset($_POST["person"]) && strval(getUserInfo($_POST["person"])->name) == ""){
    unset($_POST);
    msg("User doesn't exist");
    header("Location: /admin/portal/manage.php");
  }
  
  //Deleting admins
  if(isset($_GET["delUser"])){
    conn();
    $person = $_GET["delUser"];
    $stmt = $conn->prepare("SELECT special FROM users WHERE id=:id");
    $stmt->bindParam(":id", $person);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if($res["special"]=="admin"){
      $person = $_GET["delUser"];
      $stmt = $conn->prepare("UPDATE users SET special=null WHERE id=:id");
      $stmt->bindParam(":id", $person);
      $stmt->execute();
      if($stmt){
        $person = getUserInfo($person)->name;
        msg("$person's admin rights have been revoked");
        header("Location: /admin/portal/manage.php");
      }else{
        msg("Error revoking $person's admin rights");
        reportError("Error revoking admin rights from $person in /admin/portal/manage.php");
        header("Location: /admin/portal/manage.php");
      }
    }else{
      msg("That person is not an admin");
      header("Location: /admin/portal/manage.php");
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
        <h2>ADD ADMIN</h2>
        <form action="manage.php" method=POST>
          <input type=text name=person placeholder="User's ID">
          <button type=submit>Add admin</button>
        </form>
        <br>
      </div>
      <div class="card noHover center">
        <h2>REMOVE ADMIN</h2>
        <p><?php
          conn();
          $stmt = $conn->prepare("SELECT * FROM users WHERE special='admin'");
          $stmt->execute();
          $row = $stmt->fetchAll();
          if(sizeof($row)==0){
            echo "<i>No admins.</i>";
          }
          foreach($row as $person){
              echo "<a class=deletable href=/admin/portal/manage.php?delUser=".$person["id"].">".$person["name"]."</a><br>";
          }
        ?></p>
      </div>
    </div>
  </body>
</html>