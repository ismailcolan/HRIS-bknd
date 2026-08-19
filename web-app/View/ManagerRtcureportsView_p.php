<?php
class ManagerRtcureportsView_p extends ApplicationView
{

    public static function RenderUndignedReportList($results)
      {
          //error_reporting(E_ALL);
		
         @session_start();

          
          echo '<!DOCTYPE html>';
          echo '<html>';
          echo '<head>';
          echo '<title>Data Table</title>';
          echo '<meta charset="UTF-8">';
          echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
          echo '<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">';
          echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap.min.css">';
		  echo '<style type="text/css">
                table thead tr th,
                table tbody tr td
                  {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif !important;
                    font-size: 14px !important;
                    line-height: 1.42857143;
                    color: #333;
                  }

                  .container.m-tb-30 {
                    width: 100% !important;
                  }

                .m-tb-30
                  {
                    margin-top: 30px;
                    margin-bottom: 30px;
                  }

                  #datatable thead th:nth-last-child(2):after,
                  #datatable thead th:last-child:after
                    {
                      display: none;
                    }

                div#datatable_length
                {
                display: none !important;
                }
				.btn{font-size:inherit ! important}

                </style>';

		  
		  
		  
          echo '</head>';
          echo '<body style="background:#FFF;min-height:500px">';
          echo '<div class="container m-tb-30">';
          echo '<div class="row">';
          if(@$_SESSION["error"])
              {
                echo '<div class="alert alert-danger alert-dismissible" id="message">';
                echo '<a href="'.WP.'managertcureports" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
                echo '<strong>Success!</strong>'.$_SESSION["error"];
                echo '</div>';
              }

          if(@$_SESSION["success"])
              {
                echo '<div class="alert alert-success alert-dismissible" id="message">';
                echo '<a href="'.WP.'managertcureports" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
                echo '<strong>Success!</strong>'.$_SESSION["success"];
                echo '</div>';
              }

          echo '<div class="col-sm-12">';
          echo '<div class="table-responsive">';
          echo '<table id="datatable" class="table table-striped table-bordered" style="width:100%">';
          echo '<thead>';
          echo '<tr>';
          echo '<th>Week</th>';
          echo '<th>Employee</th>';
          echo '<th>Signed Status</th>';
          echo '<th width="30%">Action</th>';
          echo '</tr>';
          echo '</thead>';
          echo '<tbody>';
		  if(empty($results)) $results=array();
          foreach($results as $result)
              {
                $timein=date_create($result['STARTDATE']);
                $timeinshow=date_format($timein,"m/d/Y");

                $timeout=date_create($result['ENDDATE']);
                $timeoutshow=date_format($timeout,"m/d/Y");

                $sql= "select afFirstName, afLastName from apply_forms where `afID`='".$result["EMPLOYEE_ID"]."'";
                $results = db::PrepareLoadSingleRow($sql);
                $name=$results["afFirstName"]." ".$results["afLastName"];


          echo '<tr>';
          echo '<td data-sort="'. $result['STARTDATE'] .'">'.$timeinshow.' - '.$timeoutshow.'</td>';
          echo '<td><a href="'.WP.'employees?ID='.$result["EMPLOYEE_ID"].'" target="_blank">'.$name.'</a></td>';
          echo '<td>'.$result["SIGNED_STATUS"].'</td>';
          echo '<td width="20%"><a href="'.WP.'managertcureports?querytype=listattendence&empnumber='.$result["EMPLOYEE_NUMBER"].'&startdate='.$result["STARTDATE"].'"><button type="button" class="btn btn-success">View report</button></a></td>';
          echo '</tr>';

          unset($_SESSION["success"]);
          unset($_SESSION["error"]);

              }



          echo '</tbody>';
          echo '</table>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
          echo '</div>';

          echo '<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>';
         // echo '<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>';
		  echo '<script type="text/javascript" src="add/cssjs/jquery.dataTables.min.js"></script>';
          echo '<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap.min.js"></script>';
          echo '<script type="text/javascript">$(document).ready(function() { $("#datatable").dataTable({ "iDisplayLength": 12,"searching": false }); } ); </script>';
          echo '</body>';
          echo '</html>';

      }



