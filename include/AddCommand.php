<?php


class AddCommand extends Command {
    // function generateUndo() {
    //     $uc = new DeleteCommand($this->table, $this->userid);
    //     $uc->setParam($this->table . "ID", $this->id);
    //     //Globals::addToHistory($this->userid, $uc->generateQuery());
    // }

    function execute() {
        // Run the SQL Command
        MySQL::runQuery($this->generateQuery());

        // Get the ID of the insert command that just
        // executed.  If we are in a table that **doesn't**
        // AUTO_INCREMENT, we need to grab the ID from the 
        // <table>ID parameter.
        //
        $this->id = MySQL::getLastID();
        if ($this->id == 0) {
            $this->id = $this->params[$this->table . "ID"];
        }

        $this->info = MySQL::getInfo();
        $this->error = MySQL::getError();
        //$this->generateUndo();

        if ($this->error) {
            throw new exception ($this->error);
        }
        else {
            return $this->id;
        }
    }

    function generateQuery() {
        $q = "INSERT INTO " . $this->table;

        $q .= " (";
        foreach(array_keys($this->params) as $p) {
            $q .= "$p,";
        }
        $q = rtrim($q, ',');
        $q .= ") VALUES (";
        foreach(array_values($this->params) as $v) {
            $q .= "$v,";
        }
        $q = rtrim($q, ',');
        $q .= ")";
        $this->query = $q;
        return $this->query;
    }

}
