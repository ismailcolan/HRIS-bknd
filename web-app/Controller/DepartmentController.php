<?php
/** The CompanyOffice controller */
class DepartmentController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        DepartmentModel::$dataAddons+=array(self::$pg["key"]."edit");
        $cond = "`".self::$pg["pfx"]."Active`=\"" .(self::$rq==="list" ? "Yes": "No")."\"";
        DepartmentView::All(self::$pg, DepartmentModel::query($cond), DepartmentModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        DepartmentView::Summary(self::$data, self::$pg, DepartmentModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[DepartmentModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){
        DepartmentModel::Detailsvalidate("client", $lists);
        DepartmentView::Details(self::$data, self::$pg, DepartmentModel::$fldLbl, $lists);
    }
    final static function DetailsSave($lists = array()){
        try{ DepartmentModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
        
        db::Save(DepartmentModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        
        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
    }
    
}