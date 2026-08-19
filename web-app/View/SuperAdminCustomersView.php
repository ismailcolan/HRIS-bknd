<?php
/** The CompanyOffice view */
class SuperAdminCustomersView extends AppView {
    
	public static function Edit($data){ 
		SuperAdminCustomersModel::ValidateEdit("client");
		echo '<h3>Edit customer: <span style="color:#0186a7">'.$data['cCustomerName'].'</span></h3><hr>';
		msg::get();
		$args=array(
		'fld-class' => 'form-group',
		'lbl-class' => 'control-label col-sm-4 col-md-3',
		'wrap-class' => 'col-sm-4 col-md-3 col-lg-3',
		'class' => 'form-control'
		);
	
		form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel","onsubmit"=>"return check_submit()"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("enctype" => "multipart/form-data","method" => "post", 
					"validate" => true, "class" => "form-horizontal","id"=>"customer-edit-form"));
        
		
		form::openDiv();
        form::openDiv(array("class" => "fld form-group"));
        form::tag("Active:" , "label", ["class" => "control-label  col-sm-4 col-md-3"]);

        form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);

       
		 $f = "cActive";
		  if (!isset($data[$f])) {$_ck1='checked'; $_ck2='';}
		  else {
			if ($data[$f]=='yes') {$_ck1='checked'; $_ck2='';}
			if ($data[$f]=='no') {$_ck1=''; $_ck2='checked';}
		  }

		 echo '<div class = "row" style="position:relative;top:5px">
				&nbsp;&nbsp;
				<input style="cursor:pointer" type="radio" name="'.$f.'" '.$_ck1.' value="yes" id="Yes"> 
				<label for="Yes" style="font-weight:normal;cursor:pointer">Yes</label>
				&nbsp;&nbsp;
				<input style="cursor:pointer" type="radio" name="'.$f.'" '.$_ck2.' value="no" id="No"> 
				<label for="No" style="font-weight:normal;cursor:pointer">No</label>
				</div>';
					
        if (isset(form::$error["cActive"])) {
            form::html('<span class="err">' . form::$error["cActive"][0] . '</span>');
        }

      form::closeDiv();form::closeDiv();
	  
		
		
		 $args['wrap-class'] = 'col-sm-8 col-md-7 col-lg-5';
        
		 $f = 'cCustomerName';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "Customer Name:",
            $f,
            $data[$f],
            $args+['hint'=>'<b>WARNING:</b> Changing the name will change the way employees login into site.']
        );
		
		
		$f = 'cCustomerShortName';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "Customer Short Name:",
            $f,
            $data[$f],
            $args +['hint'=>'Please enter at least 2 letters and/or digits.']
        );
		/***********
		$f = 'cAdminEmail';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "Admin email:",
            $f,
            $data[$f],
            $args
        );
		/***********/
		$f = 'cPass';
		 //if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 //if(!isset($data[$f])) $data[$f] ='';
		 $data[$f] ='';
         form::inpField(
            "Admin password:",
            $f,
            $data[$f],
            $args+ ['placeholder'=>'Password is set but will not be shown','type'=>'password','hint'=>'<b>This password is for site, not for DB.</b> Please enter at least 6 letters and/or digits to change it or <b> leave it empty to keep the old one</b>.']
        );
		/*********/
		$f = 'cDBASE';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "DB name:",
            $f,
            $data[$f],
            $args+ []
        );
		
		
		echo '<input type="hidden" value="edit" name="action">';
		$args=array(
		    'fld-class' => 'form-group',
			'wrap-class' => 'col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2',
			'class' => 'btn btn-primary',
			'class-cancel' => 'btn btn-default'
		);
		
		form::inpSaveCancel('customers', $args);
		form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
	}
	
	
	public static function Add(){
		$data = array(
			'cCustomerName'=>''
		);
		
		
		SuperAdminCustomersModel::ValidateEdit("client");
		echo '<h3>Add new customer: <span style="color:#0186a7">'.$data['cCustomerName'].'</span></h3><hr>';
		msg::get();
		$args=array(
		'fld-class' => 'form-group',
		'lbl-class' => 'control-label col-sm-4 col-md-3',
		'wrap-class' => 'col-sm-4 col-md-3 col-lg-3',
		'class' => 'form-control'
		);
	
		form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel","onsubmit"=>"return check_submit()"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("enctype" => "multipart/form-data","method" => "post", 
					"validate" => true, "class" => "form-horizontal","id"=>"customer-edit-form"));
        
		
		form::openDiv();
        form::openDiv(array("class" => "fld form-group"));
        form::tag("Active:" , "label", ["class" => "control-label  col-sm-4 col-md-3"]);

        form::openDiv(["class" => "col-sm-8 col-md-7 col-lg-5"]);

       
		 $f = "cActive";
		  if (!isset($data[$f])) {$_ck1='checked'; $_ck2='';}
		  else {
			if ($data[$f]=='yes') {$_ck1='checked'; $_ck2='';}
			if ($data[$f]=='no') {$_ck1=''; $_ck2='checked';}
		  }

		 echo '<div class = "row" style="position:relative;top:5px">
				&nbsp;&nbsp;
				<input style="cursor:pointer" type="radio" name="'.$f.'" '.$_ck1.' value="yes" id="Yes"> 
				<label for="Yes" style="font-weight:normal;cursor:pointer">Yes</label>
				&nbsp;&nbsp;
				<input style="cursor:pointer" type="radio" name="'.$f.'" '.$_ck2.' value="no" id="No"> 
				<label for="No" style="font-weight:normal;cursor:pointer">No</label>
				</div>';
					
        if (isset(form::$error["cActive"])) {
            form::html('<span class="err">' . form::$error["pSalaryTypeErr"][0] . '</span>');
        }

		form::closeDiv();form::closeDiv();
	  
	  
	   
        
		
		
		
		$args['wrap-class'] = 'col-sm-8 col-md-7 col-lg-5';
        
		$f = 'cCustomerName';
		if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		if(!isset($data[$f])) $data[$f] ='';
        form::inpField(
            "Customer Name:",
            $f,
            $data[$f],
            $args+['hint'=>'<b>WARNING:</b> Changing the name will change the way employees logs in site.']
        );
		
		
		$f = 'cCustomerShortName';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "Customer Short Name:",
            $f,
            $data[$f],
            $args + ['hint'=>'Please enter at least 2 letters and/or digits.']
        );
		
		$f = 'cAdminEmail';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "Admin email:",
            $f,
            $data[$f],
            $args
        );
		
		$f = 'cPass';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "Admin password:",
            $f,
            $data[$f],
            $args+ ['hint'=>'<b>This password is for site, not for DB.</b> Please enter at least 6 letters and/or digits.']
        );
		
		$f = 'cDBASE';
		 if(isset($_POST[$f])) $data[$f]=$_POST[$f]  ;
		 if(!isset($data[$f])) $data[$f] ='';
         form::inpField(
            "DB name (without prefix): ",
            $f,
            $data[$f],
            $args+ []
        );
		
		/***************
		$file="";
		
		if ($file != ""  && file_exists(FS_ROOT . $file) ) { 
            $photo = "<img src='" . WP .''. $file .'?r='.rand(0,9999). "' class='admin-applicant-photo' />
			<br><small>Move over the picture to zoom in</small>";
		
		
			echo '<div id="image_begin"></div>';  
			form::inpInfo("Logo:", $photo, $args+['style'=>'border:0;background:#FFF']);

            
			$bio = "<label><input type='radio' value='download' name='photoCmd' checked> <a href='" . WP . "{$file}' 
				id='downloadPhoto' target='_blank'><i class='fa fa-download'></i>download</a></label> | 
				<label><input type='radio' value='remove' name='photoCmd'> <i class='fa fa-trash-o'></i>delete</input></label>";

			form::inpInfo(
				" ",
				$bio,
				$args
			);

			form::inpImg(
				"Replace logo:",
				"cLogo",
				"",
				$args + array("placeholder" => "Please upload only .jpg or .png file")
			);
				  
        } else {
		
				echo '<div id="image_begin"></div>';
                form::inpImg(
                    "Logo:",
                    "cLogo",
                    "",
                    $args + array("placeholder" => "<strong>No logo uploaded.</strong> Please upload only .jpg or .png file")
                );
				  
        }
		***********/
		
		echo '<input type="hidden" value="add" name="action">';
		$args=array(
		    'fld-class' => 'form-group',
			'wrap-class' => 'col-sm-8 col-md-7 col-lg-5 col-sm-offset-3 col-md-offset-2',
			'class' => 'btn btn-primary',
			'class-cancel' => 'btn btn-default'
		);
		
		form::inpSaveCancel('customers', $args);
		form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
	}
}
