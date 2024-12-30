<?php
  if (isset($_GET['logout']) && $_GET['logout']) {
    setcookie("wildkingsstatsuser", $_SESSION['username'], time()-3600);
    setcookie("wildkingsstatspass", $_SESSION['passwort'], time()-3600);  
  }
  elseif ($_COOKIE['wildkingsstatsuser'] != "") {
    header("location: main.php");
    exit;
  }

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Wild Kings Poker Brigade - Statistics: Login</title>
  <link type="text/css" rel="stylesheet" href="style.css" />
</head>
<body>
  <div style="text-align:center;">
    <img src="wildkings_logo.jpg" alt="Wild Kings Poker Brigade" /><br /><br />
  </div>
  <div style="margin-left:auto;margin-right:auto;width:230px;">
    <form id="logonfrm" action="main.php" method="post">
      <table>
        <tr>
          <td style="border-width:0;text-align:left;">Username:</td>
          <td style="border-width:0;text-align:left;"><input type="text" name="logonuser" /></td>
        </tr>
        <tr>
          <td style="border-width:0;text-align:left;">Password:</td>
          <td style="border-width:0;text-align:left;"><input type="password" name="logonpass" /></td>
        </tr>
        <tr>
          <td style="border-width:0;text-align:left;">Remember:</td>
          <td style="border-width:0;text-align:left;"><input type="checkbox" name="logoncookie" /></td>
        </tr>
        <tr>
          <td colspan="2" style="border-width:0;text-align:left;">
            <input type="submit" value="Logon" />
            <input type="hidden" name="logon" value="true" />
<?php
  if ($_GET['logon'] == "failed") {
    echo "<span style=\"color:red;font-weight:bold\">Login failed!</span>";
  }
?>          
          </td>
        </tr>
      </table>
    </form>
  </div>
</body>
</html>