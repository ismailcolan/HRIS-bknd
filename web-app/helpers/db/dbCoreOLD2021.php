<?php
/** Database (MySQL) core functions  */
class dbCore {
    
	
	public static $id;
    protected static $mysqli;
    
    public function __construct () {
		if(isset($_SESSION['DBNAME'])) self::SwitchToRealByName();
		else self::SwitchToMain();
    }
	
	public static function SwitchToRealByName(){
		$DBNAME=$_SESSION['DBNAME'];
		self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, $DBNAME);
		self::SQL("SET NAMES 'UTF8';");
		self:: setSiteSettingsAsGlobalVars();
	}
	
	public static function SwitchToRealByProvidedName($DBNAME){
		self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, $DBNAME);
		if (self::$mysqli->connect_error) {return false;}
		//if (self::SQL("SET NAMES 'UTF8'")) return true; 
		//else 
		self:: setSiteSettingsAsGlobalVars();
		return true;
	}
	
	public static function SwitchToRealByCompany(){
		if (!isset($_SESSION['company'])) return false;
		$customer = strtoupper(trim(preg_replace('/\s+/S', " ",$_SESSION['company'])));
		
		$q = "SELECT * FROM customers WHERE UPPER(cCustomerName) = '{$customer}' OR UPPER(cCustomerShortName) = '{$customer}'
			  AND cActive = 'yes'";
			  
		self::SwitchToMain();
		$res=db::PrepareLoadSingleRow($q);
		
		if ($res !== false){
			$DBNAME=DB_PREFIX.$res['cDBASE'];
			//echo '>>>>>>>'.SERVER_NAME.' '.USER.' '.PASS.' '.$DBNAME.'<hr>';
			self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, $DBNAME);
			self::SQL("SET NAMES 'UTF8';");
			self:: setSiteSettingsAsGlobalVars();
			return true;
		}
		else return false;

	}
	
	public static function ForceSwitchToRealByName($DBNAME){
		
		self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, DB_PREFIX.$DBNAME);
		self::SQL("SET NAMES 'UTF8';");
		self:: setSiteSettingsAsGlobalVars();
	}
	
	public static function ForceSwitchToRealByNameFast($DBNAME){
		
		self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, $DBNAME);
		self::SQL("SET NAMES 'UTF8';");
		self:: setSiteSettingsAsGlobalVars();
	}
	
	
	
	public static function SwitchToReal(){
		$customer1 = filter_input(INPUT_POST, "customer", FILTER_SANITIZE_STRING);
		$customer = strtoupper(trim(preg_replace('/\s+/S', " ", $customer1)));
		$customer = str_replace('_',' ',$customer);
		self::SwitchToMain();
		$q = "SELECT * FROM customers WHERE UPPER(cCustomerName) = '{$customer}' OR UPPER(cCustomerShortName) = '{$customer}'
			AND cActive ='yes'";
		
		$res=db::PrepareLoadSingleRow($q);

		if ( $res !== false ){
			
			
			self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, DB_PREFIX.$res['cDBASE']);
			self::SQL("SET NAMES 'UTF8';");
			$_SESSION['DBNAME'] = DB_PREFIX.$res['cDBASE'];
			$_SESSION['customerID'] = $res['cID'];
			$_SESSION['companyLong'] = $res['cCustomerName'];
			
			self:: setSiteSettingsAsGlobalVars();
			return true;
		}
		return false;
	}
	
	public static function SwitchToRealUsingName($customer1){
		$customer = strtoupper(trim(preg_replace('/\s+/S', " ", $customer1)));
		$customer = str_replace('_',' ',$customer);
		self::SwitchToMain();
		$q = "SELECT * FROM customers WHERE UPPER(cCustomerName) = '{$customer}' OR UPPER(cCustomerShortName) = '{$customer}'
			AND cActive ='yes'";
		
		$res=db::PrepareLoadSingleRow($q);

		if ( $res !== false ){
						
			self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, DB_PREFIX.$res['cDBASE']);
			self::SQL("SET NAMES 'UTF8';");
			$_SESSION['DBNAME'] = DB_PREFIX.$res['cDBASE'];
			$_SESSION['companyLong'] = $res['cCustomerName'];
			$_SESSION['customerID'] = $res['cID'];
			self:: setSiteSettingsAsGlobalVars();
			return true;
		}
		else {
			unset($_SESSION['DBNAME']);
			unset($_SESSION['customerID']);
			return false;
		}
	}
	
	public static function SwitchToRealUsingDBName($dbName){
			self::SwitchToMain();
			$q = "SELECT * FROM customers WHERE cDBASE = '{$dbName}' AND cActive ='yes'";
			
			$res=db::PrepareLoadSingleRow($q);
		
			self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, DB_PREFIX.$dbName);
			self::SQL("SET NAMES 'UTF8';");
			$_SESSION['DBNAME'] = DB_PREFIX.$res['cDBASE'];
			$_SESSION['companyLong'] = $res['cCustomerName'];
			$_SESSION['customerID'] = $res['cID'];
			self:: setSiteSettingsAsGlobalVars();
			return true;
	}
	
	public static function SwitchToMain(){
		//self::Close();
		//echo SERVER_NAME.' '. USER.' '.PASS.' '.DB_PREFIX.'main';exit;
		self::$mysqli = new \mysqli(SERVER_NAME, USER, PASS, DB_PREFIX.'main');
		if (self::$mysqli->connect_errno) {
			printf("Connect failed: %s\n", self::$mysqli->connect_error);
			exit();
		}
		self::SQL("SET NAMES 'UTF8';");
	}
    
    /** Description: Executes a MySql query
     * @param type $q
     * @return (bool) false | result */
    static function SQL($q) {
        if($q == '') { return false; }
        if(empty(self::$mysqli)) { new self; }
        $result = self::$mysqli->query($q);
        if (!$result) {
            self::writeError(self::$mysqli->error, $q);
            echo self::$mysqli->error;
        } else {
            //echo $q;
        }

        return $result;
    }
    
    /** Description: Prepare query adding conditions, orderBy and Limits. Returns false on incorrect parameters.
     * @param str $q
     * @param str $conditions
     * @param arr $orderby
     * @param int|arr $limit -> 5 or array($start,$count)
     * $param bool $useHavingCondition (default: false)
     * @return (bool) false | query-with-conditions-order-and-limit */
    static function PrepareLoadQuery($q, $conditions = null,  $orderby = array(), $limit = 0, $useHavingCondition = false) {
        if(!empty($conditions)){
            $whereCondition = $useHavingCondition !== false ? " HAVING " : " WHERE ";
            $conditionClue = strpos($q, $whereCondition) === false ? $whereCondition : " AND ";
            $q .= $conditionClue . $conditions;
        }
        $order=array();
		 //echo "\ndbCore 48:".$q;
        if(!empty($orderby)) {
            foreach ($orderby as $k => $o) {
                if (trim($o)!='') $order[] = $k . " " . strtoupper($o);
            }
            if (!empty($order)) $q .= " ORDER BY " . implode(" , ",$order);
        }
        if(!empty($limit)) {
            $q .=  " LIMIT " . (is_array($limit) ? implode(", ",$limit) : (int)$limit);
        }
		//echo ">>>dbCore 57:".$q;
        return $q;
    }
    
    /** Description: Returns array of rows every one as associative array()
     * @param type $q
     * @param type $fromIndex
     * @return (bool) false | array */
    static function PrepareLoad($q, $fromIndex = 0) {
        if($q == '') { return false; }
        if(empty(self::$mysqli)) { new self; }
        $return_arr = array();

        $result = self::SQL($q);
        if ($result) {
            if($result->num_rows==0){ return false; }
            while ($row = $result->fetch_assoc()) {
                $return_arr[$fromIndex] = $row;
                $fromIndex++;
            }
            $result->free();
        }
    	return $return_arr;
    }
    
    /** Description: Returns a single row values as associative array()
     * @param type $q
     * @return (bool) false | array */
    static function PrepareLoadSingleRow($q) {
        $res = self::PrepareLoad($q);
        return is_array($res) && !empty($res) ? $res[0] : false;
    }
    
    /** Description: Returns a single row values as associative array()
     * @param type $q
     * @return (bool) false | array */
    static function PrepareLoadSingleCellValue($q) {
        $res = self::PrepareLoadSingleRow($q);
        if($res) {
            $res = reset($res);
        }
        return $res;
    }
    
    /**Description: Closes the MySql connection */
    static function Close() {
        return self::$mysqli->close();
    }

    static function FetchArray($res, $sType = MYSQLI_BOTH ){
        return $res->fetch_array($sType);
    }

    static function FetchAssoc($res) {
        return $res->fetch_array(MYSQLI_ASSOC);		
    }

    static function FreeResult($res) {						
        return $res->free_result();		
    }

    static function FetchRow($res) {
        return $res->fetch_row();
    }

    static function FetchObject($res) {
        return $res->fetch_object();
    }

    /** Moves the internal row pointer to the specified row number
     *  @param res $res
     *  @param int $rowNumber
     *  @return type */
    public function DataSeek($res, $rowNumber) {
        return $res::data_seek($rowNumber);
    }

    static function NumRows($result) {
        return $result->num_rows;		
    }

    static function LastID() {
        return self::$mysqli->insert_id;		
    }

    public function AffectedRows() {
        return self::$mysqli->affected_rows;		
    }

    static function StartTransaction() {
        return self::SQL("START TRANSACTION;");
    }

    static function Commit() {
        return self::SQL("COMMIT;");
    }

    static function Rollback() {
        return self::SQL("ROLLBACK;");
    }

    public function NumFields() {
        return self::$mysqli->field_count;		
    }

    public function ErrorNo() {
        return self::$mysqli->errno;		
    }

    public function Error() {
        return self::$mysqli->error;
    }

    static function FastEscStr($str) {
        if(empty(self::$mysqli)) { new self; }
        return self::$mysqli->real_escape_string($str);		
    }	

    /** Escapes the unescaped_string, so that it is safe to place it in a query.
     * @param string $str
     * @param boolean $addQuotes (false)
     * @param boolean $stripTags (true) */
    static function EscapeStr($str, $addQuotes = false, $stripTags = true) {
        if($stripTags === true){
            $str = strip_tags($str);
        }
        if (get_magic_quotes_gpc()) {
                if (ini_get('magic_quotes_sybase')) {
                        $str = str_replace("''", "'", $str);
                } else {
                        $str = stripslashes($str);
                }
        }
        $str = iconv(mb_detect_encoding($str), 'UTF-8', $str);
        $str = self::FastEscStr($str);
        return $addQuotes === true ? "\"" . $str . "\"" : $str;
    }
    
    /** Add MySql error in the app.log
     *  @param type string $error => error message
     *  @param type string $query => query where error was found */
    static function writeError($error=false, $query = '')     {
        $bt=debug_backtrace();
	
        $err = "MySql:" . date_default_timezone_get() . " " 
		 .val::currentDate("Y-m-d H:i:s"). " " . $bt[2]["file"]."(". $bt[2]["line"] . ") | " . $error . (!empty($query) ? " in query:\n" . $query : "");
        log::write($err, "mysql-error.log.php");
        if (constant("LOCAL") === true || constant("DEBUG") === true) {
            echo sprintf("<pre>%s</pre>", $err);
        }
    }
	
	
	public static function setSiteSettingsAsGlobalVars(){
		$res = db::LoadDataFromTable('webapp_admin_settings');
		foreach($res as $r){
			$GLOBALS[$r['asKey']] = $r['asValue'];
		}
	}
}
