<?php

/** The CompanyOffice controller */
class EmployeeFrontController extends AppController
{

    const __DUMP__ = false;
    const __PAGESIZE__ = 20;

    public static $id, $data, $pg;


    public static function Employees()
    { 	
        if(!isset(AppModel::$usr["afID"])) {
		
            EmployeeView::RenderMustSigned();
            return;
        }

        $conds = array("afActive='Hired'");

        if(filter_has_var(INPUT_GET, "firstname")) {
            $val = filter_input(INPUT_GET, "firstname", FILTER_SANITIZE_STRING);
            if(!empty($val)) { $conds[] = "afFirstName like '%{$val}%'"; }
        }

        if(filter_has_var(INPUT_GET, "lastname")) {
            $val = filter_input(INPUT_GET, "lastname", FILTER_SANITIZE_STRING);
            if(!empty($val)) { $conds[] = "afLastName like '%{$val}%'"; }
        }

        if(filter_has_var(INPUT_GET, "position")) {
            $val = filter_input(INPUT_GET, "position", FILTER_SANITIZE_NUMBER_INT);
            if(!empty($val) && ($val > 0)) { $conds[] = "afHiredForPID = {$val}"; }
        }

        if(filter_has_var(INPUT_GET, "office")) {
            $val = filter_input(INPUT_GET, "office", FILTER_SANITIZE_NUMBER_INT);
            if(!empty($val) && ($val > 0)) { $conds[] = "afCOID = {$val}"; }
        }

        $condsStr = implode(" and ", $conds);

        $totalCnt = EmployeeFrontModel::getEmployeesCount($condsStr);
        $currentPage = 1;
        if(filter_has_var(INPUT_GET, "page")) {
            $currentPage = intval(filter_input(INPUT_GET, "page", FILTER_SANITIZE_NUMBER_INT));
            if($currentPage == 0) $currentPage = 1;
        };

        $q = 'select *, 
              (select pName from positions where pID=afHiredForPID) afHiredPosition, 
              (select coName from company_offices where coID=afCOID) as afOffice 
              from apply_forms where '.$condsStr.' order by afStartDate desc
              limit '.(($currentPage-1)*self::__PAGESIZE__).', '.self::__PAGESIZE__;
        $data = EmployeeFrontModel::PrepareLoad($q);

        EmployeeView::RenderFrontEmployeeList($data, $totalCnt, $currentPage, self::__PAGESIZE__);

    }

    public static function Employee() {
        if(!isset(AppModel::$usr["afID"])) {
            EmployeeView::RenderMustSigned();
            return;
        }

        $id = filter_input(INPUT_GET, "ID", FILTER_SANITIZE_NUMBER_INT);
        if(intval($id) > 0) {
            $empl = EmployeeFrontModel::dataById($id);
            EmployeeView::RenderEmployee($empl, ApplyFormModel::$fldLbl, self::$pg);
        } else {
            $page = new PageFront();
            $page->web404();
        }
    }

    public static function MustSigned() {
        EmployeeView::RenderMustSigned();
    }
	
	public static function downloadFile(){
        $id = intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
        
        $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser where 	fcfbuID={$id}");
        if($rec === false) {
            $rec = [];
        } 
        if(empty($rec)) {
            die();
        } else {
            $filePath = "add/uploads/files/".$rec['fcfbuFileSavedAs'].'.'.$rec['fcfbuFileЕxt'];
            if(file_exists($filePath)) { 
			      $name=$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'];
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filePath));
                header('Content-Disposition: attachment; filename="'.$name.'"');
                readfile($filePath);
                die();
            } else {            
                die();
            }
        }
    }
	
	public static function downloaEmployeedFile(){
        $id = intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
        
        $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser WHERE 	fcfbuID={$id}");
        if($rec === false) {
            $rec = [];
        } 
        if(empty($rec)) {
            die();
        } else {
            $filePath = "add/uploads/files/".$rec['fcfbuFileSavedAs'].'.'.$rec['fcfbuFileЕxt'];
            if(file_exists($filePath)) { 
			      $name=$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'];
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filePath));
                header('Content-Disposition: attachment; filename="'.$name.'"');
                readfile($filePath);
                die();
            } else {            
                die();
            }
        }
    }
	
	public static function downloadFrontFile(){

        $id = intval(filter_input(INPUT_GET, 'fid', FILTER_SANITIZE_NUMBER_INT));
        
        $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser WHERE fcfbuUserID = ".AppModel::$usr['afID']." AND fcfbuID={$id}");
		
        if($rec === false) {
            $rec = [];
        } 
        if(empty($rec)) {
            self::show404();
        } else {
            $filePath = "add/uploads/files/".$rec['fcfbuFileSavedAs'];//.'.'.$rec['fcfbuFileЕxt'];
			
            if(file_exists($filePath)) { 
			      $name=$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'];
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filePath));
                header('Content-Disposition: attachment; filename="'.$name.'"');
                readfile($filePath);
                self::show404();
            } else {            
                self::show404();
            }
        }
    }

   protected static function show404() {
        $page = new PageFront();
        $page->web404();
    }
	
	
	public static function employeesFiles(){ 
		$user_id = AppModel::$usr["afID"];

		$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
		$data=db::PrepareLoad(
		'SELECT * FROM  fcfilesbyuser LEFT JOIN fcategories ON fcID = fcfbuFileCatID '.
		' WHERE fcfbuVisible = "visible"  AND fcfbuUserID='.$user_id.' AND fcActive="Yes"  ORDER BY fcName ASC,fcfbuFileName ASC');
		
		$r=array();
		if (empty($data)) echo '<br><h4>No files available.</h4>';
		if (!empty($data)) {
		foreach ($data as $k=>$d){
			$r[$d['fcName']][] = 
			array(
				'id'=>$d['fcfbuID'],
				'saved_as'=>$d['fcfbuFileSavedAs'],
				'name'=>$d['fcfbuFileName'],
				'ext'=>$d['fcfbuFileЕxt'],
				
			);
		}

		
		echo '<br><br><h4 style="font-size:18px">Please select category:</h4><br>';
		foreach ($r as $name=>$row){
			echo '<button class="accordion" title="Category.Click to see files in that category"><b><i class="fa fa-tags"></i>&nbsp;'.$name.'</b></button>';
			echo '<div class="accpanel"><br>';
			    
				foreach ($row as $r){
					echo '<a title="Click to download file"  href="file_request?fid='.	$r['id'].'">'
					,'<i class="fa fa-download"></i>&nbsp'.$r['name'].'</a><br>';
				}
			echo '<br></div>';
		} 
				
		echo "<hr>";
	}
	}
}