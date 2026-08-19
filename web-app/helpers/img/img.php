<?php
/** Description of img - Class to handle image upload */
class img  {
    
    const ONLY_ADMIN_CAN_CROP_IMAGES = false;
    const CROP_DIALOG_TITLE = "Crop thumbnail";
    const CROP_DIALOG_MSG = "When displaying a thumbnail of this photo, only parts of it will be displayed. Please specify which part of the photo to be displayed on a thumbnail.";
    const CROP_DIALOG_BUTTON = "Use selected";
    const CROP_DIALOG_RESET = "Cancel";
    const ALLOW_LOW_RESOLUTION_IMAGES = true;
    
    /** @var array Expected img::$setup keys list<br />
     * <b>Example: <samp>self::$setup["path"] = ...</samp></b><br />
     * ["path"] path where <b>Main image</b> will be storred i.e. constant('FOO_IMAGES') //-> images/foo/<br />
     * ["allowed-extensions"]=array("jpg","png","gif") or not all of those sent as array<br />
     * ["crop"]=false|"fancy"|"inline" default is false, if Main image sizes set the default is fancybox<br />
     * ["inp-thumb-max-height"]=120 the thumb image size (image shown below the upload field)<br />
     * ["disable-delete"] => "false" <br />
     * <b>Main image</b><br />
     * ["width"] (optional), (required if height is set) number of pixels<br />
     * ["height"] (optional), (required if width is set) number of pixels<br />
     * <b>Thumb copy image (Optional)</b><br />
     * ["path-thumb-copy"]=["path"]/thumb (optional) optional path where thumbs i.e. constant('FOO_IMAGES_SMALL') //-> images/foo/thumb<br />
     * ["width-thumb-copy"] => (optional) number of pixels<br />
     * ["height-thumb-copy"] => (optional) number of pixels*/
    public static $setup = array();
    
