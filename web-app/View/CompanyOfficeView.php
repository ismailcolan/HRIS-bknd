<?php

/** The CompanyOffice view */
class CompanyOfficeView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"],'coName' /*$pg["key"]/*"coAdded"*/, "ASC", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Address")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "EmplCount")], $c);
        table::$cols[] = Array("", $pg["pfx"] . "active", array("class" => "link", "sort" => false));

        table::execute($query);
        list($delete) = self::AllLnksCode($pg, array("delete"));

        foreach (table::$data as $r => &$cells) {
            $cells[$pg["pfx"] . "Address"] = htmlspecialchars($cells[$pg["pfx"] . "Address"]);
            $cells[$pg["pfx"] . "Name"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
            $cells[$pg["pfx"] . "active"] = sprintf($delete, $cells[$pg["key"] . "edit"]);
        }
        table::load();
    }

    /**Show an model item summary page
     * @param type $dt - controller::$data
     * @param type $pg - controller::$pg
     * @param type $lbls - model::$fldLbl
     */
    final static function Summary($dt, $pg, $lbls)
    {
        $flds4panel = array();
        $flds4panel[$lbls[($c = $pg["pfx"] . "Name")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "Address")]] = $dt[$c];
        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl
     * @param type $lists - lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $list)
    {
        $args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Address")] . ":",
            $f,
            $data[$f],
            $args
        );
//        form::inpSelect(
//            $lbls[($f = $pg["pfx"] . "Active")] . ":",
//            $f,
//            $list["active"],
//            isset($data[$f]) ? $data[$f] : "Yes",
//            (array("skip-empty" => true) + $args)
//        );


        if (CompanyOfficeController::$id) {
            form::html('<div class="fld form-group">
                    <label class="control-label col-sm-3 col-md-2"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
                    Important: Editing a company office name will change all existing employees.</div></div></div>');
            $args = self::$saveCancelArgsBase + array("id" => "saveOfficeBtn");
        } else {
            $args = self::$saveCancelArgsBase;
        }

        form::inpSaveCancel(CompanyOfficeController::getBackUrl()["url"], $args);


        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function MoveEmployees($data) {
        $args = array("fld-class"=>"form-group", "lbl-class"=>"control-label col-sm-4 col-md-3", "wrap-class"=>"col-sm-8 col-md-7 col-lg-5", "class"=>"form-control");
        $saveCancelArgsBase = array("fld-class"=>"form-group", "wrap-class"=>"col-sm-offset-4 col-md-offset-3 col-sm-12 col-md-9", "class"=>"btn btn-primary", "class-cancel"=>"btn btn-default");

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

        form::openDiv(array("class"=>"row"));
        form::openDiv(array("class"=>"col-sm-12"));
        form::html('<div><p><strong>The office cannot be deleted because there are employees in it.</strong><br/></p></div>');
        form::closeDiv();
        form::closeDiv();
        form::openDiv(array("class"=>"row"));
        form::openDiv(array("class"=>"col-sm-12"));
        form::html('<div><p>If you want to move employees individually into different offices, go to Employees page (use the filter "Company Office" on top right).</p></div>');
        form::html('<div><p><a href="'.ADMIN.'employees">Click here to go to Employees page >></a></p><br/><br/></div>');
        form::closeDiv();
        form::closeDiv();

        form::inpSelect(
            "Move all employees to office:",
            "moveToCOID",
            $data["offices"],
            null,
            (array("skip-empty" => true) + $args)
        );

        form::inpSaveCancel(CompanyOfficeController::getBackUrl()["url"], $saveCancelArgsBase + array("onclick-save"=>"return confirm('Please, confirm movement!')"), "Move");

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function RemoveAfterMove($oldOffice, $newOffice, $removeUrl) {
        $html = '<div><p>The employees have been moved from office <strong>'.$oldOffice["coName"].'</strong> to office <strong>'.$newOffice["coName"].'</strong>.</p></div>
                <div><p>You can now delete office <strong>'.$oldOffice["coName"].'</strong></p> </div>
                <div><hr/><p><a href="'.$removeUrl.'">Proceed with deletion >></a></hrp></div>';


        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

        form::openDiv(array("class"=>"row"));
        form::openDiv(array("class"=>"col-sm-10 "));
        form::html($html);
        form::closeDiv();
        form::closeDiv();
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

}
