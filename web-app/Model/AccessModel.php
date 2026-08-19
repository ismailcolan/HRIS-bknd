<?php
/** The Access model functions 
 * Controls facilities and data access for the separate admin-user roles */
class AccessModel extends AppModel {
    
    static $AllowedActions, $AdminRolesWithEditEnabled = array("Admin","Editor","User");
    private static $Rq2Actions = array("add"=>"create",
                                //"summary" & "list" => "read"
                                "details"=>"update",
                                "trash"=>"delete", "delete"=>"delete"
                                );
    
    /**Check access and sets currently allowed actions by the request and role
     * (self::$admin["waaRole"]). In case the AppModel::$admin["waaRole"] not 
     * within the array ($M::$AdminRolesAccessible) values  shows error 404 page.
     * @param string $C - the current controller (of the admin section) */
    public static function GrantAccessAndActions($C){
        //Set defuult actions by the current admin role
        $default_actions = in_array(self::$admin["waaRole"], self::$AdminRolesWithEditEnabled) ? 
                            array("create","read","update","delete"):
                            array("read");
        self::$AllowedActions = self::RoleBranchActionsOr404($C, $default_actions);
    }
    private static function RoleBranchActionsOr404($C, $default_actions){
        $Branch = str_replace("Controller", "", $C);    //Current MVC branch name: "Category", "WebappAdmin"..
        $M = $Branch."Model";                           //Current Model name
        
        //All $Branch(es), except the "WebappAdmin" are accessible (2 all admins)
        if(in_array($Branch, array("WebappAdmin", "Report"))){
            //Only Admins and Editors have access to above sections
                $actions =  in_array(self::$admin["waaRole"], $M::$AdminRolesAccessible) ? 
                            array("create","read","update","delete"):
                            null;
                
        } else if ($Branch==="Order"){
            $actions = array("create","read"/*,"update"*/,"delete");
            if(in_array($C::$data["order-type"], array("pending", "backorder"))){
                $actions[] = "update";
            } else {
                if($C::$rq==="details" && filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING)!=="trash"){
                    cf::end("Error: Complete orders has no access to details view.");
                }
            }
        } else {
            $actions = $default_actions;
        }
		
        return isset($actions) ? $actions : call_user_func(array(new Page(), "web404"));
    }


    static function AdminPageHeaderLnk($C){
        $lnk = null;

        if(($C === "ApplicationController") && ($C::$data["afActive"] === "Auto-rejected")) {
            return null;
        }
		

        if($C==="ApplicationController" && $C::$rq=="summary" && $C::$data["afActive"] !== "Auto-rejected"){
            $lnk = array(
                "application-details?id=".$C::$id=>"Edit application"
                );
        } else if(in_array($C::$rq, array("list", "inactive-list")) && self::RequestAllowed("add")){
            $lnk = array($C::$pg["details"]["url"]=> "Add ".strtolower($C::$pg["item"]));
        } else if($C::$rq==="summary"){
            $lnk = array();
            if(self::RequestAllowed("delete")){ //The user has access to the delete actions
                self::SummaryShowDeleteLinkIfAllowed($C, $lnk);
            }
            if(self::RequestAllowed("details")){
                    $lnk[$C::$pg["details"]["url"]."?id=".$C::$id] = "Edit ".strtolower($C::$pg["item"]);
            }
        }
		
		
		if($C==='BDDController'){
			$lnk = array(
                "/admin/benefits"=>'<i class="fa fa-cog" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Benefits settings'
                );
		}
		
		if($C==='BenefitsController'){
			$lnk = array(
                "admin/benefits_deductions_data"=>'<i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Back to Latest Benefits Data'
                );
		}
        return $lnk;
    }
    private static function SummaryShowDeleteLinkIfAllowed($C, &$lnk){
        $M = str_replace("Controller", "Model", $C);
        if(method_exists($M, "ItemDeleteAllowed")){
            if($M==="VendorModel" && $C::$data[$C::$pg["pfx"]."DeactivateAllowed"]!=="Yes"){
                return;
            }
            if($M::ItemDeleteAllowed(true)){
                $lnk[$C::$pg["details"]["url"]."?id=".$C::$id."&cmd=trash"] = "Delete ".strtolower($C::$pg["item"]);
            } else if($C::$data[$C::$pg["pfx"]."Active"]==="No") {
                $lnk[$C::$pg["details"]["url"]."?id=".$C::$id."&cmd=restore&go=summary"] = "Reactivate ".strtolower($C::$pg["item"]);
            } else {
                $lnk[$C::$pg["details"]["url"]."?id=".$C::$id."&cmd=trash&go=summary"] = "Deactivate ".strtolower($C::$pg["item"]);
            }
        }
    }
    static function RequestAllowed($rq){
        return in_array(self::$Rq2Actions[$rq], self::$AllowedActions);
    }
}
