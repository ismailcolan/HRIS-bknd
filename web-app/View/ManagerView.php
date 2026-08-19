<?php

/** The CompanyOffice view */
class ManagerView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], $pg["key"]/*"coAdded"*/, "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Email")], $c);
        table::$cols[] = Array("", $pg["pfx"] . "active", array("class" => "link", "sort" => false));

        table::execute($query);
        if ($rq === "list") {
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "deactivate"));
        } else {
            list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view", "edit", "reactivate"));
        }

        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                if ($rq === "list") {
                    $cells[$pg["pfx"] . "active"] = sprintf($deactivate, $cells[$pg["key"] . "edit"]);
                } else {
                    $cells[$pg["pfx"] . "active"] = sprintf($reactivate, $cells[$pg["key"] . "edit"]);
                }

                $cells[$pg["key"] . "edit"] = sprintf($edit, $cells[$pg["key"] . "edit"]);
                $cells[$pg["pfx"] . "Name"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
                $cells[$pg["pfx"] . "Email"] = form::linkOut("mailto:".$cells[$pg["pfx"] . "Email"], $cells[$pg["pfx"] . "Email"]);
            }
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
        $flds4panel[$lbls[($c = $pg["pfx"] . "Email")]] =  form::linkOut("mailto:".$dt[$c], $dt[$c]);


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
            $args
        );
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Email")] . ":",
            $f,
            $data[$f],
            $args
        );
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Active")] . ":",
            $f,
            $list["active"],
            isset($data[$f]) ? $data[$f] : "Yes",
            (array("skip-empty" => true) + $args)
        );

        form::inpSaveCancel(ADMIN . self::DetailsCancelUrl($data, $pg), self::$saveCancelArgsBase);
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }
}
