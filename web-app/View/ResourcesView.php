<?php

/** The CompanyOffice view */
class ResourcesView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], "", "", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "Title")], $c);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "Description")], $c);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "CategoryNames")], $c);
        table::$cols[] = array($lbls[($c = $pg["pfx"] . "Important")], $c);
        table::$cols[] = array("", $pg["key"] . "delete", array("class" => "link", "sort" => false));

        table::execute($query);
        list($delete) = self::AllLnksCode($pg, array("delete"));

        foreach (table::$data as $r => &$cells) {
            $cells[$pg["pfx"] . "Title"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Title"]));
            if ($cells[$pg["pfx"] . "Important"] === "Yes") {
                $cells[$pg["pfx"] . "Title"] = '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <strong>' . $cells[$pg["pfx"] . "Title"] . '</strong>';
            }
            $cells[$pg["pfx"] . "Description"] = nl2br(htmlspecialchars($cells[$pg["pfx"] . "Description"]));
            $cells[$pg["pfx"] . "CategoryNames"] = htmlspecialchars($cells[$pg["pfx"] . "CategoryNames"]);
            $cells[$pg["key"] . "delete"] = sprintf($delete, $cells[$pg["key"]]);
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
        $flds4panel[$lbls[($c = $pg["pfx"] . "Title")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "Description")]] = $dt[$c];
        $flds4panel[$lbls[($c = $pg["pfx"] . "Important")]] = $dt[$c];
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

		  $f = $pg["pfx"] . "Title";
		  if (!isset($data[$f])) $data[$f]='';
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Title")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );
		
		
		  $f = $pg["pfx"] . "Description";
		  if (!isset($data[$f])) $data[$f]='';
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Description")] . ":",
            $f,
            $data[$f],
            $args + ["type" => "textarea", "rows" => "5"]
        );

		 $f = $pg["pfx"] . "Categories";
		 if (!isset($data[$f])) $data[$f]='';
        form::inpCheckList(
            $lbls[($f = $pg["pfx"] . "Categories")] . ":<span title='Required field'>*</span>",
            $f,
            RCategoriesModel::AllActiveKeyName(),
            explode(",", $data[$f]),
            $args + array("check-lbl-class" => "check-lbl small")
        );

        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Important")] . ":",
            $f,
            $list["active"],
            isset($data[$f]) ? $data[$f] : "No",
            (array("skip-empty" => true, "hint" => "Important resource (show on top of others)") + $args)
        );



        $args2 = $args;
        $args2["class"] .= " auto-height ";


        if (intval(ResourcesController::$id) > 0) {
            $files = '<div>
            <div id="filesList">' . self::renderFilesList($data["files"]) . '</div>
        <hr/>
        <p><a href="" id="addResourceFileCtrl"><i class="fa fa-upload"></i> Add file </a></p>
        </div>
        ';

            form::inpInfo(
                "Files:",
                $files,
                $args2
            );



            $links = '<div>
        <div id="linksList">' . self::renderLinksList($data["links"]) . '</div>
        <hr/>
        <p><a href="" id="addResourceLinkCtrl"><i class="fa fa-link"></i> Add link </a></p>
        </div>';

            form::inpInfo(
                "Links:",
                $links,
                $args2
            );

        }

        // form::inpSaveCancel(ResourcesController::getBackUrl()["url"], self::$saveCancelArgsBase);

		 if (!isset($data["resID"])) $data["resID"]=0;
        $btnsHTML = '
            <div class="fld form-group">
                <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2">
                    <button type="submit" name="save-btn" value="Save" class="btn btn-primary">Save</button> 
                    <button type="button" onclick="location.assign(\'resources\')" class="btn btn-default">Cancel</button>'.
                    ((intval($data["resID"]) > 0) ? '<button type="button" onclick="if(confirm(\'Do you really want to delete this resource and connected data?\')) { location.assign(\''.ADMIN.'resource-details?id='.$data["resID"].'&cmd=trash\'); } " class="btn btn-danger right">Delete Resource</button>' : '')                    
                .'</div>
            </div>';
        
        form::html($btnsHTML);



        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div");

        form::html('<div class="modal fade" id="addResourceFileModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title">Add new file</h4>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto; ">

                <div class="form-panel">
                    
                        <div class="fld form-group " style="display: block; overflow: hidden;">
                            <label class="control-label col-sm-4 col-md-3">Select file:<span title="Required field">*</span></label>
                            <div class="col-sm-8 col-md-9">
                                <input class="form-control" type="file" id="newResourceFile">
                                <div class="input-hint">Please upload only .pdf file with size up to 20MB</div>
                                <span class="err" id="addResourceFileErr"></span>
                            </div>
                        </div>
                    
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="addResourceFileApplyBtn" data-url="' . ADMIN . 'resource-details?id=' . $data["resID"] . '&cmd=addfile">Add file</button> 
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
          </div>
        </div>
      </div>
      </div>');

        form::html('<div class="modal fade" id="addResourceLinkModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Add new link</h4>
          </div>
          <div class="modal-body" style="max-height: 400px; overflow-y: auto; ">

          <div class="form-panel">
              
                  <div class="fld form-group " style="display: block; overflow: hidden;">
                      <label class="control-label col-sm-3 col-md-2">URL:<span title="Required field">*</span></label>
                      <div class="col-sm-8 col-md-9">
                          <input class="form-control" type="text" id="newResourceLink" value="http://" placeholder="http://">
                          <div class="input-hint">Examples: http://url.com or https://url.com</div>
                          
                      </div>
                      
                      <label class="control-label col-sm-3 col-md-2">Title:<span title="Required field">*</span></label>
                      <div class="col-sm-8 col-md-9">
                          <input class="form-control" type="text" id="newResourceLinkText" value="" placeholder="">
                          <div class="input-hint">Enter above what user to see as link text</div>
                          <span class="err" id="addResourceLinkErr"></span>
                      </div>
                  </div>
              
          </div>

          <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="addResourceLinkApplyBtn" data-url="' . ADMIN . 'resource-details?id=' . $data["resID"] . '&cmd=addlink">Add link</button> 
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    </div>');

    }

    public static function renderFilesList($files = [])
    {
        if (empty($files)) {
            $html = 'No files';
        } else {
            $filesHTML = [];
            foreach ($files as $file) {
                $remUrl = ADMIN . ResourcesController::$pg["details"]["url"] . "?id=" . ResourcesController::$id . "&riID={$file['riID']}&cmd=removeFile";
                $remUrl = "javascript: removeResourceFile('" . $remUrl . "')";
                $resUrl = "/resource/{$file['riID']}/{$file['riName']}";
                $filesHTML[] = '<a href="' . $resUrl . '"><i class="fa fa-download"></i> ' . $file["riName"] . '</a> | <a href="' . $remUrl . '"><i class="fa fa-trash"></i> Remove</a>';
            }
            $html = implode("<br/>", $filesHTML);
        }

        return $html;
    }

    public static function renderLinksList($links = [])
    {
        if (empty($links)) {
            $html = 'No links';
        } else {
            $linksHTML = [];
            foreach ($links as $link) {
                $remUrl = ADMIN . ResourcesController::$pg["details"]["url"] . "?id=" . ResourcesController::$id . "&riID={$link['riID']}&cmd=removeLink";
                $remUrl = "javascript: removeResourceLink('" . $remUrl . "')";
                $linkName = substr($link["riName"], 0, 45);
                if ($linkName !== $link["riName"]) {
                    $linkName .= "...";
                }
                if (isset($link["riText"]) && $link["riText"]!='' ) $linkName = $link["riText"];
                $linksHTML[] = '<a title="' . $link["riName"] . '" href="' . $link["riName"] . '" target="_blank">' . $linkName . '</a> | <a href="' . $remUrl . '"><i class="fa fa-trash"></i> Remove</a>';
            }
            $html = implode("<br/>", $linksHTML);
        }

        return $html;
    }

    public static function MoveEmployees($data)
    {
        $args = array("fld-class" => "form-group", "lbl-class" => "control-label col-sm-4 col-md-3", "wrap-class" => "col-sm-8 col-md-7 col-lg-5", "class" => "form-control");
        $saveCancelArgsBase = array("fld-class" => "form-group", "wrap-class" => "col-sm-offset-4 col-md-offset-3 col-sm-12 col-md-9", "class" => "btn btn-primary", "class-cancel" => "btn btn-default");

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-12"));
        form::html('<div><p><strong>The office cannot be deleted because there are employees in it.</strong><br/></p></div>');
        form::closeDiv();
        form::closeDiv();
        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-12"));
        form::html('<div><p>If you want to move employees individually into different offices, go to Employees page (use the filter "Company Office" on top right).</p></div>');
        form::html('<div><p><a href="' . ADMIN . 'employees">Click here to go to Employees page >></a></p><br/><br/></div>');
        form::closeDiv();
        form::closeDiv();

        form::inpSelect(
            "Move all employees to office:",
            "moveToCOID",
            $data["offices"],
            null,
            (array("skip-empty" => true) + $args)
        );

        form::inpSaveCancel(CompanyOfficeController::getBackUrl()["url"], $saveCancelArgsBase + array("onclick-save" => "return confirm('Please, confirm movement!')"), "Move");

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }

    public static function RemoveAfterMove($oldOffice, $newOffice, $removeUrl)
    {
        $html = '<div><p>The employees have been moved from office <strong>' . $oldOffice["coName"] . '</strong> to office <strong>' . $newOffice["coName"] . '</strong>.</p></div>
                <div><p>You can now delete office <strong>' . $oldOffice["coName"] . '</strong></p> </div>
                <div><hr/><p><a href="' . $removeUrl . '">Proceed with deletion >></a></hrp></div>';


        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));

        form::openDiv(array("class" => "row"));
        form::openDiv(array("class" => "col-sm-10 "));
        form::html($html);
        form::closeDiv();
        form::closeDiv();
        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }


    // -----------------------------

    public static function Resources($data)
    {
        form::openDiv(["class" => "content bg-white"]);
        form::openDiv(["class" => "row"]);
        form::tag("<strong>" . htmlspecialchars($data["category"]["rcName"]) . ":</strong>", "h4", ["style" => "margin-top: 20px"]);
        form::html("<hr/>");
        form::closeDiv(["class" => "row"]);

        form::openDiv(["class" => "accordion"]);

        if (empty($data)) {
            form::html("<div style='margin-top: 10px;'><strong>No resources</strong></div>");
        } else {
            foreach ($data as $key => $item) {
                if ($key === "category") {
                    continue;
                }

                form::openDiv(["class" => "card"]);


                form::openDiv(["class" => "card-header " . ($item['resImportant'] === 'Yes' ? "card-header-dark" : "")]);

                if ($item['resImportant'] === 'Yes') {
                    $catHTML = '<div class="mb-0" style="margin: 6px; padding: 4px; font-weight: bold;"><i class="fa fa-exclamation-triangle"></i>&nbsp;&nbsp;' . htmlspecialchars($item["resTitle"]) . '</div>';
                } else {
                    $catHTML = '<div class="mb-0" style="margin: 6px; padding: 4px; font-weight: normal;"><i class="fa fa-list"></i>&nbsp;&nbsp;' . htmlspecialchars($item["resTitle"]) . '</div>';
                }
                form::html($catHTML);
                form::closeDiv(["class" => "card-header"]);



                form::openDiv(["class" => "card-body"]);

                if (!empty($item["resDescription"])) {
                    $description = $item["resDescription"] . '<hr/>';
                    form::tag($description, "div");
                }

                form::html(self::ResourceItems($item["resID"]));
                form::closeDiv(["class" => "card-body"]);

                form::closeDiv(["class" => "card"]);
            }
        }


        form::closeDiv(["class" => "accordion"]);
        form::closeDiv(["class" => "content bg-white"]);
    }


    public static function ResourceItems($resID)
    {
        $resHTML = "";
        if ($resID > 0) {
            $files = ResourcesModel::getResourceItems($resID, 'File');
            $links = ResourcesModel::getResourceItems($resID, 'Link');

            if (!empty($files)) {
                $resHTML .= "<h4><strong>Files:</strong></h4>";
                foreach ($files as $file) {
                    $file["riName"] = htmlspecialchars($file["riName"]);
                    $resHTML .= '<p><a href="' . WP . 'resource/' . $file['riID'] . '/' . $file["riName"] . '" class="text-blue">' . $file["riName"] . '</a></p>';
                }
            }

            if (!empty($links)) {
                if (!empty($resHTML)) {
                    $resHTML = $resHTML . "<hr/>";
                }
                $resHTML .= "<h4><strong>Links:</strong></h4>";
                
                foreach ($links as $link) {
                    //echo '<pre>';print_r($link);exit;
                    if(!isset($link["riText"]) || $link["riText"]=='') $link["riText"] = $link["riName"];
                    $resHTML .= '<p><a href="' . $link["riName"] . '" class="text-blue" title="' . $link["riName"] . '" target="_blank">' . $link["riText"] . '</a></p>';
                }
            }
        }

        if (empty($resHTML)) {
            // $resHTML = "<p>This resource is empty!</p>";
            $resHTML = "<p>&nbsp;</p>";
        }

        return $resHTML;
    }
}
