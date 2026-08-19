<?php
/** Description of tableCore */
class tableCore {

    static $data=array(), $cols, $filterActive = true, $export, $exportActive = true, $exportDataAsDisplayed = true, 
	$exportFileNameFormat = "::siteName - (::dateTime)";
    protected static $sql, $sqlNoLimits, $itemsName, $orderColNo, $orderBy, $order, $pageUrl, 
	 $pageNo = 1, $pagesTotal, $rowsTotal, $rowsPerPage,
	 $custom_filter,
    $exportTypes=array("CSV"),
    //$exportTypes=array("CSV","Excel"),
    $tableDrawerType, $prepareObStarted = false;
    private static $tables = array();

    const UTF8_ASC_SYMBOL = "&#9650;"; //"&#9652;"
    const UTF8_DESC_SYMBOL = "&#9660;"; //"&#9662;"
    const ERROR_QUERY_MSG = "ERROR (in query in the table class): ";
    const EMPTY_TABLE_MSG = "No results found.";
    const UTF8_LEFT_SYMBOL = "&lsaquo;";
    const UTF8_RIGHT_SYMBOL = "&rsaquo;";
    const FILTER_CASE_SENSITIVE = false;



    protected static function requestFilter(){
        $filter = filter_input(INPUT_GET, "filter");
        if($filter){
            $filter = "\"%" . $filter . "%\"";
            $filterBy = filter_input(INPUT_GET, "filter-by", FILTER_VALIDATE_INT);
            if($filterBy === false || is_null($filterBy)){
                $filterBy = array();
                foreach(self::$cols as $v){
                    if(isset($v[2]["sort"]) && $v[2]["sort"]===false){continue;}
                    $filterBy[] = "IFNULL(".$v[1].",\"\")";
                }
                $filterBy = "CONCAT(".implode(",",$filterBy).")";
            } else {
                $filterBy = self::$cols[$filterBy][1];
            }
            $filterBy = "CONCAT(\" \",".$filterBy.",\" \")";
            if(self::FILTER_CASE_SENSITIVE !== true) {
//                $filterBy .= " COLLATE UTF8_GENERAL_CI";
            }
            $filter = $filterBy . " LIKE " . $filter;
        } else {
            $filter = false;
        }
		
        return $filter;
    }

    protected static function requestOrderBy(){
        self::$orderColNo = filter_has_var(INPUT_GET, "col") && filter_input(INPUT_GET, "col", FILTER_VALIDATE_INT)>=0 ? filter_input(INPUT_GET, "col", FILTER_VALIDATE_INT) : null;
        if(is_null(self::$orderColNo)){
            foreach (self::$cols as $k=>$v) {
                $sotAddV = isset($v[2]["sort"]) ? $v[2]["sort"] : null;
                if($v[1]===self::$orderBy || $sotAddV === self::$orderBy){
                    self::$orderColNo = $k;
                    break;
                }
            }
            $orderBy = self::$orderBy;
        } else {
            $orderBy = isset(self::$cols[self::$orderColNo][2]["sort"]) ? self::$cols[self::$orderColNo][2]["sort"] : self::$cols[self::$orderColNo][1];
        }
		 //echo "--- tableCore 62-->".$orderBy;
        return $orderBy;
    }

    protected static function requestOrder(){
       return (filter_has_var(INPUT_GET, "ord") && in_array(filter_input(INPUT_GET, "ord"), array("asc","desc"))) ? filter_input(INPUT_GET, "ord") : (filter_has_var(INPUT_GET, "col") ? "asc" : self::$order);
    }

    public static function requestExport(){
        return filter_has_var(INPUT_GET, "export") && in_array(filter_input(INPUT_GET, "export"),self::$exportTypes) ? filter_input(INPUT_GET, "export") : false;
    }

    protected static function requestPageNo(){
        $pageNo = self::$pageNo;
        if(filter_has_var(INPUT_GET, "pg") && self::$export == false) {
            if(filter_input(INPUT_GET, "pg",FILTER_SANITIZE_FULL_SPECIAL_CHARS) === self::UTF8_LEFT_SYMBOL){
                $pageNo--;
            } else if(filter_input(INPUT_GET, "pg",FILTER_SANITIZE_FULL_SPECIAL_CHARS) === self::UTF8_RIGHT_SYMBOL){
                $pageNo++;
            } else if (filter_input(INPUT_GET, "pg",FILTER_SANITIZE_NUMBER_INT)>0){
                $pageNo = filter_input(INPUT_GET, "pg",FILTER_SANITIZE_NUMBER_INT);
            }
        }
        return abs($pageNo);
    }



