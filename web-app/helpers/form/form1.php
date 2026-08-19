<?php
/** The form helper (input elements methods) */
class form extends formCore {

    static $inpFieldContainer = true;
    static $error;
    private static $nonInputFlds = array("textarea","label","span","p");

    /** Description: Process code before inpMethod  - adding style, label... */
    private static function processInpBefore($label = "", $name = "", $value = "", &$args = array(), $wrapRequired=false, $inpToLbl = false) {

        if (self::$inpFieldContainer === true) {
            self::$codeLineBreakAfterInpSKIP = true;
            $fldArgs = array("class" => "fld");
            if(isset($args["fld-class"])){
                $fldArgs["class"] .= " ".$args["fld-class"];
                unset($args["fld-class"]);
            }
            if(isset($args["fld-style"])){
                $fldArgs["style"] = $args["fld-style"];
                unset($args["fld-style"]);
            }
            if(isset($args["fld-id"])){
                $fldArgs["id"] = $args["fld-id"];
                unset($args["fld-id"]);
            }
            self::openTag("div", $fldArgs);
            self::codeStyleSkip(true);
        }
        
        if (!empty($label)) {
            $lblArgs = array();
            if(isset($args["id"])){
                $lblArgs["for"]=$args["id"];
            }
            if(isset($args["lbl-class"])){
                $lblArgs["class"] = $args["lbl-class"];
                unset($args["lbl-class"]);
            }
            if(isset($args["lbl-style"])){
                $lblArgs["style"] = $args["lbl-style"];
                unset($args["lbl-style"]);
            }
            if (self::$inpFieldContainer != true) {
                self::$codeLineBreakAfterInpSKIP = true;
            }
            if (isset(form::$jsval["validations"][$name]) && array_key_exists("required", form::$jsval["validations"][$name])) {
                $label.= ASTERISK;
            }
            
            if((bool)$inpToLbl){
                self::openTag("label", $lblArgs);
            } else {
                self::tag($label, "label", $lblArgs);
            }
            
            if (self::$inpFieldContainer != true) {
                self::codeStyleSkip(true);
            }
        }
        if($wrapRequired){
			
            $wrapClass = array("class"=>$args["wrap-class"]);
            if(isset($args["wrap-id"])) {
                $wrapClass["id"] = $args["wrap-id"];
            }
            self::openTag("div", $wrapClass);
        }

        if(isset(form::$jsval["validations"][$name])) {
            foreach(form::$jsval["validations"][$name] as $k => $v){
                $args[$k] = $v;
            }
        }
        
        if (!empty($name)) {
            $args["name"] = $name;
        }

        if((bool)$inpToLbl){
            if(array_key_exists("value", $args)){
                if(($value=self::prepareDataValue($name, $value))==$args["value"] 
				       && (string)$value===(string)$args["value"]){
                    $args["checked"]=true;
                }
				
                //pv 2018-07-09  $value = null; //<--- pv 2018-07-09
            }
        }
        
        if (!empty($value) || filter_has_var(INPUT_POST, $name)) {
            $args["value"] = self::prepareDataValue($name, $value);
        }
        
        if (is_array(self::$error) && array_key_exists($name, self::$error)) {
            if (array_key_exists("class", $args)) {
                $args["class"] .= " error";
            } else {
                $args["class"] = "error";
            }
        }
    }

    /** Description: Process code after the inpMethod  - error and text afer... */
    private static function processInpAfter($name = "", $requiredErrorTag = false, $wrapRequired = false, $inpToLbl = false, $label=null) {
        if(isset(self::$jsval["validations"][$name]) || !empty(self::$error[$name]) || $requiredErrorTag==true){
            form::tag((empty(self::$error[$name]) ? "" : self::$error[$name][0]),"span",array("class"=>"err"));
        }
        if((bool)$inpToLbl){
            self::html($label);
            self::closeTag("label");
        }
        if($wrapRequired){
            self::closeTag("div");
        }
        if (self::$inpFieldContainer === true) {
            self::$codeLineBreakAfterInpSKIP = false;
            self::closeTag("div");
            self::codeStyleSkip(false);
        } else {
            self::codeStyleSkip(false);
            self::processAfter();
        }
    }

    private static function prepareDataValue($name, $value) {	
		 if (isset($POST[$name])) {
            if (!is_array($_POST[$name])) {
                $value = htmlentities(filter_input(INPUT_POST, $name), ENT_QUOTES, "UTF-8");
            } else {
				$value = array();
				foreach($_POST[$name] as $k=>$v){
					$value[$k] = htmlentities($v, ENT_QUOTES, "UTF-8");
				}
            }
        }
        return $value;
    }