    /**Validates and sets img::$setup array
     * @param string $imgFieldName = false - on false (in process.php) gets settings from _GET["settings"]*/
    final static function validSetupData($imgFieldName = null){
        if(!is_null($imgFieldName)){
            $s = self::$setup;
            $name = $imgFieldName;
        } else  if(filter_has_var(INPUT_GET, "settings")){
            $s = (array) json_decode(stripcslashes(filter_input(INPUT_GET, "settings",FILTER_SANITIZE_URL)));
        }
            
        if(isset($s)) {
            $res = array();
            $error = null;
            if(!defined("WP") || !defined("FP")){
                $error = isset($name) ? "Error: one or both \"WP\" / \"FP\" constants are missing before the :callMethod call" : "paths";
            } else if(!defined("TMP")){
                $error = isset($name) ? "Error: \"TMP\" constant is missing before the :callMethod call" : "tmp";
            }
            
            //["path"] is required
            if(is_null($error)){
                if(!isset($s["path"])) {
                    $error = isset($name) ? "Error: img::\$setup[\"path\"] is not set before the :callMethod call" : "path";
                } else {
                    $res["path"] = $s["path"];
                    if(!file_exists(FP . $res["path"])){
                        $error = isset($name) ? "Error: img::\$setup[\"path\"] is set to inaccessible \"".$res["path"]."\" on the :callMethod call" : "path";    
                    }
                }
            }
            
            //["allowed-extensions"]
            if(is_null($error)){
                $systemExtensions = array("jpg","png","gif");
                if(isset($s["allowed-extensions"])){
                    $s["allowed-extensions"] = array_map('strtolower', $s["allowed-extensions"]);
                    foreach($s["allowed-extensions"] as $ext){
                        if(!in_array($ext, $systemExtensions)){
                            $error = isset($name) ? "Error: img::\$setup[\"allowed-extensions\"] is set to incorrect img type: \"".$ext."\" on the :callMethod call" : die("ext");
                            break;
                        }
                    }
                    $allowedImgTypes = $s["allowed-extensions"];
                } else {
                    $allowedImgTypes = $systemExtensions;
                }
                $res["allowed-extensions"] = $allowedImgTypes;
                
            }
            
            //["width"] & ["height"]
            if(is_null($error)){
                if(isset($s["width"])){
                    if(abs($s["width"])>0){
                        if(isset($s["height"])){
                            $res["width"] = abs($s["width"]);
                        } else {
                            $error = isset($name) ? "Error: img::\$setup[\"height\"] is required when crop is active on the :callMethod call" : die("height");
                        }
                    } else {
                        $error = isset($name) ? "Error: img::\$setup[\"width\"] is set to incorrect value: \"".$s["width"]."\" on the :callMethod call" : die("width");
                    }
                }
                if(isset($s["height"])){
                    if(abs($s["height"])>0){
                        if(isset($s["width"])){
                            $res["height"] = abs($s["height"]);
                        } else {
                            $error = isset($name) ? "Error: img::\$setup[\"width\"] is required when crop is active on the :callMethod call" : die("width");
                        }
                    } else {
                        $error = isset($name) ? "Error: img::\$setup[\"height\"] is set to incorrect value: \"".$s["height"]."\" on the :callMethod call" : die("height");
                    }
                }
            }
            
            //["crop"]
            if(is_null($error)){
                if(!isset($s["crop"]) || $s["crop"]===false){
                    if(isset($res["width"]) || isset($res["height"])){
                        if(isset($s["crop"]) && $s["crop"]===false){
                            $error = isset($name) ? "Error: img::\$setup[\"width\"] and img::\$setup[\"height\"] are set while img::\$setup[\"crop\"] is set to \"false\" on the ".__METHOD__." call" : "crop";
                        } else {
                            $res["crop"] = "fancybox";  //default crop method
                        }
                    } else {
                        $res["crop"] = false;       //by default no crop
                    }
                } else if(in_array($s["crop"],array("fancybox","inline"))){
                    $res["crop"] = $s["crop"];
                } else {
                    $error = isset($name) ? "Error: img::\$setup[\"crop\"] is set to \"".$s["crop"]."\" (not allowed value) on the ".__METHOD__." call" : "crop";
                }
            }
            
            //["inp-thumb-max-height"]
            if(is_null($error)){
                if(isset($s["inp-thumb-max-height"])){
                    if(abs($s["inp-thumb-max-height"]) > 0){
                        $res["inp-thumb-max-height"] = abs($s["inp-thumb-max-height"]);
                    } else {
                        $error = isset($name) ? "Error: img::\$setup[\"inp-thumb-max-height\"] is set to \"".$s["inp-thumb-max-height"]."\" (not allowed value) on the ".__METHOD__." call" : "maxHeight";    
                    }
                } else {
                    $res["inp-thumb-max-height"] = 120;
                }
                $res["inp-thumb-max-height"] = isset($s["inp-thumb-max-height"])  ? abs($s["inp-thumb-max-height"]) : 120;
            }
            
            //["disable-delete"]
            if(is_null($error)){
                if(isset($s["disable-delete"])){
                    $res["disable-delete"] = (bool)$s["disable-delete"];
                }
            }
            
            //["width-thumb-copy"] & ["height-thumb-copy"] & ["path-thumb-copy"]
            if(is_null($error)){
                if($res["crop"]===false){

                    if(isset($s["width-thumb-copy"]) || isset($s["height-thumb-copy"])){
                        $error = isset($name) ? "Error: img::\$setup[\"...-thumb-copy\"] size/s has value while img::\$setup[\"crop\"] is set to \"false\" on the ".__METHOD__." call" : "maxHeight";    
                    }
                } else {
                    if(isset($s["width-thumb-copy"]) && isset($s["height-thumb-copy"])){
                        $res["width-thumb-copy"] = abs($s["width-thumb-copy"]);
                        $res["height-thumb-copy"] = abs($s["height-thumb-copy"]);
                    } else if(isset($s["width-thumb-copy"])){
                        $res["width-thumb-copy"] = abs($s["width-thumb-copy"]);
                        //Will calculate height from the width/width-thumb-copy ratio
                        $res["height-thumb-copy"] = ($res["height"] * $res["width-thumb-copy"]/$res["width"]);
                    } if(isset($s["height-thumb-copy"])){
                        $res["height-thumb-copy"] = abs($s["height-thumb-copy"]);
                        //Will calculate height from the width/width-thumb-copy ratio
                        $res["width-thumb-copy"] = ($res["width"] * $res["height-thumb-copy"]/$res["height"]);
                    }
                    if(isset($res["width-thumb-copy"]) && isset($res["height-thumb-copy"])){
                        if(isset($s["path-thumb-copy"])){
                            $res["path-thumb-copy"] = $s["path-thumb-copy"];
                            if(!file_exists(FP . $res["path-thumb-copy"])){
                                $error = isset($name) ? "Error: img::\$setup[\"path-thumb-copy\"] is set to inaccessible \"".$res["path-thumb-copy"]."\" on the :callMethod call" : "path";    
                            }
                        } else {
                            $res["path-thumb-copy"] = $res["path"]."thumbs/";
                            if(!file_exists(FP . $res["path-thumb-copy"])){ mkdir(FP . $res["path-thumb-copy"]);}
                        }
                        if(empty($res["width-thumb-copy"]) || empty($res["height-thumb-copy"]) || empty($res["path-thumb-copy"])){
                            $error = isset($name) ? "Error: img::\$setup[\"...-thumb-copy\"] is set not allowed value on the ".__METHOD__." call" : "thumb";
                        }
                    } else if(isset($s["path-thumb-copy"])) {
                        $error = isset($name) ? "Error: img::\$setup[\"path-thumb-copy\"] is set without thumb size value/s on the ".__METHOD__." call" : "thumb";
                    }
                }
            }
        } 
        
        if(is_null($error)){
            img::$setup = $res;
        } else {
            if(isset($name)){
                $bt = debug_backtrace();
                $method = $bt[1]["class"].$bt[1]["type"].$bt[1]["function"]."()";
                $errorMsg = str_replace(":callMethod",$method,$error) . " in ".basename($bt[1]["file"])." (".$bt[1]["line"].").";
                val::setError($name, $errorMsg);
            } else {
               die("imgEror: ".$error);
            }
        }
    }

