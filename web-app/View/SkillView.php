<?php
/** The Vendor view */
class SkillView extends AppView {
    
    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg 
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl */
    final static function All($pg, $query, $lbls, $rq){
        table::create($pg["tbl"], $pg["key"]/*"coAdded"*/, "desc", self::ADMIN_LISTS_ROWS_LIMIT);
            table::$cols[] = Array($lbls[($c = $pg["pfx"]."Name")], $c);
        if($rq === "list") {
            table::$cols[] = Array("", $pg["key"] . "deactivate", array("class" => "link", "sort" => false));
        }
        if($rq === "inactive-list") {
            table::$cols[] = Array("", $pg["key"] . "reactivate", array("class" => "link", "sort" => false));
        }


            table::execute($query);
            list($edit, $reactivate, $deactivate, $delete) = self::AllLnksCode($pg, array("edit","reactivate","deactivate","delete"));
            foreach(table::$data as $r => &$cells){
                if(table::$export === false){
                    $cells[$pg["key"]."deactivate"] = sprintf($deactivate, $cells[$pg["key"]]);
                    $cells[$pg["key"]."reactivate"] = sprintf($reactivate, $cells[$pg["key"]]);

                    $name = htmlspecialchars($cells[$pg["pfx"]."Name"]);
                    $cells[$pg["pfx"]."Name"] = form::linkOut($pg["details"]["url"]."?id=".$cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"]."Name"]));
                }
            }
        table::load();
    }

    /**Show an model item summary page
     * @param type $dt - controller::$data
     * @param type $pg - controller::$pg 
     * @param type $lbls - model::$fldLbl */
    final static function Summary($dt, $pg, $lbls){
        msg::get();
        $flds4panel = array();
        $flds4panel[$lbls[($c = $pg["pfx"]."Name")]] = $dt[$c];
        self::DataPanel($flds4panel, $pg["item"]." summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data <- controller::$data
     * @param type $lbls <- model::$fldLbl 
     * @param type $lists <- lists generated in the model */
    final static function Details($data, $pg, $lbls, $list){
        $args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div",array("class"=>"form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
            form::$ofsetIndentsCount = 2;
            form::openTag("form",array("method"=>"post","validate"=>true, "class"=>"form-horizontal"));
            form::inpField(
                $lbls[($f = $pg["pfx"]."Name")].":",
                $f,
                $data[$f],
                $args
            );

            form::inpSaveCancel(SkillController::getBackUrl()["url"] , self::$saveCancelArgsBase);
            form::closeTag("form");
            form::codeStyle(true, 1, true);
        form::closeTag("div",array("class"=>"form-panel"));
    }
}
