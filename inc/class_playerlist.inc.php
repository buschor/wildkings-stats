<?php

class playerList {

  private $db;
  private $user;
  private $year;  
  
  public function __construct($aDb, $aUser, $aYear)
  {
    $this->db = $aDb;
    $this->user = $aUser;
    if ($aYear > 2000) 
      $this->year = (int)$aYear;
    else
      $this->year = (int)date("Y");
  
  }
  
  public function show()
  {          
  
    if ($_POST['playeredit'] && (($this->user->getStatus() >= 3) || ($_POST['pid'] == $this->user->getId()) ))
    {
      $pl = new player($this->db, $this->user, (int)$_POST['pid']);
      $pl->add();
    }
    elseif (($_GET['action'] == "delete") && ($this->user->getStatus() >= 3))
    {
      $sess = new player($this->db, $this->user, (int)$_GET['pid']);
      $sess->delete();
    
    }
    else
    {
      echo "<h2>Players &Uuml;bersicht</h2>";
      echo "<div style=\"float:left;width:120px;height:500px;\">";

      if ($this->user->getStatus() >= 3)
      {
        //echo "&nbsp;&nbsp;&nbsp;";
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year . "&amp;action=add\">";
        echo "Add Player</a><br /><br />";
      }      
      
      $res = $this->db->query("SELECT pid, pname FROM players " .      
        "ORDER BY pname");
      while ($obj = $res->fetch_object())
      {
        echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?site=" . $_GET['site'] . "&amp;year=" . $this->year . "&amp;pid=" . $obj->pid ."\">";
        echo $obj->pname . "</a><br />";
      } 
      $res->close();
      echo "</div>";
      
      if (($_GET['pid'] > 0) && !isset($_GET['action']))
      {
        $pl = new player($this->db, $this->user, (int)$_GET['pid']);
        $pl->show();
      }
      elseif (($_GET['action'] == "add") && ($this->user->getStatus() >= 3))
      {
        $pl = new player($this->db, $this->user, 0);
        $pl->showEditForm();
      }
      elseif (($_GET['action'] == "edit") && (($this->user->getStatus() >= 3) || ($_GET['pid'] == $this->user->getId()) ))
      {
        $pl = new player($this->db, $this->user, (int)$_GET['pid']);
        $pl->showEditForm();
      }    
    }
  }
  
    
}

?>