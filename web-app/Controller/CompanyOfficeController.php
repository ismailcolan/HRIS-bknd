<?php
/** The CompanyOffice controller */
class CompanyOfficeController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        CompanyOfficeModel::$dataAddons+=array(self::$pg["key"]."edit");
        $cond = null;
        CompanyOfficeView::All(self::$pg, CompanyOfficeModel::query($cond), CompanyOfficeModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        CompanyOfficeView::Summary(self::$data, self::$pg, CompanyOfficeModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[CompanyOfficeModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){
        CompanyOfficeModel::Detailsvalidate("client", $lists);
        CompanyOfficeView::Details(self::$data, self::$pg, CompanyOfficeModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array()){
        try{ CompanyOfficeModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
        
        db::Save(CompanyOfficeModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        
        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
    }

    public static function Delete() {

        if(filter_has_var(INPUT_POST, "moveToCOID")) {
            $oldID = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
            $newID = filter_input(INPUT_POST, "moveToCOID", FILTER_SANITIZE_NUMBER_INT);

            if(($oldID) && ($newID)) {
                $q = sprintf('update apply_forms set afCOID=%d where afCOID=%d', $newID, $oldID);
                db::SQL($q);

                $oldOffice = CompanyOfficeModel::dataById($oldID);
                $newOffice = CompanyOfficeModel::dataById($newID);
                $removeUrl = ADMIN.self::$pg["details"]["url"]."?id=".self::$id.'&cmd=trash';

                CompanyOfficeView::RemoveAfterMove($oldOffice, $newOffice, $removeUrl);
                die();
            }
            cf::Redirect(self::getBackUrl()["url"]);
        }

        if(self::$data["coEmplCount"] == 0) {
            parent::Delete();
        }

        $list["data"] = self::$data;
        $list["offices"] = CompanyOfficeModel::AllActiveKeyName();
        unset($list["offices"][self::$id]);

        CompanyOfficeView::MoveEmployees($list);
    }
    
}