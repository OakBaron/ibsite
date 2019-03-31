<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  $post = getPostInfo($_GET["post"]);
  $GLOBALS["post"] = $post;
  if($post->poster_id!=$current_user){
    msg("What if someone did that to you?");
    header("Location: /forum/post/?post=".$_GET["post"]);
  }
  $datediff = time() - strtotime($post->date);
  $mins  = round($datediff / (60));
  if($mins > 5){
    msg("You can't edit that post.");
    header("Location: /forum/post/?post=".$_GET["post"]);
  }
  
  function checked($type, $value){
    return $GLOBALS["post"]->$type==$value?"checked":"";
  }
  
  $imgArray = $post->image==NULL?"":"\"".str_replace(",", "\", \"",substr($post->image,0,-1))."\"";
?>
<!DOCTYPE html>
<html>
    <?php
      $css = "common";
      include $_SERVER['DOCUMENT_ROOT']."/res/head";
    ?>
  <body>
    <?php include $_SERVER['DOCUMENT_ROOT']."/res/top"; ?>
    <form method=POST action=submitEdit.php enctype=multipart/form-data>
      <div class="container">
          
        <div id=post class="card noHover">
          <div class="forum">
              <input type=text class="editor title" name=title placeholder="Title goes here" value="<?=$post->title?>" autocomplete=off>
            <div class=content>
              <div id=postWriter>
                <div contentEditable=true id="postEditor" name="contentPlaceholder" placeholder="Words go here" class=editor onkeyup=loadContent()><?=$post->content?></div>
                <textarea id="contentSubmitter" style="display:none" name=content></textarea>
                <script>
                  function loadContent(){
                    document.getElementById("contentSubmitter").value = document.getElementById("postEditor").innerHTML;
                  }
                </script>
              </div>
            </div>
            <div id=imageDiv>
              <input id=postImg class=edit type=file name=images[] onchange=fileUploadCounter() multiple>
              <label id=forImg for=postImg>Add Photos/Files<br><small>(PNG, JPG, JPEG, DOC, DOCX, PDF)</small></label>
            </div>
            <script>
              document.addEventListener("DOMContentLoaded", function(event){
                var i = 0;
                if(i==0){
                  text = "<?=$post->content?>";
                  document.getElementById("postEditor").innerHTML = text;
                  document.getElementById("contentSubmitter").value = text;
                  i = 21; //making it only run once
                }
              });
              
              var fileUploadCounter = function(){
                var files = document.getElementById("postImg").files.length;
                if(files != 0) {
                  document.getElementById("forImg").innerHTML = files + " files selected";
                }
              };
            </script>
          </div>
          <div class=content>
            <?php
              if($post->image != NULL){
                echo "<input type=checkbox id=hideImgs>
                <label for=hideImgs id=hide class=noSelect>HIDE ATTACHMENTS</label>
                <label for=hideImgs id=show class=noSelect>SHOW ATTACHMENTS</label>";
                $i=1;
                foreach(explode(",", substr($post->image, 0, -1)) as $file){
                  //substr gets rid of the last comma, explode makes the array
                  $exType = substr($file, strpos($file, '.')+1);
                  $docFiles = ["doc", "docx", "pdf"];
                  $imgFiles = ["jpg", "jpeg", "png"];
                  //image stuff
                  if(in_array($exType, $docFiles)){
                    echo "<iframe class='postDocPreview toggleView' src=https://docs.google.com/gview?url=http://ib.lukeogburn.com/forum/images/$file&embedded=true></iframe>";
                    //<embed src="file_name.pdf" width="800px" height="2100px" />
                  }else if(in_array($exType, $imgFiles)){
                    echo "<img class='postImage toggleView' src=/forum/images/$file>";
                  }
                }
              }
            ?>
            <!-- changing the order of posts
              <input id=orderedImgs type=text name=orderedImgs style=display:none>
            <script>
              var imgArray = [<?=$imgArray?>];
              var number = function(img){
                if(imgArray.indexOf(img) > -1){
                  //remove it from the array if it exists
                  imgArray.splice(imgArray.indexOf(img), 1);
                }
                imgArray.push(img);
                imgID = "num"+imgArray[0].replace(/\./g, "");
                for(var i = 0; i < imgArray.length; i++){
                  document.getElementById("num"+imgArray[i].replace(/\./g, "")).innerHTML = i+1;
                }
                document.getElementById("orderedImgs").value = imgArray.join("-").replace(/\./g, "");
              }
            </script>-->
          </div>
          <div id=postRadios>
            <p class=postTitle>Subject</p>
            <?php
              $classes = file_get_contents($_SERVER['DOCUMENT_ROOT']."/res/classes");
              $classes = array_filter(explode(",", $classes));
              $num = 1;
              foreach($classes as $class){
                echo "<input name=section value=$class type=radio id=class$num ".checked('section',$class).">
              <label class=tagLabel for=class$num>".ucwords(str_replace('_', ' ', $class))."</label>";
              $num++;
              }
            ?>
            <input name=section value=none type=radio id=none <?=checked("section","none")?>>
            <label class=tagLabel for="none">None</label>
          
          <!--------------------------------------------------------------->
          
            <p class=postTitle>Post type</p>
            <input name=type value=notes type=radio id=type1 <?=checked("type","notes")?>>
              <label class=typeLabel for="type1">Notes</label>
              
            <input name=type value=question type=radio id=type2 <?=checked("type","question")?>>
              <label class=typeLabel for="type2">Quesion</label>
            
            <input name=type value=humor type=radio id=type3 <?=checked("type","humor")?>>
              <label class=typeLabel for="type3">Humor</label>
            
            <input name=type value=resource type=radio id=type4 <?=checked("type","resource")?>>
              <label class=typeLabel for="type4">Resource</label>
            
            <input name=type value=other type=radio id=type5 <?=checked("type","other")?>>
              <label class=typeLabel for="type5">Other</label>
          </div>
        </div>
        
        <div id=rules class="forum card noHover">
          <h3>Rules:</h3>
          <?php require $_SERVER['DOCUMENT_ROOT']."/res/rules"; ?>
          <input type=text name=pid value=<?=$_GET["post"]?> style="display:none;">
          <button type=submit id=submitPost>I understand the rules - Save!</button>
        </div>
      </div>
    </form>
  </body>
</html>