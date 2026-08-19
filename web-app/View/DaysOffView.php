<?php

class DaysOffView extends AppView{
	public static $id, $data, $pg;

	public static function All($pg, $lbls){
		$q = 'SELECT * FROM company_offices WHERE coActive="Yes" ORDER BY coName ASC';
       $_all_offices = db::PrepareLoad($q);
	   if (!is_array($_all_offices)) $_all_offices=array();
	   $all_offices = array();
	   foreach ($_all_offices as $k=>$off){
		   $all_offices[$off['coID']]=$off['coName'];
	   }
	   $all_offices_nmb=count($all_offices);
	   
	   $q = 'SELECT * FROM positions WHERE pActive="Yes" ORDER BY pName ASC';
       $_all_positions = db::PrepareLoad($q);
	   if (!is_array($_all_positions)) $_all_positions=array();
	   $all_positions = array();
	   foreach ($_all_positions as $k=>$pos){
		   $all_positions[$pos['pID']]=$pos['pName'];
	   }
	   //cf::pr($all_positions);
	   $all_positions_nmb=count($all_positions);
	   
	   $link='<a href="?all=true">show past years</a>';
		if(isset($_GET['last_two'])) $link='<a href="?all=true">show past years</a>';
		if(isset($_GET['all'])) $link='<a href="?last_two=true">show current and next years only</a>';
		
		echo'
		<section class="page-content">        
		<div class="container-fluid"><div class="page-header row no-gutter">
		<div class="col-sm-6"><h3>Non-working days</h3></div>
		 <div class="col-sm-6 text-right">
		 '.$link.'&nbsp;&nbsp;&nbsp;&nbsp;
		  <a href="?cmd=edit&id=0" class="btn btn-primary"><i class="fa fa-plus"></i> Add new</a>
		 </div>
		</div>
		</section>';
		msg::get();
		table::create($pg["tbl"], '', "asc", 10000,null);//self::ADMIN_LISTS_ROWS_LIMIT);
		
		table::$cols[] = Array('Description', 'Description',array("class" => "link", "sort" => false));
		table::$cols[] = Array('Action', 'Action',array("class" => "link", "sort" => false));
		
		$thisYear=date('Y');
		$nextYear=$thisYear+1;
		
		$now = date('Y-m-d');
		if(!isset($_GET['all'])) $where=" (nwdYear='{$thisYear}' or nwdYear='{$nextYear}') ";
		else $where = " 1=1 ";
		$_data=array();
		
		$query="SELECT * FROM nonworking_days WHERE {$where} AND nwdDateStart >= '{$now}' ORDER BY nwdDateStart DESC";
		$res=db::PrepareLoad($query);  if($res==false) $res=array();
		$_data=$res;
		
		$query="SELECT * FROM nonworking_days WHERE {$where} AND nwdDateStart < '{$now}' ORDER BY nwdDateStart DESC";
		$res=db::PrepareLoad($query);  if($res==false) $res=array();
		foreach ($res as $r){
		   $_data[]=$r;
		}
		
		table::execute($query,null,false);
		table::$data=$_data;
		//table::$rowsTotal = count($_data);
		$year=date('Y');
		$fedDays =cf::getFederalHolydays($year);
		foreach ($fedDays as $k=>$d) {
			$fedDays[$k]=val::convDate(MYSQL_DATE,USER_DATE,$d);
		}
		echo '<tr><td>US Holidays: <b>'.implode(', ',$fedDays).'</b>; Offices: all</td><td></td></tr>';
		
		
		$fedDays =cf::getFederalHolydays($year+1);
		foreach ($fedDays as $k=>$d) {
			$fedDays[$k]=val::convDate(MYSQL_DATE,USER_DATE,$d);
		}
		echo '<tr><td>US Holidays: <b>'.implode(', ',$fedDays).'</b>; Offices: all</td><td></td></tr>';
		//echo '<tr><td><b>'.implode(', ',$fedDays).'</b> Offices: all  Official US holidays</td><td></td></tr>';
		
		
		
		foreach (table::$data as $r => &$cells) {
			
			$txt=val::convDate(MYSQL_DATE,USER_DATE,$cells['nwdDateStart']);
			if ($cells['nwdIsInterval']=='range') $txt.=' - '.val::convDate(MYSQL_DATE,USER_DATE,$cells['nwdDateEnd']);
			$txt.=': ';
			
			$offices_arr=explode('|',$cells['nwdOffices']);
			$offices=array();
			foreach($offices_arr as $k=>$i){
				if(isset($all_offices[$i])) $offices[$i]=$all_offices[$i];
			}
			$offices_nmb=count($offices);
			//$offices_diff = array_diff($all_offices, $offices);
			//$offices_diff_nmb=count($offices_diff);
			if ($offices_nmb==$all_offices_nmb){
				$off_text=' Offices: all';
			} else if ($offices_nmb <= 3){
				$off_text=' Offices: some offices';
			}
			else {
				$off_text=' Offices: some offices';
			}
			if ($offices_nmb==0) $off_text='<span style="color:red"> Offices: NOT SPECIFIED</span>';
			
			/*********************
			$positions_arr=explode('|',$cells['nwdPositions']);
			$positions=array();
			foreach($positions_arr as $k=>$i){
				if(isset($all_positions[$i])) $positions[$i]=$all_positions[$i];
			}
			$positions_nmb=count($positions);
			//$positions_diff = array_diff($all_positions, $positions);
			//$positions_diff_nmb=count($positions_diff);
			if ($positions_nmb==$all_positions_nmb){
				$pos_text=' for all positions ';
			} else if ($positions_nmb <= 3){
				$pos_text=' for next positions: '.implode(', ',$positions);
			}
			else {
				$pos_text=' for some positions: <span style="color:blue">View >></span>';
			}
			**************/
			
			$pos_text='';
			$now = date('Y-m-d');
			if ($cells['nwdDateStart'] > $now)
			$cells['Description'] ='<a href="?cmd=edit&id='.$cells['nwdID'].'"><b>'.$txt.'</b>'.$off_text.''.$pos_text.'<a>';
		    else
			$cells['Description'] ='<b>'.$txt.'</b>'.$off_text.''.$pos_text;
			
			if ($cells['nwdDateStart'] > $now)
			$cells['Action']='<a href="javascript:void(0)" onclick="if(confirm(\'Are you sure to delete this?\')) window.location=\'?cmd=delete&id='.$cells['nwdID'].'\'">Delete >></a>';
		    else $cells['Action']='';
		}
		table::load();
	}
	