    /** Description: Writes <label>$label: </label><input type="text" name="$name" value="value" {$args}/>
     *  @param str   $label
     *  @param str   $name
     *  @param str   $value
     *  @param arr   $args - tag attributes: (default) "type"=>"text" */
    static function inpField($label = "", $name = "", $value = "", $args = array()) {
        $args["type"] = isset($args["type"]) ? $args["type"] : "text";
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }

        $inpToLbl = (bool)(isset($args["type"]) && in_array($args["type"],array("checkbox","radio")));
        $wrapRequired = (bool)isset($args["wrap-class"]);

        self::processInpBefore($label, $name, $value, $args, $wrapRequired, $inpToLbl);

        $tag = !isset($nonInputFlds) || !in_array($args["type"],self::$nonInputFlds) ? "input" : $args["type"];
   
		$innerHtml = in_array($tag, self::$nonInputFlds) && isset($args["value"]) ? $args["value"] : "";
		
        if(in_array($tag, self::$nonInputFlds)) {
            unset($args["type"], $args["value"]);
        }
        if($tag==="textarea"){$codeOffset = self::$ofsetIndentsCount; self::$ofsetIndentsCount=0;}


        if(isset($args["prefix"])) {
            $prefixClass = (isset($args["prefix-class"]) ? $args["prefix-class"] : "") ;
            self::html("<div class='input-prefix ".$prefixClass." '>".$args["prefix"]."</div>");
        }

