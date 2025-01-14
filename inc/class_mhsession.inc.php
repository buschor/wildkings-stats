<?php

class mhSession {

  private $db;
  private $user;
  private $id;

  public function __construct($aDb, $aUser, $aId)
  {
    $this->db = $aDb;
    $this->user = $aUser;
    $this->id = (int)$aId;
  }

  public function show()
  {
    echo "<div>";

    $res = $this->db->query("SELECT UNIX_TIMESTAMP(sessdate) sessdate, location, comment FROM mh_sessions WHERE sid = " . $this->id);
    $obj = $res->fetch_object();
    echo date("d.m.Y", $obj->sessdate) . " ";
    echo $obj->location;
    $comment = $obj->comment;
    $res->close();
    echo "<br /><br /><table>";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Name</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Saldo</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Hands Won</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Buy-In's</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Games Played</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Penalty</b></td>";
    echo "</tr>";

    $res = $this->db->query("SELECT sd.saldo, sd.handswon, sd.buyins, sd.plays, sd.penalty, pl.pname " .
      "FROM mh_sessdata sd " .
      "JOIN players pl ON sd.pid = pl.pid " .
      "WHERE sd.sid = " . $this->id . " " .
      "ORDER BY sd.saldo DESC");
    while ($obj = $res->fetch_object())
    {
      echo "<tr>";
      echo "<td>" . htmlspecialchars($obj->pname) . "</td>";
      echo "<td style=\"text-align:right;";
      if ($obj->saldo < 0)
        echo "color:red";
      echo "\">" . $obj->saldo . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->handswon . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->buyins . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->plays . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->penalty . "</td>";
      echo "</tr>";

    }
    $res->close();

    echo "</table><br />";
    if ($comment != "")
      echo htmlspecialchars($comment) . "<br /><br /><br />";


    if ($this->user->getStatus() >= 3)
    {
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'];
        echo "&amp;action=edit&amp;sid=" . $this->id . "\">";
        echo "Edit Session</a>&nbsp;&nbsp;&nbsp;";
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'];
        echo "&amp;action=delete&amp;sid=" . $this->id . "\" onclick=\"return confirm('Die Session wirklich löschen?');\">";
        echo "Delete Session</a>";
    }

    echo "</div>";
  }

