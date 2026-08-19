<?php

/** The Offices (admin) class */
class EmployeesManager extends PageAdmin
{
    const CONTROLLER = "EmployeeController";

    public function __construct($id = 0, $C = self::CONTROLLER)
    {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        $C::$id = in_array($C::$rq, array("hired")) && $id > 0 ? $id : null;

        $C::$data = isset($C::$id) ? $M::dataById($id) : null;

        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C = self::CONTROLLER)
    {
        msg::prepare();
        if (in_array($C::$rq, array("hired", "inactive-hired", "manager-hired", "summary"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg = array("item" => "Employee", "items" => "Employees"))
    {
        $M = App_AdminModel::GetModel($C);

        $pg["hired"] = array("url" => cf::$rq["url"], "title" => $pg["items"]);
        $pg["details"] = array("url" => cf::$rq["url"], "title" => $pg["item"]);
        $pg["summary"] = array("url" => cf::$rq["url"], "title" => $pg["item"]);
        $pg["password"] = array("url" => cf::$rq["url"], "title" => $pg["item"]);
        $pg["tbl"] = $M::$dt["tbl"];
        $pg["key"] = $M::$dt["key"];
        $pg["pfx"] = $M::$dt["pfx"];

        foreach ($C::$pg = $pg as $k => $v) {
            if (is_array($v) && isset($v["url"]) && $v["url"] === cf::$rq["url"]) {
                return $C::$rq = self::$rq = $k;
            }
        }
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

$adminSection = new EmployeesManager((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));
$adminSection->ImgsSetup();

$adminSection->Header();
if(EmployeeController::$id) {
    if(filter_has_var(INPUT_POST, "save-btn") || filter_has_var(INPUT_POST, "complete-onboarding-tasks")) {
        $adminSection->DetailsSaveForManager();
    } else {
        $adminSection->DetailsForManager();
    }
} else {
    $adminSection->AllForManager();
}
$adminSection->Footer();