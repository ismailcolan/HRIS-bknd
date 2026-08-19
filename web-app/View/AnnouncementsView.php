<?php

/** The CompanyOffice view */
class AnnouncementsView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        if (!isset(table::$export)) {
            table::$export = false;
        }
        table::create($pg["tbl"], $pg["pfx"] . "EndDate", "desc", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "Headline")], $c);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "Description")], $c);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "EndDate")], $c);
        table::$cols[] = array("", $pg["pfx"] . "Active", array("class" => "link", "sort" => false));
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "delete"));

        table::execute($query);
        list($view, $edit, $delete) = self::AllLnksCode($pg, array("view", "edit", "delete"));

        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells[$pg["pfx"] . "Headline"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Headline"]));
            }
            $cells[$pg["pfx"] . "Description"] = strip_tags($cells[$pg["pfx"] . "Description"]);
            $shortDescr = substr(htmlspecialchars($cells[$pg["pfx"] . "Description"]), 0, 150);
            if ($cells[$pg["pfx"] . "Description"] != $shortDescr) {
                $cells[$pg["pfx"] . "Description"] = $shortDescr . '...';
            }
            $cells[$pg["pfx"] . "EndDate"] = date(USER_DATE, strtotime($cells[$pg["pfx"] . "EndDate"]));

            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                $cells[$pg["pfx"] . "Active"] = sprintf($delete, $cells[$pg["key"]]);
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
        $flds4panel[$lbls[($c = $pg["pfx"] . "Headline")]] = $dt[$c];
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
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/ ));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Headline")] . ":",
            $f,
            $data[$f],
            $args
        );

        form::inpField(
            $lbls[($f = $pg["pfx"] . "Description")] . ":",
            $f,
            $data[$f],
            $args + ["type" => "textarea", "rows" => "5", "id"=>"description"]
        );

        form::html('<script>CKEDITOR.replace("description");</script>');

		  if (isset($_POST[$pg["pfx"] . "EndDate"])) $data[$pg["pfx"] . "EndDate"]= $_POST[$pg["pfx"] . "EndDate"];
        form::inpField(
            $lbls[($f = $pg["pfx"] . "EndDate")] . ":",
            $f,
            $data[$f],
            $args + array("hint" => "example:".USER_DATE_EXAMPLE)
        );

        // $args = self::$saveCancelArgsBase;
        // form::inpSaveCancel(AnnouncementsController::getBackUrl()["url"], $args);

        $btnsHTML = '
            <div class="fld form-group">
                <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2">
                    <button type="submit" name="save-btn" value="Save" class="btn btn-primary">Save</button> 
                    <button type="button" onclick="location.assign(\'announcements\')" class="btn btn-default">Cancel</button>'.
                    ((intval($data["anID"]) > 0) ? '<button type="button" onclick="if(confirm(\'Do you really want to delete this announcement and connected data?\')) { location.assign(\''.ADMIN.'announcement-details?id='.$data["anID"].'&cmd=trash\'); } " class="btn btn-danger right">Delete Announcement</button>' : '')                    
                .'</div>
            </div>';
        
        form::html($btnsHTML);



        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function Announcements($data)
    {
        form::openDiv(["class" => "content bg-white"]);
        form::openDiv(["class" => "row"]);
        form::openDiv(["class" => "col-sm-12"]);
        // form::tag("Announcements", "h3");
        form::openDiv(["class" => "row"]);

        form::openDiv(["class" => "accordion", "id" => "accordion"]);

        if (empty($data)) {
            form::html("<div style='margin-top: 10px;'><strong>No announcements</strong></div>");
        } else {
            foreach ($data as $item) {
                form::openDiv(["class" => "card"]);

                form::openDiv(["class" => "card-header", "id" => "heading" . $item[AnnouncementsModel::$dt["key"]]]);
                form::openTag("h5", ["class" => "mb-0"]);

                form::html('<a class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse' . $item[AnnouncementsModel::$dt["key"]] . '" aria-expanded="true" aria-controls="collapseOne">
                <i class="fa fa-bullhorn"></i>&nbsp;&nbsp;' . htmlspecialchars($item[AnnouncementsModel::$dt["pfx"] . "Headline"]) . '</a>');
                form::closeTag("h5", ["class" => "mb-0"]);
                form::closeDiv(["class" => "card-header", "id" => "heading" . $item[AnnouncementsModel::$dt["key"]]]);

                form::html('
            <div id="collapse' . $item[AnnouncementsModel::$dt["key"]] . '" class="collapse in" aria-labelledby="heading' . $item[AnnouncementsModel::$dt["key"]] . '" data-parent="#accordion">
                <div class="card-body">' . ($item[AnnouncementsModel::$dt["pfx"] . "Description"]) . '</div>
            </div>');

                form::closeDiv(["class" => "card"]);
            }
        }

        form::closeDiv(["class" => "accordion", "id" => "accordion"]);

        form::closeDiv(["class" => "row"]);
        form::closeDiv(["class" => "col-sm-12"]);
        form::closeDiv(["class" => "row"]);
        form::closeDiv(["class" => "content bg-white"]);

        form::html('
            <script>
                var url = location.href;
                var idx = url.indexOf("#");
                if(idx > -1) {                    
                    var hash = url.substring(url.indexOf("#")+1);                
                    jQuery("#"+hash).collapse("show");
                }
            </script>
        ');
    }
}

