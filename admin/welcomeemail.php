<?php
/** The Offices (admin) class */
class WelcomeEmail extends PageAdmin
{
    const CONTROLLER = "WelcomeEmailController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();
		 
        $M = App_AdminModel::GetModel($C);

        $C::$id = !in_array($C::$rq, array("list", "inactive-list","getfile")) && $id>0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;
        
        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
		 ckeditor::prepare();
        if(in_array($C::$rq, array("list", "inactive-list", "summary"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"WelcomeEmail", 
	"items"=>"Welcome Emails")){
        $M = App_AdminModel::GetModel($C);
		$pg["list"]         =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
		$pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["item"]), "title" => "Inactive ".strtolower($pg["items"]));
		$pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["item"]);
		$pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
		$pg["getfile"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["item"]);
     
		$pg["tbl"]          =   $M::$dt["tbl"];
		$pg["key"]          =    $M::$dt["key"];
		
		$pg["pfx"]          =   $M::$dt["pfx"];
        
        foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
                return $C::$rq = self::$rq = $k;
            }
        }

    }
	
	function getTextOfTemplate(){
		$tmplId = (int)filter_input(INPUT_GET, "tid", FILTER_VALIDATE_INT);
		$res=array();
		if ( $tmplId != 0){
		   $r = db::PrepareLoadSingleRow('SELECT * FROM  email_template WHERE emtID='.$tmplId);
		  
          if (!empty($r)){
			  $res['msg'] = 'Email template copied.';
			  $res['text']= $r['emtMessage'];
			  $res['result']= 'OK';
		  }
		}
		else {
			$res['msg'] = 'Invalid number of template.';
			$res['result']= 'FAIL';
		}
		
		echo json_encode($res);
	}
}

msg::prepare();
table::prepare();
$adminSection = new WelcomeEmail((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "templ") {
       $adminSection->getTextOfTemplate();
	   exit;
}
 
$adminSection->Header();

if(in_array($adminSection::$rq, array("list", "inactive-list"))) {
    $adminSection->All();
} else if($adminSection::$rq === "summary") {
    $adminSection->Summary();
}  else if($adminSection::$rq === "details") {
	
    if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "trash") {
        WelcomeEmailController::DeleteTemplate();
	 }  else if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)=== "restore") {
        $adminSection->Reactivate();
    } else if(filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminSection->DetailsSave();
    } else {
        $adminSection->Details();
    }
}

$adminSection->Footer();