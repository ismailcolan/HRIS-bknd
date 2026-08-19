<?php

/** The General model */
class GeneralModel extends AppModel
{

    static $dataAddons = array(), 
	 $dt = array("tbl" => "settings", "key" => "cID", "pfx" => "t", "controller" => "GeneralController");
    static $mainTblFields = array("cName", "cType", "cTimeFrameNum", "cTimeFrameDim", "cDueDate", "cKeepFiles", "cActive");
    static $fldLbl = array("cID" => "ID", "cName" => "Name", "cType" => "Credentialing type", "cKeepFiles" => "Keep files if position changed",
        "cTimeFrameNum" => "Time frame", "cTimeFrameDim" => "Time frame", "cTimeFrame" => "Time frame", "cDueDate" => "Due Date", "cActive" => "Status",
        "cPositions" => "Positions required for",
        "cActive" => "Active", "cAdded" => "Added", "cUpdated" => "Last changed");
    static $postOverrided = array();

	 final static function ValidateAdminMails(){	
		$mails = explode("\n",$_POST['ADMIN_MAILS']);
		
		$row=1;
		foreach ($mails as $mail){
			$mail = trim($mail);
			if (($mail != '') && ! filter_var($mail, FILTER_VALIDATE_EMAIL)) {
				msg::set("The mail <b>".$mail."</b> on row ".$row." is not valid email", "error");
				return false;
			}
			$row++;
		}
		return true;
	}
	
	final static function getAdminMails(){
       $data = self::getData();	
		$m=$data[0]['asValue'];   
		$mails = explode("\n",$m);

		$admin_mails='';
		$_mails=array();
		foreach ($mails as $mail){
			$mail = trim($mail);
			if ($mail != '') $_mails[]=$mail;
		}
		return implode(';',$_mails);
	}
	
	
	
    final static function saveData(){
		
		$res = true;
		foreach ($_POST as $k=>$v){
			if ($k == 'save-btn') continue;
			$val=filter_input(INPUT_POST, $k, FILTER_SANITIZE_STRING);
			$q = "INSERT INTO webapp_admin_settings (asKey,asValue) VALUES ('{$k}', '{$v}')
				ON DUPLICATE KEY UPDATE asValue = '{$v}'";
				$res = $res && db::SQL($q);
		}
		
		return  $res;
	}

    final static function getData(){
		
		$q="SELECT * FROM webapp_admin_settings";
		$res=db::PrepareLoad($q);
		if($res === false ) $res =  array();
		$data = array();
		foreach ($res as $row){
		   $data[$row['asKey']] = $row['asValue'];
		}
		
		return $data;
	}

    
    static function delete($data, $dump = false)
    {
        return false;
    }

    static function query($conditions = '', array $orderby = array(), $limit = 0)
    {
        $query = "SELECT *, 
                        (select GROUP_CONCAT(pName) from positions where pID in (select cpPID from credentialing_positions where cpCID=cID)) as cPositions
                  FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $addons = array();

        return str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);
    }

    public static function AllActiveKeyName($afID = null) {
        if(empty($afID)) {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' "), self::$dt["key"], self::$dt["pfx"] . "Name");
        } else {
            $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' and exists(select ecId from employee_credentialing where ecAFID=".$afID." and ecCID=cID)"), self::$dt["key"], self::$dt["pfx"] . "Name");
        }
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item = htmlspecialchars($item);
            }
        }
        return $data;
    }



  

    static function adminPageTitleWithLinks($links = null)
    {
        return array("Edit Site Settings", $links);
    }

   
}
