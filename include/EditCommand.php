<?php


class EditCommand extends Command {
    function generateUndo() {
        $uc = new EditCommand($this->table, $this->userid);
        $uc->setID($this->id);

        $q = "SELECT * FROM " . $this->table . " WHERE " . $this->table . "ID = " . $this->id;
        MySQL::runQuery($q);

        if (MySQL::getError() == "") {
            $row = MySQL::getNextRow();
            foreach($row as $k => $v) {
                $uc->setParam($k, $v);
            }
            Globals::addToHistory($this->userid, $uc->generateQuery());
        }
        else {
            throw new Exception (MySQL::getError() . "<br />" . $q);
        }
    }

    function execute() {
        if ($this->df) {
            $this->generateUndo();
        }
        $this->query = $this->generateQuery();
        MySQL::runQuery($this->query);
        $this->info = MySQL::getInfo();
        $this->error = MySQL::getError();


        if ($this->error) {
            throw new exception ($this->error . "<br />" . $this->query);
        }
        else {
            return $this->id;
        }
    }

    function generateQuery() {
        $q = "UPDATE " . $this->table;
        $q .= " SET ";

        foreach($this->params as $k => $v) {
            $q .= "$k = $v,";
        }

        $q = rtrim($q, ",");
        $q .= " WHERE " . $this->table . "ID = " . $this->id;

        return $q;
    }

}
