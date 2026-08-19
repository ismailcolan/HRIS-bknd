<?php


class CredentialingPage extends PageFront {
    
    const CONTROLLER = "CredentialingFrontController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
        table::prepare();
    }
    
    protected function setPgRq($C = self::CONTROLLER){
        $M = App_AdminModel::GetModel($C);
        
        $pg["pending"]         =   array("url" => "Credentialing");
        $pg["tbl"]          =   $M::$dt["tbl"];
        $pg["key"]          =   $M::$dt["key"];
        $pg["pfx"]          =   $M::$dt["pfx"];
        self::$pg = $pg;
        
        $request = strlen($url = filter_input(INPUT_GET, "rq"))>0 ? $url : "pending";
        switch($request){
            case "pending":
                $rq = "pending";
                break;
            default: $rq = "pending";
        }
        self::$rq = $rq;
        
        
        self::$pageToControllerMethods = array("Credentialings", "Completed", "MustSigned");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

}


if(filter_has_var(INPUT_GET, "download")) {
    CredentialingFrontController::DownloadFile();
}
if(filter_has_var(INPUT_GET, "skip")) {
    CredentialingFrontController::SkipCredentialing();
}

//echo 'Under development.';exit;
$page = new CredentialingPage();

$page->HTMLHeader();

msg::get();

if($page::$rq === "pending") {

    if(AccessModel::$usr === null) {
        $page->MustSigned();
    }  else {
        if(isset($_FILES["credentialingFile"]) && ($_FILES["credentialingFile"]["size"] > 0) && ($_FILES["credentialingFile"]["error"] === 0)) {
            if(EmployeeCredentialingModel::SaveCredentialingFile()) {
                cf::Redirect(WP . 'credentialings');
            }
        }
        if(filter_has_var(INPUT_POST, "ecID") && filter_has_var(INPUT_POST, "newRenewalDate")) {
			
            EmployeeCredentialingModel::SetCredentialRenewalDate(
                filter_input(INPUT_POST, "ecID", FILTER_SANITIZE_NUMBER_INT),
                filter_input(INPUT_POST, "newRenewalDate", FILTER_SANITIZE_STRING),
				   filter_input(INPUT_POST, "emptyecFileName", FILTER_SANITIZE_STRING)
            );
        }
        $page->Credentialings();
    }
} else { cf::vdd($page::$rq); }

$page->HTMLFooter();
