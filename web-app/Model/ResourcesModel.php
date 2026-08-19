<?php

/** The CompanyOffice model */
class ResourcesModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "resources", "key" => "resID", "pfx" => "res", "controller" => "ResourcesController");
    static $mainTblFields = array("resTitle", "resDescription", "resImportant", "resActive");
    static $fldLbl = array(
        "resID" => "ID", "resTitle" => "Title", "resDescription" => "Description", "resImportant" => "Is important", "resActive" => "Status",
        "resAdded" => "Added", "resUpdated" => "Last changed", "resCategories" => "Categories", "resCategoryNames" => "Categories"
    );
    static $postOverrided = array();

    private static function validateTitle($name = "resTitle")
    {
        val::required($name, "Please fill out the " . strtolower(self::$fldLbl[$name]) . ".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 100);
    }

    private static function validateCategories($name = "resCategories")
    {
        if (val::$runat !== "server") {
            return true;
        }

        if (!filter_has_var(INPUT_POST, $name)) {
            val::setError($name, "Select at lieast one category.");
        }
    }

    final static function DetailsValidate($type, &$lists)
    {
        $lists["active"] = self::$active;
        val::init($type);
        self::validateTitle();
        self::validateCategories();

        if (!isset(AnnouncementsController::$id) && val::$runat === "server") { //Fix the MySql problem with more than 1 timestamp
            self::$mainTblFields[] = self::$dt["pfx"] . "Added";
            self::$postOverrided[self::$dt["pfx"] . "Added"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Added";

            self::$mainTblFields[] = self::$dt["pfx"] . "Updated";
            self::$postOverrided[self::$dt["pfx"] . "Updated"] = "NOW()";
            self::$postOverridedSkipEscape[] = self::$dt["pfx"] . "Updated";
        }
        val::throwServerRunError();
    }

    static function delete($data, $dump = false)
    {
        $res = $data;
        $files = ResourcesModel::getResourceItems($res["resID"]);

        foreach ($files as $file) {
            $fn = "../add/uploads/resources/file_" . $file["riID"];
            if (file_exists($fn)) {
                unlink($fn);
            }
        }

        $q = "delete from resource_items where riRID={$res['resID']}";
        db::SQL($q);

        $q = "delete from res_cats_link where rclRID={$res['resID']}";
        db::SQL($q);

        $q = "delete from resources where resID={$res['resID']}";
        db::SQL($q);

        msg::put("The resource were successfully deleted.");
        
        return true;
    }

    static function query($conditions = '', array $orderby = array(), $limit = 0)
    {
        $query = "SELECT *,
                    (select group_concat(rclRCID) from res_cats_link where rclRID=resID) resCategories,
                    (select group_concat(rcName) from res_cats_link left join rcategories on rcID=rclRCID where rclRID=resID order by 1) resCategoryNames
                  FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $addons = array();

        return str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);
    }

    public static function AllActiveKeyName()
    {
        $data = db::LoadKeyValueArrFromTable(self::query(
            "`" . self::$dt["pfx"] . "Active`='Yes' ",
            '',
            ["resImportant" => "DESC", "resTitle" => "ASC"]
        ), self::$dt["key"], self::$dt["pfx"] . "Title");
        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item = htmlspecialchars($item);
            }
        }
        return $data;
    }

    static function dataById($id = 0, $allLangs = false)
    {
        $data = db::PrepareLoadSingleRow(self::query(self::$dt["key"] . "=" . $id));
        if (empty($data)) {
            return false;
        }

        $data[self::$dt["pfx"] . "Title"] = htmlspecialchars($data[self::$dt["pfx"] . "Title"]);
        $data[self::$dt["pfx"] . "Description"] = htmlspecialchars($data[self::$dt["pfx"] . "Description"]);

        return $data;
    }

    static function adminPageTitleWithLinks($links = null)
    {
        $C = self::$dt["controller"];
        // var_dump($C::$rq);
        // var_dump($C::$pg[$C::$rq]);

        if (in_array($C::$rq, ["resources-categories"])) {
            $title = $C::$pg[$C::$rq]["title"];
            $links = "";
        } else if (in_array($C::$rq, array("list", "inactive-list"))) {
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if ($C::$rq === "summary") {
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"] . "Title"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"] . "Title"] : "Add " . strtolower($C::$pg[$C::$rq]["title"]));
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed()
    {
        return true;
    }

    public static function getResourceItems($forResID, $type = "File")
    {
        $resID = intval($forResID);
        if ($resID == 0) {
            return [];
        }

        $q = "select * from resource_items where riRID={$resID} and riType='{$type}' and riActive='Yes'";
        $list = db::PrepareLoad($q);
        if ($list === false) {
            $list = [];
        }
        return $list;
    }
}
