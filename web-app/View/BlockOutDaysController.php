<?php
/** The CompanyOffice controller */
class BlockOutDaysController extends AppController {

    const __DUMP__ = false;

    public static $id, $data, $pg;

	public static function Init(){
		BlockOutDaysView::All(DaysOffModel::$dt,DaysOffModel::$fldLbl);
	}

	public static function Edit(){
		if (isset($_POST['bodDateStart'])){
			
			$year=date('Y',strtotime($_POST['bodDateStart']));
			$data=array(
            'bodID' =>$_GET['id'],
            'bodYear' => $year,
			'bodIsInterval'=>$_POST['bodIsInterval'],
            'bodDateStart' => $_POST['bodDateStart'],
            'bodDateEnd' =>$_POST['bodDateEnd'], 
            'bodOffices' =>implode('|',$_POST['off']),
            'bodPositions' =>implode('|',$_POST['pos']),
        );
		}
		elseif($_GET['id']==0){
			$data=array(
            'bodID' => 0,
            'bodYear' => date('Y'),
			  'bodIsInterval'=>'single',
            'bodDateStart' => '',
            'bodDateEnd' =>'', 
            'bodOffices' =>'',
            'bodPositions' =>''
        );
		}
		else{
			$q="SELECT * FROM  bod_days WHERE bodID=".$_GET['id'];
			$res=db::PrepareLoad($q);
			$data=$res[0];
		}
		BlockOutDaysView::Edit($data);
	}
	
	public static function Delete(){
		$q="DELETE FROM  bod_days WHERE bodID=".$_GET['id'];
		$r=db::SQL($q);
		
		if ($r) msg::set('Day was deleted','success');
		else msg::set('There was problem to delete','error');
		cf::Redirect('block-out-days');
	}
	
	public static function Save(){
		$date=val::convDate(USER_DATE,MYSQL_DATE,$_POST['bodDateStart']);
		$year=date('Y',strtotime($date));
		$_POST['bodDateStart'] =$date;
		
		if (isset($_POST['pos'])) $bodPositions=implode('|',$_POST['pos']);
		else $bodPositions='';
		if (isset($_POST['off'])) $bodOffices=implode('|',$_POST['off']);
		else $bodOffices='';
		
		if ($_POST['bodIsInterval']=='single') $_POST['bodDateEnd']=$_POST['bodDateStart'];
		else 
		if (isset($_POST['bodDateEnd'])) $_POST['bodDateEnd']=val::convDate(USER_DATE,MYSQL_DATE,$_POST['bodDateEnd']);
		
		$offArray = array();
		
		foreach($_POST['off'] as $k=>$off){
			$offArray[] = "bodOffices LIKE '%{$off}%' ";
		}
		if (!empty($offArray))	$offCond ='AND ('. implode (' OR ',$offArray).')';
		else $offCond = '';
		
		
		$posArray = array();
		
		foreach($_POST['off'] as $k=>$off){
			$posArray[] = "bodPositions LIKE '%{$off}%' ";
		}
		if (!empty($offArray))	$posCond ='AND ('. implode (' OR ',$posArray).')';
		else $posCond = '';
		
		if ($_GET['id']==0) $idCond = '';
		else $idCond =' AND bodID<>'.$_GET['id'];
		
		$q="SELECT * FROM  bod_days WHERE 1=1 {$idCond} {$posCond} {$offCond}";
		$res=db::prepareLoad($q);
		
		foreach ($res as $r){
			//echo $_POST['bodDateStart'].'--'.$_POST['bodDateEnd'].' | '.$r['bodDateStart'].' '.$r['bodDateEnd'].'<br>';
		
			if ( ($_POST['bodDateStart']>=$r['bodDateStart']  && $_POST['bodDateStart']<=$r['bodDateEnd'])
				|| 
				  ($_POST['bodDateEnd']>=$r['bodDateStart'] && $_POST['bodDateEnd']<=$r['bodDateEnd'])
			   )
			   {
				$range=val::convDate(MYSQL_DATE,USER_DATE,$r['bodDateStart']);
				if ($r['bodDateStart']!=$r['bodDateEnd']) $range.=' - '
				.val::convDate(MYSQL_DATE,USER_DATE,$r['bodDateEnd']);
				
			   msg::set("Your request overlaps existing range (".$range.")  .Please correct it.",'error');
			   self::Edit();
			   exit;
			   }
		}
		
		if($_GET['id']==0){
			if ($_POST['bodDateEnd']=='NULL')
				$q="INSERT INTO bod_days SET bodYear='{$year}',bodIsInterval='{$_POST['bodIsInterval']}',
				bodDateStart='{$_POST['bodDateStart']}', bodDateEnd={$_POST['bodDateEnd']},
				bodOffices='{$bodOffices}',bodPositions='{$bodPositions}'";
			else
				$q="INSERT INTO bod_days SET bodYear='{$year}',bodIsInterval='{$_POST['bodIsInterval']}',
				bodDateStart='{$_POST['bodDateStart']}', bodDateEnd='{$_POST['bodDateEnd']}',
				bodOffices='{$bodOffices}',bodPositions='{$bodPositions}'";
		}
		else {
			if ($_POST['bodDateEnd']=='NULL')
				$q="UPDATE bod_days SET bodYear='{$year}',bodIsInterval='{$_POST['bodIsInterval']}',
				bodDateStart='{$_POST['bodDateStart']}', bodDateEnd={$_POST['bodDateEnd']},
				bodOffices='{$bodOffices}',bodPositions='{$bodPositions}' WHERE bodID=".$_GET['id'];
			else
				$q="UPDATE bod_days SET bodYear='{$year}',bodIsInterval='{$_POST['bodIsInterval']}',
				bodDateStart='{$_POST['bodDateStart']}', bodDateEnd='{$_POST['bodDateEnd']}',
				bodOffices='{$bodOffices}',bodPositions='{$bodPositions}' WHERE bodID=".$_GET['id'];
			
		}
		//echo $q;exit;
		$r=db::SQL($q);
		if ($r)	msg::set('Changes were saved','success');
		else msg::set('There was problem to save changes','success');
		cf::Redirect('block-out-days');
	}
	

}