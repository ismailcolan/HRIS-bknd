<?php
/** The table:: class
 *  PreReq: table::prepare() - before headers_sent();
 *    1. Create the table details:
 *       table::create("tableId", "sortByDbColumn", "sortDirection");
 *    2. Set the columns to be displayed:
 *       table::$cols[] = Array("innerHtml", "dbColumn|Name", array("width"=>"XXpx","sort"=>false));
 *    2. Execute the table (Prints table header and executes data query)
 *       table::execute(sqlQuery);
 *    3. (Optional) Alter the data before load
 *       foreach(table::$data as $r => &$cells){
 *          $cells["lnks"] = cf::getOutput("form", "link", array("account?id=".$cells["aID"] ,"&nbsp;",array("class"=>"tbl-ico edit")));
 *          $cells["aID"] = $cells["aID"]==3 ? array($cells["aID"] ,array("style"=>"color:red")) : $cells["aID"];
 *       }
 *    4. Load the table data:
 *       table::load(); 22222222222222222222*/
class table extends tableCore {

    /** Prepares a table
     * @param str $itemsName - (Default: items)
     * @param str $orderBy - a db column name
     * @param str $order - order direction asc/desc  (Default: self::DEFAULT_SORT_ORDER)
     * @param int $rowsPerPage  - paging size (Default: 20)*/
    static function create($itemsName = "items", $orderBy = "", $order = "asc", $rowsPerPage = 10,$custom_filter = null ){
	
        self::reset($itemsName);
        self::prepare(true);
		
        self::$rowsPerPage = abs($rowsPerPage);
        self::$orderBy = $orderBy; //!empty($orderBy) ? $orderBy  : die("Missing ".__METHOD__." order by definition.");
        self::$order = $order;
		self::$custom_filter = $custom_filter;
		 
		//!empty($order) && in_array(strtolower($order), array("asc","desc")) ? strtolower($order) : die("Invalid ".__METHOD__." sort order: " . self::$order);
    }

    /** Executes the table data query/s
     * @param string $sql - query for the needed for the table data;
     * @param (null)|string $sqlTotals - simple version of $sql (only if needed)
     * $sqlTotals is used only for Total count (shown in the table footer).
     * Example:
     * - $sql = "SELECT `ID`,
     *              (SELECT `cName` FROM `countries` WHERE ...),
     *              (SELECT `lNameName` FROM `languages` WHERE ...)
     *             FROM `table`
     *            WHERE `ID` IN (...)"
     * For query to many thousands results, will be faster to have:
     * - $sqlTotals = "SELECT * FROM `table` WHERE `ID` IN (...)" */
    static function execute($sql = "", $sqlTotals = null,$with_filter=true){
        if(helper::$pageExtension === "json"){ 
            self::prepareData($sql, $sqlTotals);
        } else {
            self::printTable("open",$with_filter);
            self::prepareData($sql, $sqlTotals);
        }
    }

    private static function prepareData($sql, $sqlTotals){
		
        $filter         =   self::requestFilter();
        self::$orderBy  =   self::requestOrderBy();
        self::$order    =   self::requestOrder();
        self::$export   =   self::requestExport();
        self::$pageNo   =   self::requestPageNo();

        if(!empty(self::$orderBy)) {
            $orderBy = array(self::$orderBy => self::$order);
        } else {
            $orderBy = [];
        }
		
        self::$sql = db::PrepareLoadQuery($sql, $filter, $orderBy, array(((self::$pageNo-1)*self::$rowsPerPage),self::$rowsPerPage), true);
      
		
		//self::$sqlNoLimits = db::PrepareLoadQuery($sql, $filter, array(self::$orderBy => self::$order), 0, true);
        //self::$sqlNoLimits = db::PrepareLoadQuery((isset($sqlTotals) && !$filter ? $sqlTotals : $sql), $filter, array(/*self::$orderBy => self::$order*/), 0, true);
        //echo 'table.php:70>>'.self::$order.'<<';
		 $arr=array();
		 if (trim(self::$order) !='') $arr=array(self::$orderBy => self::$order);
        self::$sqlNoLimits = 
		   db::PrepareLoadQuery((isset($sqlTotals) && !$filter ? $sqlTotals : $sql), $filter, $arr, 0, true);

      //echo'>>>table.php 74:'. (self::$sql);

        if (self::$sql != false) {
            self::$data = db::PrepareLoad(self::$export === false ? self::$sql : self::$sqlNoLimits);
            if(self::$data === false){self::$data = array();}
        } else {
            die(self::ERROR_QUERY_MSG . "Query($sql)");
        }
    }

