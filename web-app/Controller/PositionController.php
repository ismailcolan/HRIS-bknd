<?php

/** The Order controller */
class PositionController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;

    final static function All()
    {
        msg::get();

        $cond = PositionModel::StatusCond((self::$rq === "list" ? "submitted" : self::$rq));

        static::$pg["list-type"] = (self::$rq === "list" ? "all" : self::$rq);
        PositionView::All(static::$pg, PositionModel::query($cond), PositionModel::$fldLbl, static::$rq);
    }

    /**Serve as edit items & qty form for the admin*/
    final static function Details()
    {
        $lists = array();
        PositionModel::Detailsvalidate("client", $lists);
        PositionView::Details(self::$data, self::$pg, PositionModel::$fldLbl, []);
    }

    final static function Summary()
    {
        PositionView::Summary(self::$data, self::$pg, PositionModel::$fldLbl, []);
    }

    public static function getBackUrl()
    {
        $url = (@self::$data[PositionModel::$dt["pfx"] . "Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = Array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function DetailsSave()
    {
        if (self::$data["pCanRemove"] != "No") {
            try {
                PositionModel::DetailsValidate("server", $lists);
            } catch (Exception $e) {
				   foreach ($_POST as $k=>$v){
						self::$data[$k]=$v;
					}
                return self::Details();
            }
           // db::Save(PositionModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? 
			 //   msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");
			$skils=implode('|',$_POST['pSkills']);
			$MinEmplPerPosArr=json_encode($_POST['min_emp']);
			
			 if(!isset($_GET['id'])) {
				$q="INSERT INTO positions SET
				pName='{$_POST['pName']}',
				pSkills='{$skils}',
				pSalaryFrom='{$_POST['pSalaryFrom']}',
				pSalaryTo='{$_POST['pSalaryTo']}',
				pSalaryType='{$_POST['pSalaryType']}',
				pCanRemove='Yes',
				pActive='Yes',
				pAdded='".date('Y-m-d H:i:s')."',
				pWelcomeEmail	='{$_POST['pWelcomeEmail']}',
				pPhisician='{$_POST['pPhisician']}',
				pVacationDays	='{$_POST['pVacationDays']}',
				pCEDays='{$_POST['pCEDays']}',
				pMinEmplPerPosArr='{$MinEmplPerPosArr}',
				TwoWeekNoticeDim='{$_POST['TwoWeekNoticeDim']}',
				TwoWeekNoticeNmb={$_POST['TwoWeekNoticeNmb']}" ;
			 }
			 else {
				$q="UPDATE positions SET
				pName='{$_POST['pName']}',
				pSkills='{$skils}',
				pSalaryFrom='{$_POST['pSalaryFrom']}',
				pSalaryTo='{$_POST['pSalaryTo']}',
				pSalaryType='{$_POST['pSalaryType']}',				
				pCanRemove='Yes',
				pActive='Yes',
				pUpdated='".date('Y-m-d H:i:s')."',
				pWelcomeEmail	='{$_POST['pWelcomeEmail']}',
				pPhisician='{$_POST['pPhisician']}',
				pVacationDays	='{$_POST['pVacationDays']}',
				pCEDays='{$_POST['pCEDays']}',
				pMinEmplPerPosArr='{$MinEmplPerPosArr}',
				TwoWeekNoticeDim='{$_POST['TwoWeekNoticeDim']}',
				TwoWeekNoticeNmb={$_POST['TwoWeekNoticeNmb']}
				
				WHERE pID={$_GET['id']}" ; 
			 }
			 $res=db::SQL($q);
			 //if ($res)  msg::set(TXT_SAVE_OK, "success");
			 //else msg::set(TXT_SAVE_ERROR, "error");
			 if ($res) {
    $savedPosID = isset($_GET['id']) ? (int)$_GET['id'] : db::LastID();
    InterviewQuestionModel::saveFromPost($savedPosID);
    msg::set(TXT_SAVE_OK, "success");
} else {
    msg::set(TXT_SAVE_ERROR, "error");
}
        }
        cf::Redirect('admin/positions');
    }

    final static function Report()
    {
        msg::get();
        static::$pg["controller"] = get_called_class();
        OrderStatsModel::ReportFiltersValidate(static::$pg);
        OrderStatsView::Report(static::$pg, "Report");
    }

    static function Delete()
    {
        if (self::$data["pCanRemove"] === "No") {
            msg::set("It is a protected position!", "error");
            cf::Redirect(self::getBackUrl()["url"]);
        }

        parent::Delete();
    }
}