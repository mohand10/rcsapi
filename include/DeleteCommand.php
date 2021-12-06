<?php

class DeleteCommand extends Command {
    private $undoQuery = Array();


    // For the delete command, the Undo has to grab the data before it deletes it,
    // so we store the data in an array of rows (in case we're deleting multiple things).
    // and call addToHistory() after the query runs.
    //
    // function generateUndo() {

    //     // Get all the fields from the row(s) we're about to delete.
    //     $q = "SELECT * FROM " . $this->table . " WHERE " ;
    //     foreach($this->params as $k => $v) {
    //         $q .= "$k = $v";
    //         $q .= " AND ";
    //     }
    //     $q .= " 1 = 1";
    //     MySQL::runQuery($q);

    //     // Add an undo row for each entry we found.
    //     while ($row = MySQL::getNextRow()) {
    //         $uc = new AddCommand($this->table, $this->userid);
    //         foreach($row as $k => $v) {
    //             $uc->setParam($k, $v);
    //         }
    //         array_push($this->undoQuery, $uc->generateQuery());
    //     }
    // }

    function execute() {
        //$this->generateUndo();
        $res = str_replace( array( '\'' ), '', $this->generateQuery());
        MySQL::runQuery($res);
        if(MySQL::getAffectedRows()>0){
            return true;
        }
        else{
            return false;
        }
        //$this->info = MySQL::getInfo();
        //$this->error = MySQL::getError();

        // if ($this->hasUndo) {
        //     foreach($this->undoQuery as $uc) {
        //         Globals::addToHistory($this->userid, $uc);
        //     }
        // }
        //return $res;
    }

    function generateQuery() {
        if (count($this->params) > 1) {
            throw new Exception ("Invalid number of parameters given to DeleteCommand, expected 1, got " . count($this->params));
        }
        else {
            $q = "DELETE FROM " . $this->table;
            foreach($this->params as $k => $v) {
                $q .= " WHERE $k = $v";
            }

            $this->query = $q;
            return $this->query;
        }
    }

}

