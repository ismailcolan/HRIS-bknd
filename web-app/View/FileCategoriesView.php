<?php
/** The CompanyOffice view */
class FileCategoriesView extends AppView {

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"], "fcName", "ASC", self::ADMIN_LISTS_ROWS_LIMIT);

        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Important")], $c);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "ResCount")], $c);
		//table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Added")], $c);
		//table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Updated")], $c);
        table::$cols[] = Array("", $pg["pfx"] . "active", array("class" => "link", "sort" => false));

        table::execute($query);
        list($delete) = self::AllLnksCode($pg, array("delete"));

        foreach (table::$data as $r => &$cells) {  

			  if ($cells[$pg["pfx"] . "Updated"] == '0000-00-00 00:00:00') 
				  $updated='';
			  else $updated = val::convDate(MYSQL_DATE,LOCAL_PHP_DATETIME_LONG,$cells[$pg["pfx"] . "Updated"]);
			  //date(LOCAL_PHP_DATETIME_LONG,strtotime($cells[$pg["pfx"] . "Updated"]));<<<
			  
			  
            $cells[$pg["pfx"] . "Name"] = 
			form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
			

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
            msg::put("You cannot delete a category with files it. Please first delete or move files.", "error");    
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
            (array("skip-empty" => true, "hint" =>'')+$args) 
			//"Important category (show on top of others)") + $args)
        );

        // form::inpSaveCancel(RCategoriesController::getBackUrl()["url"], self::$saveCancelArgsBase, $btnLabel);
        $deleteMode = $data["fcResCount"] > 0 ? 'alert' : 'trash';
        $btnHTML = '
            <div class="fld form-group">
                <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2">
                    <button type="submit" name="save-btn" value="Save" class="btn btn-primary">Save</button> 
                    <button type="button" onclick="location.assign(\'filecategories\')" class="btn btn-default">Cancel</button>'.
                    ((intval($data["fcID"]) > 0) ? '<button type="button" onclick="if(confirm(\'Do you really want to delete this category and connected data?\')) { location.assign(\''.ADMIN.'filecategories-details?id='.$data["fcID"].'&cmd='.$deleteMode.'\'); } " class="btn btn-danger right">Delete Category</button>' : '')
                .'</div>
            </div>
        ';
        form::html($btnHTML);

        $args2 = $args;
        $args2["class"] .= " auto-height ";

