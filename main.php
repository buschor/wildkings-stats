<?php
  include("inc/config.inc.php");
  include("inc/class_user.inc.php");
  include("inc/class_mhsesslist.inc.php");
  include("inc/class_mhsession.inc.php");
  include("inc/class_sgsesslist.inc.php");
  include("inc/class_sgsession.inc.php");
  include("inc/class_playerlist.inc.php");
  include("inc/class_player.inc.php");
  
  
  $db = new mysqli(SQLSERVER, SQLUSER, SQLPWD, SQLDB);
  
  session_start();
  
  if (($_COOKIE['wildkingsstatsuser'] != "") && !isset($_SESSION['username'])) {    
    $_SESSION['username'] = $_COOKIE['wildkingsstatsuser'];
    $_SESSION['passwort'] = $_COOKIE['wildkingsstatspass'];
  }  
  
  if ($_POST['logon']) {
    $_SESSION['username'] = $_POST['logonuser'];
    $_SESSION['passwort'] = $_POST['logonpass'];
  }   
  
  $user = new user($db, $_SESSION['username'], $_SESSION['passwort']);
  
  if ($user->getId() == 0) {
    setcookie("wildkingsstatsuser", $_SESSION['username'], time()-3600);
    setcookie("wildkingsstatspass", $_SESSION['passwort'], time()-3600);  
    header("location: index.php?logon=failed");
    exit;
  };
  
  if ($_POST['logon']) {
    $user->setLastLogin();
	if ($_POST['logoncookie']) {
	  setcookie("wildkingsstatsuser", $_SESSION['username'], time()+5184000);
	  setcookie("wildkingsstatspass", $_SESSION['passwort'], time()+5184000);
	}
  }
  
?>
<?php echo "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
  <title>Wild Kings Poker Brigade - Statistics</title>
  <meta http-equiv="content-type" content="application/xhtml+xml; charset=iso-8859-1" />
  <link type="text/css" rel="stylesheet" href="style.css" title="" />
  <script src="inc/prototype.js" type="text/javascript"></script>
</head>
<body>
  <div style="text-align:center;">
    <img src="wildkings_logo.jpg" alt="Wild Kings Poker Brigade" /><br /><br />
  </div>
  <div style="float:left;">  
    <strong>Monday Hold'em Statistik</strong><br />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?site=mh_sess">Monday Hold'em Sessions</a><br />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?site=mh_stat">Monday Hold'em Total</a><br /><br />
    <strong>Turnierstatistik</strong><br />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?site=sg_sess">Turnierstatistik Sessions</a><br />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?site=sg_stat">Turnierstatistik Total</a><br /><br />
    <strong>Players</strong><br />
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>?site=pl_stat">Players</a><br /><br />
    <strong>Account</strong><br />
    <a href="<?php echo $_SERVER['PHP_SELF'] . "?site=pl_stat&amp;year=" . $_GET['year'] . "&amp;action=edit&amp;pid=" . $user->getId(); ?>">
      Edit
    </a><br />
    <a href="index.php?logout=true">Logout</a><br />
  </div>
  <div style="margin-left:200px;">
<?php
  

  switch ($_GET['site']) {
    case "mh_sess":      
      $mhsl = new mhSessionList($db, $user, $_GET['year'], 0);
      $mhsl->show();
      break;
    case "mh_stat":
      $mhsl = new mhSessionList($db, $user, $_GET['year'], $_GET['part']);
      $mhsl->showTotal();
      break;
    case "sg_sess":      
      $sgsl = new sgSessionList($db, $user, $_GET['year'], 0);
      $sgsl->show();
      break;
    case "sg_stat":
      $sgsl = new sgSessionList($db, $user, $_GET['year'], $_GET['part']);
      $sgsl->showTotal();
      break;
    case "pl_stat":
      $plli = new playerList($db, $user, $_GET['year']);
      $plli->show();
      break;
  }
  
  
?>
  </div>  
</body>
</html>