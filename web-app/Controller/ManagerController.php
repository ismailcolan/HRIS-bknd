<?php
/** The CompanyOffice controller */
class ManagerController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        ManagerModel::$dataAddons+=array(self::$pg["key"]."edit");
        $cond = "`".self::$pg["pfx"]."Active`=\"" .(self::$rq==="list" ? "Yes": "No")."\"";
        ManagerView::All(self::$pg, ManagerModel::query($cond), ManagerModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        ManagerView::Summary(self::$data, self::$pg, ManagerModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[ManagerModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){
        ManagerModel::Detailsvalidate("client", $lists);
        ManagerView::Details(self::$data, self::$pg, ManagerModel::$fldLbl, $lists);
    }
    final static function DetailsSave($lists = array()){
        try{ ManagerModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
        
        db::Save(ManagerModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        
        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
    }
    
}