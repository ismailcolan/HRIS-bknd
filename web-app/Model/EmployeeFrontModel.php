<?php
/** The Orders model */
class EmployeeFrontModel extends EmployeeModel {

    static $dataAddons = array(), $dt = array("tbl"=>"apply_forms", "key"=>"afID", "pfx"=>"af", "controller"=>"EmployeeFrontController");

    public static function getEmployeesCount($conds) {
        $q = db::PrepareLoadQuery('select count(afID) count from '.self::$dt["tbl"], $conds);
        $count = db::PrepareLoadSingleCellValue($q);
        return intval($count);
    }

}
