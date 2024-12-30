<?php

class sgSessionList {

  private $db;
  private $user;
  private $year;
  private $part;

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

  }

  public function show()
  {
    if (($_GET['action'] == "delete") && ($this->user->getStatus() >= 3))
    {
      $sess = new sgSession($this->db, $this->user, (int)$_GET['sid']);
      $sess->delete();

    }
    elseif ($_POST['sgsessedit'] && ($this->user->getStatus() >= 3))
    {
      $sess = new sgSession($this->db, $this->user, (int)$_POST['sid']);
      $sess->add();
    }
    else
    {
      echo "Turnierstatistik: ";
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

      $res = $this->db->query("SELECT sid, UNIX_TIMESTAMP(sessdate) sessdate, wkpn, location FROM sg_sessions " .
        "WHERE YEAR(sessdate) = " . $this->year . " " .
        "ORDER BY sessdate DESC");
      while ($obj = $res->fetch_object())
      {
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year . "&amp;sid=" . $obj->sid ."\"";
        if ($obj->wkpn)
          echo " style=\"font-weight:bold\"";
        echo ">";
        echo date("d.m.Y", $obj->sessdate) . "&nbsp;&nbsp;";
        echo $obj->location . "</a><br />";
      }
      $res->close();
      echo "</div>";

      if (($_GET['sid'] > 0) && !isset($_GET['action']))
      {
        $sess = new sgSession($this->db, $this->user, (int)$_GET['sid']);
        $sess->show();
      }
      elseif (($_GET['action'] == "add") && ($this->user->getStatus() >= 3))
      {
        $sess = new sgSession($this->db, $this->user, 0);
        $sess->showEditForm();
      }
      elseif (($_GET['action'] == "edit") && ($this->user->getStatus() >= 3))
      {
        $sess = new sgSession($this->db, $this->user, (int)$_GET['sid']);
        $sess->showEditForm();
      }
      elseif ($_POST['sgsessedit'] && ($this->user->getStatus() >= 3))
      {
        $sess = new sgSession($this->db, $this->user, (int)$_POST['sid']);
        $sess->add();
      }
    }

  }

  public function showTotal() {
    echo "Turniere Jahresstatistik: ";
    $this->showYearSelector();
    echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=9999\">Total</a>";

    echo "<hr />";
    if ($this->year == 9999)
      echo "<h2>Ewige Tabelle der Turniere</h2>";
    else {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=0\">Jahrestotal</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=5\">" . $this->partdesc[5] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=6\">" . $this->partdesc[6] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=1\">" . $this->partdesc[1] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=2\">" . $this->partdesc[2] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=3\">" . $this->partdesc[3] . "</a>&nbsp;&nbsp;&nbsp;";
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year ."&amp;part=4\">" . $this->partdesc[4] . "</a>";
      echo "<hr />";

      echo "<h2>Jahresstatistik Turniere " . $this->year;
      if ($this->part > 0)
         echo " - " . $this->partdesc[$this->part];
      echo "</h2>";
    }

    echo "<script type=\"text/javascript\">";
    echo "function update_table() {";
    echo "  var myAjax = new Ajax.Request(";
    echo "    \"inc/upd_sg_table.php\",";
    echo "    { method: 'get', parameters: Form.serialize($('frm_sg_table_upd')), onComplete: show_table }";
    echo "  );";
    echo "}";
    echo "function show_table( originalRequest ) {";
    echo "  document.getElementById('sg_table').innerHTML = originalRequest.responseText;";
    echo "}";
    echo "</script>\n";

    echo "<div style=\"float:left;width:900px;height:500px;\">";
    echo "<script src=\"inc/tbfunctions.js\" type=\"text/javascript\"></script>\n";
    echo "<script src=\"inc/box.js\" type=\"text/javascript\"></script>\n";
    echo "<form id=\"frm_sg_table_upd\" style=\"margin:0px;\" action=\"\"><p style=\"margin-top:0px;\">";
    echo "Zeige Spieler:&nbsp;&nbsp;";
    echo "<input type=\"radio\" name=\"sg_table_plsel\" value=\"0\" onclick=\"update_table()\" checked=\"checked\" />Alle&nbsp;&nbsp;";
    echo "<input type=\"radio\" name=\"sg_table_plsel\" value=\"1\" onclick=\"update_table()\" />Member&nbsp;&nbsp;";
    echo "<input type=\"radio\" name=\"sg_table_plsel\" value=\"2\" onclick=\"update_table()\" />Goldmember";
    echo "<input type=\"hidden\" name=\"year\" value=\"" . $_GET['year'] . "\" />";
    echo "<input type=\"hidden\" name=\"part\" value=\"" . $_GET['part'] . "\" /></p>";

    echo "<div id=\"sg_table\">";
    $this->showTotalTable(0, 8, 0);
    echo "</div>";
    echo "</form>";

    echo "<br />";

    echo "</div>";

  }

  public function showTotalTable($pltyp, $orderfield, $ordertype) {

    echo "<div id=\"hiddenbox\" class=\"box\"></div>";

    echo "<input type=\"hidden\" name=\"sg_table_orderfield\" value=\"$orderfield\" />";
    echo "<input type=\"hidden\" name=\"sg_table_ordertype\" value=\"$ordertype\" />";

    $orderasc_def = array(
      3 => true, 5 => true, 7 => false, 8 => false, 9 => false, 10 => false,
      11 => false, 12 => false, 13 => false, 14 => false, 15 => false);

    if ($ordertype == 1)
      $orderasc_def[$orderfield] = !$orderasc_def[$orderfield];

    echo "<table>";
    echo "<tr>";
    echo "<td style=\"background-color:#eeeeee\"><b>Rang</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(3, $orderfield, $ordertype, "Name") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(5, $orderfield, $ordertype, "Team") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(7, $orderfield, $ordertype, "Turniere") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(8, $orderfield, $ordertype, "Punkte") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\">&nbsp;</td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(9, $orderfield, $ordertype, "&oslash;") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(10, $orderfield, $ordertype, "WKPN-Pkt.") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(11, $orderfield, $ordertype, "WSOP-Pkt.") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(12, $orderfield, $ordertype, "ITM") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(13, $orderfield, $ordertype, "Preisgeld") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(14, $orderfield, $ordertype, "Davon BuyIn") . "</b></td>";
    echo "<td style=\"background-color:#eeeeee\"><b>" . $this->getHeaderLink(15, $orderfield, $ordertype, "Netto") . "</b></td>";
	echo "<td style=\"background-color:#eeeeee\">&nbsp;</td>";
    echo "</tr>";

    $sql = "SELECT pl.pid, pl.pname, pl.pname_first, pl.pname_last, pl.team, pl.status, COUNT(1) sessions, sum(points) points, " .
      "sum(points)/COUNT(1) sesspoints, SUM(IF(wkpn = 1, points, 0)) wkpnpts, " .
      "SUM(IF(wkpn = 1, 0, points)) wsoppts, SUM(IF(saldo > 0, 1, 0)) itm, SUM(saldo) saldo, SUM(buyin) buyin, " .
      "SUM(saldo) - SUM(buyin) netto " .
      "FROM players pl " .
      "JOIN sg_sessdata sd ON pl.pid = sd.pid " .
      "JOIN sg_sessions se ON sd.sid = se.sid " .
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
    $sql .= "GROUP BY pl.pid, pl.pname, pl.pname_first, pl.pname_last, pl.team, pl.status " .
      "ORDER BY $orderfield " . ($orderasc_def[$orderfield] ? "" : "DESC");

    $rang = 1;
    $res = $this->db->query($sql);

    while ($obj = $res->fetch_object())
    {
      echo "<tr style=\"background-color:#ffffff\" onclick=\"colorRow(this);\">";
      echo "<td>" . $rang . ".</td>";
      echo "<td" . ($obj->status >= 2 ? " style=\"font-weight:bold\"" : "") . " ";
      echo "onmouseover=\"showBox('hiddenbox', " . $obj->pid . ", 'sg', " . $this->year . ", " . $this->part . ");\" onmouseout=\"hideBox();\">";
      echo htmlspecialchars($obj->pname_first . " \"" . $obj->pname . "\" " . $obj->pname_last) . "</td>";
      echo "<td>" . htmlspecialchars($obj->team) . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->sessions . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->points < 0 ? "color:red" : "") . "\" ";
      echo "onmouseover=\"showBox('hiddenbox', " . $obj->pid . ", 'gs', " . $this->year . ", " . $this->part . ");\" onmouseout=\"hideBox();\">";
      echo $obj->points . "</td>";
      echo "<td>&nbsp;</td>";
      echo "<td style=\"text-align:right;" . ($obj->sesspoints < 0 ? "color:red" : "") . "\">" . number_format($obj->sesspoints, 1) . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->wkpnpts < 0 ? "color:red" : "") . "\">" . $obj->wkpnpts . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->wsoppts < 0 ? "color:red" : "") . "\">" . $obj->wsoppts . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->itm . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->saldo . "</td>";
      echo "<td style=\"text-align:right;\">" . $obj->buyin . "</td>";
      echo "<td style=\"text-align:right;" . ($obj->netto < 0 ? "color:red" : "") . "\">" . $obj->netto . "</td>";
	  echo "<td style=\"text-align:right;\"><a href=\"inc/graph.php?pid=" . $obj->pid . "&typ=gs&year=" . $this->year . "&part=" . $this->part . "\" target=\"_blank\">";
	  echo "<img src=\"graph.png\" alt=\"graph\" /></a></td>";
      echo "</tr>";
      $rang++;
    }
    $res->close();
    echo "</table>";
  }

  private function getHeaderLink($orderfield, $nowordered, $ordertype, $title) {
    $link = "<a href=\"javascript:void(0)\" onclick=\"" .
      "document.getElementById('frm_sg_table_upd').sg_table_orderfield.value = '$orderfield'; ";
    if ($orderfield == $nowordered) {
      $link .= "document.getElementById('frm_sg_table_upd').sg_table_ordertype.value = '";
      if ($ordertype == 1)
        $link .= "0";
      else
        $link .= "1";
      $link .= "'; ";
    } else
      $link .= "document.getElementById('frm_sg_table_upd').sg_table_ordertype.value = '0'; ";
    $link .= "update_table();\" class=\"";
    if ($orderfield == $nowordered)
      $link .= "tbheadsel";
    else
      $link .= "tbheader";
    $link .= "\">" . $title . "</a>";
    return $link;
  }

  private function showYearSelector() {
    $res = $this->db->query("SELECT DISTINCT YEAR(sessdate) AS year FROM sg_sessions ORDER BY YEAR(sessdate) DESC");
    while ($obj = $res->fetch_object())
    {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $obj->year ."\">";
      echo $obj->year . "</a>&nbsp;&nbsp;&nbsp;";
    }
    $res->close();
  }

}

?>