<?php
/** front index page
 * @see \web-app-router.php
 * @see Router::$classFilename("front-page");
 */

/** The front page class
 * @example http://www.example.com/...
 */
class JobDetailsPage extends PageFront
{

    const CONTROLLER = "JobFrontController";

    public function __construct($C = self::CONTROLLER)
    {
        parent::__construct($C);
    }

    protected function inHead($C = self::CONTROLLER)
    {
        msg::prepare();
    }

    protected function setPgRq($C = self::CONTROLLER)
    {
        $M = App_AdminModel::GetModel($C);

        $pg["form"] = array("url" => "application-form");
        $pg["send"] = array("url" => "application-form");

        $pg["tbl"] = $M::$dt["tbl"];
        $pg["key"] = $M::$dt["key"];
        $pg["pfx"] = $M::$dt["pfx"];
        self::$pg = $pg;

        $request = strlen($url = filter_input(INPUT_GET, "rq")) > 0 ? $url : "form";

        switch ($request) {
            case "application-form":
                $rq = "form";
                break;
            default:
                $rq = "form";
        }
        self::$rq = $rq;

        self::$pageToControllerMethods = array("Form", "Positions", "AppFormSave", "AppFormCheck");
        $this->setControlerProperties(self::$controller = $C, array("rq", "pg"));
    }

    public static function ImgsSetup()
    {
        img::$setup = array(); //Its better to reset the values
        img::$setup["path"] = ApplyFormModel::IMG_PATH;
        img::$setup["crop"] = "inline";
        img::$setup["width"] = "320";
        img::$setup["height"] = "320";
        img::$setup["disable-delete"] = true;
        img::$setup["allowed-extensions"] = array("jpg", "png");
        img::prepare();
    }

}

$page = new JobDetailsPage();
$page->ImgsSetup();


if (!filter_has_var(INPUT_GET, "mode")) {
    $page->HTMLHeader();
}
JobFrontController::JobDetails();
/***********
if ($page::$rq === "form") {
    $jID = -1;
    if (filter_has_var(INPUT_GET, "jID")) {
        $jID = filter_input(INPUT_GET, "jID", FILTER_SANITIZE_NUMBER_INT);
        if ($jID === "") $jID = -1;
    }
    $pID = null;
    if (filter_has_var(INPUT_GET, "pID")) {
        $pID = filter_input(INPUT_GET, "pID", FILTER_SANITIZE_NUMBER_INT);
        if ($pID === "") $pID = null;
    }

    if ($pID !== null) {

        if(isset(AppModel::$usr["afHiredForPID"]) && (AppModel::$usr["afHiredForPID"] == $pID)) {
            form::html('<div class="alert alert-dismissible alert-danger" style="margin-top: 24px;">
                    You\'re already hired at this position. <a href="'.WP.'">Back to home >></a></div>');
                    
            msg::clear();
            die();
        }
        msg::get();

        if (filter_has_var(INPUT_GET, "mode")) {
            if (filter_input(INPUT_GET, "mode") === "checkdata") {
                $page->AppFormCheck($jID, $pID);
                die();
            }
        } elseif (filter_has_var(INPUT_POST, "save-btn")) {
            msg::get();
            $page->AppFormSave($jID, $pID);
        } elseif (filter_input(INPUT_GET, "mode") === "skip-check") {
            if(isset($_SESSION["apply_form_data"])) {
                $_POST = $_SESSION["apply_form_data"];
                unset($_SESSION["apply_form_data"]);
                msg::get();
                $page->AppFormSave($jID, $pID);
            } else {
                cf::Redirect(WP);
            }
        }else {
            $lists = array("position" => null);
            ApplyFormModel::DetailsValidate("client", $lists);
            $page->Form($jID, $pID);
        }
    } else {
        cf::Redirect(WP);
    }

} else {
    cf::vdd($page::$rq);
}
************/
$page->HTMLFooter();