    final static function getUploadedFileName($name){
        $val=filter_input(INPUT_POST,($name.'-filename'));
        return empty($val) ? false : $val;
    }
    
    final static public function create_temp_crop_images_folder(){
		if(!file_exists(FP.TMP.__CLASS__. '/before-thumb/')){			
			if(!file_exists(FP.TMP)) {mkdir(FP.TMP);}
			if(!file_exists(FP.TMP.__CLASS__)) {mkdir(FP.TMP.__CLASS__);}
            if(!file_exists(FP.TMP.__CLASS__.'/before-thumb')) { mkdir(FP.TMP.__CLASS__.'/before-thumb');}
		}
		
		if(!file_exists(FP.TMP.__CLASS__.'/thumb/')){					
			mkdir(FP.TMP.__CLASS__.'/thumb');
		}	
	}

	final static public function get_temp_url_before_crop(){			
		return WP.TMP.__CLASS__.'/before-thumb/';
	}	
	
	final static public function get_temp_folder_before_crop(){	
		self::create_temp_crop_images_folder();
		return FP.TMP.__CLASS__.'/before-thumb/';
	}	
		
	final static public function get_temp_url_crop(){	
		return WP.TMP.__CLASS__.'/thumb/';
	}	
	
	final static public function get_temp_folder_crop(){	
		self::create_temp_crop_images_folder();
		return FP.TMP.__CLASS__.'/thumb/';
	}	
	
