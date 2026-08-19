<?php
require_once("../../boot/app-boot.php");

class CropPage extends Page {
    
	public function __construct() {		
		parent::__construct();
        img::validSetupData();
	}
			
	public function uploadAndProcess(){
		$flag_perfect_image = false;
        $ctrl=  filter_input(INPUT_GET, 'crtl' ,FILTER_SANITIZE_STRING);
		$file = $_FILES[$ctrl];
		if(empty($_FILES) || empty($file)) {
			return;
		}	

        if (!(($file["type"] == "image/gif") || ($file["type"] == "image/jpeg")
			|| ($file["type"] == "image/pjpeg") || ($file["type"] == "image/png"))) {
			return $this->breakError('Incorrect file type. Only images are allowed.');			
		} 

		if ($file["error"] > 0) {
			return ($this->admin ? $this->breakError('Error: ' . $file["error"]) : $this->breakError(5023));
		}
 
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if(!in_array($ext,img::$setup["allowed-extensions"])) {
            $ext = array_map('strtoupper', img::$setup["allowed-extensions"]);
            $s = count(img::$setup["allowed-extensions"]) === 2 ? " &amp; " : ", ";
			return $this->breakError("Incorrect file type. Only ".implode($s,$ext)." images are allowed.");
		} 

		$file_name = img::get_file_name($file['name']);	
		$fromFile = $file["tmp_name"];
        if(img::$setup["crop"]===false){
			return $this->processNoCrop($file_name, $fromFile);            
        }

		//if EXAMPLE_BIG constant is defined
		if(img::$setup["width"] > 0 && img::$setup["height"] > 0) {
			$res = img::resize_image_max_size($fromFile
					,img::get_temp_folder_original() . $file_name
					, 10000, 10000);		
			if($res) {
				$fromFile = img::get_temp_folder_original() . $file_name;
			} else {
				return $this->breakError(5011);
			}				
		}

        list($width_uploaded, $height_uploaded, $type_uploaded, $attr_uploaded) = getimagesize($fromFile);
        if($width_uploaded < img::$setup["width"] || $height_uploaded < img::$setup["height"]){
            if(img::ALLOW_LOW_RESOLUTION_IMAGES === true) {
            $res_resize_image_before_crop = img::resize_image_before_crop($fromFile			
                , img::get_temp_folder_before_crop() . $file_name, (img::$setup["width"]*1.5), (img::$setup["height"]*1.5));
            } else {
                return $this->breakError("Insufficient image resolution. Please use images larger than the recomended size below.");
            }
        } else {
            $res_resize_image_before_crop = img::resize_image_before_crop($fromFile			
                , img::get_temp_folder_before_crop() . $file_name, $width_uploaded, $height_uploaded);
            //cf::vdd($res_resize_image_before_crop, $fromFile, img::get_temp_folder_before_crop() . $file_name);
        }

		if(!$res_resize_image_before_crop) {
			return $this->breakError(5012);
		}	

        //Check perfect image
		if($res_resize_image_before_crop['w'] == img::$setup["width"] && $res_resize_image_before_crop['h'] == img::$setup["height"]) {
			$flag_perfect_image = true;			
		}
		
		if(!$flag_perfect_image) {
			$fromFile = img::get_temp_folder_before_crop() . $file_name;				
			$res_resize = img::resize_image_optimal_cut($fromFile
				, img::get_temp_folder_crop() . $file_name, img::$setup["width"], img::$setup["height"]);
			if(!$res_resize) {
				return $this->breakError(5013);
			}
		} else {
			copy(img::get_temp_folder_before_crop() . $file_name, img::get_temp_folder_crop() . $file_name);
		}	

		$img_src = img::get_temp_url_crop() . $file_name;		
		
		$num = filter_input(INPUT_GET, 'num', FILTER_SANITIZE_NUMBER_INT);
		if(empty($num)) {
			$num = 1;
		}
        $script = 'top.showUploadedImage("'.$ctrl.'","'.$file_name.'","'.$img_src.'","'.img::$setup["inp-thumb-max-height"].'");';
        form::tag($script,"script");
	}
	
	private function processNoCrop($file_name, $fromFile) {
		//Max size is set to 10k px
        $res_resize_image = $res_resize_image_before_crop = img::resize_image_max_size($fromFile			
			, img::get_temp_folder_original() . $file_name, 10000, 10000);
		if(!$res_resize_image) {	
			return $this->breakError(50121);
		}
		
		$img_src = img::get_temp_url_original() ."/". $file_name;		
		$cn = filter_input(INPUT_GET,'cname',FILTER_SANITIZE_STRING);
        form::tag('top.showUploadedImage("'.$cn.'","'.$file_name.'","'.$img_src.'","'.img::$setup["inp-thumb-max-height"].'");',"script");
	}	
	
