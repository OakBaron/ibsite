<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  if(isset($_POST["submit"]) && verifyUser("admin")){
    $txt = "\n<li>".$_POST["rule"]."</li>";
    file_put_contents('res/rules', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    unset($_POST);
  }
?>
<!DOCTYPE html>
<html>
    <?php include "res/head"; ?>
  <body>
    <?php 
      include "res/top";
    ?>
    <div id=monoContainer>
      <div class='card noHover'>
        <h2 class=center>RULES</h2>
        <?php require("res/rules"); ?>
      </div>
      <?php
        if(verifyUser("admin")){
        echo "<div class='card noHover center'>
            <form action='' method=POST>
              <h2>ADD A RULE</h2>
              <p><i>Keep in mind that you won't be able to delete this</i></p>
              <input type=text name=rule placeholder='Rule to add'>
              <button type=submit name=submit>Add</button>
            </form>
            <br>
          </div>";
        }
      ?>
    </div>
  </body>
</html>