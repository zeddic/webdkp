<?php
/*===========================================================
CLASS DESCRIPTION
=============================================================
Class Description should be placed here.
*/

class dkpProAccountLogEntry {
  /*===========================================================
  MEMBER VARIABLES
  ============================================================*/
  var $id;
  var $type;
  var $message;
  var $dateDate;
  var $dateTime;
  var $guild;
  var $txn;
  /*===========================================================
  DEFAULT CONSTRUCTOR
  ============================================================*/
  function __construct()
  {

  }
  /*===========================================================
  loadFromDatabase($id)
  Loads the information for this class from the backend database
  using the passed string.
  ============================================================*/
  function loadFromDatabase($id)
  {
      global $sql;
      $row = $sql->QueryRow("SELECT * FROM dkp_proaccountlog WHERE id='$id'");
      $this->loadFromRow($row);
  }
  /*===========================================================
  loadFromRow($row)
  Loads the information for this class from the passed database row.
  ============================================================*/
  function loadFromRow($row)
  {
      $this->id=$row["id"] ?? null;
      $this->type = $row["type"] ?? null;
      $this->message = $row["message"] ?? null;
      $this->txn = $row["txn"] ?? null;
      if($row["date"]!="")
      {
          $this->dateDate = date("F j, Y", strtotime($row["date"]));
          $this->dateTime = date("g:i A", strtotime($row["date"]));
      }
      $this->guild = $row["guild"] ?? null;
  }
  /*===========================================================
  save()
  Saves data into the backend database using the supplied id
  ============================================================*/
  function save()
  {
      global $sql;
      $type = stripSlashes($this->type);
      $type = addSlashes($type);
      $message = stripSlashes($this->message);
      $message = addSlashes($message);
      $sql->Query("UPDATE dkp_proaccountlog SET
                  type = '$type',
                  message = '$message',
                  guild = '$this->guild',
                  txn = '$this->txn'
                  WHERE id='$this->id'");
  }
  /*===========================================================
  saveNew()
  Saves data into the backend database as a new row entry. After
  calling this method $id will be filled with a new value
  matching the new row for the data
  ============================================================*/
  function saveNew()
  {
      global $sql;
      $type = stripSlashes($this->type);
      $type = addSlashes($type);
      $message = stripSlashes($this->message);
      $message = addSlashes($message);
      $sql->Query(" INSERT INTO dkp_proaccountlog SET
                  type = '$type',
                  message = '$message',
                  guild = '$this->guild',
                  date = now(),
                  txn = '$this->txn'
                  ");
      $this->id=$sql->QueryItem("SELECT id FROM dkp_proaccountlog ORDER BY ID DESC");
  }
  /*===========================================================
  delete()
  Deletes the row with the current id of this instance from the
  database
  ============================================================*/
  function delete()
  {
      global $sql;
      $sql->Query("DELETE FROM dkp_proaccountlog WHERE id = '$this->id'");
  }
}
?>