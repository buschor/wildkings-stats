<?php echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
  <title>Wild Kings Poker Brigade - Turnierpunkte-Rechner</title>
  <style type="text/css">
   
    body
    {
      background-color: #003300;
      font-family: Arial, Helvetica, Sans-Serif;
      font-size: 12px;
      color: lime;
    }   

    #btd {
      border-width:1px;
      border-style:solid;
      border-color:blue;
    }
  </style>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<p>
  <form name="tpr" action="<?= $_SERVER['PHP_SELF'] ?>" method="get">
    Anzahl Spieler:
    <input type="text" name="numpl" value="<?= (int)$_GET['numpl'] ?>" maxlength="3" />
    <input type="submit" value="Anzeigen!" />
  </form>
</p>

<?

  $anz = (int)$_GET['numpl'];

  if (($anz > 0) && ($anz < 1000)) {  

    echo "<table>";
    echo "<tr>";
    echo "<td style=\"background-color:#006600\">Rang</td>";
    echo "<td style=\"background-color:#006600\">Punkte</td>";
    echo "<td>&nbsp;</td>";
    echo "<td style=\"background-color:#006600\">Punkte (ungerundet)</td>";
    echo "</tr>";
            
    for ($i = 1; $i <= $anz / 2; $i++) {
         
      $p = (4 / $anz) * pow($anz / 2 - $i + 1, 2);

      echo "<tr>";
      echo "<td>" . $i . ".</td>";
      echo "<td style=\"font-weight:bold;text-align:right\">" . round($p) . "</td>";
      echo "<td>&nbsp;</td>";
      echo "<td style=\"color:#009900\">$p</td>";
      echo "</tr>";
    }      
      
    if ($anz % 2 != 0) {
      echo "<tr>";
      echo "<td>" . (floor($anz / 2) + 1) . ".</td>";
      echo "<td style=\"font-weight:bold;text-align:right\">0</td>";
      echo "<td>&nbsp;</td>";
      echo "<td>&nbsp;</td>";
      echo "</tr>";
    }
      
    for ($i = ceil($anz / 2 + 1); $i <= $anz; $i++) {
      echo "<tr>";
      echo "<td>" . $i . ".&nbsp;&nbsp;</td>";
      echo "<td style=\"font-weight:bold;text-align:right\">" . ceil($anz / 2 - $i) . "</td>";
      echo "<td>&nbsp;</td>";
      echo "<td>&nbsp;</td>";
      echo "</tr>";
    }   

    echo "</table>";

  }

?>

</body>
</html>