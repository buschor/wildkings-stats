<?php

class player {

  private $db;
  private $user;
  private $id;
  private $statuslist = array(0 => "Mitspieler", 1 => "Wild Kings Member", 2 => "WK Goldmember", 3 => "Administrator");

  public function __construct($aDb, $aUser, $aId) {

    $this->db = $aDb;
    $this->user = $aUser;
    $this->id = $aId;
  }

  public function show()
  {
    echo "<div>";

    $res = $this->db->query("SELECT pname, pname_first, pname_last, team, status, UNIX_TIMESTAMP(last_login) lastlog ".
      "FROM players WHERE pid = " . $this->id);
    $obj = $res->fetch_object();
    echo "<table>";
    echo "<tr>";
    echo "<td><b>Nickname</b></td>";
    echo "<td>" . htmlspecialchars($obj->pname) . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Vorname</b></td>";
    echo "<td>" . htmlspecialchars($obj->pname_first) . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Nachname</b></td>";
    echo "<td>" . htmlspecialchars($obj->pname_last) . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Team</b></td>";
    echo "<td>" . htmlspecialchars($obj->team) . "</td>";
    echo "</tr>";
    if ($this->user->getStatus() >= 3)
    {
      echo "<tr>";
      echo "<td><b>Status</b></td>";
      echo "<td>" . $this->statuslist[$obj->status] . "</td>";
      echo "</tr>";
      echo "<tr>";
      echo "<td><b>Letzter Login</b></td>";
      echo "<td>" . ($obj->lastlog > 0 ? date("d.m.Y H:i", $obj->lastlog) : "Nie") . "</td>";
      echo "</tr>";
    }
    echo "</table>";

    $res->close();

    if (($this->user->getStatus() >= 3) || ($this->id == $this->user->getId()))
    {
        echo "<br />";
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'];
        echo "&amp;action=edit&amp;pid=" . $this->id . "\">";
        echo "Edit Player</a>&nbsp;&nbsp;&nbsp;";
        $res = $this->db->query("SELECT COUNT(1) AS numsess FROM mh_sessdata WHERE pid = " . $this->id);
        $obj = $res->fetch_object();
        $numsess = $obj->numsess;
        $res->close();
        $res = $this->db->query("SELECT COUNT(1) AS numsess FROM sg_sessdata WHERE pid = " . $this->id);
        $obj = $res->fetch_object();
        $numsess += $obj->numsess;
        $res->close();
        if (($this->user->getStatus() >= 3) && ($numsess == 0) && ($this->id != $this->user->getId()))
        {
          echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'];
          echo "&amp;action=delete&amp;pid=" . $this->id . "\" onclick=\"return confirm('Den User wirklich löschen?');\">";
          echo "Delete Player</a>";
        }
    }


    echo "<br /><br />";
    echo "<div style=\"float:left\">";
    $this->showMhHistory(9999, 0);
    echo "<br /></div>";

    echo "<div style=\"margin-left:500px\">";
    $this->showSgHistory(9999, 0);
    echo "<br /></div>";

    echo "</div>";
  }

  public function showMhHistory($year, $part) {
    echo "Monday Hold'em History<br /><br /><table>";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Datum</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Saldo</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Hands Won</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Buy-In's</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Games Played</b></td>";
    echo "</tr>";

    $sql = "SELECT sd.saldo, sd.handswon, sd.buyins, sd.plays, UNIX_TIMESTAMP(sessdate) sessdate " .
      "FROM players pl " .
      "JOIN mh_sessdata sd ON pl.pid = sd.pid " .
      "JOIN mh_sessions se ON sd.sid = se.sid " .
      "WHERE pl.pid = " . $this->id . " ";
    if ($year != 9999) {
      switch($part) {
        case 0: $sql .= "AND YEAR(se.sessdate) = " . $year . " "; break;
        case 1: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 3 "; break;
        case 2: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 4 AND 6 "; break;
        case 3: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 9 "; break;
        case 4: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 10 AND 12 "; break;
        case 5: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 6 "; break;
        case 6: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 12 "; break;
      }
    }
    $sql .= "ORDER BY se.sessdate DESC";

    $res = $this->db->query($sql);
    while ($obj = $res->fetch_object())
    {
      echo "<tr>";
      echo "<td>" . date("d.m.Y", $obj->sessdate) . "</td>";
      echo "<td style=\"text-align:right;";
      if ($obj->saldo < 0)
        echo "color:red";
      echo "\">" . $obj->saldo . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->handswon . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->buyins . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->plays . "</td>";
      echo "</tr>";

    }
    $res->close();

    echo "</table>";
  }

