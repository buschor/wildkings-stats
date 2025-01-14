<?php
  header('Content-Type: text/html; charset=utf-8');

  include("config.inc.php");
  include("class_user.inc.php");
  include("class_sgsesslist.inc.php");

  session_start();

  $db = new mysqli(SQLSERVER, SQLUSER, SQLPWD, SQLDB);
  $user = new user($db, $_SESSION['username'], $_SESSION['passwort']);

  if ($user->getId() == 0)
    exit ("Access denied!");

  $sgsl = new sgSessionList($db, $user, $_GET['year'], $_GET['part']);
  $sgsl->showTotalTable((int)$_GET['sg_table_plsel'], (int)$_GET['sg_table_orderfield'], (int)$_GET['sg_table_ordertype']);

?>
