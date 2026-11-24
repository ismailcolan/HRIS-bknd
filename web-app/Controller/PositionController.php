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

    final static function Assignments()
    {
        if (empty(self::$id)) {
            cf::Redirect(ADMIN . "positions");
        }

        $lists = array();
        $lists["credentialings"] = CredentialingModel::AllActiveKeyName();
        $lists["onboarding"] = OnboardingModel::AllActiveKeyNameEmpl(null, "on");
        $lists["offboarding"] = OnboardingModel::AllActiveKeyNameEmpl(null, "off");

        $lists["assignedCredentialings"] = array_map("intval", array_column(
            CredentialingPositionsModel::LoadPositionCredentialings(self::$id),
            "cID"
        ));
        $lists["assignedOnboarding"] = array_map("intval", array_column(
            OnboardingPositionsModel::LoadPositionOnboardingsONBOnly(self::$id),
            "otID"
        ));
        $lists["assignedOffboarding"] = array_map("intval", array_column(
            OnboardingPositionsModel::LoadPositionOffboardings(self::$id),
            "otID"
        ));

        $otherPositions = PositionModel::AllActiveKeyName(null, true);
        if (!is_array($otherPositions)) {
            $otherPositions = array();
        }
        unset($otherPositions[self::$id]);
        $lists["otherPositions"] = $otherPositions;

        msg::get();
        PositionView::Assignments(self::$data, self::$pg, PositionModel::$fldLbl, $lists);
    }

    private static function assignmentIdsForPosition($pID)
    {
        $pID = (int)$pID;
        return array(
            "credentialings" => array_map("intval", array_column(
                CredentialingPositionsModel::LoadPositionCredentialings($pID),
                "cID"
            )),
            "onboarding" => array_map("intval", array_column(
                OnboardingPositionsModel::LoadPositionOnboardingsONBOnly($pID),
                "otID"
            )),
            "offboarding" => array_map("intval", array_column(
                OnboardingPositionsModel::LoadPositionOffboardings($pID),
                "otID"
            )),
        );
    }

    private static function applyPositionAssignments($pID, $newCreds, $newOn, $newOff)
    {
        $pID = (int)$pID;
        $newCreds = array_values(array_filter(array_map("intval", (array)$newCreds)));
        $newOn = array_values(array_filter(array_map("intval", (array)$newOn)));
        $newOff = array_values(array_filter(array_map("intval", (array)$newOff)));

        $oldCreds = array_map("intval", array_column(
            CredentialingPositionsModel::LoadPositionCredentialings($pID),
            "cID"
        ));

        foreach ($oldCreds as $old) {
            if (!in_array($old, $newCreds, true)) {
                EmployeeCredentialingModel::RemovePosition($old, $pID);
            }
        }
        foreach ($newCreds as $new) {
            if ($new > 0 && !in_array($new, $oldCreds, true)) {
                EmployeeCredentialingModel::AddPositionIfNotExists($new, $pID);
            }
        }

        CredentialingPositionsModel::SavePositionCredentialings($pID, $newCreds);
        OnboardingPositionsModel::SavePositionTasks($pID, $newOn, "on");
        OnboardingPositionsModel::SavePositionTasks($pID, $newOff, "off");
    }

    final static function AssignmentsSave()
    {
        if (empty(self::$id)) {
            cf::Redirect(ADMIN . "positions");
        }

        $newCreds = filter_has_var(INPUT_POST, "pCredentialings")
            ? array_values(array_map("intval", (array)filter_input(INPUT_POST, "pCredentialings", FILTER_DEFAULT, FILTER_FORCE_ARRAY)))
            : [];
        $newOn = filter_has_var(INPUT_POST, "pOnboarding")
            ? array_values(array_map("intval", (array)filter_input(INPUT_POST, "pOnboarding", FILTER_DEFAULT, FILTER_FORCE_ARRAY)))
            : [];
        $newOff = filter_has_var(INPUT_POST, "pOffboarding")
            ? array_values(array_map("intval", (array)filter_input(INPUT_POST, "pOffboarding", FILTER_DEFAULT, FILTER_FORCE_ARRAY)))
            : [];

        self::applyPositionAssignments(self::$id, $newCreds, $newOn, $newOff);

        msg::set(TXT_SAVE_OK, "success");
        cf::Redirect(ADMIN . "position-assignments?id=" . (int)self::$id);
    }

    final static function AssignmentsCopy()
    {
        if (empty(self::$id)) {
            cf::Redirect(ADMIN . "positions");
        }

        $sourceId = (int)filter_input(INPUT_POST, "copyFromPID", FILTER_VALIDATE_INT);
        if ($sourceId <= 0 || $sourceId === (int)self::$id) {
            msg::set("Please select a position to copy from.", "error");
            cf::Redirect(ADMIN . "position-assignments?id=" . (int)self::$id);
        }

        $source = PositionModel::dataById($sourceId);
        if (empty($source)) {
            msg::set("The selected position was not found.", "error");
            cf::Redirect(ADMIN . "position-assignments?id=" . (int)self::$id);
        }

        $copied = self::assignmentIdsForPosition($sourceId);
        self::applyPositionAssignments(
            self::$id,
            $copied["credentialings"],
            $copied["onboarding"],
            $copied["offboarding"]
        );

        msg::set(TXT_SAVE_OK, "success");
        cf::Redirect(ADMIN . "position-assignments?id=" . (int)self::$id);
    }
}