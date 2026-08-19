<?php
/** The Orders model */
class HomeFrontModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"positions", "key"=>"pID", "pfx"=>"p", "controller"=>"HomeFrontController");
    static $mainTblFields = array();
    static $fldLbl = array();
    static $addonFieldsByTables;
    static $postOverrided = array();


}
