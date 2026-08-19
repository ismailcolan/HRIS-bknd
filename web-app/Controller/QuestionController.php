<?php
/** The Order controller */
class QuestionController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function All(){
        msg::get();

        $cond = QuestionModel::StatusCond((self::$rq==="list"?"submitted":self::$rq));

        static::$pg["list-type"] = (self::$rq==="list" ? "all" : self::$rq);
        QuestionView::All(static::$pg, QuestionModel::query($cond), QuestionModel::$fldLbl, static::$rq);
    }

    /**Serve as edit items & qty form for the admin*/
    final static function Details(){
        $lists["allpositions"] = db::PrepareLoad(PositionModel::query(" pActive = 'Yes' "));
        $lists["positions"] = QuestionPositionsModel::LoadQuestionPositions(self::$id);

        QuestionModel::Detailsvalidate("client", $lists);
        QuestionView::Details(self::$data, self::$pg, QuestionModel::$fldLbl, $lists);
    }

    final static function Summary(){
        QuestionView::Summary(self::$data, self::$pg, QuestionModel::$fldLbl, []);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[QuestionModel::$dt["pfx"]."Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name"=>"Back to ".strtolower(self::$pg["items"]), "url"=>$url, "icon"=>"fa fa-chevron-circle-left", "args"=>array("class"=>"btn btn-info"));
        return $params;
    }

    final static function Answers() {
        if(self::$id) {
            $qPositions = QuestionPositionsModel::LoadQuestionPositions(self::$id);
            if(filter_has_var(INPUT_POST, "save-btn")) {
                try{
                    AnswerModel::DetailsValidate("server", $lists);

                    $q = "DELETE from `answers` where aQID=".self::$id;
                    db::SQL($q);

                    if(AnswerModel::saveAnswers(self::$id)) {
                        cf::Redirect(ADMIN.self::$pg["summary"]["url"]."?id=".self::$id);
                    }
                }
                catch (Exception $e) {
                }

                $i = 1; $data = [];
                while(filter_has_var(INPUT_POST, "ansName".$i)) {
                    $name = filter_input(INPUT_POST, "ansName".$i, FILTER_SANITIZE_STRING);
                    if(filter_has_var(INPUT_POST, "ansPositions".$i)) {
                        $pos = filter_input(INPUT_POST, "ansPositions".$i, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                        if($pos === false) $pos = [];
                    } else {
                        $pos = [];
                    }
                    $data["ansName".$i] = ["aName" => $name, "arPos" => $pos];
                    $i++;
                }

                self::$data["answers"] = $data;
            }

            if(!isset(self::$data["answers"])) {
                self::$data["answers"] = [];
                $ansList = AnswerModel::GetQuestionAnswers(self::$id, true);
                $i = 1;
                foreach ($ansList as $ans) {
                    self::$data["answers"]["ansName".$i] = ["aName" => $ans["aName"], "arPos" => array_column($ans["rejectedFor"], "arpPID")];
                    $i++;
                }
            }
            msg::get();
            QuestionView::Answers(self::$data, self::$pg, QuestionModel::$fldLbl, ["qPositions"=>$qPositions]);
        } else {
            cf::Redirect(ADMIN.static::$pg["list"]["url"]);
        }
    }

    final static function DetailsSave(){
        try{ QuestionModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Details();}
        if(db::Save(QuestionModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__)) {

            $positions = filter_input(INPUT_POST, "positions", FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            if($positions === false) $positions = [];

            QuestionPositionsModel::SaveQuestionPositions(self::$id, $positions);
            msg::set(TXT_SAVE_OK, "success");
        } else {
            msg::set(TXT_SAVE_ERROR,"error");
        }
        cf::Redirect(self::$pg["summary"]["url"]."?id=".self::$id);
    }

    final static function Report(){
        msg::get();
        static::$pg["controller"] = get_called_class();
        OrderStatsModel::ReportFiltersValidate(static::$pg);
        OrderStatsView::Report(static::$pg, "Report");
    }
}