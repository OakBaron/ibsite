<?php require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php"; ?>
<!--
    Hello there! Thanks for checking out this website!
    It's still very much in development, but I've made steady progress so far and I'm hopeful that it'll actually be used this school year.
    I do need some help! If you want to give me suggestions for features or design improvements, shoot me a text: 804-912-5784
-->
<!DOCTYPE html>
<html>
    <?php include "../res/head"; ?>
  <body>
    <?php 
      include "../res/top";
    ?>
    <div id=monoContainer>
      <div class="card noHover center">
        <h2>ADMIN ACCOUNTS</h2>
        <p><?php
          conn();
          $stmt = $conn->prepare("SELECT * FROM users WHERE special='owner'");
          $stmt->execute();
          $row = $stmt->fetchAll();
          foreach($row as $person){
              echo "<a class=noStyle href=/user/?user=".$person["id"].">".$person["name"]."</a><br>";
          }
          $stmt = $conn->prepare("SELECT * FROM users WHERE special='admin'");
          $stmt->execute();
          $row = $stmt->fetchAll();
          foreach($row as $person){
              echo "<a class=noStyle href=/user/?user=".$person["id"].">".$person["name"]."</a><br>";
          }
        ?></p>
      </div>
      <div class="card noHover center">
        <h2>WANT TO BE AN ADMIN?</h2>
        <p>Contact Luke Ogburn (@<?=getUserInfo("51155")->name?>) to get more information.</p>
      </div>
    </div>
  </body>
</html>