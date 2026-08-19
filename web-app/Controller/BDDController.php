<?php
/** The CompanyOffice controller */

class BDDController extends AppController {

    const __DUMP__ = false;

     public static $id, $data, $pg, $rq;
	 
	 
	final static function newDownload(){
		$res = CronController::downloadFile();
	
		if($res['result']=='OK'){
			$file = $res['file'];
			msg::set('New deductions sheet file <b>'.$res['file'].'</b> downloaded.','success');	
		}
		else {
			msg::set('There was a problem to download deductions sheet file.','error');
		}
		
        cf::Redirect(ADMIN.'benefits_deductions_data');
		exit;
	}
	 
	final static function createNewUpload(){
		$res = CronController::uploadFile();
	
		if($res['result']=='OK'){
			$file = $res['file'];
			
			msg::set('New demographic sheet file <b>'.$res['file'].'</b> was generated and uploaded.','success');
			
		}
		else {
			msg::set('There was a problem to upload new demographic sheet file','error');
		}
		
        cf::Redirect(ADMIN.'benefits_deductions_data');
		
		exit;
	}
	
	
	public static function delDemographics(){ //echo '>>>>>>'.$GLOBALS['LOCAL_UPLOAD_DIR'];exit;
		$LOCAL_DOWNLOAD_DIR = FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/for_upload/benefits_uploaded/";
		$files=array();
		if(!empty($_POST['file'])) $files=$_POST['file'];
		
		foreach($files as $file){
			unlink ($LOCAL_DOWNLOAD_DIR.$file);
		}
		if (count($files) == 0 ) {
			$msg = 'No selected Demographics sheets files to delete. Nothing deleted. ';
			msg::set($msg);
		}
		else {
		if (count($files) == 1 ) $msg = 'Demographics sheet '; else $msg = 'Demographics sheets: ';
		$msg = $msg .'<b>'.implode(', ',$files).'</b>';
		if (count($files) == 1 ) $msg.=' was deleted'; else $msg.=' were deleted';
		
		msg::set($msg,'success');
		}
		
		cf::Redirect(ADMIN.'benefits_deductions_data');
		exit;
	}
	
	public static function delDeductions(){ //echo '>>>>>>'.LOCAL_UPLOAD_DIR;exit;
		$LOCAL_DOWNLOAD_DIR = FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/downloads/benefits_downloaded/";
		$files=array();
		if(!empty($_POST['file'])) $files=$_POST['file'];
		
		foreach($files as $file){
			unlink ($LOCAL_DOWNLOAD_DIR.$file);
		}
		if (count($files) == 0 ) {
			$msg = 'No selected Deductions sheets files to delete. Nothing deleted. ';
			msg::set($msg);
		}
		else {
		if (count($files) == 1 ) $msg = 'Deductions sheet '; else $msg = 'Deductions sheets: ';
		$msg = $msg .'<b>'.implode(', ',$files).'</b>';
		if (count($files) == 1 ) $msg.=' was deleted'; else $msg.=' were deleted';
		msg::set($msg,'success');
		}
		cf::Redirect(ADMIN.'benefits_deductions_data');
		exit;
	}

