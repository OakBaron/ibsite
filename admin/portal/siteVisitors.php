<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("owner");
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
        <h2>USERS:</h2>
        <?php
          conn();
          $stmt = $conn->prepare("SELECT name FROM users WHERE id <> '51155'");
          $stmt->execute();
          $res = $stmt->fetchAll();
          foreach($res as $person){
            $person = getUserInfoByName($person[0]);
            $name = $person->name;
            $id = $person->id;
            echo "<p><a class=noStyle href=aboutUser.php?user=$id>$name</a></p>";
          }
        ?>
    </div>
  </body>
</html>