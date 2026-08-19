<?php

/** The RCategories view */
class RCategoriesView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], "", "", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Important")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "ResCount")], $c);
        table::$cols[] = Array("", $pg["pfx"] . "active", array("class" => "link", "sort" => false));
        
        table::execute($query);
        list($delete) = self::AllLnksCode($pg, array("delete"));

        foreach (table::$data as $r => &$cells) {            
            $cells[$pg["pfx"] . "Name"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
            if($cells[$pg["pfx"] . "Important"] === "Yes") {
                $cells[$pg["pfx"] . "Name"] = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>'.$cells[$pg["pfx"] . "Name"].'</strong>';
            } 
            if($cells[$pg["pfx"] . "ResCount"] == 0) {
                $cells[$pg["pfx"] . "active"] = sprintf($delete, $cells[$pg["key"]]);
            } else {
                $cells[$pg["pfx"] . "active"] = form::linkOut($pg["details"]["url"] . "?cmd=alert&id=" . $cells[$pg["key"]], '<span class="fa fa-trash-o"></span>', ["class" => "tbl-ico"]);              
            }
        }
        // <a href="rcategory-details?id=3&amp;cmd=trash" onclick="return confirm('Do you really want to delete this category and connected data?');" class="tbl-ico" title="Delete category"></a>
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
        self::DataPanel($flds4panel, $pg["item"] . " summary", "panel-default");
    }

    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl
     * @param type $lists - lists generated in the model
     */
    final static function Details($data, $pg, $lbls, $list)
    {       
        $btnLabel = "Save";
        if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "alert")  {
            msg::put("You cannot delete a category with resources in it. Please first delete or move resources.", "error");    
            $btnLabel = "Delete";        
        }

        $args = self::$fldArgsBase;
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );

        
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Important")] . ":",
            $f,
            $list["active"],
            isset($data[$f]) ? $data[$f] : "No",
            (array("skip-empty" => true, "hint" => "Important category (show on top of others)") + $args)
        );

        // form::inpSaveCancel(RCategoriesController::getBackUrl()["url"], self::$saveCancelArgsBase, $btnLabel);
        $deleteMode = $data["rcResCount"] > 0 ? 'alert' : 'trash';
        $btnHTML = '
            <div class="fld form-group">
                <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2">
                    <button type="submit" name="save-btn" value="Save" class="btn btn-primary">Save</button> 
                    <button type="button" onclick="location.assign(\'rcategories\')" class="btn btn-default">Cancel</button>'.
                    ((intval($data["rcID"]) > 0) ? '<button type="button" onclick="if(confirm(\'Do you really want to delete this category and connected data?\')) { location.assign(\''.ADMIN.'rcategory-details?id='.$data["rcID"].'&cmd='.$deleteMode.'\'); } " class="btn btn-danger right">Delete Category</button>' : '')
                .'</div>
            </div>
        ';
        form::html($btnHTML);

        $args2 = $args;
        $args2["class"] .= " auto-height ";

        if($data["rcResCount"] > 0) {
            $btnLabel = "Save";
            $resHTML = [];
            $resList = ResourcesModel::data(' exists (select rclRID from res_cats_link where rclRCID='.$data["rcID"].' and rclRID=resID) and resActive="Yes" ',
            ["resImportant"=>"ASC", "resTitle"=>"ASC"]);
            if(is_array($resList)) {
                foreach($resList as $res) {
                    $resHTML[] = 
                        '<p style="margin-top:0px"><a href="'.ADMIN.'resource-details?id='.$res["resID"].'"><strong>'.htmlspecialchars($res["resTitle"]).'</strong></a></p>';
                } 
            }
            $modal = '<div class="modal fade" id="resourcesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Resources</h4>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto; ">
                  ' . implode(" ", $resHTML) . ' 
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>';
                form::html($modal);


                $allcats = RCategoriesModel::AllActiveKeyName();                
                $options = "";
                foreach($allcats as $catID => $catName) {
                    if($catID != $data["rcID"]) {
                        $options .= '<option value="'.$catID.'">'.$catName.'</option>';
                    }
                }


                $resources = '<div><a href="#" data-toggle="modal" data-target="#resourcesModal">View Resources >></a>
                <hr/>
                <p>Move all resources to category:<br/>
                <select style="width:100%" name="moveToRCID"><option>Please, select</option>'.$options.'</select><br/></p>
                <p>
                <button type="submit" name="move-btn" value="Move" class="btn btn-primary" onclick="return confirm(\'Please confirm movement...\');">Move</button>
                <button type="submit" name="delete-btn" value="Delete" class="btn btn-default" onclick="return confirm(\'Are you sure?\');">Delete resources from this category</button>
                </p>
                </div>';
                    
                form::inpInfo(
                    " ",
                    "<br/>",
                    []
                );
                form::inpInfo(
                    "Resources:",
                    $resources,
                    $args2
                );
    
            
        }


        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }


    public static function Categories($data) 
    {
        form::openDiv(["class" => "content bg-white"]);
        form::openDiv(["class" => "row"]);
        form::tag("<strong>Please choose category:</strong>", "h4", ["style"=>"margin-top: 20px"]);
        form::html("<hr/>");
        form::closeDiv(["class" => "row"]);

        if (empty($data)) {
            form::html("<div style='margin-top: 10px;'><strong>No resources</strong></div>");
        } else {
            $itemCnt = 0;
            foreach ($data as $item) {
                if($itemCnt === 0) {
                    form::openDiv(["class"=>"row"]);
                }

                form::openDiv(["class"=>"col-sm-6"]);

                $catName = htmlspecialchars($item["rcName"])." (".$item["rcResCount"].")";
                if($item["rcImportant"] === "Yes") {
                    $catName = "<strong><i class='fa fa-exclamation-triangle'></i> {$catName}</strong>";
                }
                form::link($item["rcResCount"] == "0" ? "" : WP."resources?cat=".$item["rcID"], $catName, ["class"=>"text-blue"]);

                form::closeDiv(["class"=>"col-sm-6"]);

                $itemCnt++;
                if($itemCnt === 2) {
                    form::closeDiv(["class"=>"row"]);
                    $itemCnt = 0;
                }
            }
        }
        form::closeDiv(["class" => "content bg-white"]);
    }
    
}