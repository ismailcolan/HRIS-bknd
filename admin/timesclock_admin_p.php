<?php
$sdate=date('Y-m-d');
$q="SELECT * FROM apply_forms WHERE afID=".$_GET['id'];
$res=db::PrepareLoadSingleRow($q);

if ($res!==false){
	if (isset($res['afEmplNumber']) && $res['afEmplNumber'] != ''){	
		//ManagerRtcureportsFrontController_p::ListWorkingHours($res['afEmplNumber'],$sdate);
		header('Location:/managertcureports?user_id='.$res['afID'].'&from=admin&querytype=listattendence&empnumber='.$res['afEmplNumber'].'&MTY=&startdate=');
	}
	else {
			echo 'No employee number set';
	}
}
?>
