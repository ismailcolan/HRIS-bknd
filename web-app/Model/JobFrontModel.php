<?php
/** The CompanyOffice model */
class JobFrontModel extends JobModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"jobs", "key"=>"jID", "pfx"=>"j", "controller"=>"JobFrontController");
    static $mainTblFields = array("jName", "jPID", "jEndDate", "jDescription", "jActive");
    static $fldLbl = array( "jID"=>"ID", "jName"=>"Name", "jPID"=>"Position", "jPosition"=>"Position", "jEndDate"=>"End date", "jDescription"=>"Description",
                            "jActive"=>"Active", "jAdded"=>"Added", "jUpdated"=>"Last changed");
    static $postOverrided = array();
    
}
