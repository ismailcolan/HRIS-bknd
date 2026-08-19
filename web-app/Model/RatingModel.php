<?php
/** The Orders model */
class RatingModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"ratings", "key"=>"rID", "pfx"=>"r", "controller"=>"RatingController");
    static $mainTblFields = array("rName");
    static $fldLbl = array("rName"=>"Rating");
    static $addonFieldsByTables;
    static $postOverrided = array();

    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT * FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);
        return $q;
    }


    public static function StatusCond($orderType){
        return "";
    }


    public static function AllActiveKeyName() {
        $data = db::LoadKeyValueArrFromTable(self::query(self::StatusCond("list")), self::$dt["key"], self::$dt["pfx"]."Name");
        if($data === false) { $data = []; }
        return $data;
    }

    static function ItemDeleteAllowed(){
        return false;
    }


}