	public function cropDialog() {
		$width = abs(img::$setup["width"]);
		$height = abs(img::$setup["height"]);
		$ctrl_id = 'usrphoto';	
        $file1 = filter_input(INPUT_GET, 'pd' ,FILTER_SANITIZE_STRING);
		$f_r = explode('-', $file1);

		//security check, you can not update image after 20 min
		if(time() - abs($f_r[0]) > 1000) {
			return $this->breakError(5000);		
		}

		if($width <= 0 || $height <= 0) {
			return $this->breakError(5001);		
		}	

		if(!file_exists(img::get_temp_folder_before_crop() . $file1)) {
			return $this->breakError(5002);		
		}
        
        list($width_uploaded, $height_uploaded, $type_uploaded, $attr_uploaded) = getimagesize((img::get_temp_folder_before_crop() . $file1));

		form::tag(img::CROP_DIALOG_TITLE, 'h1',array("class"=>"crop-title"));
		form::tag(img::CROP_DIALOG_MSG, 'p');
        if(img::$setup["crop"] === "fancybox"){
            form::openTag("form",array("method"=>"post","validate"=>false,"id"=>"myform"));
        }
        form::openDiv(array("style"=>"width:100%; overflow:hidden; text-align:center;"));
            form::openTag("center", array("class"=>"image-crop-grid"));
                $img_attr = array();
                $img_attr["id"] = $ctrl_id;
                $img_attr["src"] = img::get_temp_url_before_crop() . $file1;
                $img_attr["style"] = 'position:relative; display:block; margin-left: auto; margin-right: auto; max-width:100%;';
                $img_attr["data-original-width"] = $width_uploaded;
                $img_attr["onload"] = "SetRatio(this)";
                form::tag("","img",$img_attr);
            form::closeTag("center");
        form::closeDiv();
        form::input("",array("type"=>"hidden","id"=>"x1","name"=>"x1","value"=>"0"));
        form::input("",array("type"=>"hidden","id"=>"y1","name"=>"y1","value"=>"0"));
        form::input("",array("type"=>"hidden","id"=>"x2","name"=>"x2","value"=>$width));
        form::input("",array("type"=>"hidden","id"=>"y2","name"=>"y2","value"=>$height));
        form::input("",array("type"=>"hidden","id"=>"w","name"=>"w","value"=>$width));
        form::input("",array("type"=>"hidden","id"=>"h","name"=>"h","value"=>$height));
        form::input("",array("type"=>"hidden","id"=>"ratio","name"=>"ratio"));
        $resetClick = "top.cancelCrop('".filter_input(INPUT_GET,"cname")."','".img::$setup["crop"]."');";
        form::openTag("center", array("class"=>"buttons clear"));
            if(img::$setup["crop"] === "fancybox"){
				echo '<br>';
                form::tag(img::CROP_DIALOG_BUTTON,"button",array("class1"=>"btn btn-primary","type"=>"submit"));
                form::tag(img::CROP_DIALOG_RESET,"button",array("class1"=>"btn btn-primary","type"=>"reset","onclick"=>$resetClick));
             echo '<br><br>';   
				form::closeTag("form");
            } else if(img::$setup["crop"] === "inline") {
                $SERVER_PROTOCOL = substr($_SERVER["SERVER_PROTOCOL"], 0, 5) === "HTTPS" ? "https://":"http://";
                $HTTP_HOST = $_SERVER["HTTP_HOST"];
                $REQUEST_URI = $_SERVER["REQUEST_URI"];

                $actual_link = PROTOCOL . $HTTP_HOST . $REQUEST_URI;
			
                form::tag(img::CROP_DIALOG_BUTTON,"button",
				array("class1"=>"btn btn-primary","onclick"=>"SubmitInlineCrop('".$actual_link."','".filter_input(INPUT_GET,"cname")."');","type"=>"button"));
                form::tag(img::CROP_DIALOG_RESET,"button",array("class1"=>"btn btn-primary","type"=>"reset","onclick"=>$resetClick));
				echo '<br><br>';
                $script = "top.document.getElementById(\"inline-crop-div-".filter_input(INPUT_GET,"cname")."\").innerHTML = document.body.innerHTML";
                form::tag($script,"script");
            }
        form::closeTag("center");
	}	
	
