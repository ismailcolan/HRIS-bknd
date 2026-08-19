<?php
/** The App Controller Class  */
class AppController {
    
    const __DUMP__ = true;
    
    /** User data container
     * @var array */
    public static $usr;
    /** @var str Request - basically matches the page url */
    public static $rq;
    /** @var array $pg - holds the current page data urls & tytles by request, main db_table and its key column */
    public static $pg;
    /** @var obj Validation class connector var*/
    public static $val;
    /** @var str Validation Type: client / server */
    public static $valType;
    /** @var arr save data - on save returns query components as arrays*/
    public static $saveData;
    /** Allowed field action */
    public static $allowedFieldActions = array("show","edit","validate","save","delete");

    
    /*final static function breadcrumbs($breadcrumbs, $getHtml = false){
        if($getHtml===true){  cf::buffer(); } 
        require_once(APP_HTML . "pages-breadcrumbs.html");
        if($getHtml===true){ cf::buffer($getHtml); return $getHtml; } 
    }*/
	
	
    static function ItemToBin($goToListUrl = true){
        //Check do id is set and do this id has loaded proper data array
        if((int)static::$id>0 && (int)static::$data[static::$pg["key"]]===(int)static::$id){
            $srcController = get_called_class();
            TrashModel::ItemToBin((int)static::$id, $srcController/*($srcController==="AttorneyProfileController" ? "AttorneyController":$srcController)*/);
        }
        if($goToListUrl === true){
            cf::Redirect($srcController::$pg["list"]["url"]);
        }
    }
    
    static function Delete(){
        $M = App_AdminModel::GetModel(get_called_class());
        if(method_exists($M, "ItemDeleteAllowed") && $M::ItemDeleteAllowed()!==true){
			// to deactivate Item
			
            if(in_array($M, array("CredentialingModel", "OnboardingModel","OffboardingModel","QuestionModel", "CompanyOfficeModel", "ProductModel", 
                                    "VendorModel", "PositionModel", "SkillModel", "DepartmentModel", "CompaniesModel","ManagerModel", "JobModel",
                                    "CalendarModel", "ResourcesModel", "RCategoriesModel","FileCategoriesModel"))){
                if(!in_array($M::$dt["pfx"]."Active", $M::$mainTblFields)){
                    $M::$mainTblFields[] = $M::$dt["pfx"]."Active";
                }
                $M::$postOverrided[$M::$dt["pfx"]."Active"] = "No";
                $qList = array();
                if($M==="VendorModel"){
                    $qList[] = "UPDATE `".VendorsPricesChangelogModel::$dt["tbl"]."` SET `".VendorsPricesChangelogModel::$dt["pfx"]."Deactivated`=NOW() WHERE `".VendorsPricesChangelogModel::$dt["pfx"]."VID`=".VendorController::$id.";";
                } 
                App_AdminModel::Save($M::SaveQueries($qList), static::$id, $M::$dt["tbl"], static::__DUMP__) ? msg::set(TXT_DEACTIVATE_OK,"success") : msg::set(TXT_DEACTIVATE_ERROR,"error");

                $go = filter_has_var(INPUT_GET, "go") && filter_input(INPUT_GET, "go")==="summary" ? static::$pg["summary"]["url"]."?id=".static::$id : static::$pg["inactive-list"]["url"];
                cf::Redirect(ADMIN.$go);
            } else {
                cf::end("Current action is not allowed.");
            }
        } else {
            $M::delete(static::$data, static::__DUMP__);
            cf::Redirect(ADMIN.static::$pg["list"]["url"]);
        }
    }
    static function Reactivate(){
        $M = App_AdminModel::GetModel(get_called_class());
        if(!in_array($M::$dt["pfx"]."Active", $M::$mainTblFields)){
            $M::$mainTblFields[] = $M::$dt["pfx"]."Active";
        }
        $M::$postOverrided[$M::$dt["pfx"]."Active"] = "Yes";
        App_AdminModel::Save($M::SaveQueries(), static::$id, $M::$dt["tbl"], static::__DUMP__) ? msg::set(TXT_REACTIVATE_OK,"success") : msg::set(TXT_REACTIVATE_ERROR,"error");
        
        $go = filter_has_var(INPUT_GET, "go") && filter_input(INPUT_GET, "go")==="summary" ? static::$pg["summary"]["url"]."?id=".static::$id : static::$pg["list"]["url"];
        cf::Redirect(ADMIN.$go);
    }
}