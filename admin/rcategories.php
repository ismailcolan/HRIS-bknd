<?php

/** The Offices (admin) class */
class RCategories extends PageAdmin
{
    const CONTROLLER = "RCategoriesController";

    public function __construct($id = 0, $C = self::CONTROLLER)
    {
        parent::__construct();

        $M = App_AdminModel::GetModel($C);

        if ($C::$rq == "inactive-list") {
            $C::$rq = "list";
        }

        $C::$id = !in_array($C::$rq, array("list", "inactive-list")) && $id > 0 ? $id : null;
        $C::$data = isset($C::$id) ? $M::dataById($id) : null;

        AccessModel::GrantAccessAndActions($C);
    }

    protected function inHead($C = self::CONTROLLER)
    {
        msg::prepare();
        if (in_array($C::$rq, array("list", "inactive-list", "summary"))) {
            table::prepare();
        }
    }

    protected function setPgRq($C = self::CONTROLLER, $pg = array("item" => "Category", "items" => "Categories"))
    {
        $M = App_AdminModel::GetModel($C);

        $pg["list"] = array("url" => "rcategories", "title" => $pg["items"]);
        $pg["details"] = array("url" => "rcategory-details", "title" => $pg["item"]);
        $pg["tbl"] = $M::$dt["tbl"];
        $pg["key"] = $M::$dt["key"];
        $pg["pfx"] = $M::$dt["pfx"];

        foreach ($C::$pg = $pg as $k => $v) {
            if (is_array($v) && isset($v["url"]) && $v["url"] === cf::$rq["url"]) {
                return $C::$rq = self::$rq = $k;
            }
        }
    }
}


$adminSection = new RCategories((int)filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT));

if (intval(AppModel::$admin["waaAFID"]) > 0 && !RoleHelper::isHRManager()) {
    cf::Redirect(ADMIN);
}

$adminSection->Header();

if (in_array($adminSection::$rq, array("list"))) {
    $adminSection->All();
} else if ($adminSection::$rq === "summary") {
    $adminSection->Summary();
} else if ($adminSection::$rq === "details") {
    if (filter_has_var(INPUT_POST, "delete-btn")) {
        $adminSection->RemoveResources();
    } else if (in_array(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING), ["trash", "alert"]) || filter_has_var(INPUT_POST, "move-btn")) {
        $adminSection->Delete();
    } else if (filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "restore") {
        $adminSection->Reactivate();
    } else if (filter_input(INPUT_SERVER, "REQUEST_METHOD") === "POST" || $_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminSection->DetailsSave();
    } else {
        msg::prepare();
        $adminSection->Details();
    }
}

$adminSection->Footer();