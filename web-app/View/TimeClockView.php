<?php
class TimeClockView extends ApplicationView
{

    public static function RenderWorkingHourList($errormessage,$singedoffstatus,$results,$weekstartdate,$weekenddate,$prevweekstartdate,
	$prevweekenddate,$previousbutton,$nextweekstartdate,$nextweekenddate,$nextbutton,$req=array(),$user)
      {

        //error_reporting(E_ERROR | E_PARSE);

        echo '<style>.topinfo {
    display: none !important;
}

.top-heading {
    background-color: #fff;
    margin: 0 -15px;
    border-top: 1px solid #e5e5e5;
    border-bottom: 1px solid #d4d8d8;
    padding: 15px 15px 0;
}

.top-heading .breadcrumb {
    margin-bottom: 0;
    margin-top: 20px;
    background-color: #fff;
    font-size: 17px;
    line-height: 40px;
    color: #2c3e50;
    padding: 0 0 0 30px;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 1px;
}

.top-heading .breadcrumb li {
    border-bottom: 4px solid #27ae60;
}

.pagination-top {
    overflow: hidden;
    padding: 20px;
    background-color: #fff;
    margin-top: 20px;
    border: 1px solid #e8e8e8;
    border-bottom-width: 0px;
}

.from-to-date {
    width: calc(78% - 2px);
    float: left;
    text-align: center;
    line-height: 34px;
}

.from-to-date span {
    display: inline-block;
    padding-right: 10px;
    color: #327ab7;
    font-size: 18px;
    font-weight: 700;
}

table {
    background-color: #fff;
}

table tbody tr:nth-child(odd) {
    background-color: #fff;
}

table tr th, table tr td {
    padding: 4px;
    text-align: center;
}

.search-date-starttoend {
    padding: 20px 0;
    border: 1px solid #e8e8e8;
    border-bottom-width: 0px;
    background-color: #fff;
    overflow: hidden;
    position: relative;
}

.search-date-starttoend form {
    float: right;
    width: 45%;
}

.search-date-starttoend label {
    float: left;
    font-size: 16px;
    width: 22%;
    line-height: 34px;
    margin: 0;
    font-weight: 700;
}

.search-date-starttoend label ~ select.form-control,
.search-date-starttoend label ~ select.form-control ~ input.btn {
    width: calc(23% - 20px);
    margin-right: 15px;
    float: left;
}

.search-date-starttoend label ~ select.form-control:nth-child(2) {
    width: 25%;
    padding: 6px 7px;
}

.search-date-starttoend label ~ select.form-control:nth-child(3) {
    width: 12%;
    padding: 6px 7px;
}

.search-date-starttoend label ~ select.form-control:nth-child(4) {
    width: 16%;
    padding: 6px 7px;
}

.search-date-starttoend label ~ select.form-control ~ input.btn:last-child {
    margin-right: 0;
    width: 9%;
}



</style>';
        echo '
        <div class="top-heading">
    <ol class="breadcrumb">
        <li>Timeclock</li>
    </ol>
</div>';

		$send_by = AppModel::$usr['afID'];
		//echo '----->'.$send_by;
		
		
        echo '<div class="pagination-top">';
        if($errormessage)
        {
        echo '<div class="alert alert-danger">';
        echo ''.$errormessage.'.';
        echo '</div>';
        }
		/******************  REQUEST ***********************/
		//cf::pr($user);
		msg::get();
		
		if (count(form::$error) >0 ) $display='none'; else $display='block';
		
