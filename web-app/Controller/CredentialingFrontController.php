<?php

/** The CompanyOffice controller */
class CredentialingFrontController extends AppController
{

    const __DUMP__ = false;
    const __PAGESIZE__ = 20;

    public static $id, $data, $pg;


    public static function Credentialings()
    {
        if (!isset(AppModel::$usr["afID"])) {
            self::MustSigned();
            return;
        }
		
		 $there_onetime=CredentialingController::CheckOneTimeToRecurringWithFile(AppModel::$usr["afID"]);
		 if ($there_onetime !== false ) msg::set('Credentialing requires expiration date - see credentialings in red.','error');

        $id = AppModel::$usr["afID"];
        $pid = AppModel::$usr["afHiredForPID"];
        msg::get();

        $activePage = "pending";
        $cond = [];
        if(filter_has_var(INPUT_GET, "credentialing0")) {
            $taskId = filter_input(INPUT_GET, "credentialing0", FILTER_SANITIZE_NUMBER_INT);
            if($taskId > 0) {
                $cond[] = " ecCID={$taskId}";
            }
        }
        if(!empty($cond)) {
            $condsStr = ' and '.implode(' and ', $cond);
        } else {
            $condsStr = '';
        }
		  $cond = "ecUploadedOn is null {$condsStr} ";
		  $today  = val::currentDate(MYSQL_DATE_TIME);
	     $due ="";//"ecDueDate < '{$today}'";
		
		    // KEEP THIS!
		$pending = EmployeeCredentialingModel::LoadEmployeeCredentialings($id,$cond,'',$due,'pending' );


		    // Get both completed and pending, there might be duplicates, merge them, remove duplicates
        $PendingWithDuplicates = EmployeeCredentialingModel::LoadEmployeeCredentialings($id,$cond,'',$due,'pending' );
        //echo "PENDING:<br>";
        //print_r ($PendingWithDuplicates);
        $CompletedWithDuplicates = EmployeeCredentialingModel::LoadEmployeeCredentialings($id,$cond,'',$due,'completed' );
        //echo "COMPLETED:<br>";
        //print_r ($CompletedWithDuplicates);

        $MergedArrays = array_merge($PendingWithDuplicates, $CompletedWithDuplicates);
        
            // remove duplicates
        $UniqueIDs = array();
        $UniqueArray = array();
        foreach ($MergedArrays as $Row)
        {
            if (!(in_array($Row["ecID"], $UniqueIDs)))
            {
                array_push($UniqueIDs, $Row["ecID"]);
                array_push($UniqueArray, $Row);
            }
        }
        
        //echo "MERGED UNIQUE:<br>";
        //print_r ($UniqueArray);
        $pending = $UniqueArray;
        
        
            // create combined array with only ecIDs of BOTH above arrays and remove duplicates
        $BothIDs = array();
        foreach ($PendingWithDuplicates as $SinglePending)
        {
            array_push($BothIDs, $SinglePending["ecID"]);
        }
        
        foreach ($CompletedWithDuplicates as $SingleCompleted)
        {
            array_push($BothIDs, $SingleCompleted["ecID"]);
        }
        
            // unique IDs which need to be shown - pending and completed
        $BothIDs = array_unique($BothIDs);
        
        for ($i = 0; $i < count($BothIDs); $i++)
        {
            $CombinedPendingCompleted = array();
        }
        
        
        
        

        $cond = [];
        if(filter_has_var(INPUT_GET, "credentialing1")) {
            $activePage = "completed";
            $taskId = filter_input(INPUT_GET, "credentialing1", FILTER_SANITIZE_NUMBER_INT);
            if($taskId > 0) {
                $cond[] = " ecCID={$taskId}";
            }
        }
        if(!empty($cond)) {
            $condsStr = ' and '.implode(' and ', $cond);
        } else {
            $condsStr = '';
        }
//        $completed = EmployeeCredentialingModel::LoadEmployeeCredentialings($id, "ecUploadedOn is not null {$condsStr} and exists(select cpID from credentialing_positions where cpCID=ecCID and cpPID={$pid})");
      $due ='';
	   $cond = " (ecUploadedOn IS NOT NULL or ecFileName IS NOT NULL) {$condsStr}";
   	   $completed = EmployeeCredentialingModel::LoadEmployeeCredentialings($id, $cond,'',$due,'completed');
		
		//echo 'COMPL>>>';echo "<pre>";print_r($completed);echo "</pre>";

        $prevPos = FieldHistoryModel::getAllHistory("position", $id);
		
//$prevPos=array();echo "CFContr line 68 PrevPos cleared!!!";

        $fromDate = null;

        if(count($prevPos) > 1) {
            $previous = EmployeeCredentialingModel::LoadEmployeeCredentialings($id, 
			     "ecUploadedOn is not null {$condsStr} ");

            foreach ($completed as $key=>$rec) {
                $recDate = strtotime($rec["ecUploadedOn"]);
                foreach ($prevPos as $pos) {
                    $posDate = strtotime($pos["fhAdded"]);
                    if($recDate < $posDate) {
                        unset($completed[$key]);
                    }
                }
            }

            foreach ($previous as $key=>&$rec) {
                $recDate = strtotime($rec["ecUploadedOn"]);
                foreach ($prevPos as $pos) {
                    $posDate = strtotime($pos["fhAdded"]);
                    $rec["position"] = $pos["fhValue"];
                    $rec["onPosFrom"] = val::convDate(MYSQL_DATE,USER_DATE,$posDate);
				
                    if($recDate >= $posDate) {
                        break;
                    }
                }
            }

            foreach ($completed as $compl) {
                foreach ($previous as $key=>$prev) {
                    if($compl["ecID"] === $prev["ecID"]) {
                        unset($previous[$key]);
                    }
                }
            }

        } else {
            $previous = [];
        }
/********/

        CredentialingView::RenderFrontCredentialings([
            "pending" => $pending,
            "completed" => $completed,
            "previous" => $previous
        ], $activePage);

		 
    }

    public static function SkipCredentialing() {
        $ecID = filter_input(INPUT_GET, "skip", FILTER_SANITIZE_NUMBER_INT);
        if($ecID > 0) {
			  $today = new DateTime();
			  $now = $today->format('Y-m-d H:i:s');
            $q='UPDATE employee_credentialing SET ecUploadedOn ="'.$now.'", ecIsApplicableForMe ="no" WHERE ecID='.$ecID;
			
			  db::SQL($q);
        }
    }
	
	public static function DownloadFile() {
        $ecID = filter_input(INPUT_GET, "download", FILTER_SANITIZE_NUMBER_INT);
        if($ecID > 0) {
            $data = EmployeeCredentialingModel::dataById($ecID);
            if($data) {
                $fileSource = CREDENTIALING_UPLOAD_DIR . CREDENTIALING_FILE_NAME . $ecID;
                $fileName = $data["ecFileName"];
                if(!empty($fileName)) {
                    cf::outputFile($fileSource, $fileName);
                }
            }
        }
    }

    public static function MustSigned()
    {
        EmployeeView::RenderMustSigned();
    }

}