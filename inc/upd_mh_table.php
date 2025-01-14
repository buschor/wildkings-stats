<?php
  header('Content-Type: text/html; charset=utf-8');

  include("config.inc.php");
  include("class_user.inc.php");
  include("class_mhsesslist.inc.php");

  session_start();

  $db = new mysqli(SQLSERVER, SQLUSER, SQLPWD, SQLDB);
  $user = new user($db, $_SESSION['username'], $_SESSION['passwort']);

  if ($user->getId() == 0)
    exit ("Access denied!");

  $mhsl = new mhSessionList($db, $user, $_GET['year'], $_GET['part']);
  $mhsl->showTotalTable((int)$_GET['mh_table_plsel'], (int)$_GET['mh_table_orderfield'], (int)$_GET['mh_table_ordertype']);

?>
