<?php

/** The CompanyOffice controller */
class ManagerRtcureportsFrontController_p extends AppController
{

    const __DUMP__ = false;
    const __PAGESIZE__ = 20;

    public static $id, $data, $pg;
	
	public static function delReport(){
		if (!isset($_SESSION['webapp-admin-token']) ) {
			$ret=array();
			$ret['result']='bad';$ret['message']='There was a problem to save new hours.';
			echo json_encode($ret);
			exit;
		}
		$q="DELETE FROM p_timeclock_worksegment WHERE ptw_TMCWORK_ID=".$_GET['rID'];
		$res=db::SQL($q);
		
		$ret=array();
		if ($res) {$ret['result']='ok';}
		else {$ret['result']='bad';$ret['message']='There was a problem to delete report.';}
		echo json_encode($ret);
	}
	
	
	
	public static function SaveAdminHours(){
		if (!isset($_SESSION['webapp-admin-token']) ) {
			$ret=array();
			$ret['result']='bad';$ret['message']='There was a problem to save new hours.';
			echo json_encode($ret);
			exit;
		}
		
		$id=$_GET['id'];
		$h= $_GET['h'];
		$m = $_GET['m'];
		$d = val::convDate('m-d-Y',MYSQL_DATE,$_GET['day']);
		$weekstartdate=$_GET['weekstartdate'];
		$weekenddate=$_GET['weekenddate'];
		
		$hours=$h+$m/100;
		$type=$_GET['type'];
		$EmpNumber=$_GET['empNmb'];
		
		
		if ($id==0){
			$q="INSERT INTO  p_timeclock_worksegment SET Updated_On='".date('Y-m-d H:i:s')."', Type='',EmployeeNumber='{$EmpNumber}',TimeIn='{$d} 00:00:00',TimeOut='{$d} 00:00:00',HousrWorkingAdmin={$hours}, TypeAdmin='{$type}'";
			
			$sql="SELECT * FROM  p_listofweeks WHERE EMPLOYEE_NUMBER='{$EmpNumber}' AND STARTDATE='{$weekstartdate}'";
			
			$__res=db::PrepareLoad($sql);
			if ($__res == false){
				$qry="SELECT * FROM apply_forms WHERE afEmplNumber='{$EmpNumber}'";
				$r1=db::PrepareLoadSingleRow($qry);
				if ($r1 !== false){
					$qry1="INSERT INTO  p_listofweeks SET  EMPLOYEE_NUMBER='{$EmpNumber}',STARTDATE='{$weekstartdate}',ENDDATE ='{$weekenddate}',
					EMPLOYEE_ID={$r1['afID']}, MANAGER_ID={$r1['afRMID']}, SIGNED_STATUS='No'";
					$_res=db::SQL($qry1);
				}
			}
		}
		else
			$q="UPDATE p_timeclock_worksegment SET 	HousrWorkingAdmin={$hours}, TypeAdmin='{$type}' WHERE ptw_TMCWORK_ID={$id}";
	
		$res=db::SQL($q);

		$ret=array();
		if ($res) {$ret['result']='ok';$ret['sql']=$q;}
		else {$ret['result']='bad';$ret['message']='There was a problem to save new hours.';}
		
		echo json_encode($ret);
	}
	
	
    public static function ListUnsignedReports()
      {
	  if (isset($_GET['from']) && $_GET['from']=='admin'){
		$usrId=1; 
	  }
	  else
      $usrId = token::GetIdAndReset("webapp-login-token", "session", "PT5M");

      if($usrId !== false) {
          $user = ProfileModel::dataById($usrId);
      } else {
          $user = null;
      }



      $loggedinuserid=$user["afID"];
      $employeenumber=$user["afEmplNumber"];

      if($loggedinuserid==""){ cf::Redirect(WP); }

      //code to get current week start date and end date//
      date_default_timezone_set('America/New_York');//initiate timezone//

      $managerid=$loggedinuserid;//declaring managerid//

      $sqlemployeecountquery = "select afID from apply_forms where `afRMID`='".$managerid."'";
      $employeecountresults = db::SQL($sqlemployeecountquery);
      $totalnumberofrows = db::NumRows($employeecountresults);

      if($totalnumberofrows>0)
        {
          $conn=" and (";
          $sqecnt=1;
          $sqlemployeequery = "select afID from apply_forms where `afRMID`='".$managerid."'";
          $empresults = db::PrepareLoad($sqlemployeequery);
          foreach($empresults as $empvalue)
              {
                  $conn.="`EMPLOYEE_ID`='".$empvalue["afID"]."'";
                  if($sqecnt<$totalnumberofrows){ $conn.=" or "; }
                  $sqecnt++;
              }
          $conn.=")";
        }
        else
            {
              $conn=" and `SIGNED_STATUS`='Never'";
            }
	
		$lastSunday = date('Y-m-d', strtotime('last Sunday', strtotime(date('Y-m-d'))));
      $sqlquery = "select PLOW_ID, STARTDATE, ENDDATE, EMPLOYEE_ID, EMPLOYEE_NUMBER, SIGNED_STATUS from p_listofweeks where 
	  ENDDATE <='".$lastSunday."' AND `SIGNED_STATUS`='NO'$conn";
	
      $results = db::PrepareLoad($sqlquery);
      ManagerRtcureportsView_p::RenderUndignedReportList($results);

      }