		if (!isset($_SESSION['webapp-admin-token'])) { 
		echo '
		<div id="btn_div" style="margin-bottom:10px;display:'.$display.'">
		<button type="button" style="font-size:1.015em" class="btn btn-info" onclick="$(\'#btn_div\').hide();$(\'#request_div\').show();"> Request correction >></button>
		</div>';
		
		if (count(form::$error) >0 ) $display='block'; else $display='none';
		
		echo '<div  id="request_div" style="margin-bottom:10px;width:100%;display:'.$display.'; border:1px solid lightgray;border-radius:10px;padding:10px">';
	
		echo '<button type="button" style="font-size:1.015em" class="btn btn-info" onclick="$(\'#btn_div\').show();$(\'#request_div\').hide();">Hide request correction >></button>';
			
		echo '<div style="width:100%;text-align:center;margin-bottom:20px"><h4>Timeclock change request</h4></div>';
		
			$args = self::$fldArgsBase;
			form::codeStyle(true, 1, false);
			
			form::openTag("div",array("class"=>"form-panel"));
			form::$ofsetIndentsCount = 2;
			form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));
				echo '<input type="hidden" name="id"  value="'.$user['afID'].'">';
				form::inpField(
					"Date of the report:<span title=\"Required field\">*</span>",
					'date',
					$req['date'],
					$args+array('style'=>'width:220px','readonly'=>'readonly','id'=>'date')
					/*'hint'=>'Go to week you need to make request for with <br><b><< Prev Week</b> and <b>Next Week >></b> buttons below.')*/
				);
				
				//echo '<button title="Click to select start date" id="selWeek" type="button" class="btn btn-primary">Select Start Date</button>';
			
				form::inpField(
					"Correction required:<span title=\"Required field\">*</span>",
					'correction',
					$req['correction'],
					$args+array('style'=>'width:700px','id'=>'correction')
				);
				
				/**********
				form::inpField(
					"Reason:<span title=\"Required field\">*</span>",
					'reason',
					$req['reason'],
					$args + array("style"=>"width:700px")
				);
				**********/
				
			echo '
			<div class="fld form-group">
				<div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-3">
				<button type="submit" style="font-size:1.015em" name="save-request" value="Save" class="btn btn-success">Send request</button> 
				</div>
			</div>';
		
			form::closeTag("form");
			form::closeTag("div",array("class"=>"form-panel"));
		
		echo '</div>
		<style>
		#date{background-color:#FFF}
		</style>
		<script>
		jQuery("#date").datepicker();
		</script>
		<hr>';
		
		echo "<script>
		$('#weeklyDatePicker').datetimepicker({
			format: 'DD/MM/YYYY'
		});

		//Get the value of Start and End of Week
		$('#weeklyDatePicker').on('dp.change', function (e) {
			value = $('#weeklyDatePicker').val();
			firstDate = moment(value, 'DD/MM/YYYY').day(0).format('DD/MM/YYYY');
			lastDate =  moment(value, 'DD/MM/YYYY').day(6).format('DD/MM/YYYY');
			$('#weeklyDatePicker').val(firstDate + '   -   ' + lastDate);
		});
		</script>";
		
		}// end if admin 
		/****************** END REQUEST ***********************/ 
		
        echo '<a href="?sdate='.$prevweekstartdate.'&edate='.$prevweekenddate.'">';
        echo '<button type="submit" class="btn btn-primary pull-left"> << Prev Week</button>';
        echo '</a>';
        echo '<div class="from-to-date">';
        echo '<span style="color: #000;font-weight: 900;">Week:</span>';
        echo '<span>'.$weekstartdate.'</span>';
        echo '<span style="color: #000;">-</span>';
        echo '<span>'.$weekenddate.'</span>';
		if ($singedoffstatus=='No') echo '<span style="color: #000;font-weight: 900;">Not Signed Off</span>';
        echo '</div>';
        if($nextbutton=="yes")
          {
        echo '<a href="?sdate='.$nextweekstartdate.'&edate='.$nextweekenddate.'">';
        echo '<button type="submit" class="btn btn-primary pull-right">Next Week >> </button>';
        echo '</a>';
          }
        echo '</div>';

        echo '<div class="search-date-starttoend">';
        echo '<form name="searchfortc" action="" method="get">';
        echo '<label>Jump To Date:</label>';
        echo '<select  class="form-control" name="namemonth" required>';
        echo '<option value="">Month</option>';
        if(@$_GET["namemonth"]=="1") { echo '<option value="1" selected >January</option>'; } else { echo '<option value="1">January</option>'; }
        if(@$_GET["namemonth"]=="2") { echo '<option value="2" selected >February</option>'; } else { echo '<option value="2">February</option>'; }
        if(@$_GET["namemonth"]=="3") { echo '<option value="3" selected >March</option>'; } else { echo '<option value="3">March</option>'; }
        if(@$_GET["namemonth"]=="4") { echo '<option value="4" selected >April</option>'; } else { echo '<option value="4">April</option>'; }
        if(@$_GET["namemonth"]=="5") { echo '<option value="5" selected >May</option>'; } else { echo '<option value="5">May</option>'; }
        if(@$_GET["namemonth"]=="6") { echo '<option value="6" selected >June</option>'; } else { echo '<option value="6">June</option>'; }
        if(@$_GET["namemonth"]=="7") { echo '<option value="7" selected >July</option>'; } else { echo '<option value="7">July</option>'; }
        if(@$_GET["namemonth"]=="8") { echo '<option value="8" selected >August</option>'; } else { echo '<option value="8">August</option>'; }
        if(@$_GET["namemonth"]=="9") { echo '<option value="9" selected >September</option>'; } else { echo '<option value="9">September</option>'; }
        if(@$_GET["namemonth"]=="10") { echo '<option value="10" selected >October</option>'; } else { echo '<option value="10">October</option>'; }
        if(@$_GET["namemonth"]=="11") { echo '<option value="11" selected >November</option>'; } else { echo '<option value="11">November</option>'; }
        if(@$_GET["namemonth"]=="12") { echo '<option value="12" selected >December</option>'; } else { echo '<option value="11">December</option>'; }
        echo '</select>';
        echo '&nbsp;&nbsp;';

        echo '<select  class="form-control" name="nameday" required style="width: 70px;" >';
        echo '<option value="">Date</option>';
        for($x=1;$x<=31;$x++)
            {
              if($x==@$_GET["nameday"])
                  {
                      echo '<option value="'.$x.'" selected>'.$x;
                  }
                  else
                      {
                          echo '<option value="'.$x.'">'.$x;
                      }
            }
        echo '</option>';
        echo '</select>';
        echo '&nbsp;&nbsp;';

        echo '<select  class="form-control" name="nameyear" required>';
        echo '<option value="">Year</option>';
        for($sy=2018;$sy<=date("Y");$sy++)
            {
                if($sy==@$_GET["nameyear"])
                    {
                      echo '<option value="'.$sy.'" selected>'.$sy;
                    }
                    else
                        {
                            echo '<option value="'.$sy.'">'.$sy;
                        }
            }
        echo '</option>';
        echo '</select>';
        echo '&nbsp;&nbsp;';

        echo '<input type="submit" name="submit" value="Go" class="btn btn-success">';
        echo '</form>';
        echo '</div>';


        echo '<table style="width:100%">';
        echo '<tr>';
        echo '<th>Date</th>';
        echo '<th>Day</th>';
        echo '<th>Time In</th>';
        echo '<th>Time Out</th>';
        echo '<th>Hours Worked</th>';
        echo '<th>Weekly</th>';
        echo '<th>Overtime</th>';
        echo '<th>Type</th>';
        echo '</tr>';
        if($results)
        {
        $totalworkinghours=0;
        foreach($results as $result)
              { if($result["Type"] == 'Regular') $result["Type"]='Work';
			  
			  
                $timein=date_create($result['TimeIn']);
                $timeout=date_create($result['TimeOut']);
				if ($result['Updated_On']=='0000-00-00 00:00:00') $result['Updated_On']=$result['TimeOut'];
                $lastup=date_create($result['Updated_On']);
                $dateshow=date_format($timein,"m/d/Y");
                $dayshow=date_format($timein,"l");
                $timeinshow=date_format($timein,"m/d/Y g:i A");
                $timeoutshow=date_format($timeout,"m/d/Y g:i A");
                $lastupdatedon=date_format($lastup,"m/d/Y g:i A");

                //-----------------------------------------------------------------------------------------//
                //function to sum hours//
				
				if(isset($result['HousrWorkingAdmin']) )  {
					    $oldWorkingHours = $result["WorkingHours"];
					    $result["WorkingHours"] = $result['HousrWorkingAdmin'];	
				}

                $totalworkinghours=TimeClockView::addhours($totalworkinghours,$result["WorkingHours"]);
                //-----------------------------------------------------------------------------------------//
				
                if($totalworkinghours>40)
                  {
                    $prevtotalhours=$totalworkinghours-$result["WorkingHours"];
                    if($prevtotalhours>40)
                        {
                          $regular=0;
                          $overtime=round($result["WorkingHours"],2);
                        }
                        else
                            { 
                              $overtime=$totalworkinghours-40;
							  
                              $regular=TimeClockView::subhours($result["WorkingHours"],$overtime);

                            }

                  }
                  else
                      {
                        $regular=$result["WorkingHours"];
                        $overtime=0;
                      }



        if($totalworkinghours>40)
            {
              $totalregular=40;
              $totalovertime=$totalworkinghours-40;
            }
            else
                {
                  $totalregular=$totalworkinghours;
                  $totalovertime="0";
                }

        echo '<tr>';
        echo '<td>'.$dateshow.'</td>';
        echo '<td>'.$dayshow.'</td>';
        echo '<td>'.$timeinshow.'</td>';
        echo '<td>'.$timeoutshow.'</td>';
		if(isset($result['HousrWorkingAdmin']) )  {
					echo '<td>'.number_format($result["WorkingHours"], 2, ':', '').
					'<br>
					<span style="color:red;text-decoration: line-through;" title="Disabled by admin">'.number_format($oldWorkingHours, 2, ':', '').'</span>
					</td>';
		}
		
        else echo '<td>'.number_format($result["WorkingHours"], 2, ':', '').'</td>';
		
		
        echo '<td>'.number_format($totalworkinghours, 2, ':', '').'</td>';
       // echo '<td>'.number_format($regular, 2, ':', '').'</td>';
        echo '<td>'.number_format($overtime, 2, ':', '').'</td>';
		if(isset($result['TypeAdmin']) && $result['TypeAdmin']!='')  $result['Type']=$result['TypeAdmin'];
		
		echo '<td>'.$result['Type'].'</td>';
        echo '</tr>';
              }


              echo '<tr>';
              echo '<td colspan="8">&nbsp;</td>';
              echo '</tr>';

              echo '<tr>';
			  echo '<td colspan="8" style="text-align:right"><span style="font-size: 16px;font-weight: 900;">
			  Total regular:</span> <span style="font-size: 16px;font-weight: 900;color: #327ab7;">'.number_format($totalregular, 2, ':', '').'</span>&nbsp;&nbsp;&nbsp;
			  <span style="font-size: 16px;font-weight: 900;">
			  Total overtime:</span> <span style="font-size: 16px;font-weight: 900;color: #327ab7;">'.number_format($totalovertime, 2, ':', '').'</span>&nbsp;';
			  echo '</tr>';

              echo '<tr>';
              echo '<td colspan="5">&nbsp;</td>';
              echo '<td style="font-size: 16px;font-weight: 900;">Signed Off:</td>';
              echo '<td  colspan="2" style="font-size: 16px;font-weight: 900;color: #327ab7;">'.$singedoffstatus.'</td>';
              echo '</tr>';
              echo '<tr>';
              echo '<td style="font-size: 16px;font-weight: 900;padding-left:15px;text-align:left">Last update : </td>';
              echo '<td  colspan="7" style="font-size: 16px;font-weight: 900;color: #327ab7; text-align: left;" >'.$lastupdatedon.'</td>';
              echo '</tr>';

              echo '<tr>';
              echo '<td colspan="8" style="text-align:left;padding-left:15px"><b>Note:</b> If a change has been made on TimeClockPlus since then, it will be updated here with the next daily update. Only current week reports and changes are accepted. Reports or changes from previous weeks will be ignored.</td>';
              echo '</tr>';
      }
      else
          {
            echo '<tr>';
            echo '<td colspan="8">No unsigned reports</td>';
            echo '</tr>';
          }

        echo '</table>';
      }

