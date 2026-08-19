<?php
/** The Order controller */
class ApplyFormController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    public static function Positions() {
        ApplyFormView::Positions();
    }

    public static function LoadPosition($pID)
    {
        if((int)$pID < 1) {
            $position = [
                "pID" => 0,
                "pName" => "Not sure",
                "pActive" => 'Yes',
                "pSalaryFromFormated" => "",
                "pSalaryToFormated" => "",
                "pSalaryTypeFormated" => "",
                "pSalaryType" => "Annual"
            ];
        } else {
            $position = PositionModel::dataById($pID);
            if(empty($position) && ($pID > 0)) {
                cf::Redirect(WP);
            }
            $position["pSalaryFromFormated"] = GLOBAL_MONEY_SIGN.' '.number_format($position["pSalaryFrom"], 2, ".", ",");
            $position["pSalaryToFormated"] = GLOBAL_MONEY_SIGN.' '.number_format($position["pSalaryTo"], 2, ".", ",");
            $position["pSalaryTypeFormated"] = strtolower($position["pSalaryType"]);
        }
        return $position;
    }

    public static function Form($jID, $pID) { 
        $job = JobModel::dataById($jID);
        if(($jID != "-1") && ($job === false)) {
            cf::Redirect(WP);
        }

        $position = self::LoadPosition($pID);
        if(is_null($position) || ($position === false) || ($position["pActive"] !== 'Yes')) {
            self::Positions();
            return;
        };

        $data = [];

        $data["job"] = $job;
        $data["position"] = $position;
        $questions = QuestionModel::getAllActiveQuestionsForPosition($pID);
        $data["questions"] = $questions;

        if(!empty($questions)) {
            $pass = true; $rejected = false;
            if(filter_has_var(INPUT_GET, "questionnaire")) {
                foreach ($questions as $q) {
                    $qname = "question".$q["qID"];
                    $qans = $q["answers"];

                    if(filter_has_var(INPUT_GET, $qname)) {
                        $aID = filter_input(INPUT_GET, $qname, FILTER_SANITIZE_NUMBER_INT);
                        foreach ($qans as $qa) {
                            if($qa["aID"] === $aID) {
                                foreach ($qa["rejectedFor"] as $rf) {
                                    if($rf["arpPID"] === $pID) {
                                        $rejected = true;
                                    }
                                }
                            }
                        }
                    } else {
                        $pass = false;
                        break;
                    }
                }
            } else {
                $pass = false;
            }
        } else {
            $pass = true; $rejected = false;
        }

        if(!$pass) {
            ApplyFormView::Questinnarie($pID, self::$pg, self::$rq, $data);
        } else {
            if($rejected) {
                self::RejectForm($jID, $pID, $data);
            } else {
                $jID = filter_has_var(INPUT_GET, "jID") ? filter_input(INPUT_GET, "jID") : -1;
                $job = JobModel::dataById($jID);
                if($job === false) $job = [];
                $data["job"] = $job;

                // TODO :: If user is logged automatically fill form and proceed to Welcome message

                if(isset(AppModel::$usr["afID"])) {
                    $data  = AppModel::$usr;
                    $pID = filter_has_var(INPUT_GET, "pID") ? filter_input(INPUT_GET, "pID") : -1;
                    $data["position"] = PositionModel::dataById($pID);
                    ApplyFormView::Form($pID, self::$pg, self::$rq, ApplyFormModel::$fldLbl, $data, true);
                } else {
                    ApplyFormView::Form($pID, self::$pg, self::$rq, ApplyFormModel::$fldLbl, $data);
                }
            }
        }
    }

    public static function RejectForm($jID, $pID, $data) {
        $questAsArray = ApplyFormModel::QuestionnarieQueryToArray($pID);
        $q = "INSERT INTO apply_forms (afJID, afPID, afQuestionnaire, afIP, afActive, afAdded, afApplyAsEmployeeID, afEmail, afFirstName, afLastName) values (%d, %d, '%s', '%s', '%s', %s";

        if(isset(AppModel::$usr["afID"])) {
            $usr = AppModel::$usr;
            $q .= ", {$usr['afID']}, '{$usr['afEmail']}', '{$usr['afFirstName']}', '{$usr['afLastName']}' ";
        } else {
            $q .= ", null, null, null, null ";
        }

        $q .= ")";

        $q = sprintf($q, $jID, $pID,
            db::EscapeStr(json_encode($questAsArray)),
            ApplyFormModel::VisitorIP(),
            'Auto-rejected',
            "'".
			 val::currentDate(MYSQL_DATE)
			 //date('Y-m-d H:i:s')
			 ."'");

        db::SQL($q);
        ApplyFormView::Questinnarie($pID, self::$pg, self::$rq, $data);
    }

    public static function RejectApplyMustLogin($reasonSameEmail = true) {
        if($reasonSameEmail) {
            ApplyFormView::ShowErrornMessage('There\'s an employee with that email address. If you\'re already an employee, please login and then apply again.',
                '<a href="'.WP.'" class="btn btn-primary">Log in</a>');
        } else {
            $_SESSION["apply_form_data"] = $_POST;
            $get = '';
            foreach ($_GET as $k=>$v) {
                if(!in_array($k, ["front-file", "rq"])) {
                    if(empty($get)) {
                        $get = '?';
                    } else {
                        $get .= '&';
                    }
                    $get .= $k . '=' . $v;
                }
            }
            $get = WP . 'application-form' .$get . '&skip-check=true';

            ApplyFormView::ShowErrornMessage('There\'s already an employee with the same first and last name. If you\'re already an employee, please login and then apply again.',
                '<a href="'.$get.'" class="btn btn-primary">I\'m not an employee, proceed with application >></a> <a href="'.WP.'" class="btn btn-primary">I\'m an employee, log me in >></a>');
        }
    }

    final static function TitleAndMetaTags() {
        cf::$pg["title"] = "Application form";
        cf::$pg["description"] = "Application form";
        cf::$pg["keywords"] = "application, form";

    }

    final static function AppFormCheck($jID, $pID, $lists = array()){
        self::$id = null;
        $lists["position"] = self::LoadPosition($pID);
        try{
            ApplyFormModel::DetailsValidate("server", $lists);



            $isLogged = false;
            if(isset(AppModel::$usr)) {
                $afID = intval(AppModel::$usr["afID"]);
                if($afID > 0) {
                    $isLogged = true;
                }
            }
            if(!$isLogged) {
                if(!filter_has_var(INPUT_GET, "skip-check")) {
                    $email = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
                    $fName = filter_input(INPUT_POST, "afFirstName", FILTER_SANITIZE_STRING);
                    $lName = filter_input(INPUT_POST, "afLastName", FILTER_SANITIZE_STRING);
					
						
                    $q = sprintf('select * from apply_forms where afActive = "Hired" 
					   and afFirstName="%s" and afLastName="%s"'
					   , $fName, $lName);
                    $emplData = db::PrepareLoadSingleRow($q);

                    if(is_array($emplData)) {
                        val::setError("not_logged", "Please, Log in first!");
                        val::throwServerRunError();
                    }
                }
            }

            echo json_encode(["result"=>"ok"]);
        }
        catch (Exception $e) {
            echo json_encode(["result"=>"errors", "errors"=>form::$error]);
        }
    }

    final static function AppFormSave($jID, $pID, $lists = array()){
		
        self::$id = null;
        $lists["position"] = self::LoadPosition($pID);

        try{ApplyFormModel::DetailsValidate("server", $lists); } catch (Exception $e) {return self::Form($jID, $pID);}

        ApplyFormModel::$postOverrided["afPID"] = intval($pID);
        if(filter_has_var(INPUT_GET, "jID")) {
            $jID = filter_input(INPUT_GET, "jID", FILTER_SANITIZE_NUMBER_INT);
        } else {
            $jID = -1;
        }
        ApplyFormModel::$postOverrided["afJID"] = intval($jID);
        ApplyFormModel::$postOverrided["afQuestionnaire"] = json_encode(ApplyFormModel::QuestionnarieQueryToArray($pID));
        if(isset(AppModel::$usr["afID"])) {
            ApplyFormModel::$postOverrided["afApplyAsEmployeeID"] = intval(AppModel::$usr["afID"]);
        }

        if(!empty($lists["position"]["pSkills"])) {
            $reqSkills = explode("|", $lists["position"]["pSkills"]);
            $appSkills = filter_input(INPUT_POST, "afSkills", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if(!empty($appSkills)) {
                foreach ($reqSkills as $id) {
                    if(!in_array($id, $appSkills)) {
                        self::RejectForm($jID, $pID, []);
                        return false;
                    }
                }
            } else {
                if(!empty($reqSkills)) {
                    self::RejectForm($jID, $pID, []);
                    return false;
                }
            }
        }

        $isLogged = false;
        if(isset(AppModel::$usr)) {
            $afID = intval(AppModel::$usr["afID"]);
            if($afID > 0) {
                $isLogged = true;
            }
        }
        if(!$isLogged) {
            if(!filter_has_var(INPUT_GET, "skip-check")) {
                $email = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
				   
                $q = sprintf('select * from apply_forms where afActive = "Hired"and afEmail="%s"',$email);
				
                $emplData = db::PrepareLoadSingleRow($q);
                if(is_array($emplData)) {
                        self::RejectApplyMustLogin($emplData["afEmail"] === $email);
                        die();
                }
            }
        }


        self::$id = null;

		
        if(db::Save(ApplyFormModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__)) {

            $id = self::$id;
            CVModel::processUploadedFiles($id);

            $fileName = str_replace("{---LiveID---}", $id, ApplyFormModel::IMG_FILENAME);
            img::move_images_from_temp("photo", true, $fileName);

            $data = ApplyFormModel::dataById(self::$id);
			
			  
			  if (strpos($data["afPosition"],'HR Manager')!== false) $data["afPosition"]='Not sure';
			 
            $msg = nl2br(sprintf(MAIL_NEW_APPLICATION_MSG, $data["afPosition"], $data["afFirstName"], $data["afLastName"]));
            cf::sendMail(EMAIL_ADMIN, MAIL_NEW_APPLICATION_SUBJ, $msg, EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS);
            //cf::sendMail(DEV_EMAIL_ADMIN, MAIL_NEW_APPLICATION_SUBJ, $msg, DEV_EMAIL_MAILER_NAME, DEV_EMAIL_MAILER_ADDRESS);

            $q = 'select afEmail from apply_forms where afActive="Hired" and afIsManager="yes" and exists(select mpID from manager_positions where mpAFID=afID and mpPID='.$data["afPID"].')';
            $managers = db::PrepareLoad($q);
            if(!empty($managers)) {
                foreach ($managers as $manager) {
                    cf::sendMail($manager["afEmail"], MAIL_NEW_APPLICATION_SUBJ, $msg, EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS);
                }
            }

            ApplyFormView::SuccessMessage(self::$id, self::$pg, self::$rq, []);
        } else {
            msg::set(TXT_SAVE_ERROR,"error");
        }
    }
}