        self::tag($innerHtml, $tag, $args);
        if($tag==="textarea"){ self::$ofsetIndentsCount=$codeOffset; }
        if(isset($args["hint"])) {
            $hintClass = (isset($args["hint-class"]) ? $args["hint-class"] : "") ;
            self::html("<div class='input-hint ".$hintClass." '>".$args["hint"]."</div>");
        }
        self::processInpAfter($name, $requiredErrorTag, $wrapRequired, $inpToLbl, $label);
    }

    static function inpInfo($label = "", $info = "", $args = array()) {
        $args["type"] = "info";
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }

        $inpToLbl = false;
        $wrapRequired = (bool)isset($args["wrap-class"]);

        self::processInpBefore($label, "", "", $args, $wrapRequired, $inpToLbl);

        $tag = "div";
        $innerHtml = $info;

        self::tag($innerHtml, $tag, $args);

        if(isset($args["hint"])) {
            $hintClass = (isset($args["hint-class"]) ? $args["hint-class"] : "") ;
            self::html("<div class='input-hint ".$hintClass." '>".$args["hint"]."</div>");
        }
        self::processInpAfter("", $requiredErrorTag, $wrapRequired, $inpToLbl, $label);
    }

    /** Description: Writes <label>$label: </label><select name="$name" {$args}><option value="$value"/>$valueTitle</option></select>
     *  @param string   $label
     *  @param string   $name
     *  @param array    $options
     *  @param strring  $value
     *  @param array    $args - tag attributes */
    static function inpSelect($label = "", $name = "", $options = array(), $value = "", $args = array()) {
        
        $wrapRequired = (bool)isset($args["wrap-class"]);
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }
        self::processInpBefore($label, $name, $value, $args, $wrapRequired);
        if (is_array($options)) {
            if(!array_key_exists("{!--Empty--!}", $options) && !isset($args["skip-empty"])){
				$options=(array("{!--Empty--!}" => "Please select"))+$options;
			} 
            if(isset($args["skip-empty"])){ unset($args["skip-empty"]); }
        } else {
            die("ERROR (".__METHOD__."): options provided for $name are not valid array.");
        }

        self::openTag("select", $args);
        foreach ($options as $k => $v) {
            $arr_opt = array("value" => $k);
			
            if ($k == $value || (isset($args["value"]) && $k==$args["value"])) {
				
                if($value == 0) {
                    if(strlen((string)$k) === strlen((string)$value)) {
                        $arr_opt["selected"] = "";
                    }
                } else {
                    $arr_opt["selected"] = "";
                }
            }
            self::tag($v, "option", $arr_opt);
        }
        self::closeTag("select");
        if(isset($args["hint"])) {
            $hintClass = (isset($args["hint-class"]) ? $args["hint-class"] : "") ;
            self::html("<div class='input-hint ".$hintClass." '>".$args["hint"]."</div>");
        }
		if(isset($args["add"])) {
           
            self::html("<br><br>".$args["add"]."");
        }
        self::processInpAfter($name, $requiredErrorTag, $wrapRequired);
    }

    /** Description: Writes <button type="submit" name="save-btn" value="$textSave" {$args}>$txtSave</button><button type="button" onclick="location.assign('$cancelUrl')" {$args}>$txtSave</button>
     *  @param str   $cancelUrl
     *  @param str   $args
     *  @param str   $txtSave
     *  @param arr   $txtCancel - tag attributes */
    static function inpSaveCancel($cancelUrl = "", $args = array(), $txtSave = "Save", $txtCancel = "Cancel",$nameSave='save-btn') {
        $wrapRequired = (bool)isset($args["wrap-class"]);

        if(isset($args["onclick-save"])) {
            $args["onclick"] = $args["onclick-save"];
        }
        self::processInpBefore("", "", "", $args, $wrapRequired);
        self::tag($txtSave, "button", array_merge(array("type" => "submit", "name" => $nameSave, "value" => $txtSave), $args));
        if(isset($args["class-cancel"])){
            $args["class"] = $args["class-cancel"];
        }
        if(isset($args["id-cancel"])){
            $args["id"] = $args["id-cancel"];
        }
        if(isset($args["onclick-cancel"])) {
            $args["onclick"] = $args["onclick-cancel"];
        } else {
            unset($args["onclick"]);
        }
        form::html(" ");
        if(!is_null($cancelUrl)) {
            self::tag($txtCancel, "button", array_merge(array("type" => "button", "onclick" => "location.assign('$cancelUrl')"), $args));
        }
        self::processInpAfter("", false, $wrapRequired);
    }
    
    /** Description: Writes <label>$label: </label><textarea name="$name" {$args}/>$value</textarea>
     *  @param str   $label
     *  @param str   $name
     *  @param str   $value
     *  @param arr   $args - tag attributes: (default) "type"=>"text" */
    static function inpCkEditor($label = "", $name = "", $value = "", $args = array(), $ckSettings = null) {
		 $args["type"] = !isset($args["type"]) || $args["type"]==="textarea" ? "textarea" : die(__METHOD__.": Invalid \$args[\"type\"] call.");
        $args["data-ckeditor"] = is_null($ckSettings) || empty($ckSettings) ? "true" : str_replace("\"","'",json_encode($ckSettings));
        ckeditor::set();
        self::inpField($label, $name, $value, $args);
    }
    
    /** Description: cretes file upload input for an image
	 * @param type $label
	 * @param type $name
	 * @param type $value
	 * @param type $args
	 * @throws Exception */
	static public function inpImg($label, $name, $value, $args = array()) {
        img::prepare(true);
        img::validSetupData($name);

        //Checking active validations
        $isRequired = false;
        if(isset(self::$jsval["validations"][$name]) && !empty(self::$jsval["validations"][$name])){
            foreach(self::$jsval["validations"][$name] as $validation=>$d){
                if($validation === "data-callback" && $d === "requireImg"){
                    $isRequired = true; 
                }
            }
        }
        
        if($isRequired===true && isset(img::$setup["disable-delete"]) && img::$setup["disable-delete"] === false){
            val::setError($name, "Error: img::\$setup[\"disable-delete\"] is set to false while image is required on the ".__METHOD__." call");
        } else if ($isRequired === true){
            img::$setup["disable-delete"] = true;
        }
                
        $wrapRequired = (bool)isset($args["wrap-class"]);
        
        $imageUnderMsg = "";
        // isset checks are just to solve the php warnings...

        if(isset(img::$setup["allowed-extensions"])) {
            $ext = array_map('strtoupper', img::$setup["allowed-extensions"]);
            $s = count(img::$setup["allowed-extensions"]) === 2 ? " &amp; " : ", ";
            $imageUnderMsg = "System allows only " . strtoupper(implode($s,$ext)). " files. ";
        }
        if(isset(img::$setup["crop"]) && img::$setup["crop"]!==false && isset(img::$setup["width"]) && isset(img::$setup["height"])){
            $imageUnderMsg .= "Recommended size: ".img::$setup["width"]." x ".img::$setup["height"]."px.";
            if(img::$setup["crop"] === "inline"){
                self::tag("","script",array("src"=>"//ajax.googleapis.com/ajax/libs/prototype/1.7.3.0/prototype.js"));
                self::tag("","script",array("src"=>"//ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js"));
                self::tag("","script",array("src"=>ADD."helpers/img/add/cropper/cropper.js"));
                self::tag('
                    var fileref=document.createElement("link"); fileref.setAttribute("rel", "stylesheet"); fileref.setAttribute("type", "text/css");
                    fileref.setAttribute("href", "'.ADD.'helpers/img/add/cropper/cropper.css");
                    document.getElementsByTagName("head")[0].appendChild(fileref);
                    ',"script");
            }
        } else {
            img::$setup["crop"] = false;
        }
        
        self::processInpBefore($label, $name, $value, $args, $wrapRequired);
        
        //Writing <input type="file" .../>
        $args["type"]="file";
        $args["id"]="inut-file-".$name;
        $args["data-process-url"] = ADD . 'helpers/img/process.php';
        $args["data-settings"] = urlencode(json_encode(img::$setup));
        $args["onchange"]="prepareImageBeforeCorp(this,1,'".img::$setup["crop"]."');";
        self::tag($value, "input", $args);
        
        self::tag((empty(self::$error[$name]) ? "" : self::$error[$name][0]),"span",array("class"=>"err"));
        
        self::input("",array("type"=>"hidden","id"=>("image-name-". $name),"name"=>$name."-filename"));
        
        //self::tag($imageUnderMsg,"span",array("class"=>"inp-img-after-message"));
		
        self::tag('', 'span', array('id' => 'iframe-messages-' . $name, "class"=>"inp-img-iframe-message"));		
        
     
		if(!empty($value)) {
			$original_img =  WP . img::$setup["path"] . $value;
            form::openA(array("onclick"=>"showUploadedImagePop(this)","class"=>"inp-img-preview-lnk"));
                form::tag("","img",array("src"=>$original_img,"id"=>"gallery-img-".$name, "style"=>"max-height:".img::$setup["inp-thumb-max-height"]."px"));
            form::closeA();

            if(!isset(img::$setup["disable-delete"]) || img::$setup["disable-delete"]===false){
                form::openSpan(array("class"=>"img-delete"));
                    self::$inpFieldContainer = false;
                    self::inpField("Remove file", "removeImage-".$name, "yes", array("type"=>"checkbox"));
                    self::$inpFieldContainer = true;
                form::closeSpan();
            }
            /*
			if(!empty($args['original_img'])) {
				$original_img = $args['original_img'];				
			} else if($big_wp_dir) {
				$original_img = $big_wp_dir . $value;
			} 
				
			?>
			<a class="inp-img-preview-lnk" href="javascript:void(0);" onclick="showUploadedImagePop(this)"><img id="gallery-img-<?=$name?>" alt="" src="<?=$original_img?>" style="max-height:<?=img::$setup["inp-thumb-max-height"]?>px;"/></a>
			<?php			
			if(empty($args['disable_delete'])) {
			?>
			<label for="id-removeImage-<?=$name?>" >Remove file</label>
			<input type="checkbox" id="id-removeImage-<?=$name?>" name="removeImage-<?=$name?>" value="yes" />
			<?php
			}
		
			$v_r = explode('/', $value);
			if(!empty($v_r) && is_array($v_r)) {
				$image = array_pop($v_r);
				if(strrpos($image, '.') !== false) {
					self::inpHidden("",'old_image_name_' . $name, $image);
				}
			}*/
			
		} else {
            form::openA(array("onclick"=>"showUploadedImagePop(this)","class"=>"inp-img-preview-lnk"));
                form::tag("","img",array("id"=>"gallery-img-".$name, "style"=>"display:none; max-height:".img::$setup["inp-thumb-max-height"]."px"));
            form::closeA();
			/*?><a class="inp-img-preview-lnk" href="javascript:void(0);" onclick="showUploadedImagePop(this)"><img id="gallery-img-<?=$name?>" style="display:none; max-height:<?=img::$setup["inp-thumb-max-height"]?>px;"/></a><?php*/
		}
        if($wrapRequired){
            self::closeTag("div");
        }
        self::closeTag("div");


    }

    /** Description: Writes <label>$label: </label><input type="text" id="$name" {$args}/><input type="hidden" name="$name" vale="$value"/>
     *  @uses jQuery Datapicker (jquery and jqueryUI)
     *  @link https://jqueryui.com/datepicker/ jQuery Datapicker
     *  @param string   $label
     *  @param string   $name
     *  @param string   $value
     *  @param array   $args - tag attributes: (default) "type"=>"text" */
    static public function inpCal($label, $name, $value, $args = array()){
        /* Will do curently required preparation is executed before the current method call.
         * this method require argument "inpCal" or array("inpCal",...) to be passed to form::prepare
         * in the page's $page::prepareForPageHead */
        self::prepare(true); 
        $args["readonly"]=true;
        $args["id"]=$name;
        $args["class"]="calendar-fld";
        $args["onblur"]="requireCal(this)";
        
        if (!empty($value) || filter_input(INPUT_POST, $name)!==null) {
            $value = self::prepareDataValue($name, $value);
        }
        
        $hiddenArgs = array("id"=>"hidden-".$name, "name"=>$name, "value"=>$value, "type"=>"hidden", "data-calendar"=>true);
        
        if(isset($args["format"])){
            $format = $args["format"];
            unset($args["format"]);
        }
        $hiddenArgs["data-format"] = isset($format) ? $format : str_replace(array("d","m","Y"),array("dd","mm","yy"),USER_DATE);
        
        if(isset($args["clear"]) && $args["clear"]===false ){
            unset($args["clear"]);
            $hiddenArgs["data-clear"] = "false";
        } else {
            $hiddenArgs["data-clear"] = true;
        }
        
        self::inpField(
            $label,
            $name,
            "",
            $args
        );
        self::tag("","input",$hiddenArgs);
        self::tag("var fld=document.getElementById(\"{$name}\").parentNode; var fldSpans=fld.getElementsByTagName(\"span\"); var errSpan=fldSpans[fldSpans.length-1]; fld.insertBefore(document.getElementById(\"hidden-{$name}\"),errSpan); document.getElementById(\"{$name}\").setAttribute(\"name\",null); if(document.getElementById(\"{$name}\").hasAttribute(\"required\")){document.getElementById(\"hidden-{$name}\").setAttribute(\"required\",true); /*document.getElementById(\"{$name}\").removeAttribute(\"required\");*/}","script");
    }
    
    /** Tags (aliases, sernums) selector
     * @param str $label
     * @param str $name
     * @param array $tags
     * @param arr $args */
    static public function inpTags($label = '', $name = '', $tags = array(), $args = array()) {
        self::prepare(true);

        if(self::$inpFieldContainer!==true){
            die(__METHOD__.": The field container cannot be stopped for the form::inpTags() calls.");
        } else if(isset($args["type"])){
            die(__METHOD__.": Invalid \$args[\"type\"] call. The form::inpTags() method cannot accept custom types.");
        }
        if(isset($args["fld-class"])){
            $args["fld-class"] .= " inptags";
        } else {$args["fld-class"] = "inptags"; }
        
        $wrapRequired = (bool)isset($args["wrap-class"]);
        
        self::processInpBefore($label, $name, "", $args, $wrapRequired);
        
        if(!is_array($tags)){
            die("ERROR (".__METHOD__."): options provided for $name are not valid array.");
        } else {
            $args["multiple"]=true;
        }
        form::openTag("span",array("class"=>"fld-input"));
            self::tag("","textarea",$args);
            form::tag("Enter an alias and press \"Enter\" to add one more item.","span",array("class"=>"textarea-after"));

            $options=$validSpans=array(); $counter=0;
            foreach($tags as $v) {
                $options[] = self::tagOut($v, "option",array("selected"=>true));
                $lnk = self::tagOut("<i class=\"fa fa-times\"></i>","a",array("onclick"=>"inpTags.removeMe(this,{$counter});","title"=>"Remove"));
                $validSpans[] = self::tagOut($v."&nbsp;".$lnk, "span");
                $counter++;
                //self::tag($v.' <a href="javascript:void(0);" onclick="inpTags.killMe(this);">x</a>','span',array("id"=>$name."-itvspan-".$counter));
            }
            $selArgs = array("name"=>$name.(substr($name,-2)!="[]"? "[]" : ""),"multiple"=>true, "data-counter"=>$counter);
            self::tag(implode("",$validSpans),"span",array("class"=>"valid-tags"));
            self::tag("","span",array("class"=>"invalid-tags"));
            self::tag(implode("\n",$options), "select", $selArgs);
        form::closeTag("span");//,array("class"=>"fld-input")
        self::processInpAfter($name, false, $wrapRequired);
	}
    
    /** Tags (aliases, sernums) selector
     * @param str $label
     * @param str $name
     * @param arr $args
     * @param null|int $minYear -> 1920|(int)$minYear,
     * @param null|int $maxYear -> ((int)date("Y")-18)|(int)$maxYear */
    static function inpDateSelects($label = "", $name = "", $value = "", $args = array(), $minYear=null, $maxYear=null ){
        $wrapRequired = (bool)isset($args["wrap-class"]);
        
        $args["type"] = isset($args["type"]) ? $args["type"] : "text";
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }
        
        self::processInpBefore($label, $name, $value, $args, $wrapRequired);
        //cf::vdd($args);


        $options = array();
        $options["month"] = array("{!--Empty--!}"=>"Month",1=>"January", 2=>"February", 3=>"March", 4=>"April", 5=>"May", 6=>"June", 7=>"July", 8=>"August", 9=>"September", 10=>"October", 11=>"November", 12=>"December");
        $options["date"] = array("{!--Empty--!}"=>"Date")+array_combine(range(1,31),range(1,31));
        $minY = (int)$minYear>0 ? (int)$minYear : 1920;
        $maxY = (int)$maxYear>0 ? (int)$maxYear : ((int)val::currentDate("Y")-18);


        $options["year"] = array("{!--Empty--!}"=>"Year")+array_combine(range($minY, $maxY),range($minY, $maxY));
        
        if( filter_has_var(INPUT_POST, ($name."-year")) || filter_has_var(INPUT_POST, ($name."-month")) || filter_has_var(INPUT_POST, ($name."-date"))){
            $value = array(filter_input(INPUT_POST,$name."-year"), filter_input(INPUT_POST,$name."-month"), filter_input(INPUT_POST,$name."-date"));
        } else if(!empty($value)) {
            $value = explode("-",$value);
        } else {
            $value = null;
        }
        list($dt["year"], $dt["month"], $dt["date"]) = $value;
        foreach($options as $type=>$list){
            self::openDiv(array("class"=>"date-fld"));
                $selArgs = array("name"=>$name."-".$type);
                if(isset($args["class"])){
                    $selArgs["class"] = $args["class"];
                }
                if(isset($args["required"])){
                    $selArgs["required"] = true;
                    $selArgs["data-exclude"] = "{!--Empty--!}";
                }
                self::openTag("select", $selArgs);
                foreach ($list as $k => $v) {
                    $arr_opt = array("value" => $k);
                    if ($k == $dt[$type]) {
                        $arr_opt["selected"] = "";
                    }
                    self::tag($v, "option", $arr_opt);
                }
                self::closeTag("select");
                form::tag((empty(self::$error[$name."-".$type]) ? "" : self::$error[$name."-".$type][0]),"span",array("class"=>"err"));
            self::closeDiv();
        }
        self::processInpAfter($name, $requiredErrorTag, $wrapRequired);
    }
    
    /** Description: Writes <label>$listLabel: </label><label><input name="$name" {$args}/></label><label>...
     *  @param null|string  $listLabel
     *  @param array|str    $nameLblArrOrNameStr <- array("fld1"=>"Add field 1", "fld2"=>"Add field 2") OR string (for radio)
     *  @param array        $nameValArrOrRadioValueLblArr <- array("fld1"=>"Add field 1", "fld2"=>"Add field 2")
     *  @param array        $args - tag attributes 
     * - >  If $args["type]="radio"
     *      <label>$listLabel: </label>
     *          <label><input type="radio" name="$NameStr" value="$RadioValueLblArr[1]" {$args}/>$RadioValueLblArr[1]</label>
     *          ...
     *          <label><input type="radio" name="$NameStr" value="$RadioValueLblArr[n]" {$args}/>$RadioValueLblArr[n]</label>
     * 
     * - >  If $args["type]="checkbox"
     *      <label>$listLabel: </label>
     *          <label><input type="checkbox" name="$nameLblArr[1]" value="$nameValArr[1]" {$args}/>$nameLblArr[1]</label>
     *          ...
     *          <label><input type="checkbox" name="$nameLblArr[n]" value="$nameValArr[n]" {$args}/>$nameLblArr[1]</label>
     * 
     * - >  If $args["type] is not set
     *      <label>$listLabel: </label>
     *          <label>$nameLblArr[n]<input type="text" name="$nameLblArr[1]" value="$nameValArr[1]" {$args}/></label>
     *          ...
     *          <label>$nameLblArr[n]<input type="text" name="$nameLblArr[n]" value="$nameValArr[n]" {$args}/></label>
     */
    static function inpFieldList($listLabel, $nameLblArrOrNameStr, $nameValArrOrRadioValueLblArr, $args){
        $args["type"] = isset($args["type"]) ? $args["type"] : "text";
        
        //True on $args["type"]="checkbox" OR $args["type"]="radio"
        $hasArgsValue = (bool)array_key_exists("value",$args);
        
        if((bool)array_key_exists("lbl-in-list-class",$args)){
            $lblInListClass = $args["lbl-in-list-class"];
        }

        //Validate arrays start
        if($args["type"]==="radio"){
            $valLblArr = $nameValArrOrRadioValueLblArr;
            $name = $nameLblArrOrNameStr;
            if(!is_array($valLblArr)){
                cf::vdd(__METHOD__." error: the \$valueLblArr has to be array for radio list.");
            } else if(count($valLblArr)===0){
                cf::vdd(__METHOD__." error: the \$valueLblArr cannot be empty for radio list.");
            }            
        } else {
            $nameLabelArr = $nameLblArrOrNameStr;
            $nameValArr = $nameValArrOrRadioValueLblArr;
            $arrays = array("nameLblArr"=>$nameLabelArr, "nameValArr"=>$nameValArr);
            if($hasArgsValue){$arrays["args[\"value\"]"] = $args["value"]; }
            foreach(array("nameLblArr"=>$nameLabelArr, "nameValArr"=>$nameValArr, "args[\"value\"]"=>$args["value"]) as $name => $arr){
                if(!is_array($arr)){
                    cf::vdd(__METHOD__." error: the \$$name has to be array.");
                } else if(count($arr)===0){
                    cf::vdd(__METHOD__." error: the \$$name cannot be empty.");
                }
            }
            if($hasArgsValue){
                if(count($nameLabelArr)!==count(array_intersect_key($nameLabelArr, $nameValArr, $args["value"]))){
                    cf::vdd(__METHOD__." error: the \$nameLblArr, \$nameValArr & \$args[\"value\"] must have fully matching (i.e. equal) keys.");
                }
                $nameDataArr = $args["value"]; unset($args["value"]);
            } else {
                if(count($nameLabelArr)!==count(array_intersect_key($nameLabelArr, $nameValArr))){
                    cf::vdd(__METHOD__." error: the \$nameLblArr & \$nameValArr must have fully matching (i.e. equal) keys.");
                }
            }
        }
        //Validate arrays stop
            
        
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }
        
        
        $wrapRequired = (bool)isset($args["wrap-class"]);
        if(!is_null($listLabel)){
            self::processInpBefore($listLabel, null, null, $args, $wrapRequired);
        }
        if($wrapRequired){
            unset($args["wrap-class"]);
        }
        if(isset($lblInListClass)){
            $args["lbl-class"] = $lblInListClass;
        }
        
        //Disable the self::$inpFieldContainer
        $storeInpFieldContainerState = self::$inpFieldContainer;
        self::$inpFieldContainer = false;
        if($args["type"]==="radio"){
            foreach($valLblArr as $val => $lbl){
                //cf::vd("lbl: ".$lbl.", name: ".$name.", value:".$val.", arg[value]:".$args["value"]);
                self::inpField($lbl, $name, $val, $args);
            }
        } else {
            foreach($nameLabelArr as $nme => $lbl){
                if($hasArgsValue){
                    self::inpField($lbl, $nme, $nameValArr[$nme], (array("value"=>$nameDataArr[$nme])+$args));
                } else {
                    self::inpField($lbl, $nme, $nameValArr[$nme], $args);
                }
            }
        }
        
        //Restore the self::$inpFieldContainer
        self::$inpFieldContainer = $storeInpFieldContainerState;
        
        self::processInpAfter(null, $requiredErrorTag, $wrapRequired);
    }
    
    /** Description: Writes <label>$label: </label><ul><li>...
     *  @param string   $label
     *  @param string   $namePfx
     *  @param array    $optionsKeyTitle - array(1=>"I'm no.1", 2=>"I'm no.2".....)
     *  @param array    $keysActive - array(1,3,...)
     *  @param array    $args - tag attributes 
     *  @param array    $rootsKeys - array(1,10,...) */
    static function inpUlNested($label, $namePfx, $optionsKeyTitle, $keysActive, $args, $rootsKeys){
        $wrapRequired = (bool)isset($args["wrap-class"]);
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }
        self::processInpBefore($label, null, null, $args, $wrapRequired);
        self::div(self::linkOut(null,"View selected", array("class"=>"btn btn-default"/* hidden*/,"onclick"=>"ulnested.SwitchUlViewState(this);"))." &raquo; Selected: <strong class=\"count-selected\">".count($keysActive)."</strong> (from <strong>".count($optionsKeyTitle)."</strong>) items.", array("class"=>"ul-controller"));
        self::codeStyleSkip(true);
        self::openTag("ul", (isset($args["ul-class"])? array("class"=>$args["ul-class"]): null));
        self::codeStyleSkip(false);
        $lisClass = (isset($args["li-class"])? array("class"=>$args["li-class"]): null);
        $checksArgs = array("type"=>"checkbox", "value"=>1, "name"=>$namePfx."-");
        foreach ($optionsKeyTitle as $k => $v) {
            $liClass = (is_null($lisClass)?"":$lisClass." ").("lvl-".(in_array($k, $rootsKeys)?"0":"1"));
            $checkArgs = $checksArgs;
            $checkArgs["name"].=$k;
            if(in_array($k, $keysActive)){
                $liClass.=" checked existing";
                $checkArgs["checked"]=true;
            }
            $check = self::tagOut(null, "input", $checkArgs);
            self::tag($v.$check, "li", array("class"=>$liClass, "onclick"=>"ulnested.SwitchLiState(this);"));
        }
        self::closeTag("ul");
        self::processInpAfter(null, $requiredErrorTag, $wrapRequired);
    }
    
    
    /** Description: Writes <label>$label: </label><input type="text" name="$name" value="value" {$args}/>
     *  @param str   $label
     *  @param str   $name
     *  @param str   $value
     *  @param str   $preContent
     *  @param arr   $args - tag attributes: (default) "type"=>"text" */
    static function inpFieldWithPreContent($label = "", $name = "", $value = "", $preContent=null, $args = array()) {
        $args["type"] = isset($args["type"]) ? $args["type"] : "text";
        if(isset($args["required-error-tag"])){
            $requiredErrorTag = true;
            unset($args["required-error-tag"]);
        } else {
            $requiredErrorTag = false;
        }
        
        $inpToLbl = (bool)(isset($args["type"]) && in_array($args["type"],array("checkbox","radio")));
        $wrapRequired = (bool)isset($args["wrap-class"]);
        
        self::processInpBefore($label, $name, $value, $args, $wrapRequired, $inpToLbl);
        is_string($preContent) ? self::html($preContent) : cf::end("The 4-th parameter () has to be a string.");
        $tag = !isset($args["type"]) || !in_array($args["type"],self::$nonInputFlds) ? "input" : $args["type"];
        $innerHtml = in_array($tag, self::$nonInputFlds) && isset($args["value"]) ? $args["value"] : "";
        if(in_array($tag, self::$nonInputFlds)) {
            unset($args["type"], $args["value"]);
        } 
        if($tag==="textarea"){$codeOffset = self::$ofsetIndentsCount; self::$ofsetIndentsCount=0;}
        
        
        if(!isset($args["type"]) || $args["type"] != "select"){
            self::tag($innerHtml, $tag, $args);
        } else {
            $options = isset($args["options"]) && is_array($args["options"]) ? $args["options"] : cf::end(__METHOD__." requires array in \$args[\"options\"] call. " ,2);
            self::openTag("select", $args);
            foreach ($options as $k => $v) {
                $arr_opt = array("value" => $k);
                if ($k == $value || (isset($args["value"]) && $k==$args["value"])) {
                    $arr_opt["selected"] = "";
                }
                self::tag($v, "option", $arr_opt);
            }
            self::closeTag("select");
        }
        
        if($tag==="textarea"){ self::$ofsetIndentsCount=$codeOffset; }
        self::processInpAfter($name, $requiredErrorTag, $wrapRequired, $inpToLbl, $label);
    }
    
    
            /**Writes list of checkboxes as a single array input -> $name."[]"
             * @param string $label
             * @param string $name
             * @param array() $options
             * @param type $values
             * @param array $args */
            static function inpCheckList($label = "", $name = "", $options = array(), $values = array(), $args = array()){
                $args["type"] = isset($args["type"]) ? $args["type"] : "checkbox";
                $requiredErrorTag = true;


                $wrapRequired = (bool)isset($args["wrap-class"]);

                self::processInpBefore($label, $name, "", $args, $wrapRequired);

                if(empty($values)) {
                    if(filter_has_var(INPUT_POST, $name)) {
                        $values = filter_input(INPUT_POST, $name, FILTER_DEFAULT, FILTER_FORCE_ARRAY);
                        if($values === false) {
                            $values = array();
                        }
                    }
                }

                $checked = empty($values) ? array() : $values;
                if(!is_array($checked)){ $checked = explode(",",$checked);}

                if(!is_array($options)){ 
                    cf::vdd(__METHOD__." error: An array is expected for the options value;"); 
                } else {
                    if($args["type"] === "radio") {
                        $checkArgs = array("type"=>$args["type"], "name"=>$name);
                    } else {
                        $checkArgs = array("type"=>$args["type"], "name"=>$name."[]");
                    }

                    if(isset($args["check-lbl-class"])){
                        $checkArgs["check-lbl-class"] = $args["check-lbl-class"];
                    }
                    if(isset($args["data-min"])){
                            $checkArgs["data-min"] = $args["data-min"];
                    }

                    foreach($options as $k=>$v){
                        $chCallArgs = $checkArgs;
                        $chCallArgs["value"]=$k;
                        if(in_array($k,$checked)){
                            $chCallArgs["checked"]=true;    
                        }
                        self::inpBootstrapCheck($name, $v, $chCallArgs);
                    }
                }

                self::processInpAfter($name, $requiredErrorTag, $wrapRequired);
            }
            private static function inpBootstrapCheck($name, $label, $args){
        $lblArgs = array("class"=>"check-lbl");
        if(isset($args["check-lbl-class"])){
            $lblArgs["class"].=" ".$args["check-lbl-class"];
            unset($args["check-lbl-class"]);
        }
        form::openTag("label", $lblArgs);
            form::tag("","input",$args);
            form::html($label);
        form::closeTag("label");
    }
}