    protected static function printTableHeader() {
		if (self::$cols) {
			$numCols = sizeof(self::$cols);
            self::openTag("thead");
                self::openTag("tr", array("onload"=>"table.SetTheTableColumnsHoverEffect('".self::$itemsName."-table')"));
                for ($colNo = 0; $colNo < $numCols; $colNo++) {

                    $colLabel = isset(self::$cols[$colNo][0]) ? self::$cols[$colNo][0] : false;
                    $colDbField = isset(self::$cols[$colNo][1]) ? self::$cols[$colNo][1] : false;
                    $colArgs = isset(self::$cols[$colNo][2]) ? self::$cols[$colNo][2] : false;
                    $colType = "regular";
                    if(isset($colArgs["type"])){
                        $colType = $colArgs["type"];
                        unset($colArgs["type"]);
                    }

                    $colExcludeSort = false;
                    $colAlterDataSort = null;
                    if(isset($colArgs["sort"])){
                        $colExcludeSort = $colArgs["sort"] == false ? true : false;
                        $colAlterDataSort = $colArgs["sort"] == false ? null : $colArgs["sort"];
                        unset($colArgs["sort"]);
                    }

                    if($colType == "delete" && !isset($colArgs["width"])){
                        $colArgs["width"] = "1%";
                        $colExcludeSort = true;
                    }

                    if(isset($colArgs["width"])) {
                        $newStyle = "width:".$colArgs["width"].";";
                        $colArgs["style"] = isset($colArgs["style"]) ? $newStyle . $colArgs["style"] : $newStyle;
                        unset($colArgs["width"]);
                    }

                    if($colType !== "hidden") {
                        self::openTag("th", $colArgs);
                        if(!$colExcludeSort){
                            self::openTag("a",array("onclick"=>"table.Sort({$colNo},this);"));
                        }
                        if ($colType != "delete") {
                            $spanContent = ($colAlterDataSort == self::$orderBy || $colDbField == self::$orderBy ? (self::$order == "desc" ? self::UTF8_DESC_SYMBOL : self::UTF8_ASC_SYMBOL) : "");
                       if (self::$orderBy == '')  $spanContent='';   
							self::tag($spanContent,"span");
                            self::html($colLabel);
                        } else {
                            self::html("<input id=\"".self::$itemsName."CheckDeleteAll\" type=\"checkbox\" onclick=\"checkAllDeleteCheckboxes(this, '".self::$itemsName."')\" />");
                        }
                        if(!$colExcludeSort){
                            self::closeTag("a");
                        }
                        self::closeTag("th");
                    }
                }
				
                self::closeTag("tr");
            self::closeTag("thead");
		}
	}

