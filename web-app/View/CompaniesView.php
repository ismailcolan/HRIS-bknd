<?php

/** The CompanyOffice view */
class CompaniesView extends AppView
{

    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
        table::create($pg["tbl"],'comName' /*$pg["key"]/*"coAdded"*/, "ASC", self::ADMIN_LISTS_ROWS_LIMIT);
        table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
		table::$cols[] = Array($lbls[($c = $pg["pfx"] . "ShortName")], $c);
		table::$cols[] = Array($lbls[($c = $pg["pfx"] . "CompanyIDAnual")], $c);
		table::$cols[] = Array($lbls[($c = $pg["pfx"] . "CompanyIDHourly")], $c);
//            table::$cols[] = Array("", $pg["key"]."edit", array("class"=>"link","sort"=>false));
        table::$cols[] = Array("", $pg["pfx"] . "active", array("class" => "link", "sort" => false));

        table::execute($query);
        if ($rq === "list") {
            list($view, $edit, $deactivate) = self::AllLnksCode($pg, array("view", "edit", "deactivate"));
        } else {
            list($view, $edit, $reactivate) = self::AllLnksCode($pg, array("view", "edit", "reactivate"));
        }
		//cf::pr(table::$data);
        foreach (table::$data as $r => &$cells) {
            if (table::$export === false) {
                $cells["lnks"] = sprintf($view, $cells[$pg["key"]]);
                if ($rq === "list") {
                    $cells[$pg["pfx"] . "active"] = sprintf($deactivate, $cells[$pg["key"] . ""]);
                } else {
                    $cells[$pg["pfx"] . "active"] = sprintf($reactivate, $cells[$pg["key"] . ""]);
                }

                //$cells[$pg["key"] . ""] = sprintf($edit, $cells[$pg["key"] . ""]);
                //$cells[$pg["pfx"] . "Name"] = form::linkOut($pg["details"]["url"] . "?id=" . $cells[$pg["key"]], htmlspecialchars($cells[$pg["pfx"] . "Name"]));
				$cells[$pg["pfx"] . "Name"]='<a href="'.$pg["details"]["url"] . "?id=" . $cells[$pg["key"]].'">'.htmlspecialchars($cells[$pg["pfx"] . "Name"]).'</a>';
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
		
		echo '<div>
			<style>
			.admin-applicant-photoMY{width:250px}
			.admin-applicant-photoMY:hover {width:auto}
			</style>
			';

        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"/*, "style"=>"max-width:700px; margin: 20px auto 0px;"*/));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("enctype" => "multipart/form-data","method" => "post", "validate" => true, "class" => "form-horizontal"));
        
		form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );
		form::inpField(
            $lbls[($f = $pg["pfx"] . "ShortName")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );
		
		form::html('<div class="fld form-group" >
                    <label class="control-label col-sm-4 col-md-3" ></label>
						<div class="col-sm-4 col-md-3 col-lg-5">
							<div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
							Important: Editing a <b>Company Name</b> or <b>Company Short Name</b>  will change the way employees login in.
							</div>
						</div>
					</div>');
					
        form::inpSelect(
            $lbls[($f = $pg["pfx"] . "Active")] . ":",
            $f,
            $list["active"],
            isset($data[$f]) ? $data[$f] : "Yes",
            (array("skip-empty" => true) + $args)
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "CompanyIDAnual")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "CompanyIDHourly")] . ":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );


        if (CompaniesController::$id) {
            
            $args = self::$saveCancelArgsBase + array("id" => "saveCompaniesBtn");
        } else {
            $args = self::$saveCancelArgsBase;
        }
		
		/******************************/
		
       		
		//form::openDiv();
       
		if (!isset($_GET['id'])) $_GET['id']=0;
		$file='add/images/companyLogo'.$_SESSION['customerID'].'_'.$_GET['id'].'.png';	
		if (file_exists(FS_ROOT . $file)) {
			
			form::openDiv(array("class" => "fld form-group"));	
			form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5","style"=>"width:100%;"]);

			echo '
			<style>
			.admin-applicant-photoMY{width:250px}
			.admin-applicant-photoMY:hover {max-width:800px;width:auto;}
			</style>
			';
			
            $photo = "<img  src='" . WP .''. $file .'?r='.rand(0,9999). "'  class='admin-applicant-photoMY' />
					 <br><small>Move over the picture to zoom in biger size</small>";
           
			
			
			
			$args1 =
			[
				"fld-class" => "form-group",
				"lbl-class" => "control-label col-sm-4 col-md-3",
				"wrap-class" => "col-sm-4 col-md-3 col-lg-3",
				"class" => "form-control"
			];
			form::inpInfo("Company logo:", $photo, 
			$args1+['style'=>'border:0;box-shadow:none;padding:0;margin:0;color:#000;background:#FFF;height:auto']);
		
			$bio = "<label><input type='radio' value='download' name='photoCmd' checked> <a href='" . WP . "{$file}' 
				id='downloadPhoto' target='_blank'><i class='fa fa-download'></i>download</a></label> | 
				<label><input type='radio' value='remove' name='photoCmd'> <i class='fa fa-trash-o'></i>delete</input></label>";
			
			
			form::inpInfo(
				" ",
				$bio,
				$args1+['style'=>'clear:both;border:1px solid lightgray;color:#000;background:#FFF']
			);
			
			echo "</div>";
			echo "</div>";
			
			echo "<br>";
		}
			echo '
		<div class="fld form-group">
		<label class="control-label col-sm-4 col-md-3" style="position:relative;top:6px">New logo:</label>
		<div class="col-sm-4 col-md-3 col-lg-3" style="padding:0;margin:0;box-shadow:none">
			<div class="form-control" style="border:0;background:#FFF;height:auto;box-shadow:none" type="info">
			<input type="file" id="imgInp" name="cLogo" style="padding:5px;border:1px solid lightgray;width:310px">
			<small>Please use files of types <b>.jpg, .png, .jpeg</b> only.</small>
			</div>
		</div>
		</div>
		
		
		<div class="fld form-group">
		<label class="control-label col-sm-4 col-md-3" style="position:relative;top:6px"></label>
		<div class="col-sm-4 col-md-3 col-lg-3" style="padding:0;margin:0;box-shadow:none;display:none;padding-left:10px" id="screen">
			<h5>Preview</h5>
			<img id="screenImg" class="admin-applicant-photoMY" src="#" />
			<br><small>Move over the picture to zoom in biger size</small>
		</div>
		</div>
		
		
		
		
		<script>
		function readURL(input) {

		  if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function(e) {
			  $("#screenImg").attr("src", e.target.result);
			  $("#screen").fadeIn();
			}
			reader.readAsDataURL(input.files[0]);
		  }
		}
		
		function hasExtension(inputID, exts) {
			var fileName = document.getElementById(inputID).value;
			return (new RegExp("(" + exts.join("|").replace(/\./g, "\\.") + ")$")).test(fileName);
		}

		$("#imgInp").change(function() {
		  if (!hasExtension("imgInp",[".jpg", ".png",".jpeg",".JPG", ".PNG",".JPEG"])) {
			$("#imgInp").val("");
			myalert("Warning","Please use files of types <b>.jpg, .png, .jpeg</b> only.");
			return false;
		  }
		  readURL(this);
		});
		</script>
		
		';
		
		
		
		
		
		
		
		/****************************/

        form::inpSaveCancel(PositionController::getBackUrl()["url"], $args);

        form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
    }
}
