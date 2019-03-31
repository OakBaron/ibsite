<?php
  
  require_once("config.php");
  $authUrl = $client->createAuthUrl();
  header("Location: ".$authUrl);
  
?>