    protected static function printFilter(){//echo '---TableCore 154--'.self::$custom_filter;
		
		if (isset(self::$custom_filter) && self::$custom_filter == 'onboarding-report'){ 
			$s=array(0=>'',1=>'',2=>'',3=>'');
			$filter_report = filter_input(INPUT_GET, "filter", FILTER_VALIDATE_INT);
			if (!isset($filter_report)) $filter_report = 1;
			$s[$filter_report ]='selected';
			echo '<div class="filter"><div>
			<label>Filter by Status:</label>
			<select data-table-id="employee_onboarding-table" name="filter-report" onchange="table.Filter(this)">
			<option value="0" '.$s[0].'>any</option>
			<option value="1" '.$s[1].'>incomplete (incl. overdue)</option>
			<option value="2" '.$s[2].'>overdue only</option>
			<option value="3" '.$s[3].'>complete</option>
			</select>
			</div></div>';
		}
		elseif (isset(self::$custom_filter) && self::$custom_filter == 'credentialing-report'){
			/*
			$s=array(0=>'',1=>'',2=>'',3=>'');
			$filter_report = filter_input(INPUT_GET, "filter", FILTER_VALIDATE_INT);
			if (!isset($filter_report)) $filter_report = 1;
			$s[$filter_report ]='selected';
			echo '<div class="filter"><div>
			<label>Filter by Status:</label>
			<select data-table-id="employee_onboarding-table" name="filter-report" onchange="table.Filter(this)">
			<option value="0" '.$s[0].'>any</option>
			<option value="1" '.$s[1].'>incomplete (incl. overdue)</option>
			<option value="2" '.$s[2].'>overdue only</option>
			<option value="3" '.$s[3].'>complete</option>
			</select>
			</div></div>';
			*/
		}
		else {
        $filterByGetValue = filter_input(INPUT_GET, "filter-by", FILTER_VALIDATE_INT);
        $filterGetValue = filter_input(INPUT_GET, "filter",FILTER_SANITIZE_STRING);
        if(self::$filterActive){
            self::openTag("div",array("class"=>"filter"));
                self::openTag("div");
                    self::tag("Filter by: &nbsp;", "label");
                    self::openTag("select",array("onchange"=>"table.Filter(this)"));
                        self::tag("All...","option",array("value"=>"all"));
                        foreach(self::$cols as $k=>$v){
                            if(isset($v[2]["sort"]) && $v[2]["sort"]===false){continue;}
                            if(isset($v[2]["filtered"]) && $v[2]["filtered"]===false){continue;}
                            $args = array("value"=>$k);
                            if($filterByGetValue === $k) {$args["selected"]=true;}
                            self::tag($v[0],"option",$args);
                        }
                    self::closeTag("select");
                    $args = array("type"=>"text","onkeyup"=>"table.Filter(this)","data-table-id"=>self::$itemsName."-table"); //"placeholder"=>"Add a filter here..."
                    if(!empty($filterGetValue)){ $args["value"] = db::EscapeStr($filterGetValue); }
                    self::tag("","input",$args);
                self::closeTag("div");
            self::closeTag("div");
        }
		}
    }



    protected static function exportData($what='--'){
	
		 $names= array('--'=>SITE_NAME, 
		 'applications'=>'applications-pending',
		 'for-decision-applications'=>'applications-for-decision',
		 'for-interview-applications'=>'applications-for-inteview',
		 'manually-rejected-applications'=>'applications-manually-rejected',
		 'auto-rejected-applications'=>'applications-auto-rejected',
		 'onboarding-report'=>'onboarding-report-'.@$_SESSION['onboarding-report'],
		 'credentialing-report'=>'Overdue credentialing report',
		 'employees'=>'employees',
		 'employees-terminated'=>'employees-terminated',
		 'manager-employees'=>'employees-managers',
		 'employees-of-manager'=>'employees-of-manager:'.@$_SESSION['manager-of-employees']
		 );
		

        $data = self::$exportDataAsDisplayed ? self::$data : db::PrepareLoad(self::$sql);
        $fnReplace = array();
        
		
		 $fnReplace["::siteName"]= @$names[$what];
        $fnReplace["::itemsName"]= self::$itemsName;
		 $d= new DateTime();
        $fnReplace["::dateTime"] = $d->format(USER_DATETIME_FOR_FILE);
		 
		 //db::PrepareLoadSingleCellValue("SELECT DATE_FORMAT(Now(), \"".str_replace(":"," ",GLOBAL_MYSQL_DATETIME_LONG)."\")");
        $outFilename = strtr(self::$exportFileNameFormat, $fnReplace);
	
		  $outColumns = array();
        foreach(self::$cols as $c){
            if(isset($c[2]["sort"]) && $c[2]["sort"]===false){continue;}
            $outColumns[] = $c[1];
            $outHeader[] = $c[0];
        }
        $eData = array($outHeader);

        if(count($data) > 0) {
            foreach($data as $k => $row){
		//echo "<pre>";print_r(self::$cols)	;print_r($row);exit;
                $rowData = array();
				//echo $row['afStartDate'].'<br>';
				   foreach(self::$cols as $c){
					   
					   if (in_array($c[1],array('afAdded','afStartDate'))){ 
					   
					      //if (!isset($row[$c[1]])) $row[$c[1]]=$row['afStartDate'];
						   if ($c[1]=='afAdded')
						   $row[$c[1]] = 
					       val::convDate(MYSQL_DATE_TIME ,USER_DATE,$row[$c[1]]);
						   else
							 $row[$c[1]] = 
					       val::convDate(MYSQL_DATE ,USER_DATE,$row[$c[1]]);
						   
					   }
						if (!isset($row[$c[1]])) $row[$c[1]]='';
					   $_r = is_array($row[$c[1]]) ? $row[$c[1]][0] : $row[$c[1]];
					   $rowData[] = trim($_r);
				   }
				
                $eData[] = $rowData;
            }
        }
		//exit;
		//echo "<pre>";print_r($eData);print_r(self::$data);exit;
        switch(self::$export){
            case "CSV":
                header("Content-Type:application/csv");
                header('Content-Disposition:attachment; filename="'.$outFilename.'.csv"');
                $output = fopen("php://output",'w') or die("Can't open php://output");
                foreach($eData as $v){
                    fputcsv($output, $v);
                }
                fclose($output) or die("Can't close php://output");
                break;
            default:
                $escape = function($v){ return str_replace("\t","&#9;",$v); };
                header("Content-Type:application/csv");
                header('Content-Disposition:attachment; filename="'.$outFilename.'.csv"');
                $output = fopen("php://output",'w') or die("Can't open php://output");
                foreach($eData as $v){
                    fputs($output, implode("\t",array_map($escape, $v))."\r\n");
                }
                fclose($output) or die("Can't close php://output");
                break;
        }
    }