	final static public function get_temp_url_original(){	
		return WP.TMP.__CLASS__;
	}
			
	final static public function get_temp_folder_original(){	
		self::create_temp_crop_images_folder();
		return FP.TMP.__CLASS__."/";
	}	
	
	final static public function get_file_name($file_name){
		for($ind = 0; $ind < 12; $ind++) 
		{			
			$con = rand(100, 1000);
			if($con > 600) {
				$key_r[] = chr(rand(97,122));
			} else {
				$key_r[] = rand(0,9);
			}					
		}
		
		$rand_str = implode('', $key_r);
		
		$file_name = mb_strtolower(preg_replace("/[^a-zA-Z0-9.-]/i", '_', $file_name), 'UTF-8');
		for($ind = 0; $ind < 5; $ind++) {
			$file_name = preg_replace('/__/', '_', $file_name);
		}	
		
		$occurrences = substr_count($file_name, '.'); 
		if($occurrences > 1) {
			$file_name = preg_replace('/[.]/', '_', $file_name, ($occurrences - 1));
		}
				
		$arr = explode('.', $file_name);
		if(empty($arr[0])) {
			$arr[0] = 'noname_' . rand(100, 999);
		}		
				
		if(strlen($arr[0]) > 70) {
			$arr[0] = substr($arr[0], 0, 70);
		}
		
		$arr[0] = trim($arr[0], '_');		
		$file_name = $arr[0] . '.' . $arr[1];
		
		$file_name = time() . '-' . $rand_str . '-' . $file_name;
		
		return $file_name;
	}	
    
    final static public function resize_image_optimal_cut_center($file, $new_image_file_path, $width, $height){		
	   $res = self::resize_image_core($file, $new_image_file_path, $width, $height, 'cut', array('align' => 'center'));		
	   return $res;
	}
	
	final static public function resize_image_optimal_cut($file, $new_image_file_path, $width, $height){		
	   $res = self::resize_image_core($file, $new_image_file_path, $width, $height, 'cut');
	   return $res;
	}
	
	final static public function resize_image_optimal_width($file, $new_image_file_path, $width){		
	   $res = self::resize_image_core($file, $new_image_file_path, $width, 100000, 'width');		
	   return $res;
	}
	
	final static public function resize_image_optimal_height($file, $new_image_file_path, $height){		
	   $res = self::resize_image_core($file, $new_image_file_path, 100000, $height, 'height');		
	   return $res;
	}
			
	final static public function resize_image_max_size($file, $new_image_file_path, $max_width = 600, $max_height = 600){		
	   $res = self::resize_image_core($file, $new_image_file_path, $max_width, $max_height, 'max');		
	   return $res;
	}
		
