<?php
  $GLOBALS["page"] = "banned";
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  if(getUserInfo($current_user)->special!="banned"){
    $title = "<h2>YOU HAVE NOT BEEN BANNED</h2>";
    $message = "<p class=smallWidth>Why are you even here?</p>";
  }else{
    $title = "<h2>YOU HAVE BEEN BANNED</h2>";
    $message = "<p class=smallWidth>An admin has banned you and left the following message:</p>\n<p class=smallWidth><i>".getUserInfo($current_user)->ban_reason."</i></p>";
  }
?>
<!DOCTYPE html>
<html>
    <?php
      include "../res/head";
    ?>
  <body>
    <?php 
      include "../res/top";
    ?>
    <div id=monoContainer>
      <div class="card noHover center">
        <?=$title?>
        <?=$message?>
      </div>
    </div>
  </body>
</html>