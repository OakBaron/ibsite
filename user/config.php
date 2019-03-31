<?php
  require_once($_SERVER['DOCUMENT_ROOT']."/googleApi/vendor/autoload.php");
  $client = new Google_Client();
  $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].'/googleApi/creds.json');
  $client->addScope(Google_Service_Oauth2::PLUS_LOGIN);
  $client->addScope(Google_Service_Oauth2::USERINFO_EMAIL);
  $client->setRedirectUri("https://ib.lukeogburn.com/user/callback.php");
?>