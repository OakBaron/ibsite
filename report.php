<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
?>
<!DOCTYPE html>
<html>
  <?php
    $css = "report";
    include $_SERVER['DOCUMENT_ROOT']."/res/head";
  ?>
  <body>
    <?php 
      include $_SERVER['DOCUMENT_ROOT']."/res/top";
    ?>
    <div id=monoContainer>
      <div class='card noHover center'>
        <?php 
        if($_GET["issue"]=="sub"){
          $issue = $_POST["issue"];
          $reporter = $current_user;
          
          $stmt = $conn->prepare("INSERT INTO issue_tracker (reporter, comment) VALUES (:reporter, :issue)");
          $stmt->bindParam(":issue", $issue);
          $stmt->bindParam(":reporter", $reporter);
          $result = $stmt->execute();
          
          if($stmt){
            echo "<h2>Thank You.<br>Your issue has been submitted and will be reviewed shortly.</h2><p>Until it is resolved, please bear in mind that there is only one person who maintains this entire site, and he has the same amount of schoolwork as you do.<br>Thank you for being patient.</p><br><a id=another href='?'>Need to submit another?</a>";
          }else{
            echo "<h2>There was an issue submitting your issue.</h2><p>I'd tell you to report it, but that doesn't seem to be an option. Instead, please contact Luke Ogburn in person and tell him to fix this.</p>";
          }
        }else{
        echo "
        <h2>Report Issue</h2>
        <form method=POST action=?issue=sub>
          <p id=instructions>Please explain the issue, and include anything you feel might be useful for me to know</p>
          <textarea name=issue rows=6 autofocus></textarea>
          <button type=submit name=submit>Submit</button>
        </form>";
        }
        ?>
      </div>
    </div>
  </body>
</html>