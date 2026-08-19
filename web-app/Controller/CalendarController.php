<?php
/** The CompanyOffice controller */
class CalendarController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        $cond = "`calActive`='Yes' and calDate " .(self::$rq==="list" ? ">= CURRENT_DATE": "< CURRENT_DATE");
        CalendarView::All(self::$pg, CalendarModel::query($cond, ["calDate"=>"ASC"]), CalendarModel::$fldLbl, self::$rq);
    }

    static function Summary(){
        msg::get();
        CalendarView::Summary(self::$data, self::$pg, CalendarModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[CalendarModel::$dt["pfx"]."Active"] != "No" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Details($lists = array()){
        CalendarModel::Detailsvalidate("client", $lists);
        CalendarView::Details(self::$data, self::$pg, CalendarModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array()){
        try{ CalendarModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
        db::Save(CalendarModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        cf::Redirect(ADMIN.self::getBackUrl()["url"]);
    }
    
    
    public static function Calendar() {
        $data = CalendarModel::data('calActive = "Yes" and calDate >= CURRENT_DATE', ["calDate" => "ASC"]);
        CalendarView::Calendar($data);
    }
}