        if($data["fcResCount"] > 0) {
            $btnLabel = "Save";
            $resHTML = [];
			
            $resList = FileCategoriesModel::getFilesInCategory();
            if(is_array($resList)) {
                foreach($resList as $res) {
                    $resHTML[] = 
                        '<p style="margin-top:0px"><a href="'.ADMIN.'filecategories-getfile?id='.$res["fcfbuID"].'"><strong>'.htmlspecialchars($res["fcfbuFileName"]).'</strong></a></p>';
                } 
            }
            $modal = '<div class="modal fade" id="resourcesModal" style="z-index:999999" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Files in category</h4>
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


                $allcats = FileCategoriesModel::getAllCatsExceptCurrent();                
                $options = "";
				   
                foreach($allcats as $cat) {

                    //if($cat['fcID'] != $data["fcID"]) {
                        $options .= '<option value="'.$cat['fcID'].'">'.$cat['fcName'].'</option>';
                    //}
                }


                $resources = '<div><a href="#" data-toggle="modal" data-target="#resourcesModal">View Files in Category >></a>
                <hr/>
				<input type="hidden" name="cat_id" value="'.$_GET['id'].'">
                <p>Move all files to category:<br/>
                <select style="width:100%" name="moveToRCID"><option>Please, select</option>'.$options.'</select><br/></p>
                <p>
                <button type="submit" name="files-move-btn" value="Move" class="btn btn-primary" onclick="return confirm(\'Please confirm movement...\');">Move</button>
                <button type="submit" name="fiels-delete-btn" value="Delete" class="btn btn-default" onclick="return confirm(\'Are you sure?\');">Delete files from this category</button>
                </p>
                </div>';
                    
                form::inpInfo(
                    " ",
                    "<br/>",
                    []
                );
                form::inpInfo(
                    "Files in category:",
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
        form::tag("<strong>Please select category:</strong>", "h4", ["style"=>"margin-top: 20px"]);
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
                if($item["fcImportant"] === "Yes") {
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
	
	
	final public static function renderForm($title = null, $method = "post")
    {
        if (isset($title)) {
            form::codeStyle(true, 1, false);
            form::openTag("div", array("class" => "form-panel"));
            form::$ofsetIndentsCount = 2;

            if (!empty($title)) {
                form::html('<h3>' . $title . '</h3>');
            }
			
            form::openTag("form", array("method" => $method, "action" => ADMIN . "employee-details?tab=ass_files&id=".$_GET['id']."&action=upload_file", "enctype" => "multipart/form-data", "validate" => true, "class" => "form-horizontal"));
        } else {
            form::closeTag("form");
            form::closeDiv();
        }
    }
	
	
	public static function RenderAssignedFiles($data)
   { 
       $args =array ( 
		   'fld-class' => 'form-group',
		   'lbl-class' => 'control-label col-sm-3 col-md-2',
		   'wrap-class' => 'col-sm-8 col-md-7 col-lg-5',
		   'class' => 'form-control width-70'
	   );
	   
	   
		form::openDiv(array("id" => "files_content", "class" => "container-fluid"));
		
		if (empty($data)) echo '<b>No assigned files</b>';
		else{
			echo "
			<a style=\"float:right\" class=\"btn btn-primary \" href=\"#uplad_file\">Upload New File</a>
			<h4>Please select category </h4>";
		foreach ($data as $name=>$rows){
		
			echo '<button class="accordion" title="Category.Click to see files in that category"><b><i class="fa fa-tags"></i>&nbsp;'.$name.'</b></button>';
			echo '<div class="accpanel"><br>';
				foreach ($rows as $r){
					if ($r['state'] == 'visible') $checked='checked';
					else $checked='';
					echo '
					<a title="Click to download file"  href="file_request?fid='.	$r['id'].'"><i class="fa fa-download"></i>&nbsp'.$r['name'].'</a>'	
					.'<a style="float:right;margin-right:30px" 
					onclick ="return confirm(\'Are you sure to delete '.$r['name'].' file?\')"
					title="Click to permanently delete file for this employee"  href="employee-details?id='.$_GET['id'].'&tab=ass_files&action=delete&fid='.$r['id'].'"><i class="fa fa-trash"></i> Remove</a>
					<span style="float:right; padding-right:20px;">
					<input '.$checked.' id="cb'.$r['id'].'"  type="checkbox" onchange="set_visible(this,'.$r['id'].')"/>
					<label for="cb'.$r['id'].'" style="font-weight:normal">visible for employee</label>
					</span>
					
					<br><br>
					';
				}
			echo '<br></div>';
		} 
		}
		echo "<hr>";
	
		form::codeStyle(true, 1, false);
		 //form::html('<div class="row">');
		 
		 form::openTag("div", array("class" => "form-panel","id"=>"uplad_file"//, "style"=>"max-width:700px; margin: 20px auto 0px;"
		 ));
        form::$ofsetIndentsCount = 2;
		
       // form::html('<div class="col-sm-12 col-md-12 col-lg-12">');
	


        self::renderForm("Upload New File");
			  $catsql = 'SELECT * FROM  fcategories  WHERE  fcActive ="Yes" ORDER BY fcName ASC';
			  $cats=db::PrepareLoad($catsql);
			  if ($cats==false) $cats=array();
			  $f=array();
			  foreach($cats as $k=>$c) {
				  $f[$c['fcID']]=$c['fcName'];
			  }  
			 if (isset($_POST['category_id'])) $cat=$_POST['category_id']; else $cat=0;
			 
			 echo '<input type="hidden" name="visible" value="invisible">';
			 
			 form::html('
        <div class="fld form-group">
            <label class="control-label col-sm-3 col-md-2">Visible for employee</label>
            <div class="col-sm-8 col-md-7 col-lg-5" style="padding-top:8px">
            <input type="checkbox" name="visible" value="visible" > 
			  </div>
        </div>');
		
		
			 form::inpSelect(
			  "Category:".ASTERISK,
			  'category_id',
			  $f,
			  $cat,
			  $args+ ["id" => "file"]
			  );
			  
				
			  if (isset($_POST['filename'])) $fname=$_POST['filename']; else $fname='';
            form::inpField(
                "Description:".ASTERISK,
                "filename",
                $fname,
                $args + ['autocomplete'=>'off']
            );
			
			form::inpField(
            "File:".ASTERISK,
            "myfile",
			   array(),
		      $args+["id"=>"myfile","type" => "file",'hint'=>'Please upload only <b>PDF,DOC,DOCX,XLS,XLSX,JPG</b> files']
        );
			
		  


          form::inpSaveCancel(
				null, 
				array("fld-class" => "form-group", "wrap-class" => "col-sm-9 col-sm-offset-3", 
				"class" => "btn btn-primary"), 
				"Save");

           
        self::renderForm();
        form::closeDiv(["class" => "col-sm-12 col-md-6"]);
        form::closeDiv(["class" => "row"]);

	}

    
}