	final private static function resize_image_core($file, $new_image_file_path, $width, $height, $type = 'thumb', $args = array()){
		if ($width <= 0 || $height <= 0) return false;
		
		$info = getimagesize($file);
		if($info == false) {
			return false;
		}
				
		$final_width = 0;
		$final_height = 0;
		list($width_old, $height_old) = $info;
		if(empty($width_old) || empty($height_old)) {
			return false;
		}
		
		if($type == 'width') {
			$ratio = ($width_old > $width) ? (real)($width/$width_old) : 1 ;			
			$final_width = ((int)($width_old * $ratio));
			$final_height = ((int)($height_old * $ratio));
		} else if($type == 'height') {
			$ratio = ($height_old > $height) ? (real)($height/$height_old) : 1 ;			
			$final_width = ((int)($width_old * $ratio));
			$final_height = ((int)($height_old * $ratio));						
		} else if($type == 'thumb' || $type == 'cut') {
			self::calc_crop_image($width_old, $height_old, $width
				, $height, $final_width, $final_height);
		} else if($type == 'max') {		
			
			$ratio = ($width_old > $width) ? (real)($width/$width_old) : 1 ;			
			$final_width = ((int)($width_old * $ratio));
			$final_height = ((int)($height_old * $ratio));
			
			//check for images that are still too high
			$ratio = ($final_height > $height) ? (real)($height/$final_height) : 1 ;
			$final_width = ((int)($final_width * $ratio));
			$final_height = ((int)($final_height * $ratio));
		} else {			
			throw new Exception('Invalid value for type in function "resize_image_core"');
			return false;
		}
		switch ( $info[2] ) {
			case IMAGETYPE_GIF:   $image = imagecreatefromgif($file);   break;
			case IMAGETYPE_JPEG:  $image = imagecreatefromjpeg($file);  break;
			case IMAGETYPE_PNG:   $image = imagecreatefrompng($file);   break;
			default: return false;
		}
		
		if(!$image) return false;
		
		# This is the resizing/resampling/transparency-preserving magic		
		if($type == 'cut') {			
			$image_resized = imagecreatetruecolor($width, $height );
		} else {
			$image_resized = imagecreatetruecolor( $final_width, $final_height );
		}		
		if(!$image_resized) return false;
		
		if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) 
		{
			$transparency = imagecolortransparent($image);

			if ($transparency >= 0) {
                $trnprt_indx = imagecolorat($image, 0, 0);
				$trnprt_color  = imagecolorsforindex($image, $trnprt_indx);
				$transparency       = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagefill($image_resized, 0, 0, $transparency);
				imagecolortransparent($image_resized, $transparency);
			}
			elseif ($info[2] == IMAGETYPE_PNG) {
				imagealphablending($image_resized, false);
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				imagefill($image_resized, 0, 0, $color);
				imagesavealpha($image_resized, true);
			}
		}
		
		$src_x = 0;
		$src_y = 0;
		if($type == 'cut' && isset($args['align']) && $args['align'] == 'center') 
		{			
			if($final_width > $width) {
				
				if(($final_width - $width) % 2 == 1) {
					$final_width = $final_width - 1;
				}
				
				$v1 = ($final_width - $width) / 2;
				if($v1 > 0) {
					$src_x = $v1;
				}
			} else if($final_height > $height) {				
				if(($final_height - $height) % 2 == 1) {
					$final_height = $final_height - 1;
				}
				
				$v1 = ($final_height - $height) / 2;
				if($v1 > 0) {
					$src_y = $v1;
				}
			}											
		}
		
		$res = imagecopyresampled($image_resized, $image, 0, 0, $src_x, $src_y, $final_width
			, $final_height, $width_old, $height_old);						
		if(!$res) return false;
		
		# Writing image according to type to the output destination
		switch ( $info[2] ) {
			case IMAGETYPE_GIF:   $res = imagegif($image_resized, $new_image_file_path);    break;
			case IMAGETYPE_JPEG:  $res = imagejpeg($image_resized, $new_image_file_path);   break;
			case IMAGETYPE_PNG:   $res = imagepng($image_resized, $new_image_file_path);    break;
			default: return false;
		}
		//chmod(dirname($new_image_file_path), 777);
		//cf::vd(__METHOD__, is_writable(dirname($new_image_file_path)), __FILE__, imagejpeg($image_resized, $new_image_file_path));
		if(!$res) return false;				
		imagedestroy($image);
		
