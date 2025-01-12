<?php
  header('Content-Type: text/html; charset=utf-8');

  include("config.inc.php");
  include("class_user.inc.php");
  include("class_player.inc.php");
  
  session_start();

  $db = new mysqli(SQLSERVER, SQLUSER, SQLPWD, SQLDB);
  $user = new user($db, $_SESSION['username'], $_SESSION['passwort']);
  
  if ($user->getId() == 0)
    exit ("Access denied!");

  $year = (int)$_GET['year'];
  $part = (int)$_GET['part'];  
    
  $pl = new player($db, $user, (int)$_GET['pid']);
  if ($_GET['typ'] == "mh")
    $pl->showMhHistory($year, $part);
  else
    $pl->showSgHistory($year, $part);
  
?>