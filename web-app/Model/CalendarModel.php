<?php

/** The CompanyOffice model */
class CalendarModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "calendar", "key" => "calID", "pfx" => "cal", "controller" => "CalendarController");
    static $mainTblFields = array("calHeadline", "calDescription", "calDate", "calActive");
    static $fldLbl = array(
        "calID" => "ID", "calHeadline" => "Headline", "calDescription" => "Description", "calDate" => "Date", "calActive" => "Status",
        "calAdded" => "Added", "calUpdated" => "Last changed"
    );
    static $postOverrided = array();

    private static function validateHeadline($name = "calHeadline")
    {
        val::required($name, "Please fill out the " . strtolower(self::$fldLbl[$name]) . ".");
        val::minLength($name, 2, "Please add at least 2 characters.");
        val::maxLength($name, 100);
    }

    private static function validateEndDate($name = "calDate")
    {
        $lbl = strtolower(self::$fldLbl[$name]);
        if (val::required($name, "Please enter a valid " . self::$fldLbl[$name] . " in format [" . USER_DATE . "]")) {
            if (val::validDate($name, "Invalid {$lbl} . Valid {$lbl} example: 12/30/1990")) {
                $date = DateTime::createFromFormat(USER_DATE, filter_input(INPUT_POST, $name));
                if ($date === false) {
                    $date = DateTime::createFromFormat(GLOBAL_PHP_DATE_ALT, filter_input(INPUT_POST, $name));
                }

                $today = new DateTime();
                $today->setTime(0, 0, 0);
                if ($date < $today) {
                    val::setError($name, "The date can not be in the past.");
                }
                self::$postOverrided[$name] = $date->format('Y-m-d');
            }
        }
    }

    final static function DetailsValidate($type, &$lists)
    {
        $lists["active"] = self::$active;
        val::init($type);
        self::validateHeadline();
        self::validateEndDate();

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

    static function query($conditions = '', array $orderby = array(), $limit = 0)
    {
        $query = "SELECT *
                  FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $addons = array();

        return str_replace("*", (empty($addons) ? "*" : ("*,\n" . implode(",\n", $addons))), $q);
    }

    public static function AllActiveKeyName()
    {
        $data = db::LoadKeyValueArrFromTable(self::query("`" . self::$dt["pfx"] . "Active`='Yes' and calDate >= CURRENT_DATE", ["calDate"=>"ASC"]), self::$dt["key"], self::$dt["pfx"] . "Headline");
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
        $data = parent::dataById($id, $allLangs);
        if (empty($data)) {
            return false;
        }

        $data[self::$dt["pfx"] . "Headline"] = htmlspecialchars($data[self::$dt["pfx"] . "Headline"]);
        $data[self::$dt["pfx"] . "Description"] = htmlspecialchars($data[self::$dt["pfx"] . "Description"]);
        $data[self::$dt["pfx"] . "Date"] = val::convDate(MYSQL_DATE,USER_DATE,$data[self::$dt["pfx"] . "Date"]);
		 //date(USER_DATE, strtotime($data[self::$dt["pfx"] . "Date"]));

        return $data; 
    }

    static function adminPageTitleWithLinks($links = null)
    {
        $C = self::$dt["controller"];
        if (in_array($C::$rq, array("list", "inactive-list"))) {
            $title = $C::$pg[$C::$rq]["title"];
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else if ($C::$rq === "summary") {
            $title = (filter_has_var(INPUT_GET, "id") ? $C::$data[self::$dt["pfx"] . "Headline"] : cf::end(__METHOD__));
            $links = AccessModel::AdminPageHeaderLnk($C);
        } else {
            $title = (filter_has_var(INPUT_GET, "id") ? "Editing: " . $C::$data[self::$dt["pfx"] . "Headline"] : "Add " . strtolower($C::$pg[$C::$rq]["title"]));
        }
        return array($title, $links);
    }

    static function ItemDeleteAllowed()
    {
        return true;
    }
}
