<?php

/** The CompanyOffice controller */
class TimeClockFrontController extends AppController
{

    const __DUMP__ = false;
    const __PAGESIZE__ = 20;

    public static $id, $data, $pg;


    public static function ListWorkingHours()
    {

      $usrId = token::GetIdAndReset("webapp-login-token", "session", "PT5M");
      if($usrId !== false) {
          $user = ProfileModel::dataById($usrId);
      } else {
          $user = null;
      }



      $loggedinuserid=$user["afID"];
      $employeenumber=$user["afEmplNumber"];

      if($loggedinuserid==""){ cf::Redirect("https://develop.metavisia.com"); }

          //code to get current week start date and end date//
          date_default_timezone_set('America/New_York');//initiate timezone//
		  $req=array();
		  $req['date']='';
		  $req['correction']='';
		  $req['reason']='';
		  // cf::pr($user);
		  
		 
		  
		  if(isset($_POST['save-request'])) { //cf::pr($_POST);
			    if (isset($_POST['date']) && $_POST['date']=='') {
					form::$error['date'][]='Date is required field.';	
				}
				if (isset($_POST['reason']) && $_POST['reason']=='') {
					form::$error['reason'][]='Reason is required field.';
				}
			    if (isset($_POST['correction']) && $_POST['correction']=='') {
					form::$error['correction'][]='Correction is required field.';	
				}
				if (isset($_POST['reason']) && $_POST['reason']=='') {
					form::$error['reason'][]='Reason is required field.';
				}
				
				if (count(form::$error) >0 ) {
					msg::set('There are errors in your timeclock change request.','error');

					$req['date']=$_POST['date'];
					$req['correction']=$_POST['correction'];
					//$req['reason'] = $_POST['reason'];
				}
			    else{
					
					$date = val::convDate( USER_DATE,MYSQL_DATE,$_POST['date']);
					$week = TimeClockFrontController::calculatestartdateandenddateofweek($date);
					$weekStr = val::convDate(MYSQL_DATE,USER_DATE,$week['weekstartdate']).' - '.val::convDate(MYSQL_DATE,USER_DATE,$week['weekenddate']);
					
					$date_for = $_POST['date'];
					
					$correction =filter_input(INPUT_POST, "correction", FILTER_SANITIZE_STRING);
					//$reason = filter_input(INPUT_POST, "reason", FILTER_SANITIZE_STRING);
					$userID = AppModel::$usr['afID'];
					
					$now=date('Y-m-d H:i:s');
					
					$q="INSERT INTO timeclock_change_requests SET 
						tcrAFID = {$userID},
						tcrDate ='{$date}',
						tcrWeek = '{$weekStr}' ,
						tcrDateTimeSent = '{$now}',
						tcrRequest = '{$correction}',
						tcrState = 'pending'";
						
						//tcrReason = '{$reason}',
			
			
					$res=db::SQL($q);
					
					if($res) {
						//********  MAIL TO ADMIN *******
						$subject='Request for time report correction';
						$mail_body="
						The employee ".AppModel::$usr['afFirstName']." ".AppModel::$usr['afLastName']
						." has requested the following report correction:<br><br>
						Report date: {$date_for} (week {$weekStr} )<br>
						Correction required: {$correction}<br>
						<br><br>
						".MAIL_FOOTER_HTML;
						
						//Reason: {$reason}
						
						@cf::sendMail(implode(',',explode("\n",$GLOBALS['ADMIN_MAILS'])),
									$subject, 
									$mail_body, 
									$GLOBALS['EMAIL_MAILER_NAME'], 
									$GLOBALS['EMAIL_MAILER_ADDRESS']
								);
						
						msg::set('Your timeclock change request has been sent.','success');
					}
					else
						msg::set('For some reason you request was not saved.Please contact admin.','error');
				
				}
				
		  }
		  

          if(@$_GET["nameyear"]!="" and @$_GET["namemonth"]!="" and @$_GET["nameday"]!="")
            {
                $getsearchdateint=@$_GET["nameyear"]."-".@$_GET["namemonth"]."-".@$_GET["nameday"];
                $getsearchdateint=date_create($getsearchdateint);
                $getsearchdate=date_format($getsearchdateint,"Y-m-d");
            }
            else
                {
                $getsearchdate=date("Y-m-d");
                }


          if(@$_GET["nameyear"]!="" and @$_GET["namemonth"]!="" and @$_GET["nameday"]!="")
              {
                $currentdatevar=$getsearchdate;
              }
              else
                  {
                      $currentdatevar=date("Y-m-d");
                  }



		 /********************
          if($getsearchdate<$user["afStartDate"])
              {
                  $currentdatevar=date("Y-m-d");

                  $getweekdatesdata=TimeClockFrontController::calculatestartdateandenddateofweek($currentdatevar);

                  $errormessage="Selected date is smaller than employee's start date so data is not available.";

                  $weekstartdate=$getweekdatesdata["weekstartdate"]." 00:00:00";//week first date//
                  $weekenddate=$getweekdatesdata["weekenddate"]." 23:59:59";//week end date//

                  $startdate=$getweekdatesdata["weekstartdate"];
                  $enddate=$getweekdatesdata["weekenddate"];
              }
              else
				  **************/
			  
				  if($getsearchdate>date("Y-m-d"))
                      {

                          $currentdatevar=date("Y-m-d");

                          $getweekdatesdata=TimeClockFrontController::calculatestartdateandenddateofweek($currentdatevar);

                          $errormessage="You can not select search date in future.";

                          $weekstartdate=$getweekdatesdata["weekstartdate"]." 00:00:00";//week first date//
                          $weekenddate=$getweekdatesdata["weekenddate"]." 23:59:59";//week end date//

                          $startdate=$getweekdatesdata["weekstartdate"];
                          $enddate=$getweekdatesdata["weekenddate"];
                      }
                      else
                          {

                            $getweekdatesdata=TimeClockFrontController::calculatestartdateandenddateofweek($currentdatevar);

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


              if($currentdate<$enddate)
                {
                  $nextbutton="no";
                }
                else
                    {
                        $nextbutton="yes";
                    }

          $joiningdate=$user["afStartDate"];
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

         $sqlsorquery = "select * from p_listofweeks where `STARTDATE`='".$startdate."' and `ENDDATE`='".$enddate."'
		 AND 	EMPLOYEE_NUMBER = '{$employeenumber}'";
         $sorresults = db::PrepareLoad($sqlsorquery);
         if(@$sorresults)
            {
                foreach($sorresults as $sorresult){ $singedoffstatus=$sorresult["SIGNED_STATUS"]; }
            }
            else
                {
                   $singedoffstatus="";
                }


          $sqlquery = "select * from p_timeclock_worksegment where `EmployeeNumber`='".$employeenumber."' and (`TimeIn` Between '".$weekstartdate."' and '".$weekenddate."')";
          $results = db::PrepareLoad($sqlquery);
          TimeClockView::RenderWorkingHourList($errormessage,$singedoffstatus,$results,$showstartdate,$showenddate,$prevweekstartdate,
		  $prevweekenddate,$previousbutton,$nextweekstartdate,$nextweekenddate,$nextbutton,$req,$user);
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