        public static function RenderWorkingHourList($empname,$errormessage,$singedoffstatus,$results,$weekstartdate,$weekenddate,$prevweekstartdate,$prevweekenddate,$previousbutton,$nextweekstartdate,$nextweekenddate,$nextbutton)
          { 
			$delRepWrite = false;
		   
            $navweeksdateinter=date_create($weekstartdate);
            $navweeksdate=date_format($navweeksdateinter,"Y-m-d");
            $navweekedateinter=date_create($weekenddate);
            $navweekedate=date_format($navweekedateinter,"Y-m-d");

            $sqlemployeequery = "select * from p_listofweeks where `EMPLOYEE_NUMBER`='".$_GET["empnumber"]."' and `STARTDATE`='".$navweeksdate."' and `ENDDATE`='".$navweekedate."'";
            
			$empvalue = db::PrepareLoadSingleRow($sqlemployeequery);
			
            $weekrecordid=$empvalue["PLOW_ID"];
            
			$signed=$empvalue["SIGNED_STATUS"];
			
        echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:400,700,900">';
		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>';
	    echo '<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">';
		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>';
	
		echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">';
		
        echo '<style>
        body {
            font-family: Lato, sans-serif;
            font-weight: 300;
        }

        .topinfo {
            display: none !important;
        }

        .main-section {
            background-color: #fff;
            padding: 20px;
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
        }

        .from-to-date {
            width: calc(100% - 190px);
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
            margin-bottom: 0;
        }

        .search-date-starttoend label {
            float: left;
            font-size: 16px;
            width: 22%;
            line-height: 34px;
            margin: 0;
            font-weight: 700;
        }

        .search-date-starttoend label ~ select.form-control {
            font-size: 16px;
        }

        .search-date-starttoend label ~ select.form-control,
        .search-date-starttoend label  select.form-control  input.btn {
            width: calc(23% - 20px);
            margin-right: 14px;
            float: left;
        }

        .search-date-starttoend label ~ select.form-control:nth-child(2) {
            width: 25%;
            padding: 6px 7px;
        }

        .search-date-starttoend label ~ select.form-control:nth-child(3) {
            width: 70px;
            padding: 6px 7px;
        }

        .search-date-starttoend label ~ select.form-control:nth-child(4) {
            width: 16%;
            padding: 6px 7px;
        }

        .search-date-starttoend label  select.form-control  input.btn:last-child {
            margin-right: 0;
            width: 9%;
        }


        table {
            background-color: #fff;
        }

        table tbody tr:nth-child(odd) {
            background-color: #fff;
        }

        table tr th,
        table tr td {
            padding: 4px;
            text-align: center;
        }
		.btn{font-size:inherit ! important}

    </style>';
echo '
  <div class="modal fade" id="alertModal" role="dialog" style="z-index:9999999">
    <div class="modal-dialog modal-sm" >
      <div class="modal-content" >
        <div class="modal-header" >
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title" id="msg_title">Alert</h4>
        </div>
        <div class="modal-body" >
          <input style="width:100%" placeholder="Reason to delete" id="delReason" >
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="okPress=true;">Continue</button>
        </div>
      </div>
    </div>
  </div>
</div>';


			 echo '<div  id="page-container">';

            echo '<div class="pagination-top">';

            if(@$_SESSION["error"])
                {
                  echo '<div class="alert alert-danger alert-dismissible">';
                  echo '<a href="'.WP.'managertcureports" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
                  echo '<strong>Success!</strong>'.$_SESSION["error"];
                  echo '</div>';
                }

            if(@$_SESSION["success"])
                {
                  echo '<div class="alert alert-success alert-dismissible">';
                  echo '<a href="'.WP.'managertcureports" class="close" data-dismiss="alert" aria-label="close">&times;</a>';
                  echo '<strong>Success!</strong>'.$_SESSION["success"];
                  echo '</div>';
                }

            if($errormessage)
                {
            echo '<div class="alert alert-danger">';
            echo ''.$errormessage.'.';
            echo '</div>';
                }

            echo '<a href="?querytype=listattendence&empnumber='.$_GET["empnumber"].'&sdate='.$prevweekstartdate.'&edate='.$prevweekenddate;
			if (isset($_GET['from'])) echo '&from='.$_GET['from'];
			echo '">';
			
            echo '<button type="submit" class="btn btn-primary pull-left">Prev Week</button>';
            echo '</a>';
            echo '<div class="from-to-date">';
			if (!isset($_GET['from'])){
				echo '<span style="color: #000;font-weight: 900;">Employee:</span>';
				echo '<span style="color: #327ab7;font-weight: 900;">'.$empname.', </span>';
		    }
            echo '<span style="color: #000;font-weight: 900;">Week:</span>';
            echo '<span>'.$weekstartdate.'</span>';
            echo '<span style="color: #000;">-</span>';
            echo '<span>'.$weekenddate.'</span>';
            echo '</div>';
            if($nextbutton=="yes")
              {
                echo '<a href="?querytype=listattendence&empnumber='.$_GET["empnumber"].'&sdate='.$nextweekstartdate.'&edate='.$nextweekenddate;
				if (isset($_GET['from'])) echo '&from='.$_GET['from'];
				echo '">';
                echo '<button type="submit" class="btn btn-primary pull-right">Next Week</button>';
                echo '</a>';
              }
            echo '</div>';
/***********
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

            echo '<select  class="form-control" name="nameday" required>';
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

            echo '<input type="submit" name="submit" value="Go" class="btn btn-success" style="margin-left: -12px;">';
            echo '</form>';

            echo '</div>';
************/
            echo '<table style="width:100%"  class="table table-bordered">';
            echo '<tr>';
            echo '<th>Date</th>';
            echo '<th>Day</th>';
            echo '<th>Time In</th>';
            echo '<th>Time Out</th>';
            echo '<th>Hours Worked</th>';
            echo '<th>Weekly</th>';
            //echo '<th>Work</th>';
            echo '<th>Overtime</th>';
			echo '<th>Type</th>';
			if (isset($_SESSION['webapp-admin-token'])) echo '<th></th>';
            echo '</tr>';
            if($results)
            {
            $totalworkinghours=0;
			//
			$ch='<div class="admin_div">
			<a href="havascript:void(0)"
			onclick="if ( jQuery(\'#div%d\').is(\':visible\') ) jQuery(\'#div%d\').hide(); else jQuery(\'#div%d\').show();">Modify hours</a>';
			
			
			
            foreach($results as $result)
                  { //cf::pr($result);
				 
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
					$old=$result['WorkingHours'];
					$new='';
					
					if (isset($result['HousrWorkingAdmin'])) { 
						$old=$result['WorkingHours'];
						$new=$result['HousrWorkingAdmin'];
						$result['WorkingHours']=$result['HousrWorkingAdmin'];
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
			
			
			if (isset($result["TypeAdmin"]) && $result["TypeAdmin"]!='' && $result["Type"] != $result["TypeAdmin"]) 
					$result["Type"] = $result["TypeAdmin"];
			if($result["Type"] == 'Regular') $result["Type"]='Work';
			
            echo '<tr data-rowid ="'.$result['ptw_TMCWORK_ID'].'"  data-type="'.$result["Type"].'">';
            echo '<td>'.$dateshow.'</td>';
            echo '<td>'.$dayshow.'</td>';
            echo '<td>'.$timeinshow.'</td>';
            echo '<td>'.$timeoutshow.'</td>';
			
			$add ='<span id="'.val::convDate(USER_DATE,'m-d-Y',$dateshow).'">'.str_replace(".",":",$old).'</span>';
			if ($new != '') {
				$add=
				'<span id="'.val::convDate(USER_DATE,'m-d-Y',$dateshow).'" style="color:black" title="Entered by admin">'.number_format($new, 2, ':', '').'</span>'.
				'<br><span style="color:red;text-decoration: line-through;" title="Disabled by admin">'.number_format($old, 2, ':', '').'</span> ';
				
			}
			

            echo '<td>'.$add.'</td>';
            echo '<td>'.number_format($totalworkinghours, 2, ':', '').'</td>';
            //echo '<td>'.str_replace(".",":",$regular).'</td>';
			
            echo '<td>'.number_format($overtime, 2, ':', '').'</td>';
			//if ($result["Type"]=='Regular') $type='Work'; else $type=$result["Type"];
			//if ($result["TypeAdmin"]=='Regular') $result["TypeAdmin"]='Work';
			//if ($result["TypeAdmin"]=='Regular') $result["TypeAdmin"]='Work';
			$type = $result["Type"];
			if (isset($result["TypeAdmin"]) && $result["TypeAdmin"]!='' && $result["Type"] != $result["TypeAdmin"]) {
				if($type =='Regular') $type='Work';
				if($result["TypeAdmin"]=='Regular') $result["TypeAdmin"]='Work';
				
				$type= '<span style="color:black;" title="Entered by admin">'.$result["TypeAdmin"].'</span>
			    <br><span style="color:red;text-decoration: line-through;" title="Entered by system">'.$type.'</span>';
			}	
			echo '<td>'.$type.'</td>';
			
			if (isset($_SESSION['webapp-admin-token'])) {
				if ($signed == "No" && !self::isThisLastWeek($weekenddate) ) echo '<td><a href="javascript:void()" onclick="delReport('.$result['ptw_TMCWORK_ID'].')" title="Delete report"><i class="fa fa-trash" ></i></a></td>';
				else echo '<td></td>';
				if(!$delRepWrite){
					echo'
					
					<script>
					var okPress = false;
					var rID;
					
					jQuery("#alertModal").on("hidden.bs.modal", function () {
						var reason = jQuery("#delReason").val();
						if (okPress) {
							alert("OK "+reason+" ID:"+rID)
						} 
						
						else alert ("FAIL "+reason+" ID:"+rID);
					});
					function myalert(head,txt){ 
						jQuery("#delReason").val(txt);
						jQuery("#msg_title").html(head);
						//jQuery("#alertModal").css("z-index",999999999);
						jQuery("#alertModal").modal("show");
					}
					function delReport(ID){
						rID= ID;
						okPress=false;
						if (confirm("Are you sure to delete this report?")) {
							var url="'.WP.'managertcureports?querytype=admin_del_hours&rID="+ID;
							
							jQuery.ajax({
							url: url,
							success: function (result) {  //alert("RESULT: "+result);
							  resp = jQuery.parseJSON(result); 
							  if (resp.result === "ok")  {
								alert("Report was deleted.");
								window.location.reload();
							  }
							  else alert(resp.message);
						}
					});
						}
						
						
					}
					</script>';
				}
				$delRepWrite = true;
				
			}
            echo '</tr>';
                  }

                  echo '<tr>';
					if (isset($_SESSION['webapp-admin-token']))  echo '<td colspan="9">&nbsp;</td>';
					else 
					  echo '<td colspan="8">&nbsp;</td>';
                  echo '</tr>';
				
				if (($signed == "No") && self::isTodayAfter() && !self::isThisLastWeek($weekenddate)&& isset($_SESSION['webapp-admin-token']) ) 
				{
					echo '<tr>';
					//echo '<td colspan="5">&nbsp;</td>';
					if (isset($_SESSION['webapp-admin-token'])) echo  '<td  colspan="9" style="font-size:16px;font-weight:900;" >';
					else
					echo '<td  colspan="8" style="font-size:16px;font-weight:900;" >';
				
				    echo '
					<div style="float:right;width:310px;text-align:left">
					<div style="text-align:center">
					<a href="javascript:void(0)" id="modifyHoursBtn" onclick="showHideMod()" style="float:right;width:150px" class="btn btn-primary">Modify hours</a>
					</div>
					<div id="sel_days" style="display:none"><br>
					';
					$sdate=val::convDate(USER_DATE,MYSQL_DATE,$weekstartdate);
					$edate=val::convDate(USER_DATE,MYSQL_DATE,$weekenddate);
					echo '<br><span style="display:inline-block;width:90px;text-align:right" >Select day:</span> 
					<select name="date_to_process" style="width:210px" id="date_to_process" onchange="doNext(this)">';
					echo '<option value-"0">Select day</option>';
					for($i=0;$i<7;$i++){
						echo '<option value="'.date('m-d-Y', strtotime($sdate ."+{$i} day")).'">'.date('l, '.USER_DATE, strtotime($sdate ."+{$i} day")).'</option>';
					}
					echo '</select>
					
					
					
			<div style="display:none;height:160px;text-align:left" id="hours_div" >
			<br>
			<span style="display:inline-block;width:90px;text-align:right">Hours:</span>
			<select name="hours" id="hours">
				<option>0</option>
				<option>1</option>
				<option>2</option>
				<option>3</option>
				<option>4</option>
				<option>5</option>
				<option>6</option>
				<option>7</option>
				<option>8</option>
				<option>9</option>
				<option>10</option>
				<option>11</option>
				<option>12</option>
				<option>13</option>
				<option>14</option>
				<option>15</option>
				<option>16</option>
				<option>17</option>
				<option>18</option>
				<option>19</option>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<span style="">Minutes:</span>
			    <select name="min" id="min">
				<option>00</option>
				<option>01</option>
				<option>02</option>
				<option>03</option>
				<option>04</option>
				<option>05</option>
				<option>06</option>
				<option>07</option>
				<option>08</option>
				<option>09</option>
				<option>10</option>
				<option>11</option>
				<option>12</option>
				<option>13</option>
				<option>14</option>
				<option>15</option>
				<option>16</option>
				<option>17</option>
				<option>18</option>
				<option>19</option>
				<option>20</option>
				<option>21</option>
				<option>22</option>
				<option>23</option>
				<option>24</option>
				<option>25</option>
				<option>26</option>
				<option>27</option>
				<option>28</option>
				<option>29</option>
				<option>30</option>
				<option>31</option>
				<option>32</option>
				<option>33</option>
				<option>34</option>
				<option>35</option>
				<option>36</option>
				<option>37</option>
				<option>38</option>
				<option>39</option>
				<option>40</option>
				<option>41</option>
				<option>42</option>
				<option>43</option>
				<option>44</option>
				<option>45</option>
				<option>46</option>
				<option>47</option>
				<option>48</option>
				<option>49</option>
				<option>50</option>
				<option>51</option>
				<option>52</option>
				<option>53</option>
				<option>54</option>
				<option>55</option>
				<option>56</option>
				<option>57</option>
				<option>58</option>
				<option>59</option>
				
			</select>
			
			<br><br>
			<span style="display:inline-block;width:90px;text-align:right;">Type: </span>
			<select name="dayType" id="dayType">
			<option value="Work">Work</option>
			<option value="Office closed">Office closed</option>
			<option value="Day off">Day off</option>
			</select>
			<input type="hidden" name="reqID" id="reqID">
			<input type="hidden" name="empNmb" id="empNmb" value="'.$result['EmployeeNumber'].'">
			<br><div style="text-align:center"><br><a class="btn btn-success" style="width:150px;float:right" href="javascript:void(0)" onclick="sendIt()">Save</a></div>
			</div>
			</div>
			
			</div>
			<script>
					function showHideMod(){
						if (! jQuery(\'#sel_days\').is(":visible")) {
							jQuery(\'#sel_days\').fadeIn();
							jQuery(\'#modifyHoursBtn\').text("Hide Modify hours");
						}
						else {
							jQuery(\'#sel_days\').fadeOut();
							jQuery(\'#modifyHoursBtn\').text("Modify hours");
						}
						parent.doResizeIframes();
					}
					function doNext(){
							
							var id = jQuery("#date_to_process").val();
							if(id=="Select date") return;
							var spNmb=jQuery("#"+id).length;
							
							if ( spNmb == 0) {
								var h = "0";
								var m = "00";
								_id=0;
								_type="Work";
							}
							else {
								var v =jQuery("#"+id).text().split(":");
								var h = v[0] ;
								var m = v[1];
								var _id=jQuery("#"+id).parent("td").parent("tr").data("rowid");
								_type=jQuery("#"+id).parent("td").parent("tr").data("type");
							}
					
						jQuery("#hours").val(h);
						jQuery("#min").val(m);
						jQuery("#reqID").val(_id);
						
						if(_type=="Work" || _type=="Office closed" || _type=="Day off"  ) jQuery("#dayType").val(_type);
						
						jQuery("#hours_div").show();
						
						parent.doResizeIframes();
					}
				
				function sendIt(id){		
					var h=jQuery("#hours").val();
					var m=jQuery("#min").val();
					var id=jQuery("#reqID").val();
					
					var type=jQuery("#dayType").val();
					var day = jQuery("#date_to_process").val();
					var empNmb = jQuery("#empNmb").val();
					//alert(empNmb);
					var url="'.WP.'managertcureports?querytype=admin_set_hours&weekstartdate='.val::convDate(USER_DATE,MYSQL_DATE,$weekstartdate).
					'&weekenddate='.val::convDate(USER_DATE,MYSQL_DATE,$weekenddate).'&day="+day+"&empNmb="+empNmb+"&id="+id+"&type="+type+"&m="+m+"&h="+h;
					//alert(url);
					jQuery.ajax({
						url: url,
						success: function (result) {  //alert("RESULT: "+result);
						  resp = jQuery.parseJSON(result); 
						  if (resp.result === "ok")  {
							alert("Changes saved.");
							window.location.reload();
						  }
						  else alert(resp.message);
						}
					});
				}
				</script>';

				echo '</td></tr>';
				}
				  
				  
				  
				  
                  echo '<tr>';
				 if (isset($_SESSION['webapp-admin-token'])) echo  '<td colspan="9" style="text-align:right"><span style="font-size: 16px;font-weight: 900;">';
				 else
					echo '<td colspan="8" style="text-align:right"><span style="font-size: 16px;font-weight: 900;">';
                  echo '
				  Total regular:</span> <span style="font-size: 16px;font-weight: 900;color: #327ab7;">'.number_format($totalregular, 2, ':', '').'</span>&nbsp;&nbsp;&nbsp;
				  <span style="font-size: 16px;font-weight: 900;">
				  Total overtime:</span> <span style="font-size: 16px;font-weight: 900;color: #327ab7;">'.number_format($totalovertime, 2, ':', '').'</span>&nbsp;';
                  echo '</tr>';

                  echo '<tr>';
				  if (isset($_SESSION['webapp-admin-token'])) echo '<td colspan="6">&nbsp;</td>';
                  else 
				  echo '<td colspan="5">&nbsp;</td>';
                  echo '<td style="font-size: 16px;font-weight: 900;">Signed Off:</td>';
				  //cf::pr($empvalue);
				  /*******************
				  if($signed == 'Yes') {
					  $_q="SELECT * FROM apply_forms WHERE afID={$empvalue['SIGNEDBY_MANAGERID']}";
					  $_r=db::PrepareLoadSingleRow($_q);
					  $_name=$_r['afFirstName'].' '.$_r['afLastName'];
					  //$empvalue["SIGNEDBY_MANAGERID"]
					  $signed.='<br>by '.$_name.'<br> on '.val::convDate(MYSQL_DATE_TIME,USER_DATE_TIME,$empvalue["SIGNEDON_DATE"]).
					  '<br> from IP: '.$empvalue["SIGN_IP"];
					  
				  }
				  *************/
				  if(!isset($signed )) {$signed = "No";}
				  if ( isset($_SESSION['webapp-admin-token']) && $signed == "Yes" && self::withinLimit($weekenddate) )
					$signed .='<br><a href="#" onclick="unsignedreports();"><span class="btn btn-primary">Un-sign report</span></a>';
                  echo '<td  colspan="2" style="font-size: 16px;font-weight: 900;color: #327ab7;">'.$signed.'</td>';
                  echo '</tr>';

				
				
				
				if (($signed == "No") && self::isTodayAfter() && !self::isThisLastWeek($weekenddate) )  //&& !isset($_SESSION['webapp-admin-token']) ) 
				{
					echo '<tr>';
					echo '<td colspan="5">&nbsp;</td>';
					echo '<td  colspan="4" style="font-size:16px;font-weight:900;color:red;" " #327ab7;">
					<a  href="#" onclick="signedreports();" style="float:right;width:150px" class="btn btn-primary">Sign off report</a>
					</td>';
					echo '</tr>';
				}
				//class="sign_btn pull-right"
				

                 echo '<tr>';
                 echo '<td style="font-size: 16px;font-weight: 900;padding-left:15px;text-align:left">Last update:</td>';
                 echo '<td  colspan="8" style="font-size: 16px;font-weight: 900;color: #327ab7; text-align: left;" >'.$lastupdatedon.'</td>';
                 echo '</tr>';

                  echo '<tr>';
				  if (isset($_SESSION['webapp-admin-token'])) echo '<td colspan="9" style="text-align: left;padding-left:15px">';
                  else 
				  echo '<td colspan="8" style="text-align: left;padding-left:15px">';
				  
				  echo '
				  <b>Note:</b> If a change has been made on TimeClockPlus since then, it will be updated here with the next daily update. Only current week reports and changes are accepted. Reports or changes from previous weeks will be ignored.
				  <br>Note: Hours with <span style="color:red">'.ASTERISK.'</span> mean Day off (paid or unpaid) or Holiday. </td>';
                  echo '</tr>';
          }
          else
              {
                echo '<tr>';
				if (isset($_SESSION['webapp-admin-token'])) echo '<td colspan="9">';
				else 
				echo '<td colspan="8">';
                echo 'No unsigned reports</td>';
                echo '</tr>';
				
				$signed = 'No';
				
				if (($signed == "No") && self::isTodayAfter() && !self::isThisLastWeek($weekenddate)&& isset($_SESSION['webapp-admin-token']) ) 
				{
					echo '<tr>';
					//echo '<td colspan="5">&nbsp;</td>';
					if (isset($_SESSION['webapp-admin-token'])) echo '<td  colspan="9" style="font-size:16px;font-weight:900;" >';
					else 
					echo '<td  colspan="8" style="font-size:16px;font-weight:900;" >';
					
					echo '<div style="float:right;width:300px;text-align:left">
					<div style="text-align:center">
					<a href="javascript:void(0)" id="modifyHoursBtn" onclick="showHideMod()" style="float:right;width:150px" class="btn btn-primary">Modify hours</a>
					</div>
					<div id="sel_days" style="display:none"><br>
					';
					$sdate=val::convDate(USER_DATE,MYSQL_DATE,$weekstartdate);
					$edate=val::convDate(USER_DATE,MYSQL_DATE,$weekenddate);
					echo '<span style="display:inline-block;width:90px;text-align:left" >Select day:</span> <select name="date_to_process" id="date_to_process" onchange="doNext(this)">';
					echo '<option value-"0">Select day</option>';
					for($i=0;$i<7;$i++){
						echo '<option value="'.date('m-d-Y', strtotime($sdate ."+{$i} day")).'">'.date('l, '.USER_DATE, strtotime($sdate ."+{$i} day")).'</option>';
					}
					echo '</select>
					
					
					
			<div style="display:none;height:160px;text-align:left" id="hours_div" >
			<br>
			<span style="display:inline-block;width:90px;text-align:left">Hours:</span>
			<select name="hours" id="hours">
				<option>0</option>
				<option>1</option>
				<option>2</option>
				<option>3</option>
				<option>4</option>
				<option>5</option>
				<option>6</option>
				<option>7</option>
				<option>8</option>
				<option>9</option>
				<option>10</option>
				<option>11</option>
				<option>12</option>
				<option>13</option>
				<option>14</option>
				<option>15</option>
				<option>16</option>
				<option>17</option>
				<option>18</option>
				<option>19</option>
			</select>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<span style="display:inline-block;width:90px;text-align:left">Min:</span>
			    <select name="min" id="min">
				<option>00</option>
				<option>01</option>
				<option>02</option>
				<option>03</option>
				<option>04</option>
				<option>05</option>
				<option>06</option>
				<option>07</option>
				<option>08</option>
				<option>09</option>
				<option>10</option>
				<option>11</option>
				<option>12</option>
				<option>13</option>
				<option>14</option>
				<option>15</option>
				<option>16</option>
				<option>17</option>
				<option>18</option>
				<option>19</option>
				<option>20</option>
				<option>21</option>
				<option>22</option>
				<option>23</option>
				<option>24</option>
				<option>25</option>
				<option>26</option>
				<option>27</option>
				<option>28</option>
				<option>29</option>
				<option>30</option>
				<option>31</option>
				<option>32</option>
				<option>33</option>
				<option>34</option>
				<option>35</option>
				<option>36</option>
				<option>37</option>
				<option>38</option>
				<option>39</option>
				<option>40</option>
				<option>41</option>
				<option>42</option>
				<option>43</option>
				<option>44</option>
				<option>45</option>
				<option>46</option>
				<option>47</option>
				<option>48</option>
				<option>49</option>
				<option>50</option>
				<option>51</option>
				<option>52</option>
				<option>53</option>
				<option>54</option>
				<option>55</option>
				<option>56</option>
				<option>57</option>
				<option>58</option>
				<option>59</option>
				
			</select>
			
			<br><br>
			<span style="display:inline-block;width:90px">Type: </span>
			<select name="dayType" id="dayType">
			<option value="Work">Work</option>
			<option value="Office closed">Office closed</option>
			<option value="Day off">Day off</option>
			</select>
			<input type="hidden" name="reqID" id="reqID">
			<input type="hidden" name="empNmb" id="empNmb" value="'.$_GET["empnumber"].'">
			<br><div style="text-align:center"><br><a class="btn btn-success" href="javascript:void(0)" onclick="sendIt()">Save</a></div>
			</div>
			</div>
			
			</div>
			<script>
					function showHideMod(){
						if (! jQuery(\'#sel_days\').is(":visible")) {
							jQuery(\'#sel_days\').fadeIn();
							jQuery(\'#modifyHoursBtn\').text("Hide Modify hours");
						}
						else {
							jQuery(\'#sel_days\').fadeOut();
							jQuery(\'#modifyHoursBtn\').text("Modify hours");
						}
						parent.doResizeIframes();
					}
					function doNext(){
							var id = jQuery("#date_to_process").val();
							if(id=="Select date") return;
							var spNmb=jQuery("#"+id).length;
							
							if ( spNmb == 0) {
								var h = "0";
								var m = "00";
								_id=0;
								_type="Regular";
							}
							else {
								var v =jQuery("#"+id).text().split(":");
								var h = v[0] ;
								var m = v[1];
								var _id=jQuery("#"+id).parent("td").parent("tr").data("rowid");
								_type=jQuery("#"+id).parent("td").parent("tr").data("type");
							}
					    
						jQuery("#hours").val(h);
						jQuery("#min").val(m);
						jQuery("#reqID").val(_id);
						if(_type=="Work" || _type=="Office closed" || _type=="Day off"  ) jQuery("#dayType").val(_type);
						
						jQuery("#hours_div").show();
						parent.doResizeIframes();
					}
				
				function sendIt(id){		
					var h=jQuery("#hours").val();
					var m=jQuery("#min").val();
					var id=jQuery("#reqID").val();
					
					var type=jQuery("#dayType").val();
					var day = jQuery("#date_to_process").val();
					var empNmb = jQuery("#empNmb").val();
					
					var url="'.WP.'/managertcureports?querytype=admin_set_hours&weekstartdate='.val::convDate(USER_DATE,MYSQL_DATE,$weekstartdate).
					'&weekenddate='.val::convDate(USER_DATE,MYSQL_DATE,$weekenddate).'&day="+day+"&empNmb="+empNmb+"&id="+id+"&type="+type+"&m="+m+"&h="+h;
					//alert(url);
					jQuery.ajax({
						url: url,
						success: function (result) {  //alert(result);
						  resp = jQuery.parseJSON(result); 
						  if (resp.result === "ok")  {
							alert("Chqanges were saved.");
							window.location.reload();
						  }
						  else alert(resp.message);
						}
					});
				}
				</script>';

				echo '</td></tr>';
				}
              }

            echo '</table>';

            echo '<script> 
			jQuery( document ).ready(function() {
				var loc = top.window.location.href;
				n = loc.indexOf("admin");
				if (n== -1) jQuery(".admin_div").remove();
				else jQuery(".sign_btn").remove();
			});
			function signedreports(){ 
				if (confirm("Are you sure you want to sign this week report?")) { 
				  window.location = "'.WP.'managertcureports?querytype=updatefromdetail&rstatus=Yes&id='.$weekrecordid.'&empnumber='.$_GET["empnumber"].'&startdate='.$navweeksdate.'"; 
				 } 
				 else { } 
			}
			
			function unsignedreports(){ 
				if (confirm("Are you sure you want to unsign this week report?")) { 
				  window.location = "'.WP.'managertcureports?querytype=updatefromdetail&rstatus=No&id='.$weekrecordid.'&empnumber='.$_GET["empnumber"].'&startdate='.$navweeksdate.'"; 
				 } 
				 else { } 
			}
			 
			 
			</script>';
			 echo '</div>';// id="page-container"

            unset($_SESSION["error"]);
            unset($_SESSION["success"]);

          }

        public static function addhours($old,$new)
        {
        $oldPlayTime=$old;
        $PlayTimeToAdd=$new;
        $old=explode(".",$oldPlayTime);
        $play=explode(".",$PlayTimeToAdd);

        $hours=$old[0]+$play[0];
        $minutes=$old[1]+$play[1];

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
          $oldPlayTime=$old;
          $PlayTimeToAdd=$new;
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

     public static function isTodayAfter(){
		 $q="SELECT * FROM cron WHERE crName='timesclock_datasync_weekly'";
		 $r=db::PrepareLoadSingleRow($q);
//echo date('Y-m-d H:i:s').' '.$r['crLastRun'].'<br>';
		 if (date('Y-m-d H:i:s')> $r['crLastRun'] ) return true;
		 else return false;
	 }
	 
	 public static function isThisLastWeek($weekenddate){
		$now=date('Y-m-d');
		
		/****************
		$unixTimestamp = strtotime($now);
		$dayOfWeek = date("l", $unixTimestamp);
		if ($dayOfWeek=='Sunday') $weekenddate=$now;
		else
		//$weekenddate= date('m/d/Y', strtotime('next Sunday', strtotime(date('m/d/Y'))));
	//echo '>>>'.$weekenddate;
	   // $startdate = date('Y-m-d', strtotime('previous Mondey', strtotime($weekenddate)));
	   ****************/
		$weekenddate=val::convDate(USER_DATE,MYSQL_DATE,$weekenddate);
		if ($weekenddate > $now ) {return true;}
		else {return false;}
	 }
	 
	  public static function withinLimit($weekenddate){
		$now=date('Y-m-d H:i:s');
		$limit=val::convDate(USER_DATE_TIME,MYSQL_DATE_TIME,$weekenddate.' 11:59 pm');
		
		$q = 'SELECT * FROM webapp_admin_settings WHERE asKey="UNSIGN_REPORT_DAYS"';
		$r = db::PrepareLoadSingleRow($q);
		$days = $r['asValue'];
		
		$q = 'SELECT * FROM webapp_admin_settings WHERE asKey="UNSIGN_REPORT_HOURS"';
		$r = db::PrepareLoadSingleRow($q);
		$hours = $r['asValue'];
		
		
		
		$end_date = date('Y-m-d H:i:s',strtotime($limit." +{$days} days {$hours} hours"));
		//echo $now.'  ENDDATE:'.$end_date;
		if ( $now <= $end_date) return true; 
		else return false;
		  
	  }
	 
	 

}