    final static function BenefitsDeductionsDataList()
    {
		msg::get();
		$dir =FP . 'add/customer'.$_SESSION['customerID'].'/uploads/for_upload/benefits_uploaded/';
		$url= WP . 'add/customer'.$_SESSION['customerID'].'/uploads/for_upload/benefits_uploaded/';
		
		$myDirectory = opendir($dir);
		// get each entry
		$dirArray=array();
		while($entryName = readdir($myDirectory)) {
			$dirArray[] = $entryName;
		}

		closedir($myDirectory);

		$indexCount	= count($dirArray);
		$files=$indexCount-2;
		//echo '<span style="color:#AAA">'.$files.' files total in folder</span><br>';
		
		$aFiles=array();
		for($index=0; $index < $indexCount; $index++) {
				if (substr("$dirArray[$index]", 0, 1) != "."){ // don't list hidden files
				$aFiles[]=array('fname'=>$dirArray[$index],'fdate'=>date('Y-m-d H:i:s',stat($dir.'/'.$dirArray[$index])['atime']));
			}
		}

		
		function cmp($a, $b)
		{
			if ($a['fdate'] == $b['fdate']) {
				return 0;
			}
			return ($a['fdate'] > $b['fdate']) ? -1 : 1;
		}
		usort($aFiles, "cmp");
		echo '<h4>Demographics sheets:</h4>';
		 if (count($aFiles) > 1 ){
		 echo '<a href="javascript:void(0)" onclick = "showOld()" id="viewOlder" style="width:300px" class="btn btn-info">
		 <i class="fa fa-folder-open" aria-hidden="true"></i>&nbsp;&nbsp;View older Demographics sheets >></a>
		 <a href="javascript:void(0)" style="display:none;width:300px" onclick = "hideOld()" id="hideOlder" class="btn btn-info">
		 <i class="fa fa-folder" aria-hidden="true"></i>&nbsp;&nbsp;Hide older Demographics sheets >></a>
		 &nbsp;&nbsp;&nbsp;';
		 }
		 echo '<a href="?cmd=new_upload" class="btn btn-primary" style="width:450px"><i class="fa fa-cloud-upload" aria-hidden="true"></i>&nbsp;&nbsp; Generate and upload new Demographic sheet now >></a>
		 <br><br>';
		if (count($aFiles)==0) echo '<div style="text-align:center">No demographics sheets uploaded succesfully.</div>';
		else {
		echo '<form method="POST">';
		echo '<table id="demographicsTable" border="1" cellpadding="5" cellspacing="0" >';
		echo '<thead><tr><th style="text-align:center"><input type="checkbox" style="cursor:pointer" title="Select / Deselect all" id="selAllDemographics" 
		onclick="SelDeselDemographics()"></th><th>Filename</th><th>Uploaded</th></tr></thead>';
		$i=0;
		foreach ($aFiles as $k =>$aFile) {
			     if ($i>0) {
					echo '<tr style="display:none" class="old">'; 
				 }
				 else echo '<tr>';
				 echo '<td style="text-align:center;" ><input style="cursor:pointer" name="file[]" type="checkbox" value="'.$aFile['fname'].'" class="selAllDemographicsData"></td>';
				 echo '<td style="width:70%"><a title="Click to download" href="'.$url.$aFile['fname'].'">'.$aFile['fname'].'</a></td>';			
				 echo '<td>'.date(USER_DATE_TIME,strtotime($aFile['fdate'])).'</td>';
				 echo '</tr>';
				 
				 $i++;
		}
		
		 echo "</table>";
		 
		  echo  '<script>
		  
		    function SelDeselDemographics(){
				showOld();
				if (jQuery("#selAllDemographics").is(":checked")) {
					jQuery(".selAllDemographicsData").prop("checked",true);
				}
				else { 
					jQuery(".selAllDemographicsData").prop("checked",false);
				}
			}
			function showOld(){
				jQuery("#demographicsTable tr.old" ).each(function( index ) { 
				  jQuery(this).show();
				});
				jQuery("#viewOlder").hide();
				jQuery("#hideOlder").show();
			}
			
			function hideOld(){
				jQuery("#demographicsTable tr.old" ).each(function( index ) { 
				  jQuery(this).hide();
				});
				jQuery("#viewOlder").show();
				jQuery("#hideOlder").hide();
			}
		 </script>';
		  echo '<br><input type="submit" onclick="return confirm(\'Are you sure to delete selected Demographics sheets files?\')" name="submitDemographics" 
				style="width:300px" class="btn btn-warning" value="Delete selected Demographic sheets >>">';
		echo "</form>";
		}
		echo '<hr><br>';
		/********************************************************************/
		
		$dir =FP . 'add/customer'.$_SESSION['customerID'].'/uploads/downloads/benefits_downloaded/';
		$url= WP . 'add/customer'.$_SESSION['customerID'].'/uploads/downloads/benefits_downloaded/';
		
		$myDirectory = opendir($dir);
		// get each entry
		$dirArray=array();
		while($entryName = readdir($myDirectory)) {
			$dirArray[] = $entryName;
		}

		closedir($myDirectory);

		$indexCount	= count($dirArray);
		$files=$indexCount-2;
		//echo '<span style="color:#AAA">'.$files.' files total in folder</span><br>';
		
		$aFiles=array();
		for($index=0; $index < $indexCount; $index++) {
				if (substr("$dirArray[$index]", 0, 1) != "."){ // don't list hidden files
				$aFiles[]=array('fname'=>$dirArray[$index],'fdate'=>date('Y-m-d H:i:s',stat($dir.'/'.$dirArray[$index])['atime']));//atime,mtime,ctime
			}
		}
		usort($aFiles, "cmp");
		
		
		echo '<h4>Deductions sheets:</h4>';
		if (count($aFiles) > 1 ){
		 echo '<a href="javascript:void(0)" onclick = "showOldDeductions()" id="viewOlderDeductions" style="width:300px" class="btn btn-info">
		 <i class="fa fa-folder-open" aria-hidden="true"></i>&nbsp;&nbsp;View older Deductions sheets >></a>
		 <a href="javascript:void(0)"  onclick = "hideOldDeductions()" style="display:none;width:300px" id="hideOlderDeductions" class="btn btn-info">
		 <i class="fa fa-folder" aria-hidden="true"></i>&nbsp;&nbsp;Hide older Deductions sheets  >></a>
		 &nbsp;&nbsp;&nbsp;';
		 }
		 echo '<a href="?cmd=new_download" class="btn btn-primary" style="width:450px"><i class="fa fa-cloud-download" aria-hidden="true"></i>&nbsp;&nbsp;Download Deductions sheet now >></a><br><br>';
		
        if (count($aFiles)==0) echo '<div style="text-align:center">No deductions sheets downloaded.</div>';
		else {
		echo '<form method="POST">';
		echo '<table id="deductionsTable" border="1" cellpadding="5" cellspacing="0" >';
		echo '<thead><tr><th style="text-align:center;"><input style="cursor:pointer" type="checkbox"  title="Select / Deselect all" id="selAllDeductions" 
		onclick="SelDeselDeductions()"></th><th>Filename</th><th>Downloaded</th></tr></thead>';
		$i=0;
		foreach ($aFiles as $k =>$aFile) {
			     if ($i>0) {
					echo '<tr style="display:none" class="old">'; 
				 }
				 else echo '<tr>';
				 echo '<td style="text-align:center"><input name="file[]" style="cursor:pointer" type="checkbox" class="selAllDeductionsData" value="'.$aFile['fname'].'" ></td>';
				 echo '<td style="width:70%"><a title="Click to download" href="'.$url.$aFile['fname'].'">'.$aFile['fname'].'</a></td>';			
				 echo '<td>'.date(USER_DATE_TIME,strtotime($aFile['fdate'])).'</td>';
				 echo '</tr>';
				 
				 $i++;
		}
		
		 echo "</table>";
		 
		  echo  '<script>
		  
		    function SelDeselDeductions(){
				showOldDeductions();
				if (jQuery("#selAllDeductions").is(":checked")) {
					jQuery(".selAllDeductionsData").prop("checked",true);
				}
				else { 
					jQuery(".selAllDeductionsData").prop("checked",false);
				}
			}
			function showOldDeductions(){
				jQuery("#deductionsTable tr.old" ).each(function( index ) { 
				  jQuery(this).show();
				});
				jQuery("#viewOlderDeductions").hide();
				jQuery("#hideOlderDeductions").show();
			}
			
			function hideOldDeductions(){
				jQuery("#deductionsTable tr.old" ).each(function( index ) { 
				  jQuery(this).hide();
				});
				jQuery("#viewOlderDeductions").show();
				jQuery("#hideOlderDeductions").hide();
			}
		 </script>';
		
		echo '<br><input type="submit" onclick="return confirm(\'Are you sure to delete selected Deductions sheet files?\')" name="submitDeductions" 
				style="width:300px" class="btn btn-warning" value="Delete selected Deduction sheets >>">';
		echo "</form>";
		}
		echo '<hr>';
		echo '<div class="alert alert-warning" style="text-align:center"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
        Demographics sheet automatically uploaded on Sundays and Deductions sheet downloaded automatically on Fridays.
		</div>';
		
		
		 
    }

    
}