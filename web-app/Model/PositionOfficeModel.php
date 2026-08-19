<?php

/** The Orders model */
class PositionOfficeModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "positions", "key" => "pID", "pfx" => "p", "controller" => "PositionOfficeController");
    static $mainTblFields = array("pName", "pSkills", "pSalaryType", "pSalaryFrom", "pSalaryTo", "pActive");
    static $fldLbl = array("pID" => "ID", "pName" => "Position", "pSkills" => "Required skills", "pSalaryType" => "Salary type", "pSalaryFrom" => "Salary from", "pSalaryTo" => "Salary to");
    static $addonFieldsByTables;
    static $postOverrided = array();

    public static function adminPageTitleWithLinks(){
        $C = self::$dt["controller"];

        $title = "Positions & Offices";
        $links = AccessModel::AdminPageHeaderLnk($C);
        return array($title, $links);

    }

    static function getAllActivePositions($afID)
    {
        $q = 'select pName from manager_positions left join positions on pID=mpPID where mpAFID='.$afID.' and pActive="Yes"';
        $data = db::PrepareLoad($q);

        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["pName"] = htmlspecialchars($item["pName"]);
            }
        }
        return $data;
    }

    static function getAllActiveOffices($afID)
    {
        $q = 'select coName from manager_offices left join company_offices on coID=moCOID where moAFID='.$afID.' and coActive="Yes"';
        $data = db::PrepareLoad($q);

        if ($data === false) {
            $data = [];
        } else {
            foreach ($data as &$item) {
                $item["coName"] = htmlspecialchars($item["coName"]);
            }
        }
        return $data;
    }
}
