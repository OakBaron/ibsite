<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
?>
<!DOCTYPE html>
<html>
    <?php
      $css = "common";
      include $_SERVER['DOCUMENT_ROOT']."/res/head";
    ?>
  <body>
    <?php include $_SERVER['DOCUMENT_ROOT']."/res/top"; ?>
    <form method=POST action=submitPost.php enctype=multipart/form-data>
      <div class="container">
          
        <div id=post class="card noHover">
          <div class="forum">
              <input type=text class="editor title" name=title placeholder="Title goes here" autocomplete=off required>
            <div class=content>
              <div id=postWriter>
                <div contentEditable=true id="postEditor" name="contentPlaceholder" placeholder="Words go here" class=editor onkeyup=loadContent()></div>
                <textarea id="contentSubmitter" style="display:none" name=content></textarea>
                <script>
                  function loadContent(){
                    document.getElementById("contentSubmitter").value = document.getElementById("postEditor").innerHTML;
                  }
                </script>
              </div>
            </div>
            <div id=imageDiv>
              <input id=postImg class=edit type=file name=images[] onchange=fileUploadCounter() accept=".png,.jpg,.jpeg,.doc,.docx,.pdf" multiple>
              <label id=forImg for=postImg>Add Photos/Files<br> <small>(PNG, JPG, JPEG, DOC, DOCX, PDF)</small></label>
            </div>
            <script>
              var fileUploadCounter = function(){
                var files = document.getElementById("postImg").files.length;
                if(files != 0) {
                  document.getElementById("forImg").innerHTML = files + " files selected";
                }
              };
            </script>
            
          </div>
          <div id=postRadios>
            <p class=postTitle>Subject</p>
            <?php
              $classes = file_get_contents($_SERVER['DOCUMENT_ROOT']."/res/classes");
              $classes = array_filter(explode(",", $classes));
              $num = 1;
              foreach($classes as $class){
                echo "<input name=section value=$class type=radio id=class$num>
              <label class=tagLabel for=class$num>".ucwords(str_replace("_", " ", $class))."</label>";
              $num++;
              }
            ?>
            <input name=section value=none type=radio id=none checked><label class=tagLabel for=none>None</label>
          
            <p class=postTitle>Post type</p>
            <input name=type value=notes type=radio id=type1><label class=typeLabel for="type1">Notes</label>
            <input name=type value=question type=radio id=type2><label class=typeLabel for="type2">Quesion</label>
            <input name=type value=humor type=radio id=type3><label class=typeLabel for="type3">Humor</label>
            <input name=type value=resource type=radio id=type4><label class=typeLabel for="type4">Resource</label>
            <input name=type value=other type=radio id=type5 checked><label class=typeLabel for="type5">Other</label>
          </div>
        </div>
        
        <div id=rules class="forum card noHover">
          <h3>Rules:</h3>
          <?php require $_SERVER['DOCUMENT_ROOT']."/res/rules"; ?>
          <button type=submit id=submitPost>I understand the rules - Submit!</button>
        </div>
      </div>
    </form>
  </body>
</html>