    protected static function jsonGetBodyData(){
		
        $jsonArrBody = array();
        $outColumns = array();
        foreach(self::$cols as $c){
            if(!isset($c[2]["type"]) || ($c[2]["type"] !== "hidden") || (table::requestExport())) {
                $outColumns[] = $c[1];
            }
        }
        if(count(self::$data) > 0) {
            foreach(self::$data as $k => $row){
                $rowData = array();
                foreach($outColumns as $dbName){
                    if(isset($row[$dbName]))    $rowData[] = $row[$dbName];
                }
                $jsonArrBody[] = $rowData;
            }
        } else {
            $jsonArrBody[] = array(array(self::EMPTY_TABLE_MSG, array("colspan"=>count(self::$cols),"class"=>"no-results")));
        }
        return $jsonArrBody;
    }

    protected static function jsonGetFooterData(){
        $jsonArrFooter = array();
        if(count(self::$data) > 0) {
            if(self::$pageNo===1 && ($cntPaged = count(self::$data)) < self::$rowsPerPage){
                //Skips total count query
                self::$rowsTotal = $cntPaged;
            } else { 
			   //echo "SELECT COUNT(*) AS countRows FROM (". self::$sqlNoLimits .") AS dt";
			   //echo ">>> tableCore 254: ".self::$sqlNoLimits.'<hr>';
			   $rowsTotal = db::PrepareLoad("SELECT COUNT(*) AS countRows FROM (". self::$sqlNoLimits .") AS dt");
             self::$rowsTotal = $rowsTotal[0]["countRows"];
            }
            self::$pagesTotal = ceil(self::$rowsTotal / self::$rowsPerPage);

            $footerDataAsStr = "";
            if(count(self::$data) >= 0){
                $fromNo = self::$pageNo * self::$rowsPerPage - self::$rowsPerPage + 1;
                $toNo = (self::$pageNo*self::$rowsPerPage < self::$rowsTotal) ? self::$pageNo*self::$rowsPerPage : self::$rowsTotal;
				   $footerDataAsStr .= cf::getOutput(__CLASS__, "openTag", array("div",array("class"=>"footer")));
				   
				   
						
							$footerDataAsStr .= cf::getOutput(__CLASS__, "openTag", array("div",array("class"=>"data")));
								
							
							if ( !strpos($_SERVER['REQUEST_URI'],'to_do=tsr')
							&&	(
								 strpos($_SERVER['REQUEST_URI'],'applications') !== false
								|| strpos($_SERVER['REQUEST_URI'],'employees') !== false 
								|| strpos($_SERVER['REQUEST_URI'],'credentialing-report') !== false
								|| strpos($_SERVER['REQUEST_URI'],'onboarding-report') !== false 
								|| strpos($_SERVER['REQUEST_URI'],'onboarding-report') !== false
								)
							)

							{	
									
								if(self::$exportActive === true){
									$expUrl = (strtok(self::$pageUrl,"?&") . ".json?table=" . self::$itemsName . "&export=");
									foreach(self::$exportTypes as $e){
										  $footerDataAsStr .= cf::getOutput
										(__CLASS__, "tag" ,array($e,"a",array("href"=>$expUrl.$e,"onclick"=>"return table.Export(this,'{$e}');","class"=>"exp")));
									}
								}
							
							}	
							$footerDataAsStr .= cf::getOutput(__CLASS__, "closeTag" , array("div"));
					
                    //$footerDataAsStr .= cf::getOutput(__CLASS__, "openTag", array("div",array("class"=>"paging")));
					    	
                        $footerDataAsStr .= 
							 "<div class='paging' style='font-style:normal'>"
							 .self::createPaginationLinks()
							 ."<span style='float:right'>Showing {$fromNo} - {$toNo} from ".self::$rowsTotal."</span>"
							 ."</div>";
                   // $footerDataAsStr .= cf::getOutput(__CLASS__, "closeTag" , array("div"));
                $footerDataAsStr .= cf::getOutput(__CLASS__, "closeTag" , array("div"));
						   
                $jsonArrFooter[] = array(array($footerDataAsStr,array("colspan"=>count(self::$cols))));
            }
        }
        return $jsonArrFooter;
    }

