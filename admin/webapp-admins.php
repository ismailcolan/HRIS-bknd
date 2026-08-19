<?php
/** Web App Admins */
class adminSection extends PageAdmin {

    /*protected static $id, $data, $controller;*/
    const CONTROLLER = "WebappAdminController";
    
    public function __construct($id = 0, $C = self::CONTROLLER){
        parent::__construct();

        if (!in_array(AppModel::$admin["waaRole"], array("Admin","Editor"))){
            cf::Redirect("home");
        }
                
        $C::$id = $C::$rq!=="list" && $id>0 ? (int)$id : null;
        $C::$data = $C::$rq!=="list" && isset($C::$id) ? WebappAdminModel::dataById($id) : null;
    }
    
    final function adminPassword() {
        if(self::$rq === "password") {
            self::adminPasswordValidate("client");
            form::codeStyle(true, 1, false);
            form::openTag("div",array("class"=>"form-panel"));
                form::$ofsetIndentsCount = 2;
                form::openTag("form",array("method"=>"post","validate"=>true,"class"=>"form-horizontal clearfix"));
                if(self::$usr["waaID"] == self::$id){
                    form::inpField(
                        "Old Password:",
                        "waatPassOld",
                        "",
                        array("type"=>"password","fld-class"=>"col-sm-6","lbl-class"=>"col-sm-4","class"=>"form-control col-sm-8", "autofocus"=>true)
                    );
                    form::div("",array("class"=>"clear"));
                }
        }
                    form::inpField(
                        "Password:",
                        "waatPass",
                        "",
                        array("type"=>"password","fld-class"=>"col-sm-6","lbl-class"=>"col-sm-4","class"=>"form-control col-sm-8")
                    );
                    form::inpField(
                        "Re password:",
                        "waatPassRe",
                        "",
                        array("type"=>"password","fld-class"=>"col-sm-6","lbl-class"=>"col-sm-4","class"=>"form-control col-sm-8")
                    );
        if(self::$rq === "password") {
                form::$ofsetIndentsCount = 2;
                form::inpSaveCancel(ADMIN.self::$pg["list"]["url"],array("fld-class"=>"col-sm-6 col-sm-offset-2"));
                form::closeTag("form");
                form::codeStyle(true, 1, true);
            form::closeTag("div",array("class"=>"form-panel"));
        }
    }
    
    /*final function adminPasswordSave(&$qList = array()){
        if(empty($qList)) {
            self::adminPasswordValidate("server");

            //We have to validate/confirm old password
            if(self::$usr["waaID"] == self::$id){
                $oldPass = filter_input(INPUT_POST, "waatPassOld", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
                $salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=".self::$id);
                $token = cf::hashTokenByPassAndSalt($oldPass, $salt);
                $q = "SELECT waatID FROM webapp_admins_tokens WHERE waatToken = AES_ENCRYPT(\"" . $token . "\", \"".WEBAPP_SQL_SECRET_KEY."\") AND waatID = ".self::$usr["waaID"];
                if( db::PrepareLoadSingleCellValue($q) !=  self::$usr["waaID"]){
                    form::$error["waatPassOld"][] = "Incorrect old password.";
                }
            }

            if(!empty(form::$error)) {	
                $this->adminPassword();
                return;
            }
        }else {
            $qListRequestOnly = true;
        }

        $qList["webapp_admins_tokens"] = array("key"=>"waatID", "data"=>array());
        if(self::$id == 0){
            $usernameForSalt = filter_input(INPUT_POST, "waaUsername", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
            $salt = cf::saltAnWord($usernameForSalt);
            $qList["webapp_admins_tokens"]["data"][] = "waatSalt = \"" . $salt . "\"";
        } else {
            $salt = !empty($salt) ? $salt : db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=".self::$id);
        }
        $pass = filter_input(INPUT_POST, "waatPass", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
        $token = cf::hashTokenByPassAndSalt($pass, $salt);
        $qList["webapp_admins_tokens"]["data"][] = "waatToken = AES_ENCRYPT(\"" . $token . "\", \"".WEBAPP_SQL_SECRET_KEY."\")";

        if(isset($qListRequestOnly)){
            return;
        }
        
        foreach ($qList as $table=>$val) {
			$data = implode(', ', $val["data"]);
            $qList[$table]  = !self::$id
                            ? "INSERT INTO {$table} SET " . ($table!=self::$pg['tbl'] ? $val["key"]."= {---MainID---},": "") . " {$data}"
                            : "UPDATE {$table} SET {$data} WHERE " . $val["key"] . " = ".self::$id;
		}
		db::StartTransaction();
		foreach($qList as $table=>$query) {
			$query = str_replace("{---MainID---}", self::$id, $query);
//			print($query."<br />");
			if(!db::SQL($query)){
				msg::set(TXT_SAVE_ERROR,"error");
				db::Rollback();
			}
			if(!self::$id) {
				self::$id = db::LastID();
			}
		}
//die();
        db::Commit();

        msg::set(TXT_SAVE_OK,"success");
        cf::Redirect(self::$pg["list"]["url"]);
    }
    
    protected function adminPasswordValidate($type){
        val::init($type);
        if(self::$usr["waaID"] == self::$id){
            val::required("waatPassOld","Please fill out the old password.");
            val::minLength("waatPassOld", 6, "Please add at least 6 characters.");
            val::maxLength("waatPassOld", 50);
        }
        val::required("waatPass","Please fill out the password.");
        val::minLength("waatPass", 6, "Please add at least 6 characters.");
        val::maxLength("waatPass", 50);

        val::required("waatPassRe","Please fill out the Re password.");
        val::equals("waatPassRe", "waatPass", "Passwords doesn't match.");
    }*/
    
    
    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        if(self::$rq === "list") {
            table::prepare();
            cf::appendToHeadTag(ADD. "admin/webapp-admins.css", "css");
        } else if(self::$rq == "details") {
            //cf::$page["title"] = self::$pg[self::$rq]["title"];
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"admin user", "items"=>"admin users")){
        self::$pageToControllerMethods += array(count(self::$pageToControllerMethods)=>"ChangePassword", "ChangePasswordProcess");
        $M = App_AdminModel::GetModel($C);
        
        $pg["list"]         =   array("url" => "admins", "title" =>ucfirst($pg["items"]));
        $pg["details"]      =   array("url" => "admin", "title" => ucfirst($pg["item"]));
        $pg["password"]     =   array("url" => "admin-password", "title" => "Access password");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        
        //self::setControlerProperties(self::$controller = "WebappAdminController", array("pg","data","id", "rq"));

        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                self::$rq = $C::$rq = $k;
                break;
            }
        }

        AccessModel::GrantAccessAndActions($C);
    }
}

$adminSection = new adminSection(filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

$adminSection->Header();

if($adminSection::$rq === "list") {
        $adminSection->All();
} else if($adminSection::$rq === "details") {
    if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "delete") {
        $adminSection->Delete();
    } else if(filter_has_var(INPUT_POST,"waaUsername")) {
        $adminSection->DetailsSave();
    } else {
        $adminSection->Details();
    }
} else if($adminSection::$rq === "password") {
    if(filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminSection->ChangePasswordProcess();
    } else {
        $adminSection->ChangePassword();
    }   
}

$adminSection->Footer();
