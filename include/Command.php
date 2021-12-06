<?php

abstract class Command {
    var $table;
    //var $userid;
    var $params = array();
    var $id;
    var $hasUndo;

    private $query;
    private $error;
    private $info;

    // function __construct($tablename, $userid) {
    //     $this->table = $tablename;
    //     $this->userid = $userid;
    //     $this->hasUndo = ($this->userid) ? true : false;
    // }
    function __construct($tablename) {
            $this->table = $tablename;
        }

    function getInfo() {
        return $this->info;
    }

    function setUndo($v = false) {
        $this->hasUndo = $v;
    }

    function getError() {
        return $this->error;
    }

    function setID($id) {
        $this->id = $id;
    }

    function getQuery() {
        return $this->query;
    }

    function getID() {
        return $this->id;
    }

    function setParam($name, $val) {
        $v = $val;

        // Translates the value from a PHP val to 
        // something MySQL understands.
        //
        if (is_bool($v)) {
            $v = ($v) ? "TRUE" : "FALSE";
        }
        elseif (($v === "") || ($v === "NULL") || (is_null($v))) {
            $v = "NULL";
        }
        elseif (is_numeric($v)) {
            $v = $v;
        }
        elseif (is_string($v)) {
            $v = "'$v'";
        }

        $this->params[$name] = $v;
    }

    //abstract protected function generateUndo();
    abstract protected function generateQuery();
    abstract protected function execute();
}



?>