  public function showSgHistory($year, $part) {
    echo "Turniere History<br /><br /><table>";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Datum</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Rang</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Punkte</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Preisgeld</b></td>";
    echo "</tr>";

    $sql = "SELECT se.sid, sd.points, sd.saldo, UNIX_TIMESTAMP(sessdate) sessdate " .
      "FROM players pl " .
      "JOIN sg_sessdata sd ON pl.pid = sd.pid " .
      "JOIN sg_sessions se ON sd.sid = se.sid " .
      "WHERE pl.pid = " . $this->id . " ";
    if ($year != 9999) {
      switch($part) {
        case 0: $sql .= "AND YEAR(se.sessdate) = " . $year . " "; break;
        case 1: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 3 "; break;
        case 2: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 4 AND 6 "; break;
        case 3: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 9 "; break;
        case 4: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 10 AND 12 "; break;
        case 5: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 6 "; break;
        case 6: $sql .= "AND YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 12 "; break;
      }
    }
    $sql .= "ORDER BY se.sessdate DESC";

    $res = $this->db->query($sql);
    while ($obj = $res->fetch_object())
    {
      $rang = 1;
      $anzp = 0;
      $break = false;
      $rsr = $this->db->query("SELECT pid FROM sg_sessdata " .
        "WHERE sid = " . $obj->sid . " " .
        "ORDER BY points DESC");
      while ($obr = $rsr->fetch_object()) {
        if ($this->id == $obr->pid)
          $break = true;

        $anzp++;

        if (!$break)
          $rang++;
      }
      $rsr->close();

      echo "<tr>";
      echo "<td>" . date("d.m.Y", $obj->sessdate) . "</td>";
      echo "<td>" . $rang ." / ". $anzp. "</td>";
      echo "<td style=\"text-align:right;";
      if ($obj->points < 0)
        echo "color:red";
      echo "\">" . $obj->points . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->saldo . "</td>";
      echo "</tr>";

    }
    $res->close();

    echo "</table>";
  }

  public function showEditForm() {

    if ($this->id > 0)
    {
      $res = $this->db->query("SELECT pname, pname_first, pname_last, team, status FROM players WHERE pid = " . $this->id);
      $obj = $res->fetch_object();
      $edname = $obj->pname;
      $ednamefirst = $obj->pname_first;
      $ednamelast = $obj->pname_last;
      $edteam = $obj->team;
      $edstatus = $obj->status;
      $res->close();
    }

    echo "<form id=\"playeradd\" action=\"";
    echo $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\" method=\"post\">";

    echo "<table>";
    echo "<tr>";
    echo "<td><b>Nickname (Login)</b></td>";
    echo "<td><input type=\"text\" name=\"plname\" value=\"" .  htmlspecialchars($edname) . "\" maxlength=\"15\"/></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Passwort</b></td>";
    echo "<td><input type=\"password\" name=\"plpwd\"  maxlength=\"12\"/>";
    if ($this->id > 0)
      echo "leer lassen, wenn keine &Auml;nderung";
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Vorname</b></td>";
    echo "<td><input type=\"text\" name=\"plnamefirst\" value=\"" .  htmlspecialchars($ednamefirst) . "\" maxlength=\"30\"/></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Nachname</b></td>";
    echo "<td><input type=\"text\" name=\"plnamelast\" value=\"" .  htmlspecialchars($ednamelast) . "\" maxlength=\"30\"/></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><b>Team</b></td>";
    echo "<td><input type=\"text\" name=\"plteam\" value=\"" .  htmlspecialchars($edteam) . "\" maxlength=\"20\"/></td>";
    echo "</tr>";
    if ($this->user->getStatus() >= 3)
    {
      echo "<tr>";
      echo "<td><b>Status</b></td>";
      echo "<td>";
      echo "<select name=\"plstatus\">";

      foreach ($this->statuslist as $key => $value){
        echo "<option value=\"" . $key . "\"";
        if ($key == $edstatus )
          echo " selected=\"selected\"";
        echo ">" . $value . "</option>";
      }

      echo "</select>";
      echo "</td>";
      echo "</tr>";
    }
    echo "</table>";

    echo "</table><div><br />";
    echo "<input type=\"hidden\" name=\"playeredit\" value=\"true\" />";
    if ($this->id > 0)
      echo "<input type=\"hidden\" name=\"pid\" value=\"" . $this->id . "\" />";
    echo "<input type=\"submit\" value=\"Player ";
    if ($this->id > 0)
      echo "&auml;ndern!";
    else
      echo "eintragen!";
    echo "\" />";
    echo "</div></form>";
  }

