<?php

/** The Orders model */
class DaysOffFrontModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "positions", "key" => "pID", "pfx" => "p", "controller" => "PositionController");
    static $mainTblFields = array("pName", "pSkills", "pSalaryType", "pSalaryFrom", "pSalaryTo",
	"pActive","pWelcomeEmail","pPhisician","pVacationDays","pCEDays");
    static $fldLbl = array("pID" => "ID", "pName" => "Position", "pSkills" => "Required skills", "pSalaryType" => "Salary type",
	"pSalaryFrom" => "Salary from", "pSalaryTo" => "Salary to","pWelcomeEmail"=>"Email Template",
	"pVacationDays"=>"Annual paid days Vacation","pCEDays"=>"Annual paid days CE");
    static $addonFieldsByTables;
    static $postOverrided = array();

    public static function getUsedDays($userID,$year,$vacType,$paidUnpaid='paid'){
		
		foreach($vacType as $k=>$vt){
			$vacType[$k]=" vrType= '".$vt."' ";
		}
		
		$vac=implode(' OR ',$vacType);
		if($vac !='') $vac=' AND ('.$vac.')'; else $vac='';
		
		$q="SELECT sum(vrDays) as days  FROM vacation_requests WHERE vrYear='{$year}' AND vrAFID={$userID} 
		{$vac} AND 	vrPaydUnpaid='{$paidUnpaid}'
		AND ( vrState	='approved')";
		//vrState	='pending' OR vrState='used' OR
		$res=db::PrepareLoadSingleRow($q);
		
		if (empty($res['days'])) $days=0; else $days=$res['days'];
		
		return $days;
	}
	
	public static function getUsedDaysPerMonth($userID,$year,$month,$vacType,$paidUnpaid='paid'){
		
		foreach($vacType as $k=>$vt){
			$vacType[$k]=" vrType= '".$vt."' ";
		}
		
		$vac=implode(' OR ',$vacType);
		if($vac !='') $vac=' AND ('.$vac.')'; else $vac='';
		
		$q="SELECT *  FROM vacation_requests WHERE vrYear='{$year}' AND vrAFID={$userID} 
		{$vac} AND 	vrPaydUnpaid='{$paidUnpaid}'
		AND (vrState='used' OR vrState='approved')";
		
		$res=db::PrepareLoad($q);
		if (empty($res)) $res=array();
		$days=0;
		foreach ($res as $k=>$r){
			$d=date('Y-m',strtotime($year.'-'.$month.'-1'));
			if (strpos( $r['vrDateStart'],$d) !==false || strpos($r['vrDateEnd'],$d)!==false){
				$days_array=explode(',',$r['vrRealDays']);
				
				foreach ($days_array as $day){
					if(strpos($day,$d)!== false) $days++;
				}
			}
		}
		
		return $days;
	}
	
	public static function getDefaultDays($userID,$year,$vacType){
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		
		if (in_array($vacType,array('Vacation','Sick leave'))) $daysPerYear=cf::getEmployeePaidDaysOff($year,$userID);//  //$user['afVacationDays'];
		else if($vacType=='CE') $daysPerYear=$user['afCEDays'];
		else $daysPerYear='N/A';

		if(strpos($user['afStartDate'],(string)$year)!== false && $daysPerYear!=0) 
		  $daysPerYear=round(cf::getDaysToAsPercent($user['afStartDate']) * $daysPerYear +0.5);
	   
	   return $daysPerYear;
	}
	
	
	public static function getBODatesAsJS($userID,$year){
		 $res= self::getBlockOutDates($userID,$year);
		 $ret=array();
		 if ($res==false) $res=array();
        foreach ($res as $k=>$r){
		   $days= cf::getWorkdaysBetween($r['bodDateStart'], $r['bodDateEnd']);
		   
		   foreach($days as $j=>$d){
				$ret[]='"'.val::convDate(MYSQL_DATE,USER_DATE,$year.'-'.$d).'"';
		   }
	   }
	   
		return implode(',',$ret);
	}
	
	
	public static function getBlockOutDates($userID,$year,$all=false){
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		
		if ($all==false)
		$q="SELECT * FROM bod_days WHERE bodOffices LIKE '%{$user['afCOID']}%'  AND bodPositions LIKE '%{$user['afHiredForPID']}%'
			AND bodYear='{$year}' ORDER by bodDateStart ASC";	
		else 
			$q="SELECT * FROM bod_days WHERE bodOffices LIKE '%{$user['afCOID']}%'  AND bodPositions LIKE '%{$user['afHiredForPID']}%'
			 ORDER by bodDateStart ASC";
			 
		$res=db::PrepareLoad($q);	
		return $res;
	}
	
	
	public static function getDaysOffDates($userID,$year){
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		
		$q="SELECT * FROM nonworking_days WHERE nwdOffices LIKE '%{$user['afCOID']}%'
			AND nwdYear='{$year}'";
		$res=db::PrepareLoad($q);	
		return $res;
	}
	
	public static function getDaysOffDatesAsJS($userID,$year){
		$res= self::getDaysOffDates($userID,$year);
		 $ret=array();
		 if ($res==false) $res=array();
        foreach ($res as $k=>$r){
		   $days= cf::getWorkdaysBetween($r['nwdDateStart'], $r['nwdDateEnd']);
		   
		   foreach($days as $j=>$d){
				$ret[]='"'.val::convDate(MYSQL_DATE,USER_DATE,$year.'-'.$d).'"';
		   }
	   }
	   
		return implode(',',$ret);
	}
	
	
	
	public static function getVacDates($userID,$year){
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$userID} AND vrState='approved'
			AND vrYear='{$year}'";
		$res=db::PrepareLoad($q);	
		return $res;
	}
	
	
	public static function getVacDatesFuture($userID,$year){
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		$today=date('Y-m-d');
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$userID} AND vrState='approved'
			AND vrYear='{$year}' AND vrDateEnd >'{$today}' ";
		$res=db::PrepareLoad($q);
		return $res;
	}
	
	public static function getVacDatesAsJSFuture($userID,$year){
		 $res= self::getVacDatesFuture($userID,$year);
		 $ret=array();
		 if ($res==false) $res=array();
        
		 foreach ($res as $k=>$r){
		   $days= explode(',',$r['vrRealDays']);
		   foreach($days as $j=>$d){
				$ret[]='"'.val::convDate(MYSQL_DATE,USER_DATE,$d).'"';
		   }
	   }
	   
		return implode(',',$ret);
	}
	
	public static function getVacDatesAsJS($userID,$year){
		 $res= self::getVacDates($userID,$year);
		 $ret=array();
		 if ($res==false) $res=array();
        
		 foreach ($res as $k=>$r){
		   $days= explode(',',$r['vrRealDays']);
		   foreach($days as $j=>$d){
				$ret[]='"'.val::convDate(MYSQL_DATE,USER_DATE,$d).'"';
		   }
	   }
	   
		return implode(',',$ret);
	}
	
	
	public static function getPaidDaysPerMonth($userID,$year,$month){
		
		$q="SELECT *  FROM vacation_requests WHERE vrYear='{$year}' AND vrAFID={$userID} 
		AND 	vrPaydUnpaid='paid' AND (vrState='used' OR vrState='approved')";
		
		$res=db::PrepareLoad($q);
		if (empty($res)) $res=array();
		$days=0;
		foreach ($res as $k=>$r){
			$d=date('Y-m',strtotime($year.'-'.$month.'-1'));
			if (strpos( $r['vrDateStart'],$d) !==false || strpos($r['vrDateEnd'],$d)!==false){
				$days_array=explode(',',$r['vrRealDays']);
				
				foreach ($days_array as $day){
					if(strpos($day,$d)!== false) $days++;
				}
			}
		}
		
		return $days;	
		
	}
	
	public static function getUnpaidDaysPerMonth($userID,$year,$month){
		$q="SELECT *  FROM vacation_requests WHERE vrYear='{$year}' AND vrAFID={$userID} 
		AND vrPaydUnpaid='unpaid' AND (vrState='used' OR vrState='approved')";
		
		$res=db::PrepareLoad($q);
		if (empty($res)) $res=array();
		$days=0;
		foreach ($res as $k=>$r){
			$d=date('Y-m',strtotime($year.'-'.$month.'-1'));
			if (strpos( $r['vrDateStart'],$d) !==false || strpos($r['vrDateEnd'],$d)!==false){
				$days_array=explode(',',$r['vrRealDays']);
				
				foreach ($days_array as $day){
					if(strpos($day,$d)!== false) $days++;
				}
			}
		}
		
		return $days;	
	}
	
	
	
	
	
	public static function getUnpaidDatesAsJS($userID,$year,$vacType=array()){
		
		foreach($vacType as $k=>$vt){
			$vacType[$k]=" vrType= '".$vt."' ";
		}
		
		$vac=implode(' OR ',$vacType);
		
		if($vac !='') $vac=' AND ('.$vac.')'; else $vac='';
		
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		
		$q="SELECT * FROM vacation_requests WHERE vrAFID={$userID} AND (vrState='approved' OR vrState='used')
			AND vrPaydUnpaid='unpaid' AND vrYear='{$year}' {$vac}";
		
		$res=db::PrepareLoad($q);
		if($res==false) $res=array();
		$ret=array();
       foreach ($res as $k=>$r){
		   $unp=explode(',',$r['vrRealDays']);
		   foreach($unp as $j=>$u){
				$ret[]='"'.val::convDate(MYSQL_DATE,USER_DATE,$u).'"';
		   }
	   }
		return implode(',',$ret);
	}
	
	
	public static function getWorkingDaysBetweenFull($userID,$startDate,$endDate){
		$days=self:: getWorkingDaysBetween($userID,$startDate,$endDate);
		$year=date('Y',strtotime($startDate));
		foreach ($days as $k=>$d){
			$days[$k]=$year.'-'.$d;
		}
		return $days;
	}
	
	public static function getWorkingDaysBetween($userID,$startDate,$endDate){
		if(isset(AppModel::$usr) && AppModel::$usr['afID']==$userID){
			$user=AppModel::$usr;
		}
		else {
			$q="SELECT * FROM apply_forms  WHERE afID=".$userID;
			$user=db::PrepareLoadSingleRow($q);
		}
		$year=date('Y',strtotime($startDate));
		$q="SELECT * FROM nonworking_days WHERE nwdOffices LIKE '%{$user['afCOID']}%' AND nwdYear='{$year}'";
		$res=db::PrepareLoad($q);
		if($res==false) $res=array();
		$patron=array();
		foreach ($res as $k=>$r){
			if ($r['nwdIsInterval']=='single')  $patron[]= date('m-d',strtotime($r['nwdDateStart']));
			else {
				$start = strtotime($r['nwdDateStart']);
				$end   = strtotime($r['nwdDateEnd']);
				for ($i = $start; $i <= $end; $i = strtotime("+1 day", $i)){
					$patron[]= date('m-d', $i);
				}
			}
		}

		$days=cf::getWorkdaysBetween($startDate,$endDate,FALSE,$patron);
		
		return $days;
	}
}
