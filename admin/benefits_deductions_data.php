<?php
/** The Offices (admin) class */
class BenefitsDeductionsData extends PageAdmin
{
    const CONTROLLER = "BDDController";

    public function __construct($id=0, $C = self::CONTROLLER) {
        parent::__construct();
		
        $M = App_AdminModel::GetModel($C);
       
        AccessModel::GrantAccessAndActions($C);
		
    }

    protected function inHead($C=self::CONTROLLER) {
        msg::prepare();
		table::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER, $pg=array("item"=>"benefits_deductions_data", "items"=>"Benefits Deductions Data")){
        $M = App_AdminModel::GetModel($C);
		$pg["list"]         =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
		$pg["inactive-list"]=   array("url" => "inactive-".strtolower($pg["item"]), "title" => "Inactive ".strtolower($pg["items"]));
		$pg["details"]      =   array("url" => strtolower($pg["item"])."-details", "title" => $pg["items"]);
		$pg["summary"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
		$pg["getfile"]      =   array("url" => strtolower($pg["item"]), "title" => $pg["items"]);
		
		$pg["tbl"]          =   $M::$dt["tbl"];
		$pg["key"]          =    $M::$dt["key"];
		
		$pg["pfx"]          =   $M::$dt["pfx"];
		
		
		
		foreach($C::$pg = $pg as $k=>$v){
            if(is_array($v) && isset($v["url"]) && $v["url"]===cf::$rq["url"]){
				
                return $C::$rq = self::$rq = $k;
            }
        }
		
    }
	
}

msg::prepare();

$adminSection = new BenefitsDeductionsData();
if(intval(AppModel::$admin["waaAFID"]) > 0) {
    cf::Redirect(ADMIN);
}
if(isset($_GET['cmd']) && $_GET['cmd'] == "new_upload") {$adminSection->createNewUpload();}
if(isset($_GET['cmd']) && $_GET['cmd'] == "new_download") {$adminSection->NewDownload();}

if(!empty($_POST)){
  if(isset($_POST['submitDemographics'])) $adminSection->delDemographics();
  if(isset($_POST['submitDeductions'])) $adminSection->delDeductions();
}


$adminSection->Header();

msg::get();

//if (in_array($adminSection::$rq, array("list"))) {
 $adminSection->BenefitsDeductionsDataList();
//} 


$adminSection->Footer();