<?php
class SuperAdminCustomersController extends AppController {
	
	const __DUMP__ = false;
    public static $id, $data, $pg;
	
	
	public static function SuperAdminCustomers(){
		
		echo '<div style="width:100%;">
		<div style="display:inline-block;float:right;position:relative;top:20px;">
		<a href="?cmd=add" class="btn btn-primary"><i class="fa fa-plus"></i> Add new customer</a>
		</div>
		<h3 style="display:inline-block;">Add / Edit Customers</h3>
		</div>
		<hr>';
		msg::get();
		self::SuperAdminCustomersAjax();
	}
	

	public static function SuperAdminCustomersAjax(){
		db::SwitchToMain();	
		$pg["pfx"]='';
		table::create('customers','cID' /*$pg["key"]/*"coAdded"*/, "ASC", ADMIN_TABLES_ROWS_PER_PAGE);
		table::$cols[] = Array("ID" , "cID");
        table::$cols[] = Array("Customer Name" , "cCustomerName");
		table::$cols[] = Array("Customer Short Name" , "cCustomerShortName");
		table::$cols[] = Array("DB Name" , "cDBASE");
		//table::$cols[] = Array("Admin Email" , "cAdminEmail");
		//table::$cols[] = Array("Pass" , "cPass");
		table::$cols[] = Array("Active" , "cActive");
		
        table::$cols[] = Array("Action", "cAction", array("class" => "link", "sort" => false));
		$query = "select * from customers";
        table::execute($query);
		
		foreach (table::$data as $r => &$cells) {
			$name = $cells['cCustomerName'];
			if($cells['cActive']=='no') $style='style="color:red"'; else $style='';
			$cells['cCustomerName']='<a '.$style.' href="?cmd=edit&id='.$cells['cID'].'" title="Edit customer '.$cells['cCustomerName'].'">'.$cells['cCustomerName'].'</a>';
			if(!isset($cells['cUser'])) $cells['cUser']='Not set';
			if(!isset($cells['cPass'])) $cells['cPass']='Not set';
			
			if($cells['cActive']=='yes')	 
				$cells['cAction']='<a onclick=\'return confirm("Are you sure to deactivate '.$name.'?")\'
				href="?cmd=deactivate&id='.$cells['cID'].'">Deactivate</a>';
			else 
				$cells['cAction']='<a onclick=\'return confirm("Are you sure to activate '.$name.'?")\'
				href="?cmd=activate&id='.$cells['cID'].'">Activate</a>';
		}
        
        table::load();
	}
	
	
	public static function SuperAdminCustomerActivate(){
		db::SwitchToMain();	
		
		$q='SELECT * FROM customers WHERE cID='.$_GET['id'];
		$row=db::PrepareLoadSingleRow($q);
		
		$q = "UPDATE customers SET cActive='yes' WHERE cID = ".$_GET['id'];
		$r= db::SQL($q);
		if($r) 
		msg::set('The customer '.$row['cCustomerName'].' was activated.','success');
		else msg::set('The customer '.$row['cCustomerName'].' can not be activated for some reason.','error');
		
		cf::Redirect("customers");
	}
	
	public static function SuperAdminCustomerDeactivate(){
		db::SwitchToMain();	
		
		$q='SELECT * FROM customers WHERE cID='.$_GET['id'];
		$row=db::PrepareLoadSingleRow($q);
		
		$q = "UPDATE customers SET cActive='no' WHERE cID = ".$_GET['id'];
		$r= db::SQL($q);
		if($r) 
		msg::set('The customer '.$row['cCustomerName'].' was deactivated.','success');
		else msg::set('The customer '.$row['cCustomerName'].' can not be deactivated for some reason.','error');
		
		cf::Redirect("customers");
	}
	
	public static function SuperAdminCustomerEdit(){
		db::SwitchToMain();	
		$q='SELECT * FROM customers WHERE cID='.$_GET['id'];
		$row=db::PrepareLoadSingleRow($q);
		SuperAdminCustomersView::Edit($row);
	
	}
	
	private static function SetCustomerAdminPass($pass,$DBNAME) { 
		db::SwitchToRealByProvidedName(DB_PREFIX.$DBNAME);
		$salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=1");

        $token=cf::hashTokenByPassAndSalt($pass, $salt);
        $q = "UPDATE webapp_admins_tokens SET waatToken=AES_ENCRYPT(\"" . $token . "\", \"".WEBAPP_SQL_SECRET_KEY."\") 
		WHERE waatID = 1";
		//echo $q;exit;
        db::SQL($q) ? msg::set('Admin password updated.',"success") : msg::set('Admin password was not updated.',"error");
        //echo '>>>>'.$pass;echo "<hr>".$q; exit;		
		db::SwitchToMain();
        return true;
    }
	