   public static function UpdateReportstatus($reportstatus,$peoprtid)
    {
        //code to get current week start date and end date//
        date_default_timezone_set('America/New_York');//initiate timezone//

        $usrId = token::GetIdAndReset("webapp-login-token", "session", "PT5M");
        if($usrId !== false) { $user = ProfileModel::dataById($usrId); } else { $user = null; }

        $loggedinuserid=$user["afID"];
        $employeenumber=$user["afEmplNumber"];

		if($loggedinuserid==""  && !isset($_SESSION['webapp-admin-token'])){ cf::Redirect(WP); }

          if(isset($reportstatus)) //=="Yes")
            {
                  $id=$peoprtid;//base64_decode($peoprtid);
                  $sqlemployeecountquery = "select * from p_listofweeks where `PLOW_ID`='{$id}'";//and `SIGNED_STATUS`='No'";
				  //echo '--->'.$sqlemployeecountquery;exit;
                  $employeecountresults = db::SQL($sqlemployeecountquery);
                  $totalnumberofrows = db::NumRows($employeecountresults);
				  $IP=cf::get_ip_address();
				 
                  if($totalnumberofrows>0)
                    {	$is_admin=0;
						if (isset($_SESSION['webapp-admin-token'])) $is_admin=1;
						
                        $updatedate=date("Y-m-d H:i:s");
                        $updatequery="UPDATE p_listofweeks SET `Is_admin`={$is_admin}, `SIGNED_STATUS`='{$reportstatus}',`SIGN_IP`='{$IP}', 
						`SIGNEDBY_MANAGERID`='".$loggedinuserid."', `SIGNEDON_DATE`='".$updatedate."' WHERE `PLOW_ID`='".$id."'";
						
                        db::SQL($updatequery);
                        $status="Success";
                    }
                    else
                        {
                          $status="Error";
                        }
            }
            else
                {
                    $status="Error";
                }

          return $status;

    }


