<?php
  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  conn();
?>
<!DOCTYPE html>
<html>
    <?php
      $css = "search";
      require $_SERVER['DOCUMENT_ROOT']."/res/head";
    ?>
  <body>
    <?php 
      include $_SERVER['DOCUMENT_ROOT']."/res/top";
    ?>
    <div id=results>
      <?php
      //Setting number of results per page
      $GLOBALS["limit"] = 20;
      $GLOBALS["page"] = is_numeric($_GET["page"])&&$_GET["page"]>0?$_GET["page"]:1;
      $GLOBALS["check"] = $limit * ($page - 1);
      
      function search($statement, $searchByClass){
        $limit = $GLOBALS["limit"] + 1;
        $page = $GLOBALS["page"];
        $start = ($limit - 1) * ($page - 1);
        //$limit is set one higher to check if there will be a next page, but is subtracted in the $start math because the math relies on the limit.
        
        if($searchByClass == false){
          $stmt = $GLOBALS['conn']->prepare("SELECT * FROM forums WHERE (title LIKE CONCAT('%', :search, '%')) OR (content LIKE CONCAT('%', :search, '%')) ORDER BY date DESC LIMIT $start,$limit");
        }else{
          $stmt = $GLOBALS['conn']->prepare("SELECT * FROM forums WHERE (title LIKE CONCAT('%', :search, '%') AND section = :section) OR (content LIKE CONCAT('%', :search, '%') AND section = :section) ORDER BY date DESC LIMIT $start,$limit");
          $stmt->bindParam(":section", $searchByClass);
        }
        $stmt->bindParam(":search", $statement);
        $stmt->execute();
        return $stmt->fetchAll();
      }
      
      function searchClassOnly($searchByClass){
        $limit = $GLOBALS["limit"] + 1;
        $page = $GLOBALS["page"];
        $start = ($limit - 1) * ($page - 1);
        //The above was just copied from function search()
        //If the user looks up something like "chemistry:", there will be no results so might as well give them ALL of the chemistry posts
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM forums WHERE section = :section ORDER BY date DESC LIMIT $start,$limit");
        $stmt->bindParam(":section", $searchByClass);
        $stmt->execute();
        return $stmt->fetchAll();
      }
      
        //Setting the array for storing posts' ID, used to make sure results only turn up once. Also initializing the counter for amount of posts
        $resArray = array();
        $count = 0;
        
        //Getting class specification, returning false if none
        preg_match_all("#([A-Za-z]+)(?=:)#", strtok($_GET["q"], " "), $matches);
        $class = strtolower($matches[0][0]);
        $classes = file_get_contents($_SERVER['DOCUMENT_ROOT']."/res/classes");
        $classes = array_filter(explode(",", $classes));
        if(in_array($class, $classes)){
          $searchByClass = $class;
        }else{
          $searchByClass = false;
        }
        
        //Getting more exact results by searching for strict phrase
        foreach(search($search, $searchByClass) as $post){
          $resArray[] = $post["post_id"];
        }
        
        //Getting more general results by searching for each word indevidually
        $noSpaces = preg_replace('/\s+/', ' ', $_GET["q"]." ");
            //Adding the space to the GET[q] solves the issue where searching nothing in a class (e.g. "history:") didn't turn up results
        $search = $searchByClass==false?$noSpaces:substr($noSpaces, strpos($noSpaces, ":")+1);
        $search = explode(" ", strtolower($search));
        $exclude = explode("\n", file_get_contents("stopwords.txt"));
        $search = array_diff($search, $exclude);
        //gets rid of superflous stop words in search
        foreach(array_filter($search) as $word){
          foreach(search($word." ", $searchByClass) as $post){
            $resArray[] = $post["post_id"];
          }
          foreach(search(" ".$word, $searchByClass) as $post){
            $resArray[] = $post["post_id"];
          }
        }
        
        if(count(array_filter($search)) == 0 && $searchByClass != false){
            foreach(searchClassOnly($searchByClass) as $post){
                $resArray[] = $post["post_id"];
            }
        }
        
        $resArray = array_unique($resArray);
        
        foreach($resArray as $post){
          $count++;
        }
        
        echo "<h2 id=summary>";
        if($count==1){
          echo "There was 1 result";
        }else if($count > $GLOBALS["limit"] || $GLOBALS["page"] > 1){
          echo "There were lots of results";
        }else{
          echo "There were $count results";
        }
        echo "</h2>";
        
        foreach($resArray as $post){
          makePost(getPostInfo($post));
        }
        
        $resArray = array_slice($resArray, 0, $GLOBALS["limit"]);
        
        $page = $GLOBALS["page"];
        $query = urlencode($_GET["q"]);
        echo "<div id=pages>";
        echo $page!=1?"<div id=prevPage><a href=/search/?q=$query&page=".($page-1).">&larr;</a></div>":"<div></div>";
        echo $count>$GLOBALS["limit"]?"<div id=nextPage><a href=/search/?q=$query&page=".($page+1).">&rarr;</a></div>":"<div></div>";
        echo "</div>";
      ?>
    </div>
  </body>
</html>