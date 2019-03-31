<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("admin");
  
  //Banning user
  if(isset($_POST["person"]) && strval(getUserInfo($_POST["person"])->name) != ""){
    if(verifyUser("admin", $_POST["person"])){
      msg("You cannot ban that user");
      unset($_POST);
      header("Location: /admin/portal/banUser.php");
      exit();//needed for some reason, else the code below runs
    }
    $person = $_POST["person"];
    $reason = $_POST["reason"];
    unset($_POST);
    conn();
    $stmt = $conn->prepare("UPDATE users SET special='banned', ban_reason=:rsn WHERE id=:id");
    $stmt->bindParam(":rsn", $reason);
    $stmt->bindParam(":id", $person);
    $stmt->execute();
    if($stmt){
      $user = getUserInfo($person)->name;
      msg("$user has been banned");
      header("Location: /admin/portal/banUser.php");
    }
  }else if(isset($_POST["person"]) && strval(getUserInfo($_POST["person"])->name) == ""){
    unset($_POST);
    msg("User doesn't exist");
    header("Location: /admin/portal/banUser.php");
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
        <h2>BAN USER</h2>
        <p id=userBanMsg>Banning a user will make them unable to access the website. Only do this if there is good reason to do so (e.g. cheating or bullying). This can only be undone by Luke Ogburn.</p>
        <form action="" method=POST>
          <p>User's ID (NOT their username):</p>
          <input type=text name=person placeholder="User's ID" required><br>
          <p>Reason for banning user (for them to read):</p>
          <input type=text name=reason placeholder="Reason for ban" required>
          <button type=submit>Ban User</button>
        </form>
        <br>
      </div>
    </div>
  </body>
</html>