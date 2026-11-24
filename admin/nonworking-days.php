<?php
/** The Offices (admin) class */
class NonWorkingDays extends PageAdmin
{
    const CONTROLLER = "DaysOffController";

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
	}
	
}

msg::prepare();
table::prepare();
$adminSection = new NonWorkingDays();

$adminSection->HeaderOnly();
 if(!empty($_POST)) $adminSection->Save();
 else {
	 if(cf::requestStr(INPUT_GET, "cmd")=== "add") {
			$adminSection->Add();
	 }
	 else if(cf::requestStr(INPUT_GET, "cmd")=== "edit") {
			$adminSection->Edit();
	 }
	 else  if(cf::requestStr(INPUT_GET, "cmd")=== "delete") {
			$adminSection->Delete();
	 }
	 else $adminSection->Init();
 }

$adminSection->Footer();