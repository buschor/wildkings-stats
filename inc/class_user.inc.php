<?php

class user {

  private $db;

  private $id;
  private $name;
  private $pass;
  private $status;

  public function __construct($aDb, $user, $pwd) {

    $this->db = $aDb;
    $this->name = $user;
    $this->pass = $pwd;

    $res = $this->db->query("SELECT pid, status FROM players " .
      "WHERE pname = " . (NULL === $this->name ? "NULL" : '"'. $this->db->real_escape_string($this->name).'"') . " " .
      "AND ppass = " . (NULL === $this->pass ? "NULL" : '"'. $this->db->real_escape_string($this->pass).'"'));

    if ($res->num_rows == 1) {
      $obj = $res->fetch_object();
      $this->id = $obj->pid;
      $this->status = $obj->status;
    } else {
      $this->id = 0;
      $this->status = 0;
    }

  }

  public function getId() {
    return $this->id;
  }

  public function getStatus() {
    return $this->status;
  }

  public function setLastLogin() {
    $this->db->query("UPDATE players SET last_login = NOW() WHERE pid = " . $this->id);
  }

}

?>