	public function processCrop() {
		$width = abs(img::$setup["width"]);
		$height = abs(img::$setup["height"]);
        $file_name = filter_input(INPUT_GET,'pd',FILTER_SANITIZE_STRING);
		$from_file = img::get_temp_folder_before_crop() . $file_name;
		$to_file = img::get_temp_folder_crop() . $file_name;
        //list($width_uploaded, $height_uploaded, $type_uploaded, $attr_uploaded) = getimagesize(($from_file));
		/*$x1 = abs(filter_input(INPUT_POST, 'x1', FILTER_SANITIZE_NUMBER_INT))*abs(filter_input(INPUT_POST, 'ratio', FILTER_SANITIZE_NUMBER_INT));
		$y1 = abs(filter_input(INPUT_POST, 'y1', FILTER_SANITIZE_NUMBER_INT))*abs(filter_input(INPUT_POST, 'ratio', FILTER_SANITIZE_NUMBER_INT));
        $w = abs(filter_input(INPUT_POST, 'w', FILTER_SANITIZE_NUMBER_INT))*abs(filter_input(INPUT_POST, 'ratio', FILTER_SANITIZE_NUMBER_INT));
		$h = abs(filter_input(INPUT_POST, 'h', FILTER_SANITIZE_NUMBER_INT))*abs(filter_input(INPUT_POST, 'ratio', FILTER_SANITIZE_NUMBER_INT));*/
		
        $x1 = abs(filter_input(INPUT_POST, 'x1'))*abs(filter_input(INPUT_POST, 'ratio'));
		$y1 = abs(filter_input(INPUT_POST, 'y1'))*abs(filter_input(INPUT_POST, 'ratio'));
        $w = abs(filter_input(INPUT_POST, 'w'))*abs(filter_input(INPUT_POST, 'ratio'));
		$h = abs(filter_input(INPUT_POST, 'h'))*abs(filter_input(INPUT_POST, 'ratio'));
        //cf::vdd((float)filter_input(INPUT_POST, 'x1'), (int)filter_input(INPUT_POST, 'x1', FILTER_SANITIZE_NUMBER_INT), $_POST);
		$flagNoErrors = img::create_image_thumb($from_file, $to_file, $x1, $y1, $w, $h, $width, $height);		
		
		if(!$flagNoErrors) {
			return $this->breakError(5003);	
        } 
        
        $ctrl = filter_input(INPUT_GET,'cname');
        $img_src = img::get_temp_url_crop().$file_name;
        
        if(img::$setup["crop"]==="fancybox"){
            $script = "top.showUploadedImage(\"".$ctrl."\",\"".$file_name."\",\"".$img_src."\",\"".img::$setup["inp-thumb-max-height"]."\");";
            $script .= " parent.$.fancybox.close();";
            form::tag($script,"script");
        } else if (img::$setup["crop"]==="inline"){
            $str = json_encode(array("ctr"=>$ctrl, "file"=>$file_name, "img"=>$img_src, "maxHeight"=>img::$setup["inp-thumb-max-height"]));
            die($str);
        }
	}	
	
	private function breakError($text){
		$ctrl = filter_input(INPUT_GET, "cname",FILTER_SANITIZE_STRING);
        $errorMsg = is_numeric($text) ? "Unexpected error: {$text}" : $text;
        $script = "top.setFormFieldError(parent.document.getElementById(\"inut-file-".$ctrl."\").form,\"".$ctrl."\",\"".$errorMsg."\");";
        if(img::$setup["crop"]==="fancybox"){
            $script .= " parent.$.fancybox.close();";
        } else if(img::$setup["crop"]==="inline"){
            $script .= " var i = top.document.getElementById(\"inline-crop-div-".$ctrl."\"); if(i!==null){ i.innerHTML=\"\";} top.croperror = true;";
        }
        form::tag($script,"script");
		return false;
	}
    
    protected function inHead() {
        cf::$pg["title"] = "Image Cropper";
        cf::appendToHeadTag("//fonts.googleapis.com/css?family=Open+Sans:400italic,400,300,600,700,800|Open+Sans+Condensed:300,700,300italic|Cabin:400,500,600,700|Lato:100,300,400,700,900","css");
        cf::appendToHeadTag(WP."add/bootstrap/bootstrap.min.css","css");
        cf::appendToHeadTag(WP."add/bootstrap/bootstrap-theme.min.css","css");
        cf::appendToHeadTag(WP."add/admin/cstyle.css","css");
        cf::appendToHeadTag(WP."add/admin/login.css","css");
        cf::appendToHeadTag(WP."add/helpers/form/add/form.css","css");
        if(filter_input(INPUT_GET,'cmd') == 'crop' && !filter_has_var(INPUT_POST, 'x1')){
            cf::appendToHeadTag(ADD."helpers/img/cropper/cropper.css","css");
            cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js");
            cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js");
            cf::appendToHeadTag(ADD."helpers/img/cropper/cropper.js");
            cf::appendToHeadTag(ADD."helpers/img/img.js");
        } else {
            cf::appendToHeadTag("//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js");
            cf::appendToHeadTag(WP."add/bootstrap/bootstrap.min.js");
        }
    }
}

$page = new CropPage(img::ONLY_ADMIN_CAN_CROP_IMAGES);

if(img::$setup["crop"]!=="inline") {
    $page->Header("simple");
}

if(filter_input(INPUT_GET,'cmd',FILTER_SANITIZE_STRING) == 'prepare') {	
	$page->uploadAndProcess();	
} else if(filter_input(INPUT_GET,'cmd',FILTER_SANITIZE_STRING) == 'crop' && !filter_has_var(INPUT_POST, 'x1')) {
	$page->cropDialog();
} else if(filter_input(INPUT_GET,'cmd',FILTER_SANITIZE_STRING) == 'crop' && filter_has_var(INPUT_POST, 'x1')) {
	$page->processCrop();
}	

if(img::$setup["crop"]!=="inline") {
    $page->Footer("simple");
}