    private static function createPaginationLinks( ) {
		
		
        $lnks = array();
        $code = cf::getOutput(__CLASS__, "openTag", array("ul"));
        self::addVarToUrl("table", self::$itemsName);
        self::addVarToUrl("pg");
        $paginatorUrl = self::$pageUrl;
		
        if(self::$pagesTotal > 1){
            $code .= cf::getOutput(__CLASS__, "openTag", array("li", array("data-page"=>(self::$pageNo-1))));
			  
                if(self::$pageNo !== 1){
                    $code .= cf::getOutput(__CLASS__, "tag", array(self::UTF8_LEFT_SYMBOL, "a", array("href"=>$paginatorUrl.(self::$pageNo-1),"onclick"=>"return table.GoPage(this);")));
                } else {
                    $code .= cf::getOutput(__CLASS__, "html", array("&nbsp;"));
                }
            $code .= cf::getOutput(__CLASS__, "closeTag", array("li"));

            $paginatorLimit = 10;
			
            $paginationLinksCount = self::$pagesTotal > $paginatorLimit ? $paginatorLimit : self::$pagesTotal;
            $paginationStart = self::$pageNo <= ($paginatorLimit/2) ? 1 : (self::$pageNo-$paginatorLimit/2);
            $paginationFinal = $paginatorLimit;
            if(self::$pageNo > (self::$pagesTotal-($paginatorLimit/2))){
				  $paginationFinal = self::$pagesTotal;//(self::$pageNo+$paginatorLimit/2)
            } else if(self::$pageNo > ($paginatorLimit/2)){

                $paginationFinal = $paginationStart + $paginatorLimit;
				
            }
			  if ($paginationFinal >= self::$pagesTotal ) $paginationFinal = self::$pagesTotal;
			  
            for($i=$paginationStart; $i <= $paginationFinal; $i++){
				
                $iArgs = $i !== self::$pageNo ? array("li") : array("li",array("class"=>"a"));
				
                $code.= cf::getOutput(__CLASS__, "openTag", $iArgs);
                if($i !== self::$pageNo) {
                    $code .= cf::getOutput(__CLASS__, "tag", array($i, "a", array("href"=>$paginatorUrl.$i,"onclick"=>"return table.GoPage(this);")));
                } else {
                    $code .= cf::getOutput(__CLASS__, "html", array($i));
                }
                $code .= cf::getOutput(__CLASS__, "closeTag", array("li"));
            }
            $code .= cf::getOutput(__CLASS__, "openTag", array("li", array("data-page"=>(self::$pageNo+1))));
                if(self::$pageNo < self::$pagesTotal){
                    $code .= cf::getOutput(__CLASS__, "tag", array(self::UTF8_RIGHT_SYMBOL, "a", array("href"=>$paginatorUrl.(self::$pageNo+1),"onclick"=>"return table.GoPage(this);")));
                } else {
                    $code .= cf::getOutput(__CLASS__, "html", array("&nbsp;"));
                }
            $code .= cf::getOutput(__CLASS__, "closeTag", array("li"));
        }
		
        $code .= cf::getOutput(__CLASS__, "closeTag", array("ul"));

        return $code;
    }

