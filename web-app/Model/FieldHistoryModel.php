<?php
/** The CompanyOffice model */
class FieldHistoryModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"fields_history", "key"=>"fhID", "pfx"=>"fh");
    static $mainTblFields = array("fhField", "fhValue");
    static $fldLbl = array();
    static $postOverrided = array();
    
    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT * FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);
        $addons = array();
        return str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
    }

    static function dataById($id=0, $allLangs=false){
        $data = parent::dataById($id, $allLangs);
        if(empty($data)) {
            return false;
        }
        $data[self::$dt["pfx"]."Added"] = val::convDate(MYSQL_DATE,USER_DATE,$data[self::$dt["pfx"]."Added"]);
		 //date(USER_DATE, strtotime($data[self::$dt["pfx"]."Added"]));<<
        $data[self::$dt["pfx"]."Value"] = htmlspecialchars($data[self::$dt["pfx"]."Value"]);
        return $data;
    }

    public static function AddHistory($field, $afID, $value, $addDate = null)
    {
        if($addDate === null) {
            $addDate = new DateTime();
        }
        $q = 'INSERT INTO fields_history (fhAFID, fhField, fhValue, fhAdded) VALUES (%s, "%s", "%s", "%s")';
        $q = sprintf($q, $afID, $field, db::FastEscStr($value), $addDate->format("Y-m-d H:i:s"));
		
        return DB::SQL($q);
    }

    static function getAllHistory($field = "salary", $afID = -1) {
        $q = "SELECT * FROM fields_history
              WHERE fhAFID = %s AND fhField = '%s' ORDER BY fhAdded DESC";
        $q = sprintf($q, $afID, $field);
        $data = db::PrepareLoad($q);

        if($data === false) { $data = []; }
        else {
            foreach ($data as &$item) {
                $item[self::$dt["pfx"]."Value"] = htmlspecialchars($item[self::$dt["pfx"]."Value"]);
                $item[self::$dt["pfx"]."Added"] = val::convDate(MYSQL_DATE_TIME,USER_DATE,$item[self::$dt["pfx"]."Added"]);
				  //date(USER_DATE, strtotime($item[self::$dt["pfx"]."Added"]));
            }
        }
        return $data;
    }
}
