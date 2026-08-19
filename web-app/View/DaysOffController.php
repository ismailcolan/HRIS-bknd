<?php
class DaysOffController extends AppController{

    const __DUMP__ = false;

    public static $id, $data, $pg;

	public static function Init(){
		DaysOffView::All(DaysOffModel::$dt,DaysOffModel::$fldLbl);
	}

	public static function Edit(){
		
		if (isset($_POST['nwdDateStart'])){ 
			
			$year=date('Y',strtotime($_POST['nwdDateStart']));
			$data=array(
            'nwdID' =>$_GET['id'],
            'nwdYear' => $year,
			'nwdIsInterval'=>$_POST['nwdIsInterval'],
            'nwdDateStart' => $_POST['nwdDateStart'],
            'nwdDateEnd' =>$_POST['nwdDateEnd'], 
            'nwdOffices' =>implode('|',$_POST['off']),
            'nwdPositions' =>''
        );
		}
		elseif($_GET['id']==0){ 
			$data=array(
            'nwdID' => 0,
            'nwdYear' => date('Y'),
			'nwdIsInterval'=>'single',
            'nwdDateStart' => '',
            'nwdDateEnd' =>'', 
            'nwdOffices' =>'',
            'nwdPositions' =>''
        );
		}
		else{
			$q="SELECT * FROM  nonworking_days WHERE nwdID=".$_GET['id'];
			$res=db::PrepareLoad($q);
			$data=$res[0];
		}
		DaysOffView::Edit($data);
	}
	
	public static function Delete(){
		$q="DELETE FROM  nonworking_days WHERE nwdID=".$_GET['id'];
		$r=db::SQL($q);
		
		if ($r) msg::set('Day was deleted','success');
		else msg::set('There was problem to delete','error');
		cf::Redirect('nonworking-days');
	}
	
	public static function Save(){
		
		$date=val::convDate(USER_DATE,MYSQL_DATE,$_POST['nwdDateStart']);
		$year=date('Y',strtotime($date));
		$_POST['nwdDateStart'] =$date;
		
		if (isset($_POST['pos'])) $nwdPositions=implode('|',$_POST['pos']);
		else $nwdPositions='';
		if (isset($_POST['off'])) $nwdOffices=implode('|',$_POST['off']);
		else $nwdOffices='';
		
		if ($_POST['nwdIsInterval']=='single') $_POST['nwdDateEnd']=$_POST['nwdDateStart'];
		else 
			if (isset($_POST['nwdDateEnd'])) $_POST['nwdDateEnd']=val::convDate(USER_DATE,MYSQL_DATE,$_POST['nwdDateEnd']);
	
		
		$offArray = array();
		
		foreach($_POST['off'] as $k=>$off){
			$offArray[] = "nwdOffices LIKE '%{$off}%' ";
		}
		if (!empty($offArray))	$offCond ='AND ('. implode (' OR ',$offArray).')';
		else $offCond = '';
		
		if ($_GET['id']==0) $idCond = '';
		else $idCond =' AND nwdID<>'.$_GET['id'];
		
		$q="SELECT * FROM nonworking_days WHERE 1=1 {$idCond} {$offCond}";
		
		$res=db::prepareLoad($q);
		
		
		foreach ($res as $r){
			if ( ($_POST['nwdDateStart']>=$r['nwdDateStart']  && $_POST['nwdDateStart']<=$r['nwdDateEnd'])
				|| 
				  ($_POST['nwdDateEnd']>=$r['nwdDateStart'] && $_POST['nwdDateEnd']<=$r['nwdDateEnd'])
			   )
			   {
				$range=val::convDate(MYSQL_DATE,USER_DATE,$r['nwdDateStart']);
				if ($r['nwdDateStart'] != $r['nwdDateEnd']) $range.=' - '.val::convDate(MYSQL_DATE,USER_DATE,$r['nwdDateEnd']);
				
			   msg::set("Your request overlaps existing range (".$range."). Please correct it.",'error');
			   self::Edit();
			   exit;
			   }
		}
		
		
			
		if($_GET['id']==0){
			if ($_POST['nwdDateEnd']=='NULL')
				$q="INSERT INTO nonworking_days SET nwdYear='{$year}',nwdIsInterval='{$_POST['nwdIsInterval']}',
				nwdDateStart='{$_POST['nwdDateStart']}', nwdDateEnd={$_POST['nwdDateEnd']},
				nwdOffices='{$nwdOffices}',nwdPositions='{$nwdPositions}'";
			else
				$q="INSERT INTO nonworking_days SET nwdYear='{$year}',nwdIsInterval='{$_POST['nwdIsInterval']}',
				nwdDateStart='{$_POST['nwdDateStart']}', nwdDateEnd='{$_POST['nwdDateEnd']}',
				nwdOffices='{$nwdOffices}',nwdPositions='{$nwdPositions}'";
		}
		else {
			if ($_POST['nwdDateEnd']=='NULL')
				$q="UPDATE nonworking_days SET nwdYear='{$year}',nwdIsInterval='{$_POST['nwdIsInterval']}',
				nwdDateStart='{$_POST['nwdDateStart']}', nwdDateEnd={$_POST['nwdDateEnd']},
				nwdOffices='{$nwdOffices}',nwdPositions='{$nwdPositions}' WHERE nwdID=".$_GET['id'];
			else
				$q="UPDATE nonworking_days SET nwdYear='{$year}',nwdIsInterval='{$_POST['nwdIsInterval']}',
				nwdDateStart='{$_POST['nwdDateStart']}', nwdDateEnd='{$_POST['nwdDateEnd']}',
				nwdOffices='{$nwdOffices}',nwdPositions='{$nwdPositions}' WHERE nwdID=".$_GET['id'];
			
		}
		//echo $q;exit;
		$r=db::SQL($q);
		if ($r)	msg::set('Changes were saved','success');
		else msg::set('There was problem to save changes','success');
		cf::Redirect('nonworking-days');
	}
	

}