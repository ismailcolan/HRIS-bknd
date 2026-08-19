<?php
/** The CompanyOffice view */
class CompanyLogoView extends AppView {

    /** Shows a form to edit admin mails
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl 
     * @param type $lists - lists generated in the model */
    final static function Edit(){
		
		
		$args=array(
			'fld-class' => 'form-group',
			'lbl-class' => 'control-label col-sm-4 col-md-3',
			'wrap-class' => 'col-sm-4 col-md-3 col-lg-3',
			'class' => 'form-control'
		);
		
		form::openTag("form", array("enctype" => "multipart/form-data","method" => "post", 
					"validate" => true, "class" => "form-horizontal","id"=>"customer-edit-form"));
		form::openDiv();
        form::openDiv(array("class" => "fld form-group"));
        
        form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5","style"=>"width:100%"]);
		echo '<div>';
		
		echo '<style>.admin-applicant-photo {clear:both}</style>';
		
		$file='add/images/customerLogo'.$_SESSION['customerID'].'.png';
		//cf::pr($args);
		if (file_exists(FS_ROOT . $file)) {
			echo '<div>
			<style>
			.admin-applicant-photoMY{width:250px}
			.admin-applicant-photoMY:hover {width:auto}
			</style>
			';
            $photo = "<img  src='" . WP .''. $file .'?r='.rand(0,9999). "'  class='admin-applicant-photoMY' />
					 <br><small>Move over the picture to zoom in full size</small>";
            echo '<div style="clear:both;">';
			
			
			form::inpInfo("Current logo:", $photo, $args+['style'=>'border:0;box-shadow:none;padding:0;margin:0;background:#FFF;height:auto']);
			//echo '</div>';
			echo '</div>';
            
			$bio = "<label><input type='radio' value='download' name='photoCmd' checked> <a href='" . WP . "{$file}' 
				id='downloadPhoto' target='_blank'><i class='fa fa-download'></i>download</a></label> | 
				<label><input type='radio' value='remove' name='photoCmd'> <i class='fa fa-trash-o'></i>delete</input></label>";
			echo "<div>";
			form::inpInfo(
				" ",
				$bio,
				$args+['style'=>'clear:both']
			);
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
			<br><small>Move over the picture to zoom in fill size</small>
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
		
		
		
		
		
		echo '<input type="hidden" value="save" name="action">';
		$args=array(
		    'fld-class' => 'form-group',
			'wrap-class' => 'col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2',
			'class' => 'btn btn-primary',
			'class-cancel' => 'btn btn-default'
		);
		
		form::inpSaveCancel('', $args);
		form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
		
		
    }


}
