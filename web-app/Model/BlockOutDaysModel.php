<?php
/** The General model */
class BlockOutDaysModel extends AppModel
{
  
   static $dataAddons = array(), 
	 $dt = array("tbl" => "bod_days", "key" => "bodID", "pfx" => "bod", "controller" => "BlockOutDaysController");
    static $mainTblFields = array('bodID','bodYear','bodDatesStart' ,'bodDateEnd','bodOffices','bodPositions');
	 static $fldLbl = array('bodID'=>'ID','bodYear'=>'Year','bodDateStart'=>'Start date' ,
	 'bodDateEnd'=>'End date','bodOffices'=>'Offices','bodPositions'=>'Positions');
    static $addonFieldsByTables;
    static $postOverrided = array();

}