		$result_dimensions =  (array('w' => $final_width, 'h' => $final_height));
		return $result_dimensions;
		//return true;
	}

	final static public function create_image_thumb($from_file, $to_file, $src_x, $src_y, $src_w, $src_h, $thumb_width = 0, $thumb_height = 0){
		$src_x = abs($src_x);
		$src_y = abs($src_y);
		$src_w = abs($src_w);
		$src_h = abs($src_h);
		$thumb_width = abs($thumb_width);
		$thumb_height = abs($thumb_height);
		
		if ($src_w <= 0 || $src_h <= 0) return false;
		
		if(empty($thumb_width)) {
			$thumb_width = abs($src_w);
		}
		
		if(empty($thumb_height)) {
			$thumb_height = abs($src_h);
		}
		
		$info = getimagesize($from_file);
		switch ( $info[2] ) {
			case IMAGETYPE_GIF:   $image = imagecreatefromgif($from_file);   break;
			case IMAGETYPE_JPEG:  $image = imagecreatefromjpeg($from_file);  break;
			case IMAGETYPE_PNG:   $image = imagecreatefrompng($from_file);   break;
			default: return false;
		}		
		if(!$image) return false;
		
		//$image_resized = imagecreatetruecolor($src_w, $src_h);
		$image_resized = imagecreatetruecolor($thumb_width, $thumb_height);
		if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG)) 
		{
			$transparency = imagecolortransparent($image);

			if ($transparency >= 0) {
				$transparent_color  = imagecolorsforindex($image, $trnprt_indx);
				$transparency       = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagefill($image_resized, 0, 0, $transparency);
				imagecolortransparent($image_resized, $transparency);
			}
			elseif ($info[2] == IMAGETYPE_PNG) {
				imagealphablending($image_resized, false);
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				imagefill($image_resized, 0, 0, $color);
				imagesavealpha($image_resized, true);
			}
		}
				
		//$res = imagecopy($image_resized, $image, 0, 0, $src_x, $src_y, $src_w, $src_h);		
		//imagecopyresized(
		$res =  imagecopyresampled($image_resized, $image, 0 , 0, $src_x, $src_y, $thumb_width, $thumb_height, $src_w, $src_h);
		
		if(!$res) return false;
		
		switch ( $info[2] ) {
			case IMAGETYPE_GIF:   $res = imagegif($image_resized, $to_file);    break;
			case IMAGETYPE_JPEG:  $res = imagejpeg($image_resized, $to_file);   break;
			case IMAGETYPE_PNG:   $res = imagepng($image_resized, $to_file);    break;
			default: return false;
		}
		
		if(!$res) return false;				
		imagedestroy($image);
		
		return true;		
	}	
	
	final static public function resize_image_before_crop($file, $new_image_file_path, $thumb_width, $thumb_height){				
		$res = self::resize_image_core($file, $new_image_file_path, $thumb_width, $thumb_height, 'thumb');		
		return $res;
	}	
			
	final static public function calc_crop_image($imgW, $imgH, $tW, $tH, &$iW, &$iH){		
		if(($tW/$tH) == ($imgW/$imgH)) {		
			$iW = $tW;
			$iH = $tH;
			return;
		}

		$tProm1 = ($tW > $tH) ? $tW : $tH;
		$imgProm1 = ($imgW < $imgH) ? $imgW : $imgH;

		$res = $tProm1 / $imgProm1;
		$iW = ((int)($res * $imgW) + 1);
		$iH = ((int)($res * $imgH) + 1);

		$coef = 1;
		$minus = 0.00005;

		while(true)
		{	
			$coef = $coef - $minus;		
			$iW1 = (int)($iW * $coef);
			$iH1 = (int)($iH * $coef);

			if(($iW1 < $tW || $iH1 < $tH) 
				//|| ((($tW + 1) > $iW1 || ($tH + 1) > $iH1))
				|| ($coef == 0)) {				
				break;
			}	

			$iW2 = $iW1;
			$iH2 = $iH1;							
		}

		if(isset($iW2) && isset($iH2)) {
			$iW = $iW2;
			$iH = $iH2;
		}	

		return;
	}
	
    /* final static function crop_image_add_js($img_id, $thumb_width, $thumb_height  ){		
		?>
		<script type="text/javascript">
		$(document).ready(function(){
			$('#<?=$img_id?>').imgAreaSelect({
				handles: true,
				movable: true,
				resizable: true,
				show: true,
				persistent: true,
				x1: $('#x1').val(),
				y1: $('#y1').val(),
				x2: ($('#x1').val() * 1) + <?= $thumb_width ?>,
				y2: ($('#y1').val() * 1) + <?= $thumb_height ?>,				
				minWidth: <?= $thumb_width/2 ?>,
				minHeight: <?= $thumb_height/2 ?>,
				aspectRatio: '<?= $thumb_width ?>:<?= $thumb_height ?>',
				keys: { arrows: 1, ctrl: 1, shift: 'resize' },

				onSelectEnd: function(img, selection){
					$('#x1').val(selection.x1);
					$('#y1').val(selection.y1);
					$('#w').val(selection.width);
					$('#h').val(selection.height);
					$('#x2').val(selection.x2);
					$('#y2').val(selection.y2);
				},
				onSelectStart: function(img, selection){
					$('#x1').val(selection.x1);
					$('#y1').val(selection.y1);
					$('#x2').val(selection.x2);
					$('#y2').val(selection.y2);
					$('#w').val(selection.width);
					$('#h').val(selection.height);
				},
				onSelectChange: function(img, selection){
					$('#x1').val(selection.x1);
					$('#y1').val(selection.y1);
					$('#x2').val(selection.x2);
					$('#y2').val(selection.y2);
					$('#w').val(selection.width);
					$('#h').val(selection.height);
				}
			});
		});	
        function runSelect(){
            $('#<?=$img_id?>').imgAreaSelect({
				handles: true,
				movable: true,
				resizable: true,
				show: true,
				persistent: true,
				x1: $('#x1').val(),
				y1: $('#y1').val(),
				x2: ($('#x1').val() * 1) + <?= $thumb_width ?>,
				y2: ($('#y1').val() * 1) + <?= $thumb_height ?>,				
				minWidth: <?= $thumb_width/2 ?>,
				minHeight: <?= $thumb_height/2 ?>,
				aspectRatio: '<?= $thumb_width ?>:<?= $thumb_height ?>',
				keys: { arrows: 1, ctrl: 1, shift: 'resize' },

				onSelectEnd: function(img, selection){
					$('#x1').val(selection.x1);
					$('#y1').val(selection.y1);
					$('#w').val(selection.width);
					$('#h').val(selection.height);
					$('#x2').val(selection.x2);
					$('#y2').val(selection.y2);
				},
				onSelectStart: function(img, selection){
					$('#x1').val(selection.x1);
					$('#y1').val(selection.y1);
					$('#x2').val(selection.x2);
					$('#y2').val(selection.y2);
					$('#w').val(selection.width);
					$('#h').val(selection.height);
				},
				onSelectChange: function(img, selection){
					$('#x1').val(selection.x1);
					$('#y1').val(selection.y1);
					$('#x2').val(selection.x2);
					$('#y2').val(selection.y2);
					$('#w').val(selection.width);
					$('#h').val(selection.height);
				}
			});
        }
		</script>
		<?php
	} */
	
	public static function crear_temp_files($dir){
		if ($handle = opendir($dir)) 
		{
			while (false !== ($entry = readdir($handle))) 
			{				

				if ($entry != "." && $entry != ".." && $entry != 'Thumbs.db') 
				{					
					if(is_dir($dir . $entry)) {		
						//Recursion
						self::crear_temp_files($dir . $entry . '/');						
					} else {						
						$name_arr = explode('-', $entry);																														
						
						if(count($name_arr) > 2 
						 && is_numeric($name_arr[0]) 
						 && abs($name_arr[0]) > 1200000000 
						 && abs($name_arr[0]) < 2100000000) 
						{
							$t1 = $name_arr[0];
							$test = time() - $t1;
							$seconds = 4 * 60 * 60;							
							
							if(defined('LOCAL') && LOCAL === true) {
								$seconds = 60;								
							}

                            if($test > ($seconds)) {
								unlink($dir . $entry);								
							}							
						}
					}
				}
			}
			closedir($handle);
		}
	}
				
	public static function clear_crop_images_from_temp($image_name, $clear_old = false){
		if(file_exists(self::get_temp_folder_before_crop() . $image_name)) {
			unlink(self::get_temp_folder_before_crop() . $image_name);
		}
		if(file_exists(self::get_temp_folder_crop() . $image_name)) {
		unlink(self::get_temp_folder_crop() . $image_name);
		}
		if(file_exists(self::get_temp_folder_original() . $image_name)) {
			unlink(self::get_temp_folder_original() . $image_name);
		}
		if($clear_old === true) {
			self::crear_temp_files(self::get_temp_folder_original());
		}
	}	
	
	/*public static function delete_image_files($image, $constant_base_name){
		if(empty($image)) { 
			return; 		
		}
				
		$image_dir = constant($constant_base_name);
		$image_dir_big = constant($constant_base_name . '_BIG');
		
		if(file_exists(FP . $image_dir . $image)) {
			unlink(FP . $image_dir . $image);
		}
					
		if(file_exists(FP . $image_dir_big . $image)) {
			unlink(FP . $image_dir_big . $image);
		}
	}*/	
	
    /** Delete one or list of images
     * @param str|array $fn - filename.xyz | array(array(filename=>path),array(filename2=>path2)...);
     * @param str $path - ignored if $fn is array */
    public static function del($fn, $path = null){
        if(empty($fn)){ return; }
        if(is_array($fn) && is_null($path)){
            //if(!is_array($fn[0])) {die("Error: ".__METHOD__." expects to contain arrays: array(filename=>path),...");}
            $delArray = $fn;
        } else {
            $delArray = array();
            $delArray[$fn] = $path;
        }
        foreach($delArray as $f=>$p){
            if(file_exists(FP.$p.$f)){ 
                unlink(FP.$p.$f); 
            }
        }
    }
    
	public static function move_images_from_temp($name, $rigts_to_delete_old_temp = false, $imgFixed=null) {				
        //We have to validate data submitted 
        self::validSetupData($name);
        $image_name = self::getUploadedFileName($name);
        $imgNew = is_null($imgFixed) ? $image_name : $imgFixed;

        if(empty(self::$setup)){
            die("Error: img::\$setup[] is not defined on the ".__METHOD__."() call");
        }

        $imgDir = FP . self::$setup["path"];
        
        if(!empty($image_name) && !file_exists($imgDir . $image_name)){
            $cropSuccess = false;
            if(isset(self::$setup["crop"]) && self::$setup["crop"]===false){
                if(copy(self::get_temp_folder_original() . $image_name, ($imgDir . $imgNew))){								
                    $cropSuccess = true;
                }
            } else {
                if(copy(self::get_temp_folder_crop() . $image_name, ($imgDir . $imgNew))){
                    if(isset(self::$setup["path-thumb-copy"])) {
                        $thumb = img::resize_image_optimal_cut((self::get_temp_folder_crop() . $image_name), (FP.self::$setup["path-thumb-copy"].$imgNew), img::$setup["width-thumb-copy"], img::$setup["height-thumb-copy"]);
                        if($thumb){
                            $cropSuccess = true;
                        } 
                    } else {
                        $cropSuccess = true;
                    }
                }
            }
            //delete thumb files, if user is admin can delete old temp files
            if($cropSuccess === true){
                self::clear_crop_images_from_temp($image_name, $rigts_to_delete_old_temp);
                return $cropSuccess;
            }
        }
		return false;				
	}	

    static function prepare($setOrCheck = false){
        if( helper::prepare($setOrCheck, (__METHOD__)) !== true ){
            return; //Stops the further execution in case the helper call doesn't return true.
        }
        cf::appendToHeadTag(ADD."helpers/img/add/img.js");
        //cf::appendToHeadTag(ADD."fancybox/jquery.fancybox-1.3.4_patch.js");
        //cf::appendToHeadTag(ADD."fancybox/jquery.fancybox-1.3.4.css","css");
        //cf::appendToHeadTag(ADD."helpers/img/img.css", "css");
    }
}