  public static function ListWorkingHours($empnumber,$sdate)
    {
	$errormessage='';
	  if (isset($_GET['from']) && $_GET['from']=='admin'){
			$q="SELECT * FROM apply_forms WHERE afEmplNumber='{$_GET['empnumber']}'";
			$res=db::PrepareLoadSingleRow($q);
			if ($res==false) $usrId=false;
			else
			$usrId=$res['afID']; 
	  }
	  else
      $usrId = token::GetIdAndReset("webapp-login-token", "session", "PT5M");
  
      if($usrId !== false) {
          $user = ProfileModel::dataById($usrId);
		 
      } else {
          $user = null;
      }

      $loggedinuserid=$user["afID"];
      $employeenumber=$user["afEmplNumber"];

      if($loggedinuserid=="" && !isset($_SESSION['webapp-admin-token'])) { cf::Redirect(WP); }

      //--function to get userdata--//
      $sqluser = "select afStartDate, afFirstName, afLastName from apply_forms where `afEmplNumber`='".$empnumber."'";
      $results = db::PrepareLoadSingleRow($sqluser);
      $startworkdate=$results["afStartDate"];
      $empname=$results["afFirstName"]." ".$results["afLastName"];

          //code to get current week start date and end date//
          date_default_timezone_set('America/New_York');//initiate timezone//

          $getdate=$sdate;//base64_decode($sdate);


          if(@$_GET["nameyear"]!="" and @$_GET["namemonth"]!="" and @$_GET["nameday"]!="")
            {
                $getsearchdateint=@$_GET["nameyear"]."-".@$_GET["namemonth"]."-".@$_GET["nameday"];
                $getsearchdateint=date_create($getsearchdateint);
                $getsearchdate=date_format($getsearchdateint,"Y-m-d");
                $currentdatevar=$getsearchdate;
            }
            else
                {
                  if(@$_GET["sdate"]=="" and @$_GET["edate"]=="")
                      {
                          $getsearchdate=$getdate;
                          $currentdatevar=$getdate;
                      }
                      else
                          {
                            $getsearchdate=$_GET["sdate"];
                            $currentdatevar=$_GET["sdate"];
                          }
                }

		
		  if($getsearchdate>date("Y-m-d"))
                      {

                          $currentdatevar=date("Y-m-d");

                          $getweekdatesdata=ManagerRtcureportsFrontController_p::calculatestartdateandenddateofweek($currentdatevar);

                          //$errormessage="You can not select search date in future.";

                          $weekstartdate=$getweekdatesdata["weekstartdate"]." 00:00:00";//week first date//
                          $weekenddate=$getweekdatesdata["weekenddate"]." 23:59:59";//week end date//

                          $startdate=$getweekdatesdata["weekstartdate"];
                          $enddate=$getweekdatesdata["weekenddate"];
                      }
                      else
                          {

                            $getweekdatesdata=ManagerRtcureportsFrontController_p::calculatestartdateandenddateofweek($currentdatevar);

                              $errormessage="";

                              if(@$_GET["sdate"]=="" and @$_GET["edate"]=="")
                                  {
                                      $weekstartdate=$getweekdatesdata["weekstartdate"]." 00:00:00";//week first date//
                                      $weekenddate=$getweekdatesdata["weekenddate"]." 23:59:59";//week end date//

                                      $startdate=$getweekdatesdata["weekstartdate"];
                                      $enddate=$getweekdatesdata["weekenddate"];
                                 }
                                 else
                                    {
                                      $weekstartdate=$_GET["sdate"]." 00:00:00";//week first date//
                                      $weekenddate=$_GET["edate"]." 23:59:59";//week end date//

                                      $startdate=$_GET["sdate"];
                                      $enddate=$_GET["edate"];
                                    }

                        }

              $prevweekstartdate=$stop_date = date('Y-m-d', strtotime($startdate . ' -7 day'));
              $prevweekenddate=$stop_date = date('Y-m-d', strtotime($startdate . ' -1 day'));

              $nextweekstartdate=$stop_date = date('Y-m-d', strtotime($enddate . ' +1 day'));
              $nextweekenddate=$stop_date = date('Y-m-d', strtotime($nextweekstartdate . ' +6 day'));

              $currentdate=date("Y-m-d");


              if($currentdate <= $enddate)
                {
                  $nextbutton="no";
                }
                else
                    {
                        $nextbutton="yes";
                    }

          $joiningdate=$startworkdate;
          if($enddate<$joiningdate)
            {
              $previousbutton="no";
            }
            else
                {
                  $previousbutton="yes";
                }

          $showstartdateintermediate=$timein=date_create($startdate);
          $showstartdate=date_format($showstartdateintermediate,"m/d/Y");

          $showenddateintermediate=$timein=date_create($enddate);
          $showenddate=date_format($showenddateintermediate,"m/d/Y");

         $sqlsorquery = "select SIGNED_STATUS from p_listofweeks where `STARTDATE`='".$startdate."' and `ENDDATE`='".$enddate."' and `EMPLOYEE_NUMBER`='".base64_decode($empnumber)."'";
         $sorresults = db::PrepareLoad($sqlsorquery);
         if(@$sorresults)
            {
                foreach($sorresults as $sorresult){ $singedoffstatus=$sorresult["SIGNED_STATUS"]; }
            }
            else
                {
                   $singedoffstatus="";
                }

          $sqlquery = "select * from p_timeclock_worksegment where `EmployeeNumber`='".$empnumber."' and (`TimeIn` Between '".$weekstartdate."' and '".$weekenddate."')
		  ORDER BY TimeIn ASC";
          $results = db::PrepareLoad($sqlquery);

		  if ($results===false) $errormessage='';
          ManagerRtcureportsView_p::RenderWorkingHourList($empname,$errormessage,$singedoffstatus,$results,$showstartdate,$showenddate,$prevweekstartdate,$prevweekenddate,$previousbutton,$nextweekstartdate,$nextweekenddate,$nextbutton);
    }


    public static function calculatestartdateandenddateofweek($curendatevariable=NULL)
              {
                if($curendatevariable!="") { $currentdateinf=$curendatevariable; } else { $currentdateinf=date("Y-m-d");}
                $date=date_create($currentdateinf);
                $Current=date_format($date,"N");

                //$Current = Date('N');
                $DaysToSunday = 7 - $Current;
                $DaysFromMonday = $Current - 1;
                $Sunday = Date('Y-m-d', StrToTime($currentdateinf. "+ {$DaysToSunday} Days"));
                $Monday = Date('Y-m-d', StrToTime($currentdateinf. "- {$DaysFromMonday} Days"));

                $resultwsed=array('weekstartdate'=>$Monday, 'weekenddate'=>$Sunday);

                return $resultwsed;

              }



}
