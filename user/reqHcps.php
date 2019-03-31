<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  
  conn();
  $stmt = $conn->prepare("DELETE FROM login_tokens WHERE user_id = :tk");
  $stmt->bindParam(":tk", $_COOKIE["IB_ID"]);
  $stmt->execute();
  
  setcookie("IB_ID", $_COOKIE["IB_ID"], time()-3600, "/");
  setcookie("IB_SESSION", $_COOKIE["IB_SESSION"], time()-3600, "/");
?>
<html>
  <head>
    <?php
      include $_SERVER['DOCUMENT_ROOT']."/res/head";
    ?>
    <style>
      #error{
        color: red;
        margin-top: 10%;
      }
      .link{
        text-decoration: none;
        display: inline;
      }
      .link:hover{
        cursor: pointer;
        text-decoration: underline;
      }
      #ebody{
        margin: 0 15%;
        text-align: center;
      }
    </style>
  </head>
  <body>
      <?php
        require $_SERVER['DOCUMENT_ROOT']."/res/top";
      ?>
      <div id=ebody>
        <h3 id=error>You need to use your school account.</h3>
        <br><!-- so -->
        <p class=link>(<a class=link href=login.php>Back to login page</a>)</p>
        <br><!-- sorry -->
        <br><!-- for -->
        <br><!-- this -->
        <p>
          If you weren't given the option, you need to:<br>
          <div style=display:inline-block;margin-left:auto;margin-right:auto;>
            <ol style=text-align:left;>
              <li>Go to <a class=link target=_BLANK href=https://google.com/>google.com</a></li>
              <li>Sign in with your HCPS account</li>
              <li>Re-login here</li>
            </ol>
          </div>
        </p>
      </div>
  </body>
</html>