  public function showEditForm() {

    $eddate =  date("d.m.Y");
    $edloc = "";
    $edcomm = "";

    if ($this->id > 0)
    {
      $res = $this->db->query("SELECT UNIX_TIMESTAMP(sessdate) sessdate, location, comment " .
        "FROM mh_sessions WHERE sid = " . $this->id);
      $obj = $res->fetch_object();
      $eddate =  date("d.m.Y", $obj->sessdate);
      $edloc = htmlspecialchars($obj->location);
      $edcomm = htmlspecialchars($obj->comment);
      $res->close();

      $res = $this->db->query("SELECT sd.pid, sd.saldo, sd.handswon, sd.buyins, sd.plays, sd.penalty " .
        "FROM mh_sessdata sd " .
        "WHERE sd.sid = " . $this->id . " " .
        "ORDER BY sd.saldo DESC");

      $i = 0;
      while ($obj = $res->fetch_object())
      {
        $edpid[$i] = $obj->pid;
        $edsaldo[$i] = $obj->saldo;
        $edhwon[$i] = $obj->handswon;
        $edbuyin[$i] = $obj->buyins;
        $edplays[$i] = $obj->plays;
        $edpenalty[$i] = $obj->penalty;
        $i++;
      }
      $res->close();
    }

    echo "<div style=\"margin-left:200px;\">";
    echo "<script src=\"inc/checkform.js\" type=\"text/javascript\"></script>\n";
    echo "<form id=\"mh_sessadd\" action=\"";
    echo $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\" method=\"post\" ";
    echo "onsubmit=\"return checkAddMhSession()\">";

    echo "<div>";
    echo "Datum: <input type=\"text\" name=\"mhdate\" value=\"$eddate\" maxlength=\"10\" style=\"width:80px;\"/>";
    echo "&nbsp;(dd.mm.yyyy) &nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Location: <input type=\"text\" name=\"mhlocation\" value=\"$edloc\" maxlength=\"20\" /><br />";
    echo "Comment: <input type=\"text\" name=\"mhcomment\" value=\"$edcomm\" style=\"width:350px;\" maxlength=\"80\" /><br /><br />";
    echo "</div>";
    echo "<table>\n";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Name</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Saldo</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Hands Won</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Buy-In's</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Games Played</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Penalty</b></td>";
    echo "</tr>\n";
    for ($i = 0; $i < 20; $i++)
    {
      echo "<tr>";
      echo "<td>";
      echo "<select name=\"mhname[$i]\">";
      echo "<option value=\"\"></option>";

      $res = $this->db->query("SELECT pid, pname, status, IF(status < 2, 0, 1) gold FROM players " .
        "ORDER BY gold DESC, pname");
      while ($obj = $res->fetch_object())
      {
        echo "<option value=\"" . $obj->pid . "\"";
        if ($obj->pid == $edpid[$i])
          echo " selected=\"selected\"";
        if ($obj->gold)
          echo " style=\"color:#990000\"";
        echo ">" . $obj->pname . "</option>";
      }
      $res->close();

      echo "</select>";
      echo "</td>";
      echo "<td><input type=\"text\" name=\"mhsaldo[$i]\" style=\"width:80px;\" value=\"" . (isset($edsaldo[$i]) ? $edsaldo[$i] : "") . "\" maxlength=\"10\"/></td>";
      echo "<td><input type=\"text\" name=\"mhhwons[$i]\" style=\"width:80px;\" value=\"" . (isset($edhwon[$i]) ? $edhwon[$i] : "") . "\" maxlength=\"4\"/></td>";
      echo "<td><input type=\"text\" name=\"mhbuyin[$i]\" style=\"width:80px;\" value=\"" . (isset($edbuyin[$i]) ? $edbuyin[$i] : "") . "\" maxlength=\"2\"/></td>";
      echo "<td><input type=\"text\" name=\"mhgamep[$i]\" style=\"width:80px;\" value=\"" . (isset($edplays[$i]) ? $edplays[$i] : "") . "\" maxlength=\"4\"/></td>";
      echo "<td><input type=\"text\" name=\"mhpenal[$i]\" style=\"width:80px;\" value=\"" . (isset($edpenalty[$i]) ? $edpenalty[$i] : "") . "\" maxlength=\"10\"/></td>";
      echo "</tr>\n";
    }
    echo "</table><div><br />";
    echo "<input type=\"hidden\" name=\"mhsessedit\" value=\"true\" />";
    if ($this->id > 0)
      echo "<input type=\"hidden\" name=\"sid\" value=\"" . $this->id . "\" />";
    echo "<input type=\"submit\" value=\"Session ";
    if ($this->id > 0)
      echo "&auml;ndern!";
    else
      echo "eintragen!";
    echo "\" />";
    echo "</div></form></div>";
  }

