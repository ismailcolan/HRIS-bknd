<?php
/** The Order controller */
class SkillController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();
        $cond = SkillModel::StatusCond((self::$rq==="list"?"submitted":self::$rq));
        static::$pg["list-type"] = (self::$rq==="list" ? "all" : self::$rq);
        SkillView::All(static::$pg, SkillModel::query($cond), SkillModel::$fldLbl, static::$rq);
    }

    static function Summary(){
        msg::get();
        SkillView::Summary(self::$data, self::$pg, SkillModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[SkillModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    /**Serve as edit items & qty form for the admin*/
    final static function Details(){
        $lists = array();
        SkillModel::Detailsvalidate("client", $lists);
        SkillView::Details(self::$data, self::$pg, SkillModel::$fldLbl, []);
    }

    final static function DetailsSave(){
        try{ SkillModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
		//print_r (SkillModel::saveQueries());echo '<br>>>>'.self::$id;exit;
        db::Save(SkillModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");
        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
    }

    final static function Report(){
        msg::get();
        static::$pg["controller"] = get_called_class();
        OrderStatsModel::ReportFiltersValidate(static::$pg);
        OrderStatsView::Report(static::$pg, "Report");
    }
}