    private static function printTable($action = "all",$with_filter=true){
        if($action === "open"){
            self::openTag("div", array("id"=>self::$itemsName."-list","class"=>"table"));
            if ($with_filter) self::printFilter();
            self::openTag("table",array("id"=>self::$itemsName."-table", "data-table"=>"js"));
            self::printTableHeader();
        } else if ($action === "close"){
            self::openTag("tbody");
            self::printTableRows("body");
            self::closeTag("tbody");
            self::openTag("tfoot");
            self::printTableRows("footer");
            self::closeTag("tfoot");
            self::closeTag("table");
            //self::tag('tablesLoadData();',"script");
            self::tag("table.init(\"".self::$itemsName."-table\");","script");
            self::closeTag("div");
        } else {
            self::printTable("open");
            self::printTable("close");
        }
    }

    static function load(){
        if(helper::$pageExtension === "json"){
            //>>>> ob_get_clean();
            if(!filter_has_var(INPUT_GET, "table-id") || filter_input(INPUT_GET, "table-id")!==self::$itemsName."-table"){ return; }
            if(self::$export){
				   $what = str_replace('admin','',$_SERVER['REQUEST_URI']);
				   $what = str_replace('/','',$what);
				   $_w=explode('.',$what);
				   $what=$_w[0];
                return self::exportData($what);
            } else {
                $jsonArr = array();
                $jsonArr["body"] = self::jsonGetBodyData();
                $jsonArr["footer"] = self::jsonGetFooterData();
                header('Content-Type: application/json');

                die(json_encode($jsonArr));
            }
        } else {
            self::printTable("close");
        }
    }

    private static function printTableRows($rowsFor){
        if(in_array($rowsFor, array("body","footer"))) {
            $data = $rowsFor == "footer" ? self::jsonGetFooterData() : self::jsonGetBodyData();
            if($rowsFor !== "footer"){
                foreach($data as $r){
                    self::openTag("tr");
                    if(isset($r[0][1]) && isset($r[0][1]["class"]) && $r[0][1]["class"]==="no-results"){
                        self::tag($r[0][0],"td",$r[0][1]);
                    } else {
                        self::openTag("td");
                        self::html(implode("</td><td>",$r));
                        self::closeTag("td");
                    }
                    self::closeTag("tr");
                }
            } else {
                if(self::$rowsTotal>0) {
                    self::openTag("tr");
                    self::tag($data[0][0][0],"td",$data[0][0][1]);
                    self::closeTag("tr");
                }
            }
        }
    }


    /** Adds needed js and css into the page header, it has to be included before "headers_sent()"
     * @param str|bool $setOrCheck - uses helper class to verify own load as described above */
    static function prepare($setOrCheck = false){
        self::$pageUrl = str_replace(".json","",filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));
        if( helper::prepare($setOrCheck, (__METHOD__)) !== true ){
            if(helper::$pageExtension == "json" && self::$prepareObStarted == false){
               
				ob_start();
				   self::$prepareObStarted = true;
            }
            return; //Stops the further execution in case the helper call doesn't return true.
        }
        self::$tableDrawerType = in_array(helper::$pageExtension,array("json","inline")) ? helper::$pageExtension : "json";
        cf::appendToHeadTag(ADD."helpers/table/add/table.js");
        cf::appendToHeadTag(ADD."helpers/table/add/table.css", "css");
    }

}