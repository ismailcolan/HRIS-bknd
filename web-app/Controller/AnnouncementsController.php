<?php
/** The CompanyOffice controller */
class AnnouncementsController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        $cond = "`anActive`='Yes' and anEndDate " .(self::$rq==="list" ? ">= CURRENT_DATE": "< CURRENT_DATE");
        AnnouncementsView::All(self::$pg, AnnouncementsModel::query($cond), AnnouncementsModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        AnnouncementsView::Summary(self::$data, self::$pg, AnnouncementsModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[AnnouncementsModel::$dt["pfx"]."Active"] != "No" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){
        AnnouncementsModel::Detailsvalidate("client", $lists);
        AnnouncementsView::Details(self::$data, self::$pg, AnnouncementsModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array()){
        try{ AnnouncementsModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
        db::Save(AnnouncementsModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        cf::Redirect(ADMIN.self::getBackUrl()["url"]);
    }

    public static function Announcements() {
        $data = AnnouncementsModel::data('anActive = "Yes" and anEndDate >= CURRENT_DATE', ["anID" => "DESC"]);
        AnnouncementsView::Announcements($data);
    }
}