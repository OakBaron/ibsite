<?php

/**
 * Please note that files uploaded are placed in /forum/images (even non-image 
 * files) because this function used to only be for images. It has since been
 * updated to allow for doc files too.
 * 
**/

  require $_SERVER['DOCUMENT_ROOT']."/globalFuncs.php";
  restrictAccess("owner");
  echo("Change the file /forum/foo.php if you really want to do this");
  exit();
  exit();//just to be safe lol
  conn();
  
  $lipsum = "Sed facilis libero enim. Omnis molestias ut nihil a ea rem magnam repellat. Dolorum fugit quod quo ipsam qui molestiae aut. Voluptatem doloribus sint natus aut sequi illum quo. Ut est et ut odio et et totam. Et saepe quis maxime rerum et omnis qui. Tenetur sunt ratione iure atque aut blanditiis. Vitae consequuntur esse et qui. Officia sunt eum et distinctio sunt aut rem et. Excepturi nostrum et cum. Id aut delectus id sit. Sed impedit placeat in eos et qui ab. Distinctio doloremque veritatis qui velit voluptas. Velit corporis soluta similique et. Et iure enim quaerat aut qui non porro neque. Laudantium non voluptas illum error sit qui expedita. Deserunt similique officiis blanditiis voluptatibus dicta ea sunt. Deserunt aut reprehenderit ut quia minima facere. Praesentium facere laudantium nisi quasi corrupti accusantium perferendis quidem. Quo dolorem maiores iure officiis aspernatur eos. Quisquam ut excepturi facilis iusto nemo fugiat. Et sequi nostrum asperiores unde cumque perspiciatis aperiam. Pariatur adipisci eum illo quis maxime fugit consequatur. Quo sit fugiat voluptatum. Quia soluta ex ut neque aliquam aperiam. Aperiam ut ad enim. Voluptas ducimus rem fugit. Tempora autem voluptatem cum aliquid. Dolorum iure impedit cumque vel soluta dolores alias. Voluptatem rerum tempora accusantium deserunt nam voluptatem. Voluptas non cum nostrum. Enim possimus vero voluptas rem est voluptatem odit. Ea laudantium odio soluta molestias eligendi aut. Repellendus tempore et consectetur beatae praesentium. Dolorum id quis ad. Est nihil et debitis dolor laborum delectus cum aspernatur. Aliquam voluptates ipsum velit delectus laudantium. Suscipit ratione quia ea hic non veritatis eos neque. Quas quos impedit perferendis sed pariatur quisquam et. Dolores eveniet quas adipisci itaque ipsa veritatis cum nisi.";
  $tipsum = "Suscipit ratione quia ea hic non veritatis eos neque. Quas quos impedit perferendis sed pariatur quisquam et. Dolores eveniet quas adipisci itaque ipsa veritatis cum nisi";
  function random_pic(){
    $files = glob('images/*.*');
    $file = array_rand($files);
    return basename($files[$file]).",";
  }
  
  for($i=1; $i<=100; $i++){
    $pid = randID();
    $uid = $current_user;
    $sbj = "none";
    $typ = "other";
    $ttl = "$i: ".substr($tipsum, 0, rand(0, strlen($tipsum)));
    $ctt = substr($lipsum, 0, rand(0, strlen($lipsum)));
    $img = rand(0,19)<=5?random_pic():NULL;
    
    $stmt = $conn->prepare("INSERT INTO forums (post_id, poster_id, section, type, title, content, image) VALUES (:pid, :uid, :sbj, :typ, :ttl, :ctt, :img)");
    $stmt->bindParam(":pid", $pid);
    $stmt->bindParam(":uid", $uid);
    $stmt->bindParam(":sbj", $sbj);
    $stmt->bindParam(":typ", $typ);
    $stmt->bindParam(":ttl", $ttl);
    $stmt->bindParam(":ctt", $ctt);
    $stmt->bindParam(":img", $img);
    $stmt->execute();
    if(!$stmt){
      msg("Error fooing posts :(");
      header("Location: /forum");
    }
    
  }
  msg("Success fooing posts :D");
  header("Location: /forum")
?>