	public static function createNewCustomerFolders($ID){
		$root = FS_ROOT.'/add/customer'.$ID.'/uploads/';
		if( 
				mkdir(FS_ROOT.'/add/customer'.$ID.'/uploads', 0777, true)
				&& mkdir(FS_ROOT.'/add/customer'.$ID.'/form-photo', 0777, true)
				&& mkdir($root.'credentialings', 0777, true)
				&& mkdir($root.'downloads', 0777, true)
				&& mkdir($root.'downloads/benefits_downloaded', 0777, true)
				&& mkdir($root.'files', 0777, true)
				&& mkdir($root.'for_upload', 0777, true)
				&& mkdir($root.'for_upload/benefits_uploaded', 0777, true)
				&& mkdir($root.'onboarding_files', 0777, true)
				&& mkdir($root.'resources', 0777, true)
				
		)
		return true;
		else return false;
	}
	
	
	public static function SuperAdminCustomerSaveAdd(){
		db::SwitchToMain();
		$cont=true;
		try{ SuperAdminCustomersModel::ValidateEdit("server"); } 
		    catch (Exception $e) 
			{
			 self::SuperAdminCustomerAdd();
			 $cont=false;
			}
		if($cont){
			/**** SAVE BEGIN ********/
			
			if(isset($_GET['id'])) $ID=$_GET['id'];
			$Active = filter_input(INPUT_POST, "cActive", FILTER_SANITIZE_STRING);
			$cCustomerName = filter_input(INPUT_POST, "cCustomerName", FILTER_SANITIZE_STRING);
			$cCustomerName=trim(preg_replace('/\s+/S', " ", $cCustomerName)); //strtoupper(
			$cCustomerShortName = filter_input(INPUT_POST, "cCustomerShortName", FILTER_SANITIZE_STRING);
			$cCustomerShortName=trim(preg_replace('/\s+/S', " ", $cCustomerShortName));//strtoupper
			$cPass = filter_input(INPUT_POST, "cPass", FILTER_SANITIZE_STRING);
			$cDB = filter_input(INPUT_POST, "cDBASE", FILTER_SANITIZE_STRING);
			
			//$cEmail = filter_input(INPUT_POST, "cAdminEmail", FILTER_SANITIZE_STRING);
			
			if(self::SetCustomerAdminPass($cPass,$cDB)){
				$q = "INSERT INTO customers SET cCustomerName='{$cCustomerName}',cCustomerShortName='{$cCustomerShortName}',
				cActive = '{$Active}',cDBASE='{$cDB}'";//cAdminEmail='{$cEmail}', 
				
				if (db::SQL($q)){
					msg::set("New customer {$cCustomerName} were addedd.",'success');
					$NewID = db::LastID();
					if (self::createNewCustomerFolders($NewID)) msg::set("File structure for new customer were created succesfully.",'success');
					else {
						msg::set("For some reason file structure for customer was not creafed. Custumer will be created, bit not activated.",'error');
						$Active = 'no';
						$q = "UPDATE customers SET cActive = '{$Active}' WHERE cID={$NewID}";
						db::SQL($q);
					}				
				}

				else msg::set("Error adding new customer  {$cCustomerName}.",'error');
			}
			else msg::set("For some reason customer admin password can not be set. Noting added.",'error');

			cf::Redirect('customers');
			/**********/
		}
	}
	
	
	public static function SuperAdminCustomersSaveChanged(){ 
		db::SwitchToMain();
		$cont=true;
		try{ SuperAdminCustomersModel::ValidateEdit("server"); } 
		    catch (Exception $e) 
			{
			 self::SuperAdminCustomerEdit();
			 $cont=false;
			}
		
		if($cont){
			/**** SAVE BEGIN ********/
			//cf::pr($_POST);cf::pr($_FILES);exit;
			$ID=$_GET['id'];
			$Active = filter_input(INPUT_POST, "cActive", FILTER_SANITIZE_STRING);
			$cCustomerName = filter_input(INPUT_POST, "cCustomerName", FILTER_SANITIZE_STRING);
			$cCustomerName=trim(preg_replace('/\s+/S', " ", $cCustomerName)); //strtoupper(
			$cCustomerShortName = filter_input(INPUT_POST, "cCustomerShortName", FILTER_SANITIZE_STRING);
			$cCustomerShortName=trim(preg_replace('/\s+/S', " ", $cCustomerShortName));//strtoupper(
			$cPass = filter_input(INPUT_POST, "cPass", FILTER_SANITIZE_STRING);
			$cDB = filter_input(INPUT_POST, "cDBASE", FILTER_SANITIZE_STRING);
			//$cEmail = filter_input(INPUT_POST, "cAdminEmail", FILTER_SANITIZE_STRING);
			
			    $q = "UPDATE customers SET cCustomerName='{$cCustomerName}',cCustomerShortName='{$cCustomerShortName}',
				cActive = '{$Active}', cDBASE='{$cDB}' WHERE cID={$ID}"; //cPass ='{$cPass}',cAdminEmail ='{$cEmail}',
				//echo $q;exit;
				if(db::SQL($q))	msg::set("Changes of customer {$cCustomerName} were saved.",'success');
				else msg::set("Error in saving changes for customer <b> {$cCustomerName}.</b>",'error');
				
			if (isset($_POST['cPass']) && $_POST['cPass'] !=''){
				self::SetCustomerAdminPass($cPass,$cDB);	
			}
			else msg::set('Admin password was not changed.','success');
			
			cf::Redirect('customers');
			
		}
	}
	
	public static function SuperAdminCustomerAdd(){
		db::SwitchToMain();	
		SuperAdminCustomersView::Add();
	}
	
	
}
