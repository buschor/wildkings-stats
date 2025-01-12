<?php

class mhSessionList {

  private $db;
  private $user;
  private $year;
  private $part;

  private $action;

  private $partdesc = array(
    1 => "1. Quartal",
    2 => "2. Quartal",
    3 => "3. Quartal",
    4 => "4. Quartal",
    5 => "1. Semester",
    6 => "2. Semester");

  public function __construct($aDb, $aUser, $aYear, $aPart)
  {
    $this->db = $aDb;
    $this->user = $aUser;
    if ($aYear > 2000)
      $this->year = (int)$aYear;
    else
      $this->year = (int)date("Y");
    $this->part = (int)$aPart;

    $this->action = isset($_GET['action']) ? $_GET['action'] : "";
  }

  public function show()
  {
    if (($this->action == "delete") && ($this->user->getStatus() >= 3))
    {
      $sess = new mhSession($this->db, $this->user, (int)$_GET['sid']);
      $sess->delete();

    }
    elseif (isset($_POST['mhsessedit']) && $_POST['mhsessedit'] && ($this->user->getStatus() >= 3))
    {
      $sess = new mhSession($this->db, $this->user, (int)$_POST['sid']);
      $sess->add();
    }
    else
    {
      echo "Monday Hold'em Statistik: ";
      $this->showYearSelector();

      echo "<hr />";
      echo "<h2>Sessions " . $this->year . "</h2>";

      echo "<div style=\"float:left;width:200px;height:500px;\">";

      if ($this->user->getStatus() >= 3)
      {
          //echo "&nbsp;&nbsp;&nbsp;";
          echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year . "&amp;action=add\">";
          echo "Add Session</a><br /><br />";
      }

      $res = $this->db->query("SELECT sid, UNIX_TIMESTAMP(sessdate) sessdate, location FROM mh_sessions " .
        "WHERE YEAR(sessdate) = " . $this->year . " " .
        "ORDER BY sessdate DESC");
      while ($obj = $res->fetch_object())
      {
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year . "&amp;sid=" . $obj->sid ."\">";
        echo date("d.m.Y", $obj->sessdate) . "&nbsp;&nbsp;";
        echo $obj->location . "</a><br />";
      }
      $res->close();
      echo "</div>";

      if (isset($_GET['sid']) && ($_GET['sid'] > 0) && ($this->action == ""))
      {
        $sess = new mhSession($this->db, $this->user, (int)$_GET['sid']);
        $sess->show();
      }
      elseif (($this->action == "add") && ($this->user->getStatus() >= 3))
      {
        $sess = new mhSession($this->db, $this->user, 0);
        $sess->showEditForm();
      }
      elseif (($this->action == "edit") && ($this->user->getStatus() >= 3))
      {
        $sess = new mhSession($this->db, $this->user, (int)$_GET['sid']);
        $sess->showEditForm();
      }
      elseif (isset($_POST['mhsessedit']) && $_POST['mhsessedit'] && ($this->user->getStatus() >= 3))
      {
        $sess = new mhSession($this->db, $this->user, (int)$_POST['sid']);
        $sess->add();
      }
    }
  }

  public function showTotal() {
    echo "Monday Hold'em Jahresstatistik: ";
    $this->showYearSelector();
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=9999\">Total</a>";

    echo "<hr />";

    if ($this->year == 9999)
      echo "<h2>Ewige Tabelle Monday Hold'em</h2>";
    else {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=0\">Jahrestotal</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=5\">" . $this->partdesc[5] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=6\">" . $this->partdesc[6] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=1\">" . $this->partdesc[1] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=2\">" . $this->partdesc[2] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=3\">" . $this->partdesc[3] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=4\">" . $this->partdesc[4] . "</a>";
      echo "<hr />";

      echo "<h2>Jahresstatistik Monday Hold'ems " . $this->year;
      if ($this->part > 0)
        echo " - " . $this->partdesc[$this->part];
      echo "</h2>";
    }

    echo "<script type=\"text/javascript\">";
    echo "function update_table() {";
    echo "  var myAjax = new Ajax.Request(";
    echo "    \"inc/upd_mh_table.php\",";
    echo "    { method: 'get', parameters: Form.serialize($('frm_mh_table_upd')), onComplete: show_table }";
    echo "  );";
    echo "}";
    echo "function show_table( originalRequest ) {";
    echo "  document.getElementById('mh_table').innerHTML = originalRequest.responseText;";
    echo "}";
    echo "</script>\n";

    echo "<div style=\"float:left;width:880px;height:500px;\">";
    echo "<script src=\"inc/tbfunctions.js\" type=\"text/javascript\"></script>\n";
    echo "<script src=\"inc/box.js\" type=\"text/javascript\"></script>\n";
    echo "<form id=\"frm_mh_table_upd\" style=\"margin:0px;\" action=\"\"><p style=\"margin-top:0px;\">";
    echo "Zeige Spieler:&nbsp;&nbsp;";
    echo "<input type=\"radio\" name=\"mh_table_plsel\" value=\"0\" onclick=\"update_table()\" checked=\"checked\" />Alle&nbsp;&nbsp;";
    echo "<input type=\"radio\" name=\"mh_table_plsel\" value=\"1\" onclick=\"update_table()\" />Member&nbsp;&nbsp;";
    echo "<input type=\"radio\" name=\"mh_table_plsel\" value=\"2\" onclick=\"update_table()\" />Goldmember";
    echo "<input type=\"hidden\" name=\"year\" value=\"" . $this->year . "\" />";
    echo "<input type=\"hidden\" name=\"part\" value=\"" . $this->part . "\" /></p>";

    echo "<div id=\"mh_table\">";
    $this->showTotalTable(0, 5, 0);
    echo "</div>";
    echo "</form>";

    echo "<br />";
    echo "</div>";

  }