  public function add() {
    echo "<div>";
    echo "<h2>Daten eintragen!</h2>";

    $errmsg = "";

    if (!checkdate(substr($_POST['mhdate'], 3, 2), substr($_POST['mhdate'], 0, 2), substr($_POST['mhdate'], 6, 4)))
      $errmsg .= "Ung&uuml;ltiges Datum<br />";

    if (trim($_POST['mhlocation']) == "")
      $errmsg .= "Ung&uuml;ltige Location<br />";

    $anz = 0;
    for ($i = 0; $i < 20; $i++)
    {
      if ($_POST['mhname'][$i] > 0)
      {
        if (!is_numeric($_POST['mhsaldo'][$i]))
          $errmsg .= "Ung&uuml;ltiges Saldo ($i)<br />";
        if (!is_numeric($_POST['mhhwons'][$i]) || ($_POST['mhhwons'][$i] < 0))
          $errmsg .= "Ung&uuml;ltige Anzahl Hands Won ($i)<br />";
        if (!is_numeric($_POST['mhbuyin'][$i]) || ($_POST['mhbuyin'][$i] < 0))
          $errmsg .= "Ung&uuml;ltige Anzahl Buy Ins ($i)<br />";
        if (!is_numeric($_POST['mhgamep'][$i]) || ($_POST['mhgamep'][$i] <= 0))
          $errmsg .= "Ung&uuml;ltige Anzahl Games Played ($i)<br />";
        if (!is_numeric($_POST['mhpenal'][$i]))
          $errmsg .= "Ung&uuml;ltiges Penalty ($i)<br />";
        $anz++;
      }
    }
    if ($anz < 2)
      $errmsg .= "Zu wenig Spieler eingegeben<br />";

    //print_r(array_count_values($_POST['mhname']));
    $arr = array_count_values($_POST['mhname']);
    foreach($arr as $key => $value)
    {
      if (($key > 0) && ($value != 1))
        $errmsg .= "Ein Spieler wurde mehrfach eingetragen ($key, $value)<br />";
    }

    if ($errmsg != "")
      echo "<span class=\"error\">" . $errmsg .  "</span>";
    else
    {
      $mhdate[0] = (int)substr($_POST['mhdate'], 0, 2); //Tag
      $mhdate[1] = (int)substr($_POST['mhdate'], 3, 2); //Monat
      $mhdate[2] = (int)substr($_POST['mhdate'], 6, 4); //Jahr

      if ($this->id > 0) {
        $this->db->query("UPDATE mh_sessions SET sessdate ='" . $mhdate[2] . "-" . $mhdate[1] . "-" . $mhdate[0] . "', " .
          "location = '" . $this->db->real_escape_string($_POST['mhlocation']) . "', " .
		  "comment = '" . $this->db->real_escape_string($_POST['mhcomment']) . "' " .
          "WHERE sid = " . $this->id);

        $this->db->query("DELETE FROM mh_sessdata WHERE sid = " . $this->id);

        $sid = $this->id;
      }
      else
      {
        if (!$this->db->query("INSERT INTO mh_sessions (sid, sessdate, location, comment) " .
          "VALUES (NULL, '" . $mhdate[2] . "-" . $mhdate[1] . "-" . $mhdate[0] . "', " .
          "'" . $this->db->real_escape_string($_POST['mhlocation']) . "', " .
          "'" . $this->db->real_escape_string($_POST['mhcomment']) . "')")) {
          echo "<span class=\"error\">" . $this->db->error . "</span>";
        }
        $sid = $this->db->insert_id;
      }

      for ($i = 0; $i < 20; $i++)
      {
        if ($_POST['mhname'][$i] > 0)
        {
          $this->db->query("INSERT INTO mh_sessdata (sid, pid, saldo, handswon, buyins, plays, penalty) " .
            "VALUES ($sid, " .
            $_POST['mhname'][$i] . ", " .
            $_POST['mhsaldo'][$i] . ", " .
            $_POST['mhhwons'][$i] . ", " .
            $_POST['mhbuyin'][$i] . ", " .
            $_POST['mhgamep'][$i] . ", " .
            $_POST['mhpenal'][$i] . ")");
        }
      }

      echo "Session wurde eingetragen<br />";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\">Zur &Uuml;bersicht</a>";

    }

    echo "</div>";

  }

  public function delete() {
    echo "<div>";
    echo "<h2>Session l&ouml;schen</h2>";

    $this->db->query("DELETE FROM mh_sessdata WHERE sid = " . $this->id);
    $this->db->query("DELETE FROM mh_sessions WHERE sid = " . $this->id);

    echo "Session wurde gel&ouml;scht<br />";
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\">Zur &Uuml;bersicht</a>";

    echo "</div>";

  }

}

?>