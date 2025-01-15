<?php

class sgSession {

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
    echo "<div style=\"margin-left:200px;\">";

    $res = $this->db->query("SELECT UNIX_TIMESTAMP(sessdate) sessdate, buyin, location, comment FROM sg_sessions WHERE sid = " . $this->id);
    $obj = $res->fetch_object();
    echo date("d.m.Y", $obj->sessdate) . " ";
    echo $obj->location;
    echo ", " . $obj->buyin . " Fr. Buy In";
    $comment = $obj->comment;
    $res->close();
    echo "<br /><br /><table>";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Rang</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Name</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Team</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Points</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Preisgeld</b></td>";
    echo "</tr>";

    $rang = 1;
    $res = $this->db->query("SELECT sd.saldo, sd.points, pl.pname, pl.pname_first, pl.pname_last, pl.team " .
      "FROM sg_sessdata sd " .
      "JOIN players pl ON sd.pid = pl.pid " .
      "WHERE sd.sid = " . $this->id . " " .
      "ORDER BY sd.points DESC");
    while ($obj = $res->fetch_object())
    {
      echo "<tr>";
      echo "<td>" . $rang . ".</td>";
      echo "<td>" . htmlspecialchars($obj->pname_first . " \"" . $obj->pname . "\" " . $obj->pname_last) . "</td>";
      echo "<td>" . htmlspecialchars($obj->team) . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->points . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->saldo . "</td>";
      echo "</tr>";
      $rang++;
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
    $edbuyin = 10;
    $edwkpn = false;
    $edloc = "";
    $edcomm = "";

    if ($this->id > 0)
    {
      $res = $this->db->query("SELECT UNIX_TIMESTAMP(sessdate) sessdate, buyin, wkpn, location, comment " .
        "FROM sg_sessions WHERE sid = " . $this->id);
      $obj = $res->fetch_object();
      $eddate = date("d.m.Y", $obj->sessdate);
      $edbuyin = $obj->buyin;
      $edwkpn = $obj->wkpn;
      $edloc = htmlspecialchars($obj->location);
      $edcomm = htmlspecialchars($obj->comment);
      $res->close();

      $res = $this->db->query("SELECT sd.pid, sd.points, sd.saldo " .
        "FROM sg_sessdata sd " .
        "WHERE sd.sid = " . $this->id . " " .
        "ORDER BY sd.points DESC");

      $i = 0;
      while ($obj = $res->fetch_object())
      {
        $edpid[$i] = $obj->pid;
        $edpoints[$i] = $obj->points;
        $edsaldo[$i] = $obj->saldo;
        $i++;
      }
      $res->close();
    }

    echo "<div style=\"margin-left:200px;\">";
    echo "<script src=\"inc/checkform.js\" type=\"text/javascript\"></script>\n";
    echo "<form id=\"sg_sessadd\" action=\"";
    echo $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\" method=\"post\" ";
    echo "onsubmit=\"return checkAddSgSession()\">";

    echo "<div>";
    echo "Datum: <input type=\"text\" name=\"sgdate\" value=\"$eddate\" maxlength=\"10\" style=\"width:80px;\"/>";
    echo "&nbsp;(dd.mm.yyyy) &nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Location: <input type=\"text\" name=\"sglocation\" value=\"$edloc\" maxlength=\"20\" /><br />";
    echo "Buy In: <input type=\"text\" name=\"sgbuyin\" value=\"$edbuyin\" style=\"width:80px;\" maxlength=\"8\" />&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "Wild Kings Poker Night: <input type=\"checkbox\" name=\"sgwkpn\" " . ($edwkpn ? "checked=\"checked\"" : ""). " /><br />";
    echo "Comment: <input type=\"text\" name=\"sgcomment\" value=\"$edcomm\" style=\"width:350px;\" maxlength=\"80\" /><br /><br />";
    echo "</div>";
    echo "<table>\n";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Name</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Points</b> ";
    echo "<a href=\"ptscalc.php\" target=\"_blank\">Rechner</a>";
    echo "</td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Preisgeld</b></td>";
    echo "</tr>\n";
    for ($i = 0; $i < 32; $i++)
    {
      echo "<tr>";
      echo "<td>";
      echo "<select name=\"sgname[$i]\">";
      echo "<option value=\"\"></option>";

      $res = $this->db->query("SELECT pid, pname, pname_first, pname_last, status, IF(status < 2, 0, 1) gold FROM players " .
        "ORDER BY gold DESC, pname_first, pname_last");
      while ($obj = $res->fetch_object())
      {
        echo "<option value=\"" . $obj->pid . "\"";
        if ($obj->pid == $edpid[$i])
          echo " selected=\"selected\"";
        if ($obj->gold)
          echo " style=\"color:#990000\"";
        echo ">" . htmlspecialchars($obj->pname_first . " \"" . $obj->pname . "\" " . $obj->pname_last) . "</option>";
      }
      $res->close();

      echo "</select>";
      echo "</td>";
      echo "<td><input type=\"text\" name=\"sgpoints[$i]\" style=\"width:80px;\" value=\"" . (isset($edpoints[$i]) ? $edpoints[$i] : "") . "\" maxlength=\"3\"/></td>";
      echo "<td><input type=\"text\" name=\"sgsaldo[$i]\" style=\"width:80px;\" value=\"" . (isset($edsaldo[$i]) ? $edsaldo[$i] : "") . "\" maxlength=\"8\"/></td>";
      echo "</tr>\n";
    }
    echo "</table><div><br />";
    echo "<input type=\"hidden\" name=\"sgsessedit\" value=\"true\" />";
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

    if (!checkdate(substr($_POST['sgdate'], 3, 2), substr($_POST['sgdate'], 0, 2), substr($_POST['sgdate'], 6, 4)))
      $errmsg .= "Ung&uuml;ltiges Datum<br />";

    if (trim($_POST['sglocation']) == "")
      $errmsg .= "Ung&uuml;ltige Location<br />";

    if (!is_numeric($_POST['sgbuyin']))
      $errmsg .= "Ung&uuml;ltiger Buy In<br />";

    $anz = 0;
    for ($i = 0; $i < 32; $i++)
    {
      if ($_POST['sgname'][$i] > 0)
      {
        if (!is_numeric($_POST['sgpoints'][$i]))
          $errmsg .= "Ung&uuml;ltige Punktzahl ($i)<br />";
        if (!is_numeric($_POST['sgsaldo'][$i]) && ($_POST['sgsaldo'][$i] != ""))
          $errmsg .= "Ung&uuml;ltiges Preisgeld ($i)<br />";
        $anz++;
      }
    }
    if ($anz < 2)
      $errmsg .= "Zu wenig Spieler eingegeben<br />";

    $arr = array_count_values($_POST['sgname']);
    foreach($arr as $key => $value)
    {
      if (($key > 0) && ($value != 1))
        $errmsg .= "Ein Spieler wurde mehrfach eingetragen ($key, $value)<br />";
    }

    if ($errmsg != "")
      echo "<span class=\"error\">" . $errmsg .  "</span>";
    else
    {
      $sgdate[0] = (int)substr($_POST['sgdate'], 0, 2); //Tag
      $sgdate[1] = (int)substr($_POST['sgdate'], 3, 2); //Monat
      $sgdate[2] = (int)substr($_POST['sgdate'], 6, 4); //Jahr

      if ($this->id > 0) {
        $this->db->query("UPDATE sg_sessions SET sessdate ='" . $sgdate[2] . "-" . $sgdate[1] . "-" . $sgdate[0] . "', " .
          "buyin = " . (float)$_POST['sgbuyin'] . ", wkpn = " . ((isset($_POST['sgwkpn']) && $_POST['sgwkpn']) ? "1" : "0") . ", " .
          "location = '" . $this->db->real_escape_string($_POST['sglocation']) . "', " .
		  "comment = '" . $this->db->real_escape_string($_POST['sgcomment']) . "' " .
          "WHERE sid = " . $this->id);

        $this->db->query("DELETE FROM sg_sessdata WHERE sid = " . $this->id);
        $sid = $this->id;
      }
      else
      {
        $this->db->query("INSERT INTO sg_sessions (sid, sessdate, buyin, wkpn, location, comment) " .
          "VALUES (NULL, '" . $sgdate[2] . "-" . $sgdate[1] . "-" . $sgdate[0] . "', " . (float)$_POST['sgbuyin'] . ", " .
          ((isset($_POST['sgwkpn']) && $_POST['sgwkpn']) ? "1" : "0") . ", '" . $this->db->real_escape_string($_POST['sglocation']) . "', '" .
            $this->db->real_escape_string($_POST['sgcomment']) . "')");
        $sid = $this->db->insert_id;
      }

      for ($i = 0; $i < 32; $i++)
      {
        if ($_POST['sgname'][$i] > 0)
        {
          $this->db->query("INSERT INTO sg_sessdata (sid, pid, points, saldo) " .
            "VALUES ($sid, " .
            $_POST['sgname'][$i] . ", " .
            $_POST['sgpoints'][$i] . ", " .
            (float)$_POST['sgsaldo'][$i] . ")");
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

    $this->db->query("DELETE FROM sg_sessdata WHERE sid = " . $this->id);
    $this->db->query("DELETE FROM sg_sessions WHERE sid = " . $this->id);

    echo "Session wurde gel&ouml;scht<br />";
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $_GET['year'] . "\">Zur &Uuml;bersicht</a>";

    echo "</div>";

  }

}

?>