    protected static function addVarToUrl($varName, $varValue = null){
        $url = self::$pageUrl;
        if(strpos($url,$varName)!==false){
            $pos = strpos($url, $varName);
            $preVarUrl = substr($url,0,$pos);
            $postVarUrl = strpos($url,"&",$pos)!==false ? substr($url, strpos($url,"&",$pos)) : null;
        } else {
            $preVarUrl = strpos($url, "?")!==false ? $url."&" : $url."?";
            $postVarUrl = null;
        }
        self::$pageUrl = $preVarUrl . $varName . "=" . $varValue . $postVarUrl;
    }

    protected static function reset($tableId){
        if(!in_array($tableId, self::$tables)){
            self::$tables[] = $tableId;
            self::$itemsName = $tableId;
            self::$cols = array();
            self::$rowsTotal = null;
        } else {
            die("Existing table ID used in a table::create() call: " . $tableId);
        }
    }



    /** Description: Validate and prepare the table header args
     *  @param str   $tag
     *  @param arr   $args
     *  Return str   (space separated attribute="value" list) */
    private static function processArgs($tag, &$args) {
        $tag = strtolower($tag);
        if(!in_array($tag, array("table", "tr", "thead", "th", "tbody", "td", "tfoot", "a", "div", "span", "ul", "li", "input", "select", "option", "script", "label"))){
            die("Invalid html table tag: {$tag}");
        }
        if (!empty($args) && is_array($args)) {
            $table_th_TagHTML5Attributes = array("abbr", "colspan", "headers", "rowspan", "scope", "sorted", "filtered");
            $table_a_TagHTML5Attributes = array("onclick", "href");
            $validHTML5StandardAttributes = array("id","class","colspan","style","title","type", "onchange", "onkeyup", "onload", "value", "placeholder", "disabled", "selected");
            $crntValidHTML5Var = "table_".$tag."_TagHTML5Attributes";
            $validHTML5Attributes = isset($$crntValidHTML5Var) ? array_merge($validHTML5StandardAttributes, $$crntValidHTML5Var) : $validHTML5StandardAttributes;
            $validCustomArgs = array();

            $res = array();
            foreach ($args as $key => $value) {
                if (in_array(strtolower($key), $validHTML5Attributes) || substr($key, 0, 5) == "data-") {
                    $res[] = $value===true ? $key : $key."=\"".$value."\"";
                    unset($args[$key]);
                }
            }
            // Validate remining args
            if (count($args) > 0) {
                foreach ($args as $key => $value) {
                    if (!in_array(strtolower($key), $validCustomArgs)) {
                        die("Invalid table class call. Incorrect argument: {$key} => {$value}");
                    }
                }
            }
            return implode(" ", $res);
        }
    }

    /** Description: Writes <tag {$args}>
     *  @param str   $innerHtml
     *  @param str $tag
     *  @param arr $args - tag attributes */
    static function tag($innerHtml = "", $tag = "", $args = array()) {
		  if (in_array($innerHtml,array('CSV'))) $innerHtml='Export as '.$innerHtml;
        self::openTag($tag, $args);
        self::html($innerHtml);
        self::closeTag($tag);
    }

    /** Description: Writes <tag {$args}>
     *  @param str $tag
     *  @param arr $args - tag attributes */
    static function openTag($tag = "", $args = array()) {
        $tagAttributes = self::processArgs($tag, $args);
        $html = "<" . $tag . (!empty($tagAttributes) ? " " : "") . $tagAttributes . ">";
        self::html($html);
    }

    /** Description: Writes </tag> - repeated $closeRepeater times
     *  @param str $tag
     *  @param int $closeRepeater */
    static function closeTag($tag = "", $closeRepeater = 1) {
        $rep = abs($closeRepeater) > 0 ? $closeRepeater : 1;
        $html = str_repeat("</" . $tag . ">", $rep);
        self::html($html);
    }

    /** Description: Outputs the $code (echo)
     *  @param str $code */
    static function html($code) {
        echo $code;
    }
}
