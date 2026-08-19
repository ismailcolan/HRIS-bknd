<?php
/** The CompanyOffice controller */
class JobController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        JobModel::$dataAddons+=array(self::$pg["key"]."edit");
        $cond = "`".self::$pg["pfx"]."Active`=\"" .(self::$rq==="list" ? "Yes": "No")."\"";
        JobView::All(self::$pg, JobModel::query($cond), JobModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        JobView::Summary(self::$data, self::$pg, JobModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[JobModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }


    final static function Details($lists = array()){
        if(empty($lists)) {
            $lists = ["positions"=>PositionModel::AllActiveKeyName()];
        }
        JobModel::Detailsvalidate("client", $lists);
        JobView::Details(self::$data, self::$pg, JobModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array()){
        try{ JobModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}

        db::Save(JobModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        
        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
    }
    
}