  public function add() {
    echo "<div>";
    echo "<h2>Player eintragen!</h2>";

    $errmsg = "";

    if (trim($_POST['plname']) == "")
      $errmsg .= "Ung&uuml;ltiger Nickname<br />";

    if (((strlen($_POST['plpwd']) < 4) && ($this->id == 0)) || (($this->id > 0) && (strlen($_POST['plpwd']) > 0) && (strlen($_POST['plpwd']) < 4)))
      $errmsg .= "Ung&uuml;ltiger Passwort, mindestens 4 Zeichen<br />";

    if (trim($_POST['plnamefirst']) == "")
      $errmsg .= "Ung&uuml;ltiger Vorname<br />";

    if (trim($_POST['plnamelast']) == "")
      $errmsg .= "Ung&uuml;ltiger Nachname<br />";

    if ($errmsg != "")
      echo "<span class=\"error\">" . $errmsg .  "</span>";
    else
    {
      if ($this->id > 0)
      {
        $sql = "UPDATE players SET pname = '" . $_POST['plname'] . "', " .
          "pname_first = '" . $_POST['plnamefirst'] . "', " .
          "pname_last = '" . $_POST['plnamelast'] . "', ";
        if (strlen($_POST['plpwd']) >= 4) {
          $sql .= "ppass = '" . $_POST['plpwd'] . "', ";
          if ($this->user->getId() == $this->id)
            $_SESSION['passwort'] = $_POST['plpwd'];
        }
        $sql .= "team = '" . $_POST['plteam'] . "' ";
        if ($this->user->getStatus() >= 3)
          $sql .= ", status = '" . (int)$_POST['plstatus'] . "' ";
        $sql .= "WHERE pid = " . $this->id;

        $this->db->query($sql);
      }
      else
      {

        if ($this->user->getStatus() >= 3)
        {
          $res = $this->db->query("SELECT COUNT(1) AS nickcount FROM players WHERE pname = '" . $_POST['plname'] . "'");
          $obj = $res->fetch_object();
          $nickcount = $obj->nickcount;
          $res->close();
          if ($nickcount > 0)
            echo "Fehler: Dieser Nickname (" . $_POST['plname'] . ") ist schon vorhanden!<br />";
          else {
            $this->db->query("INSERT INTO players (pid, pname, pname_first, pname_last, ppass, team, status) " .
              "VALUES (NULL, '" . $_POST['plname'] . "', '" . $_POST['plnamefirst'] . "', '" . $_POST['plnamelast'] . "', " .
              "'" . $_POST['plpwd'] . "', '" . $_POST['plteam'] . "', " . (int)$_POST['plstatus'] . ")");
          }
        }
      }

      echo "Player wurde eingetragen<br />";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "\">Zur &Uuml;bersicht</a>";

    }

    echo "</div>";

  }

  public function delete() {
    echo "<div>";
    echo "<h2>Player l&ouml;schen</h2>";
    $res = $this->db->query("SELECT COUNT(1) AS numsess FROM mh_sessdata WHERE pid = " . $this->id);
    $obj = $res->fetch_object();
    $numsess = $obj->numsess;
    $res->close();
    $res = $this->db->query("SELECT COUNT(1) AS numsess FROM sg_sessdata WHERE pid = " . $this->id);
    $obj = $res->fetch_object();
    $numsess += $obj->numsess;
    $res->close();

    if (($numsess == 0) && ($this->id != $this->user->getId()))
    {
      $this->db->query("DELETE FROM players WHERE pid = " . $this->id);
      echo "Player wurde gel&ouml;scht<br />";
    }
    else
    {
      echo "Spieler kann nicht gelöscht werden, da dieser in Sessions vorhanden ist!<br />";
    }


    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\">Zur &Uuml;bersicht</a>";

    echo "</div>";
  }

