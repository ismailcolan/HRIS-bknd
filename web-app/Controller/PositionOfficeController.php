<?php
/** The Order controller */
class PositionOfficeController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg, $title = "Positions & Offices";

    public static function All(){

        $pos = PositionOfficeModel::getAllActivePositions(AppModel::$admin["waaAFID"]);
        $offc = PositionOfficeModel::getAllActiveOffices(AppModel::$admin["waaAFID"]);

        form::openDiv(array("class"=>"col-sm-6"));
        form::openDiv(array("class"=>"jumbotron"));
        form::tag("Offices", "h5");
        form::html("<hr/>");
        form::openTag("ul");
        foreach ($offc as $item) {
            form::tag($item["coName"], "li");
        }
        form::closeTag("ul");
        form::closeDiv();
        form::closeDiv();



        form::openDiv(array("class"=>"col-sm-6"));
        form::openDiv(array("class"=>"jumbotron"));
        form::tag("Positions", "h5");
        form::html("<hr/>");
        form::openTag("ul");
        foreach ($pos as $item) {
            form::tag($item["pName"], "li");
        }
        form::closeTag("ul");
        form::closeDiv();
        form::closeDiv();
    }


}