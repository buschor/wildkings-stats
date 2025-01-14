<?php

  // erstellen eines leeren Bildes mit 400px Breite und 300px Höhe
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

  header("Content-type: image/png");
  $pl->drawGraph($_GET['typ'], $year, $part);
?>