	public static function Edit($d){
		
		if(!isset($d['nwdYear'])) {$this_year=date('Y');} else {$this_year=$d['nwdYear'];}
		$next_year=$this_year+1;
		
		$q = 'SELECT * FROM company_offices WHERE coActive="Yes" ORDER BY coName ASC';
       $all_offices = db::PrepareLoad($q);
	   
	   $q = 'SELECT * FROM positions WHERE pActive="Yes" ORDER BY pName ASC';
      $all_positions = db::PrepareLoad($q);
	  
	  
		if ($d['nwdID'] ==0 ) $head='Add new Non-working day(s)';
		else $head='Edit Non-working day(s)';
		
		
		
		echo'
		<section class="page-content">        
		<div class="container-fluid"><div class="page-header row no-gutter">
		<div class="col-sm-6"><h3>'.$head.'</h3></div>
		</section>';
		
		
		echo '<div id="content" style="text-align:center;width:100%">';
		
		echo '<div id="days_content" style="text-align:left;margin:0 auto;width:70%;">';
		msg::get();
		echo '<form id="myform" method="POST" onsubmit="return checkSubmit()">';
	    
			
		   echo '<input type="hidden" name="nwdYear" value="'.$d['nwdYear'].'"><br>';
		   
		   $sch='checked';$sdr='';
		   if ($d['nwdIsInterval']=='single'){$sch='checked';$sdr='';}
		   else {$sch='';$sdr='checked';}
		   echo '<br>
		   <input '.$sch.' onclick="jQuery(\'#end_date\').hide();jQuery(\'#enddate\').val(\'\').prop(\'disabled\',true); " type="radio" id="single" name="nwdIsInterval" value="single"><label for="single">&nbsp;Single Day</label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input '.$sdr.' onclick="jQuery(\'#end_date\').show()" type="radio" id="range" name="nwdIsInterval" value="range"><label for="range">&nbsp;Date Range</label> 
		   <br><br>';
		   
		   if(!empty($d['nwdDateStart'])) $d['nwdDateStart']=val::convDate(MYSQL_DATE,USER_DATE,$d['nwdDateStart']);
		   
		   echo '<label for="startdate"  style="width:100px;font-weight:bold">Start date:<span style="color:#F00">'.ASTERISK.'</span></label>
		   <input readonly style="width:200px"  id="startdate" class="days"  name="nwdDateStart" 
		   onchange ="changeStartDate()" value="'.$d['nwdDateStart'].'"><br>';
		   
		   if(!empty($d['nwdDateStart'])) $d['nwdDateEnd']=val::convDate(MYSQL_DATE,USER_DATE,$d['nwdDateEnd']);
		   
		   if ($d['nwdIsInterval']=='single') $display='style="display:none"';
		   else $display='';
		   
		   echo '<div '.$display.' id="end_date">
		   <label for="enddate" style="width:100px;font-weight:bold">End date:<span style="color:#F00">'.ASTERISK.'</span></label>
		   <input disabled  readonly style="width:200px"  id="enddate" class="days"  name="nwdDateEnd" 
		   value="'.$d['nwdDateEnd'].'"></div>';
		
			/*******************
			echo '<br><div class="wrap" ><label style="width:100px;font-weight:bold">Positions:</label>
			<input id="mass" onchange="massAssign(this)" type="checkbox" >
			&nbsp;<label for="mass" style="font-weight:normal">Select / Deselect all</label>
			<br>';
			$positions=explode('|',$d['nwdPositions']);
			foreach ($all_positions as $o){
				echo '<div style="width:33.3%;display:inline-block;">';
				$checked='';
				if (in_array($o['pID'],$positions)) $checked='checked';
					echo '<input '.$checked.' id="pos'.$o['pID'].'" type="checkbox" name="pos[]" value="'.$o['pID'].'">';
					echo '&nbsp;<label style="font-weight:normal" for="pos'.$o['pID'].'">'.$o['pName'].'</label><br>';
				echo '</div>';
			}
			echo '</div>';
			*********************/
			echo '<br><br><div class="wrap"><label style="width:100px;font-weight:bold">Offices:</label>
			<input id="_mass" onchange="massAssign(this)" type="checkbox" >
			&nbsp;<label style="font-weight:normal" for="_mass" >Select / Deselect all</label>
			<br>';
			$offices=explode('|',$d['nwdOffices']);
			foreach ($all_offices as $o){
				echo '<div style="width:33.3%;display:inline-block;">';
				$checked='';
				if (in_array($o['coID'],$offices)) $checked='checked';
					echo '<input '.$checked.' id="of'.$o['coID'].'" type="checkbox" name="off[]" value="'.$o['coID'].'">';
					echo '&nbsp;<label style="font-weight:normal" for="of'.$o['coID'].'">'.$o['coName'].'</label><br>';
				echo '</div>';
			}
		echo '
		<hr>
		</div>
		</div>
		<button class="btn btn-primary" onclick="goSubmit()">Save</button>
		</form>
		
		';
				
		
		
		echo ' 
		
		<script>
		
		var to_submit=false;
		var to_continue=false;
		var startDate="'.val::convDate(MYSQL_DATE,USER_DATE,date('Y-m-d')).'";
		
		function changeStartDate(){
			if (to_continue==false){to_continue=true; return;}
			var startDate=jQuery("#startdate").val();
			var endDate=jQuery("#enddate").val("");
			var day = new Date(startDate);
			var nextDay = new Date(day);
			nextDay.setDate(day.getDate()+1);
			var nextDayStr=nextDay.toLocaleDateString("en-US");
			jQuery("#enddate").prop("disabled", false); 
			jQuery("#enddate").datepicker("destroy").datepicker({
				startDate: nextDayStr,
				endDate: "12/31/'.$next_year.'",
				daysOfWeekDisabled: "0",
				format: "m/d/yyyy",
			    autoclose: true,
				weekStart:0,
			  //  multidate: true,
			    defaultViewDate: { year: '.$this_year.', month: 0, day: 1 }
			});
		}
		function initDatepicker(){
			
			jQuery("#startdate").datepicker({
				startDate: startDate,
				endDate: "12/31/'.$next_year.'",
				daysOfWeekDisabled: "0",
				format: "m/d/yyyy",
				weekStart:0,
			   autoclose: true,
			  //  multidate: true,
			   defaultViewDate: { year: '.$this_year.', month: 0, day: 1 }
			});
		}
		
		jQuery( document ).ready(function() {
			initDatepicker();
		});
		
		function checkSubmit(){
			return to_submit;
		}
		
		function goSubmit(){ 
			var ret=true;
			
			jQuery(".days:visible").each(function( index ) {
				var val=jQuery(this).val();
				if(val==""){
					ret=false;
					return false;
				}
			});
			
			if(ret==false) {
				myalert("Warning","There are non filled in Dates fields. Please fill in.");
				return;
			}
			
			
			to_submit=true;
			jQuery("#enddate").prop("disabled",false);
			jQuery("#myform").submit();
			
		}
		
		function massAssign(that){ 
			if(jQuery(that).is(":checked")){ 
				jQuery(that).closest(".wrap").find("input[type=checkbox]").prop("checked","checked");
				jQuery(that).closest(".wrap").find("input[type=checkbox]").attr("checked","checked");
			}
			else {
				jQuery(that).closest(".wrap").find("input[type=checkbox]").removeProp("checked");
				jQuery(that).closest(".wrap").find("input[type=checkbox]").removeAttr("checked");
			}	
		}	
		</script>';
	}
}

