<?php
/** Database functions */
class db extends dbCore {

    /**Gets data array from the main table (self::$dt["tbl"]) by the s$conditions
     * @param string $conditions
     * @param array $orderby
     * @param integer $limit
     * @return array|null */
    static function data($conditions = '', array $orderby = array(), $limit = 0){
		
        $sql = static::query($conditions, $orderby, (int)$limit,OnboardingModel::$type);
	     $res = static::PrepareLoad($sql);
		return ($res) ? $res : null;
    }
	
	static function update($tbl='',$what=array(),$condition=''){
		$sql='UPDATE '.$tbl.' SET ';
		foreach ($what as $k=>$v){
			$sql.=' '.$k.'="'.$v.'" ';
		}
		$sql.='  WHERE'.$condition;
		db::SQL($sql);
	}
    
    
    /**Gets an row from the main table (self::$dt["tbl"]) by the key (self::$dt["key"])
     * @param int $id - the model row id
     * @param bool $allLangs - (optional) flag: (skip)/add all translations
     * @return arr|null */
    static function dataById($id=0, $allLangs=false){
		$condition = static::$dt["key"]."=".(int)$id;				
		$res = static::data($condition);
        if(!is_null($data = (count($res) !== 1 ? null : $res[0])) && (bool)$allLangs){
            $data = webValuesModel::dataAddValuesTranslations($data, static::$dt);
        }
		return $data;
    }
    
    /** Saving processing
     * @param str|arr $qList array(table=> str Query | array())
     * @param int $baseIdValue - the base id (to be returned and carried to next queries)
     * @param str $tableOfBaseId - the table from where the base id to be acquired
     * @param bool $dump - If true list queries (showing errors if any) and die just before the db::Commit()
     * @return bool  */
    public static function Save($qList, &$baseIdValue=null, $tableOfBaseId=null, $dump=false, $multyCall=false){

        $queriesArr  = is_string($qList) ? array($qList) : $qList;      //if string ? convert it to array($str)
        $baseIdValue = (int)$baseIdValue>0 ? (int)$baseIdValue : null;  //cast to Null|Integer
        
        $optionsMap = self::SaveBuildQueriesAndOptionsMap($queriesArr, $baseIdValue, $tableOfBaseId);

        return self::saveExecute($queriesArr, $baseIdValue, $optionsMap, $dump, $multyCall);
    }
    
    /** BuildQueriesAndOptionsMap - process array of tables lists of data fileds, creating array of tables with table-queries
     * @param array $qArr
     * @param int|null $baseIdValue
     * @param str|null $getBaseIdTbl
     * @return array */
    private static function SaveBuildQueriesAndOptionsMap(&$qArr, $baseIdValue, $getBaseIdTbl){
        $oMap = array("bs-id-tbl"=>$getBaseIdTbl);
        $oMap["keys"]=array("tables"=>array(), "find"=>array(), "replace"=>array());
        
        foreach ($qArr as $tbl=>$val) {
            if(is_array($val)) {
                if(isset($val["data"])){
                    //Expects every table value to be array("key"=>TableBaseKeyLinkColumn, "data"=>TheListoOfDataStrings)
                    $k = $val["key"];
                    $data = isset($val["data"]) ? implode(', ', $val["data"]) : null;
                    $baseKeyLink = ($tbl!=$getBaseIdTbl ? $k."={---MainID---}, ": "");
                    $qArr[$tbl]  = is_null($baseIdValue)
                                    ? "INSERT INTO {$tbl} SET " . $baseKeyLink . $data
                                    : "UPDATE {$tbl} SET {$data} WHERE $k=".$baseIdValue;
									
									
                } /*else if(isset($val[webValuesModel::KEYS_VAR_NAME])){
                    webValuesModel::processTranslationQuery($oMap, $qArr, $baseIdValue, $tbl, $val);
                }*/
            }
		}
        return $oMap;
    }
    