  public function showTotalTable($pltyp, $orderfield, $ordertype) {

    echo "<div id=\"hiddenbox\" class=\"box\"></div>\n";

    echo "<input type=\"hidden\" name=\"mh_table_orderfield\" value=\"$orderfield\" />";
    echo "<input type=\"hidden\" name=\"mh_table_ordertype\" value=\"$ordertype\" />";

    $orderasc_def = array(
      2 => true, 4 => false, 5 => false, 6 => false, 7 => false, 8 => false, 9 => true,
      10 => false, 11 => false, 12 => false, 13 => false, 14 => false, 15 => true);

    if ($ordertype == 1)
      $orderasc_def[$orderfield] = !$orderasc_def[$orderfield];

    echo "<table>";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>#</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(2, $orderfield, $ordertype, "Name") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(4, $orderfield, $ordertype, "Sessions") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(5, $orderfield, $ordertype, "Saldo") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(6, $orderfield, $ordertype, "H. Won") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(7, $orderfield, $ordertype, "Buy In's") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(8, $orderfield, $ordertype, "Plays") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(9, $orderfield, $ordertype, "Penalty") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\">&nbsp;</td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(10, $orderfield, $ordertype, "&oslash;-\$ / H.") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(11, $orderfield, $ordertype, "%-Won H.") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(12, $orderfield, $ordertype, "Buyin/Sess") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(13, $orderfield, $ordertype, "&oslash;$/Sess.") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(14, $orderfield, $ordertype, "high win") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(15, $orderfield, $ordertype, "high lost") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\">&nbsp;</td>";
    echo "<td style=\"background-color:#eeeeee\"><b>Day Wins</b></td>";
	echo "<td style=\"background-color:#eeeeee\">&nbsp;</td>";
    echo "</tr>";

    $sql = "SELECT pl.pid, pl.pname, pl.status, COUNT(1) sessions, SUM(sd.saldo) saldo, SUM(sd.handswon) handswon, ".
      "SUM(sd.buyins) buyins, SUM(sd.plays) plays, SUM(sd.penalty) penalty, " .
      "SUM(sd.saldo)/SUM(sd.handswon) saldohand, " .
      "SUM(sd.handswon)/SUM(sd.plays)*100 dwonh, " .
      "SUM(sd.buyins)/COUNT(1) buysess, " .
      "SUM(sd.saldo)/COUNT(1) saldosess, " .
      "MAX(sd.saldo) maxsaldo, MIN(sd.saldo) minsaldo " .
      "FROM players pl ".
      "JOIN mh_sessdata sd ON pl.pid = sd.pid " .
      "JOIN mh_sessions se ON sd.sid = se.sid " .
      "WHERE pl.status >= ". $pltyp . " ";
    if ($this->year != 9999) {
      switch($this->part) {
        case 0: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " "; break;
        case 1: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 1 AND 3 "; break;
        case 2: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 4 AND 6 "; break;
        case 3: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 7 AND 9 "; break;
        case 4: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 10 AND 12 "; break;
        case 5: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 1 AND 6 "; break;
        case 6: $sql .= "AND YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 7 AND 12 "; break;
      }
    }
    $sql .= "GROUP BY pl.pid, pl.pname, pl.status " .
      "ORDER BY $orderfield " . ($orderasc_def[$orderfield] ? "" : "DESC");

    $rang = 1;
    $res = $this->db->query($sql);
    while ($obj = $res->fetch_object())
    {
      //SQL ist zu langsam
      //$sql = "SELECT COUNT(1) AS dwins FROM mh_sessdata " .
      //  "WHERE pid = " . $obj->pid . " " .
      //  "AND (sid, saldo) IN ( " .
      $dwins = 0;
      $sql = "SELECT sd.sid, sd.pid, sd.saldo FROM mh_sessdata sd ";
      if ($this->year != 9999)
      {
        $sql .= "JOIN mh_sessions se ON sd.sid = se.sid ";
        switch($this->part) {
          case 0: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " "; break;
          case 1: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 1 AND 3 "; break;
          case 2: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 4 AND 6 "; break;
          case 3: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 7 AND 9 "; break;
          case 4: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 10 AND 12 "; break;
          case 5: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 1 AND 6 "; break;
          case 6: $sql .= "WHERE YEAR(se.sessdate) = " . $this->year . " AND MONTH(se.sessdate) BETWEEN 7 AND 12 "; break;
        }

      }
      $sql .= "ORDER BY sd.sid, sd.saldo desc";
      $res2 = $this->db->query($sql);

      $sid_old = 0;
      while($obj2 = $res2->fetch_object()) {

        if (($sid_old != $obj2->sid) && ($obj2->pid == $obj->pid))
          $dwins++;

        $sid_old = $obj2->sid;
      }

      echo "<tr style=\"background-color:#ffffff\" onclick=\"colorRow(this);\">";
      echo "<td>" . $rang . ".</td>";
      echo "<td" . ($obj->status >= 2 ? " style=\"font-weight:bold\"" : "").  " ";
      echo "onmouseover=\"showBox('hiddenbox', " . $obj->pid . ", 'mh', " . $this->year . ", " . $this->part . ");\" onmouseout=\"hideBox();\">";
      echo htmlspecialchars($obj->pname) . "</td>";
      echo "<td style=\"text-align:right\">" . $obj->sessions . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->saldo < 0 ? "color:red" : "") . "\" ";
      echo "onmouseover=\"showBox('hiddenbox', " . $obj->pid . ", 'gm', " . $this->year . ", " . $this->part . ");\" onmouseout=\"hideBox();\">";
      echo $obj->saldo . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->handswon . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->buyins . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->plays . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->penalty . "</td>";
      echo "<td>&nbsp;</td>";
      echo "<td style=\"text-align:right;" . ($obj->saldohand < 0 ? "color:red" : "") . "\">" . number_format($obj->saldohand, 2) . "</td>";
      echo "<td style=\"text-align:right;\">" . number_format($obj->dwonh, 2) . "%</td>";
      echo "<td style=\"text-align:right;\">" . number_format($obj->buysess, 2) . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->saldosess < 0 ? "color:red" : "") . "\">" . number_format($obj->saldosess, 2) . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->maxsaldo < 0 ? "color:red" : "") . "\">" . number_format($obj->maxsaldo, 2) . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->minsaldo < 0 ? "color:red" : "") . "\">" . number_format($obj->minsaldo, 2) . "</td>";
      echo "<td>&nbsp;</td>";
      echo "<td style=\"text-align:right;\">" . $dwins . "</td>";
	  echo "<td style=\"text-align:right;\"><a href=\"inc/graph.php?pid=" . $obj->pid . "&typ=gm&year=" . $this->year . "&part=" . $this->part . "\" target=\"_blank\">";
	  echo "<img src=\"graph.png\" alt=\"graph\" /></a></td>";
      echo "</tr>";
      $rang++;
    }
    $res->close();
    echo "</table>";

  }

  private function getHeaderLink($orderfield, $nowordered, $ordertype, $title) {
    $link = "<a href=\"javascript:void(0)\" onclick=\"" .
      "document.getElementById('frm_mh_table_upd').mh_table_orderfield.value = '$orderfield'; ";
    if ($orderfield == $nowordered) {
      $link .= "document.getElementById('frm_mh_table_upd').mh_table_ordertype.value = '";
      if ($ordertype == 1)
        $link .= "0";
      else
        $link .= "1";
      $link .= "'; ";
    } else
      $link .= "document.getElementById('frm_mh_table_upd').mh_table_ordertype.value = '0'; ";
    $link .= "update_table();\" class=\"";
    if ($orderfield == $nowordered)
      $link .= "tbheadsel";
    else
      $link .= "tbheader";
    $link .= "\">" . $title . "</a>";
    return $link;
  }

  private function showYearSelector() {
    $res = $this->db->query("SELECT DISTINCT YEAR(sessdate) AS year FROM mh_sessions ORDER BY YEAR(sessdate) DESC");
    while ($obj = $res->fetch_object())
    {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $obj->year ."\">";
      echo $obj->year . "</a>&nbsp;&nbsp;&nbsp;";
    }
    $res->close();
  }

}

?>