  public function drawGraph($typ, $year, $part) {

    if ($typ == 'gm') {
      $sql = "SELECT se.sid, sd.saldo FROM mh_sessions se " .
        "LEFT JOIN mh_sessdata sd ON se.sid = sd.sid AND sd.pid = " . $this->id . " ";
      if ($year != 9999) {
        switch($part) {
          case 0: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " "; break;
          case 1: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 3 "; break;
          case 2: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 4 AND 6 "; break;
          case 3: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 9 "; break;
          case 4: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 10 AND 12 "; break;
          case 5: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 6 "; break;
          case 6: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 12 "; break;
        }
      }
      $sql .= "ORDER BY se.sessdate";
    }
    else {
      $sql = "SELECT se.sid, sd.points AS saldo FROM sg_sessions se " .
        "LEFT JOIN sg_sessdata sd ON se.sid = sd.sid AND sd.pid = " . $this->id . " ";
      if ($year != 9999) {
        switch($part) {
          case 0: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " "; break;
          case 1: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 3 "; break;
          case 2: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 4 AND 6 "; break;
          case 3: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 9 "; break;
          case 4: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 10 AND 12 "; break;
          case 5: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 1 AND 6 "; break;
          case 6: $sql .= "WHERE YEAR(se.sessdate) = " . $year . " AND MONTH(se.sessdate) BETWEEN 7 AND 12 "; break;
        }
      }
      $sql .= "ORDER BY se.sessdate";
    }
    $res = $this->db->query($sql);

    $i = 0;
    $saldo_sum = 0;
    while ($obj = $res->fetch_object())
    {
      $saldi[$i] = $saldo_sum + $obj->saldo;
      $saldo_sum += $obj->saldo;
      $i++;
    }
    //$saldi = array(10, -4.25, 78.5, 78.25, 150, -10.25, 5.7, -23.6, 55, 55, 47, 0, 22, 25, 12, 12);

    $maxsaldo = max($saldi);
    $minsaldo = min($saldi);

    $numsessions = count($saldi)-1;

    if ($typ == 'gm') {
      $pixfr = 1.5;  //Pixel pro Franken
      $gridx = 10;   //Session-Abstand
      $gridy = 10 * $pixfr; //Abstand Linie Franken
      $randy = 5 * $pixfr; //abstand oben und unten
    }
    else {
      $pixfr = 2;  //Pixel pro Franken
      $gridx = 10;   //Session-Abstand
      $gridy = 10 * $pixfr; //Abstand Linie Franken
      $randy = 5 * $pixfr; //abstand oben und unten
    }

    $startx = 25; //rand links
    $font = 1;

    $imgwidth = $numsessions * $gridx + $startx;
    $imgheight = abs(min(-10, $minsaldo) * $pixfr) + abs(max(10, $maxsaldo) * $pixfr) + 2 * $randy;

    $im = imagecreatetruecolor($imgwidth, $imgheight);

    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    $red = imagecolorallocate($im, 255, 0, 0);
    $gray = imagecolorallocate($im, 232, 232, 232);
    $dgray = imagecolorallocate($im, 184, 184, 184);

    imageantialias ($im, true);
    imagefill($im, 0, 0, $white);

    //Gitter
    $nully = abs(max(10, $maxsaldo) * $pixfr) + $randy;

    $j = 0;
    for ($i = $nully; $i >= 0; $i -= $gridy) {
       imageline($im, $startx, $i, $imgwidth, $i, $gray);
       imagestring($im, $font, $startx - 4 - imagefontwidth($font) * strlen($j), $i - 2, $j, $dgray);
       $j += $gridy / $pixfr;
    }
    $j = 0;
    for ($i = $nully; $i <= $imgheight; $i += $gridy) {
       imageline($im, $startx, $i, $imgwidth, $i, $gray);
       imagestring($im, $font, $startx - 4 - imagefontwidth($font) * strlen($j), $i - 2, $j, $dgray);
       $j -= $gridy / $pixfr;

    }
    for ($i = $startx; $i <= $imgwidth; $i += $gridx)
       imageline($im, $i, 0, $i, $imgheight, $gray);
    imageline($im, $startx, $nully, $imgwidth, $nully, $red); //Nulllinie

    imagestring($im, $font, $startx - 4 - imagefontwidth($font) * strlen("0"), $nully - 2, "0", $red);

    //Graph
    for ($i = 0; $i < count($saldi) - 1; $i++) {
       imageline($im, $startx + $i * $gridx, $nully - $saldi[$i] * $pixfr, $startx + ($i + 1) * $gridx, $nully - $saldi[$i + 1] * $pixfr, $black);
    }

    // Output
    //header('Content-type: image/png');

    imagepng($im);
    imagedestroy($im);
  }

}

?>