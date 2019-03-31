<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("owner");
  $classes = file_get_contents($_SERVER['DOCUMENT_ROOT']."/res/classes");
  $classes = array_filter(explode(",", $classes));
  
  if(isset($_GET["del"]) && in_array($_GET["del"], $classes)){
    $classes = array_diff($classes, array($_GET["del"]));
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/res/classes", implode(",", $classes));
    msg("Class removed.");
    header("Location: editClasses.php");
    exit();
  }
  if(isset($_GET["add"]) && !in_array($_GET["add"], $classes)){
    $class = str_replace(" ", "_", $_GET["add"]);
    $class = strtolower($class);
    array_push($classes, $class);
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/res/classes", implode(",", $classes));
    msg("Class added.");
    header("Location: editClasses.php");
    exit();
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
        <h2>DELETE A CLASS:</h2>
        <?php
          foreach($classes as $class){
            echo "<p><a class=deletable href=?del=$class>".ucwords(str_replace('_', ' ', $class))."</a></p>";
          }
        ?>
    </div>
    <div class='card noHover center'>
      <h2>ADD A CLASS:</h2>
      <form>
        <input type=text name=add>
        <button type=submit>Add Class</button>
      </form>
    </div>
  </body>
</html>