    /** Executes queries array
     * @param arr $qArr
     * @param int|null $baseIdValue
     * @param arr $oMap
     * @param bool $dump
     * @return bool */
    private static function saveExecute($qArr, &$baseIdValue, $oMap, $dump, $multyCall=false){
        $errorFlag = false;
        if($multyCall===false){
            db::StartTransaction();
        }
		
		//echo '>>>>';print_r($qArr);;exit;
            foreach($qArr as $tbl=>$queries) {
                $qtyArr = is_string($queries) ? array($queries) : $queries;
                $qty = !empty($oMap["keys"]["find"]) ?  str_replace($oMap["keys"]["find"], $oMap["keys"]["replace"], $qtyArr) : $qtyArr;

                foreach($qty as &$query){
                    print($dump===true ? $query."<br />" : "");
//echo 'db line 103: '.$query;exit;
                    if(!db::SQL($query)){
                        if(constant("LOCAL")===true || constant("DEBUG")===true) { die("<p style\"display:block; color:#f00;\"><strong>ERROR:</strong><em> ".$query."</em></p>"); }
                        db::Rollback();
                        $errorFlag = true;
                        break(2);
                    }
                    if($tbl===$oMap["bs-id-tbl"]) {
                        if(is_null($baseIdValue)) { 
                            $baseIdValue = $oMap["keys"]["replace"]["id"]=db::LastID(); $oMap["keys"]["find"]["id"]="{---MainID---}";
                            //webValuesModel::updateMainIdInValuesTable($qty, $oMap["keys"]["replace"]["id"]);
                        }
                    } else if(in_array($tbl, array_keys($oMap["keys"]["tables"]))){
                        $oMap["keys"]["replace"][] = db::LastID();
                    }
                }
            }

            if($dump===true && $multyCall===false){ die("<strong>".__METHOD__."</strong>: ".($errorFlag===false?"<strong style=\"color:green\">Successfull save</strong> (No errors)":"<strong style=\"color:red\">Error!</strong>")); }
        if($errorFlag === false && $multyCall===false){ db::Commit(); }
        return (boolean) $errorFlag === false;
    }

    
    public static function LoadDataFromTable($tableName, $conditions = '', $orderby = array()) {		
        $query = "SELECT * FROM {$tableName}";
        $query = self::PrepareLoadQuery($query, $conditions, $orderby);
        $res_arr = self::PrepareLoad($query);
        return $res_arr;
    }
	
    public static function LoadKeyValueArrFromTable($q, $colKeyName = '', $colValueName = ''){
        $query = (strpos($q," ")?"":"SELECT {$colKeyName}, {$colValueName} FROM ").$q;
//echo '<hr>'.$q.'<br>'.$query;
        $result = self::SQL($query);						
        if(self::NumRows($result) == 0) { return false; }
        $res_arr = array();
        while($row = self::FetchArray($result)) {
            if($colKeyName != '' && $colValueName != '') {
                $res_arr[($row[$colKeyName])] = ($row[$colValueName]);
            } else {
                $res_arr[($row[0])] = $row[1];
            }			
        }		
        self::FreeResult($result);
        return $res_arr;

    }
	
    public static function CreateKeyValueArr($arr, $key, $val) {
        if(empty($arr)) { return false; }
        $res_arr = array();
        foreach ($arr as $value) {
            $res_arr[($value[$key])] = $value[$val];
        }
        return $res_arr;
    }

    public static function getEnumFieldValues($tbl_name = '', $column_name = ''){
        $res = self::SQL("SHOW COLUMNS FROM $tbl_name LIKE '$column_name';");
        if(self::NumRows($res) == 0){ return false; }
        $row = self::FetchArray($res);
        if(preg_match('/enum\((.*)\)$/', $row['Type'], $matches)){
            $aValues = str_getcsv($matches[1], ',', "'");
            return array_combine($aValues, $aValues);
        } else { return false; }
    }

    /** Removes quotes and/or tags from a string */
    public static function clearStr($str = "", $strpQuotes=true, $strpTags=true) {
        if($strpTags){$str = strip_tags($str);}
        if($strpQuotes){$str = str_replace(array("\"","'"),"",$str);}
        return $str;
    }
	public static function my_escape_str($str){
		return self::$mysqli->real_escape_string($str);
	}
}
