<?php
class DaysOffModel extends AppModel
{

    static $dataAddons = array(), $dt = array("tbl" => "nonworking_days", "key" => "nwdID", "pfx" => "nwd", 
	   "controller" => "DaysOffController");
    static $mainTblFields = array('nwdID','nwdYear','nwdDatesStart' ,'nwdDateEnd','nwdOffices','nwdPositions');
	 static $fldLbl = array('nwdID'=>'ID','nwdYear'=>'Year','nwdDateStart'=>'Start date' ,
	 'nwdDateEnd'=>'End date','nwdOffices'=>'Offices','nwdPositions'=>'Positions');
    static $addonFieldsByTables;
    static $postOverrided = array();

}
