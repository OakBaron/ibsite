<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  if(!(isset($_COOKIE["IB_ID"]))){
    header("Location: https://ib.lukeogburn.com/user/login.php");
  }
  conn();
  $stmt = $GLOBALS['conn']->prepare("SELECT * FROM users WHERE id = :id");
  $id = $current_user;
  $stmt->bindParam(":id", $id);
  $stmt->execute();
  $row = $stmt->fetch(PDO::FETCH_OBJ);
  $GLOBALS['grade'] = $row->grade;
  $GLOBALS['userClasses'] = explode(",", $row->classes);
  $GLOBALS['name'] = $row->name;
  $GLOBALS['teacher'] = $row->teacher;
  $GLOBALS['dark_theme'] = $row->dark_theme;
  $GLOBALS['snow'] = $row->snow;
  
  function dt($val){
    if($val == $GLOBALS['dark_theme']){
      return "checked";
    }
  }
  function sw($val){
    if($val == $GLOBALS['snow']){
      return "checked";
    }
  }
?>
<!DOCTYPE html>
<html>
  <?php
    $css = "updateInfo";
    include $_SERVER['DOCUMENT_ROOT']."/res/head";
  ?>
  <body>
    <?php 
      include $_SERVER['DOCUMENT_ROOT']."/res/top";
    ?>
    <div id=monoContainer>
      <div class='card noHover center'>
        <h2>Account Settings</h2>
        <form method=POST action="updateFunc.php">
          <p class=question>Dark theme?</p>
          <input type=radio name=dark_theme id=darkThemeOff value=0 <?=dt(0)?>>
          <label class=sideBySide for=darkThemeOff>OFF</label>
          <input type=radio name=dark_theme id=darkThemeOn value=1 <?=dt(1)?>>
          <label class=sideBySide for=darkThemeOn>ON</label>
          
          
          <p class=question>Snow?</p>
          <input type=radio name=snow id=snowOff value=0 <?=sw(0)?>>
          <label class=sideBySide for=snowOff>OFF</label>
          <input type=radio name=snow id=snowOn value=1 <?=sw(1)?>>
          <label class=sideBySide for=snowOn>ON</label>
          
          <p class=question>What is your name?</p>
          <input type=text name=name autocomplete=off maxlength=20 placeholder="Your name" value=<?php echo "\"".$GLOBALS['name']."\""; ?>>
          
          
          <?php
            function a($level){
              if($level == $GLOBALS['grade']){
                return "checked";
              }
            }
            function b($class){
              if(in_array($class, $GLOBALS['userClasses'])){
                return "checked";
              }
            }
          if($GLOBALS['teacher'] == NULL){
          echo "
          <p class=question>What grade level are you?</p>
          <input type=radio name=grade id=fm value=freshman ".a('freshman')." >
            <label for=fm>Freshman</label>
          <input type=radio name=grade id=sp value=sophmore ".a('sophmore')." >
            <label for=sp>Sophmore</label>
          <input type=radio name=grade id=jr value=junior ".a('junior')." >
            <label for=jr>Junior</label>
          <input type=radio name=grade id=sn value=senior ".a('senior')." >
            <label for=sn>Senior</label>
            
          <p class=question>Which of these classes are you in?</p>";
          $classes = file_get_contents($_SERVER['DOCUMENT_ROOT']."/res/classes");
          $classes = array_filter(explode(",", $classes));
          $tag = 1;
          foreach($classes as $class){
            echo "<input name=classes[] value=$class type=checkbox id=tag$tag ".b($class)."><label class=tagLabel for=tag$tag>".ucwords(str_replace("_", " ", $class))."</label>\n";
            $tag++;
          }
          }
          ?>
          
          <button class=save name=btn type=submit value=submit>Save</button>
        </form>
      </div>
      <br> <!-- shows the margin-bottom of the last .card for some reason (also adds another space, which doesn't look horrible so I'm keeping it lol -->
    </div>
  </body>
</html>