public static function addhours($old,$new)
  {
    $oldPlayTime=$old;
    $PlayTimeToAdd=$new;
    $old=explode(".",$oldPlayTime);
    $play=explode(".",$PlayTimeToAdd);

    $hours=$old[0]+$play[0];
    $minutes=@$old[1]+@$play[1];

    if($minutes > 59){
      $minutes=$minutes-60;
      $hours++;
      }

      if($minutes < 10){
        $minutes = "0".$minutes;
        }

        if($minutes == 0){
          $minutes = "00";
        }

    $totalworkinghours=$hours.".".$minutes;
    return $totalworkinghours;
  }




  public static function subhours($old,$new)
    {
      $oldPlayTime=number_format($old, 2, '.', '');
      $PlayTimeToAdd=number_format($new, 2, '.', '');
      $old=explode(".",$oldPlayTime);
      $play=explode(".",$PlayTimeToAdd);
	  
      if($play[1]>$old[1])
      {
        $hours=($old[0]-1)-$play[0];
        $minutes=($old[1]+60)-$play[1];
      }
      else
          {
            $hours=$old[0]-$play[0];
			
            $minutes=$old[1]-$play[1];
          }



        if($minutes < 10){
          $minutes = "0".$minutes;
          }

          if($minutes == 0){
            $minutes = "00";
          }

      $totalworkinghours=$hours.".".$minutes;
      return $totalworkinghours;
    }



}
