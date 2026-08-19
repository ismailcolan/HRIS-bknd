<?php

/** The EmployeeOffice model */
class EmployeeOnboardingModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "employee_onboarding", "key" => "eoID", "pfx" => "eo", "controller" => "");
    static $mainTblFields = array("eoAFID", "eoOTID", "eoTimeFrame", "eoCompletedOn", "eoCompletedBy", "eoActive");
    static $fldLbl = array("eoAFID" => "Employee", "eoOPID" => "Onboarding Task", "eoTimeFrame" => "Deadline", "eoCompletedOn" => "Completed On",
        "eoCompletedBy" => "Completed By", "eoActive" => "Status");
    static $postOverrided = array();

	
	
	public static function AmIResponsiveFor($myid){
		/**************
		$q = "select *, afID,
		(select concat(afFirstName, ' ', afLastName) from apply_forms where afID=otAFID) as otRespEmpl 
		from `employee_onboarding` 
		left join `onboarding_tasks` on `otID` = `eoOTID` 
		left join `apply_forms` ON  `afID` = `otAFID`
		where ( `eoAFID` = {$myid} AND afID={$myid} )
  		OR ( `eoAFID` = {$myid} AND `eoOTID`= ".EMPLOYEE_THEMSELF_ID.")";// AND 	eoCompletedOn is NULL";
	
		$res = db::PrepareLoad($q);
		***********/
		
		$res=self::LoadResponsibleOnboardingTasks
			($myid, "eoCompletedOn is null ",'','check','HAVING otEmployeer IS NOT NULL'); //
		if (empty($res)) return false; else return true;
		
	}

    public static function LoadEmployeeOnboardingTasks($afID, $cond = "", $order = " eoCompletedOn ASC, eoTimeFrame ASC",$type='on')
    {
        $afid = intval($afID);
        $q = "select *, 
                  (select concat(afFirstName, ' ', afLastName) from apply_forms where afID=otAFID) as otRespEmpl
              from `" . self::$dt['tbl'] . "`
              left join `onboarding_tasks` on `otID` = `eoOTID` 
              where otType='".$type."' AND (`eoAFID` = " . $afid .' OR (`eoAFID` = '. $afid .' AND `otAFID`= '.EMPLOYEE_THEMSELF_ID.' ) ) ';

        if (!empty($cond)) {
            $q .= " and " . $cond;
        }

        if (!empty($order)) {
            $q .= " order by " . $order;
        }

        $data = db::PrepareLoad($q);
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["otDescription"] = htmlspecialchars($item["otDescription"]);
            }
        }
        return $data;
    }

    public static function LoadResponsibleOnboardingTasks(
	 $afID, $cond = "", $order = " eoCompletedOn ASC, eoTimeFrame ASC",$tab='any',$having='',$type='on')
	 //($myid, "eoCompletedOn is null ",'','check','otEmployeer IS NOT NULL')
    {
	
       $afid = intval($afID);
		if ($type == 'on') {
			$andHired=" AND afActive='Hired' "; 
			$orCond=" OR eoAFID = {$afid} ";
		}
		else {
			$andHired="";
			$orCond="";
		}
		
		if ($tab == 'check'){
			$q = "select *, 
                  IF((select concat(afFirstName, ' ', afLastName) from apply_forms 
				    where afID=otAFID {$andHired} )
					 = '".EMPLOYEE_THEMSELF_NAME."', 
				    '".EMPLOYEE_THEMSELF_NAME_FRONT."'
					, (select concat(afFirstName, ' ', afLastName) from apply_forms 
					where afID=eoAFID {$andHired} ) ) 
					as otEmployeer
					,(select concat(afFirstName, ' ', afLastName) 
					  from apply_forms where afID = otAFID {$andHired}) as otResponsibleEmployeer
  
				   FROM `" . self::$dt['tbl'] . "`
              left join `onboarding_tasks` on `otID` = `eoOTID`
              where otType='".$type."' and  
			    (`otAFID` = " . $afid." {$orCond} OR (eoAFID =".$afid." AND `otAFID`= ".EMPLOYEE_THEMSELF_ID.' ))';
				
			
			
		}
		
		if ($tab == 'pending'){ 
			$q = "select *, 
                  IF((select concat(afFirstName, ' ', afLastName) from apply_forms 
				    where afID=otAFID {$andHired} )
					 = '".EMPLOYEE_THEMSELF_NAME."', 
				    '".EMPLOYEE_THEMSELF_NAME_FRONT."'
					, (select concat(afFirstName, ' ', afLastName) from apply_forms 
					where afID=eoAFID {$andHired} ) ) 
					as otEmployeer
					,(select concat(afFirstName, ' ', afLastName) 
					  from apply_forms where afID = otAFID {$andHired}) as otResponsibleEmployeer
  
				   FROM `" . self::$dt['tbl'] . "`
              left join `onboarding_tasks` on `otID` = `eoOTID`
              where otType='".$type."' and 
			    (`otAFID` = " . $afid." {$orCond} OR (eoAFID =".$afid." AND `otAFID`= ".EMPLOYEE_THEMSELF_ID.' ))';
			
		}
		
		if ($tab=='any'){
			$q = "select *, 
				 IF((select concat(afFirstName, ' ', afLastName) from apply_forms where afID=otAFID)
					 = '".EMPLOYEE_THEMSELF_NAME."', '".EMPLOYEE_THEMSELF_NAME_FRONT."'
					, (select concat(afFirstName, ' ', afLastName) from apply_forms where afID=otAFID) ) 
					as otEmployeer
					,(select concat(afFirstName, ' ', afLastName) 
					  from apply_forms where afID = otAFID {$andHired}) as otResponsibleEmployeer
  
					FROM `" . self::$dt['tbl'] . "`
				 left join `onboarding_tasks` on ( `otID`=`eoOTID` )
				 where otType='".$type."' and (`otAFID` = " . $afid.' OR `otAFID` = '.EMPLOYEE_THEMSELF_ID.')';
				 
				//echo $q.'  and '.$cond;	
		}	

       if ($tab=='about_me'){ //`otAFID` = " . $afid." OR
			$q = "select *, 
					IF((select concat(afFirstName, ' ', afLastName) from apply_forms 
					  where afID=otAFID {$andHired})
					 = '".EMPLOYEE_THEMSELF_NAME."', '".EMPLOYEE_THEMSELF_NAME_FRONT."'
					, (select concat(afFirstName, ' ', afLastName) from apply_forms 
					 where afID=otAFID {$andHired}) ) 
					as otEmployeer
					,(select concat(afFirstName, ' ', afLastName) 
					  from apply_forms where afID = otAFID {$andHired}) as otResponsibleEmployeer
  
					FROM `" . self::$dt['tbl'] . "`
					
				 left join `onboarding_tasks` on ( `otID`=`eoOTID` )
				 where otType='".$type."' and (( `eoAFID` = ".EMPLOYEE_THEMSELF_ID.") OR "
				 ." ( eoAFID =".$afid." ))";
			//echo $q;
		}		
			  
		 if ($tab=='completed'){
			  // where afID=eoAFID) was changet to where afID=otAFID)
			  //   afID = otAFID) as otResponsibleEmployeer  shows WHO COMPLETED
			  $q = "select *, 
                  IF((select concat(afFirstName, ' ', afLastName) from apply_forms 
				     where afID=otAFID {$andHired})
					 = '".EMPLOYEE_THEMSELF_NAME."', '".EMPLOYEE_THEMSELF_NAME_FRONT."'
					, (select concat(afFirstName, ' ', afLastName) from apply_forms 
					where afID=eoAFID {$andHired}) ) 
					as otEmployeer
				     ,(select concat(afFirstName, ' ', afLastName) 
					  from apply_forms where afID = otAFID) as otResponsibleEmployeer
  
				     FROM `" . self::$dt['tbl'] . "`
              left join `onboarding_tasks` on `otID` = `eoOTID`
             
			  where (
			  otType='".$type."' and (			  
			  otAFID ={$afid} OR `eoAFID` = {$afid} OR ( eoAFID ={$afid}  AND`otAFID` = ".EMPLOYEE_THEMSELF_ID. ' ))
			  )';
			  
			  
		 }
		 
        if (!empty($cond)) {
           $q .= " and " . $cond; // and 
        }
		
		 if (!empty($having)) {
            $q .= "  " . $having; // and 
        }

        if (!empty($order)) {
            $q .= " order by " . $order;
        }
		
		// if ($tab == 'about_me') echo '<br>about_me '.$q.'<br>';
//echo '<br>|'.$cond.'|'.$q.'<br>';
        $data = db::PrepareLoad($q);
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["otDescription"] = htmlspecialchars($item["otDescription"]);
            }
        }
		
        return $data;
		
    }

    public static function CompleteEmployeeOnboardingTasks($completedBy, $tasks = [])
    {
		
		//print_r($tasks);exit;
        $q = 'update employee_onboarding set eoCompletedOn="%s", eoCompletedBy=%d where eoID=%d';
		
        if ($completedBy > 0) {
          //  $q .= ' and (select otAFID from onboarding_tasks where otID=eoOTID) = ' . $completedBy;
        }
        
		 $_date = new DateTime();
        foreach ($tasks as $task) {
            $q1 = sprintf($q, $_date->format('Y-m-d H:i:s'), $completedBy, $task);
			  //echo $q1.' '.$completedBy;exit;
            db::SQL($q1);
        }
    }
	
	 public static function ClearEmployeeOffboardingTasks($afID){
		$q = "SELECT * FROM employee_onboarding LEFT JOIN onboarding_tasks ON otID=eoOTID WHERE 
		otType='off' AND eoAFID={$afID} ";
		$tasks = db::PrepareLoad($q);
		if (! empty($tasks)) {
			foreach ($tasks as $task){
				$sql='DELETE from employee_onboarding WHERE eoID='.$task['eoID'];
				db::SQL($sql);
			}
		}
	 }
	
	 public static function AssignEmployeeOffboardingTasks($afID, $afHiredForPID,$termOrRet) {
		 $termOrRet=strtolower($termOrRet);
		 
        $q = "SELECT * FROM onboarding_tasks LEFT JOIN onboarding_positions ON otID=otpOTID WHERE 
		  otType='off' AND otApplayTo LIKE '%{$termOrRet}%' AND otActive='Yes' AND otpPID=".$afHiredForPID;

        $tasks = db::PrepareLoad($q);

        $respEmpls = [];
		 
        $startDate = new DateTime();
		 if (! empty($tasks)){
			foreach ($tasks as $task) {
				$task["startDate"] = $startDate;
				self::AddRecord($afID, $task);
				if(!in_array($task["otAFID"], $respEmpls)) {
					$respEmpls[] = $task["otAFID"];
				}
			}
			foreach ($respEmpls as $respEmpl) {
				MailController::OffboardingNewEmployee($afID, $respEmpl);
			}
		 }
    }

    public static function AssignEmployeeOnboardingTasks($afID, $afHiredForPID, $startDate = null) {
        $q = "SELECT * FROM onboarding_tasks LEFT JOIN onboarding_positions ON otID=otpOTID WHERE 
		  otType='on' AND otActive='Yes' AND otpPID=".$afHiredForPID;
        $tasks = db::PrepareLoad($q);

        $respEmpls = [];
        if($startDate === null) $startDate = new DateTime();
		if (! empty($tasks)){
			foreach ($tasks as $task) {
				$task["startDate"] = $startDate;
				self::AddRecord($afID, $task);
				if(!in_array($task["otAFID"], $respEmpls)) {
					$respEmpls[] = $task["otAFID"];
				}
			}
			
			foreach ($respEmpls as $respEmpl) {
				
				MailController::OnboardingNewEmployee($afID, $respEmpl);
			}
		}
		
    }

    public static function ChangeEmployeePosition($afID, $newPID) {
        $oldTasks = self::LoadEmployeeOnboardingTasks($afID);
        $newTasks = OnboardingPositionsModel::LoadPositionOnboardingsONBOnly($newPID);

        foreach($oldTasks as $keyOld=>$oldTask) {
            if(!empty($oldTask["eoCompletedOn"]) && ($oldTask["otKeepStatus"] == "Yes")) {
                foreach ($newTasks as $keyNew=>$newTask) {
                    if($newTask["otID"] == $oldTask["otID"]) {
                        unset($newTasks[$keyNew]);
                    }
                }
            } else {
                self::RemoveRecord($oldTask["eoID"]);
                unset($oldTasks[$keyOld]);
            }
        }

        $respEmpls = [];
        foreach ($newTasks as $newTask) {
            self::AddRecord($afID, $newTask);
            if(!in_array($newTask["otAFID"], $respEmpls)) {
                $respEmpls[] = $newTask["otAFID"];
            }
        }

        foreach ($respEmpls as $respEmpl) {
            MailController::OnboardingEmployeePositionChanged($afID, $respEmpl);
        }
    }

    public static function ChangeOnboardingTaskTimeFrame($otID, $oldTFNum, $oldTFDim, $newTFNum, $newTFDim) {
        $q = 'select * from employee_onboarding where eoOTID='.$otID;
        $tasks = self::PrepareLoad($q);

        if(is_array($tasks)) {
            $zeroTime = new DateTime();

            $oTF = self::ConvertTimeFrameToTimestamp($oldTFNum, $oldTFDim, $zeroTime);
            $nTF = self::ConvertTimeFrameToTimestamp($newTFNum, $newTFDim, $zeroTime);
            $tfDiv = $nTF - $oTF;

            foreach ($tasks as $task) {
                $oldDate = strtotime($task["eoTimeFrame"]);
                $newDate = $oldDate + $tfDiv;
                $q = 'update employee_onboarding set eoTimeFrame="'.date('Y-m-d H:i:s', $newDate).'" where eoID='.$task["eoID"];
                db::SQL($q);
            }
        }
    }

    public static function ConvertTimeFrameToTimestamp($tfNum, $tfDim, $startDate = null) {
        if(isset($startDate) && (!empty($startDate))) {
		
			  if (!is_string($startDate))
            $startDate = $startDate->format(GLOBAL_PHP_DATETIME);
            $expDate = strtotime($startDate, time());
            $timeframe = $expDate;
        } else {
            $startDate = null;
            $timeframe = time();
        }

        switch ($tfDim) {
            case 'day(s)': $timeframe = strtotime("+{$tfNum} days", $timeframe); break;
            case 'week(s)': $timeframe = strtotime("+{$tfNum} weeks", $timeframe); break;
            case 'month(s)': $timeframe = strtotime("+{$tfNum} months", $timeframe); break;
            case 'year(s)': $timeframe = strtotime("+{$tfNum} years", $timeframe); break;
        }

        return $timeframe;
    }

    public static function AddRecord($afID, $data) {
		$q="SELECT * FROM apply_forms WHERE afID=".$afID;
		$_r=db::PrepareLoadSingleRow($q);
			 
		 if ($data['otType']=='off'){
			 $q="SELECT * FROM apply_forms WHERE afID=".$afID;
			 $_r=db::PrepareLoadSingleRow($q);
			 if (!isset($_r['afDateLeaving']) || $_r['afDateLeaving']=='') return;
			 $date = DateTime::createFromFormat('Y-m-d',$_r['afDateLeaving']);
			 $ti=  $data["otTimeFrameNum"].' '.$data["otTimeFrameDim"];						
			 $date->add(date_interval_create_from_date_string($ti));
			 $timeframe=strtotime($date->format(MYSQL_DATE_TIME));			
		 }
		 else
			$timeframe = self::ConvertTimeFrameToTimestamp($data["otTimeFrameNum"], $data["otTimeFrameDim"], $_r["afStartDate"]);
		 
		 $d=new DateTime();
		 $today=$d->format(MYSQL_DATE_TIME);
			
		 if ($data['otTaskOrDocsToSign']=='docstosign'){
			$docs=OnboardingModel::getLastOnboardings($data['otID']);
		
			$_docs=array();
			foreach ($docs as $k=>$d){
				$_docs[] = $d['ofID'];
			}
			$docs_str=implode('|',$_docs);

			$q = 'insert into employee_onboarding 
			(eoAFID, eoOTID, eoTimeFrame, eoActive, eoAdded,eoDocsToSign,eoDocsRemainToSign) 
			values (%d, %d, "%s", "Yes", "%s","%s","%s")';
			$q = sprintf($q, $afID, $data["otID"], 
			date("Y-m-d H:i:s", $timeframe),$today,$docs_str,$docs_str); 
		 }
		 else{
			$q = 'insert into employee_onboarding 
			(eoAFID, eoOTID, eoTimeFrame, eoActive, eoAdded) 
			values (%d, %d, "%s", "Yes", "%s")';
			$q = sprintf($q, $afID, $data["otID"], date("Y-m-d H:i:s", $timeframe),$today);
		 }
		 
        db::SQL($q);
    }

    public static function RemoveRecord($eoID) {
        $q = 'delete from `'.self::$dt["tbl"].'` where `'.self::$dt["key"].'`='.intval($eoID);
        db::SQL($q);
    }
	
	
}
