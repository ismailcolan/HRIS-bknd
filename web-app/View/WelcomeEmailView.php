<?php
/** The CompanyOffice view */
class WelcomeEmailView extends AppView {

    final static function getPositionsOfTemplate($id){
		if (! isset($id)) return '';
	   $sql='SELECT pName FROM positions WHERE pWelcomeEmail ='.$id;
	  
	   $r=db::SQL($sql);
	   //$res = $r->fetch_all(MYSQLI_ASSOC);//db::FetchArray($r,MYSQLI_ASSOC);
	   $res=db::PrepareLoad($sql);
	   //print_r($res);
	   $z=array();
	   if (empty($res)) return '';
	   foreach ($res as $k=>$a){
		  $z[]=$a['pName'] ;
	   }
	   return implode(', ',$z);
	   
   }
    /** Shows the controller list table - allowing the type filtering
     * @param array $pg - controller::$pg
     * @param string $query - model::query($cond)
     * @param array $lbls - model::$fldLbl
     */
    final static function All($pg, $query, $lbls, $rq)
    {
		
        table::create($pg["tbl"], $pg["key"], "desc", self::ADMIN_LISTS_ROWS_LIMIT);

			table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Name")], $c);
			//table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Subject")], $c);
			//table::$cols[] = Array($lbls[($c = $pg["pfx"] . "Message")], $c);
			table::$cols[] = Array("Assigned to position(s)",
			   $pg["pfx"]."Assigned to", array("sort" => false));
		   table::$cols[] = Array("", $pg["key"] . "delete", array("class" => "link", "sort" => false));
       
        table::execute($query);
		
        list($view, $edit, $reactivate, $deactivate, $delete) = 
		     self::AllLnksCode($pg, array("view", "edit", "reactivate", "deactivate", "delete"));
			 
        foreach (table::$data as $r => &$cells) {
			//print_r($cells);exit;
          //  if (table::$export === false) {
                $name = htmlspecialchars($cells[$pg["pfx"] . "Name"]);
                $cells[$pg["pfx"] . "Name"] = '<a href="/admin/welcomeemail-details?id=' . $cells[$pg["key"]] . '" title="Edit ' . $name . '">' . $name . '</a>';
				   $cells[$pg["key"] . "delete"] = sprintf($delete, $cells[$pg["key"]]);
			      $cells[$pg["pfx"] . "Assigned to"] = self::getPositionsOfTemplate($cells[$pg["pfx"].'ID']);    
          //  }
			/****
			  if ( strlen($cells[$pg["pfx"] . "Message"])>20 )
			  $cells[$pg["pfx"] . "Message"] =substr($cells[$pg["pfx"] . "Message"],0,20).'...' ;
		    ***/
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
	     global $_TEMPL_ALLOWED,$_TEMPL_ALLOWED_ADDON;
		  msg::get();
		  ckeditor::prepare();
        $btnLabel = "Save";
        if(filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING) === "alert")  {
            msg::put("You cannot delete a category with files it. Please first delete or move files.", "error");    
            $btnLabel = "Delete";        
        }

        $args = self::$fldArgsBase;
		 ///////print_r($args);
		$args['lbl-class'] ='control-label col-sm-4 col-md-2';
        form::codeStyle(true, 1, false);
        form::openTag("div", array("class" => "form-panel"));
        form::$ofsetIndentsCount = 2;
        form::openTag("form", array("method" => "post", "validate" => true, "class" => "form-horizontal"));
		  if(isset($_GET['id'])) $pos=self::getPositionsOfTemplate($_GET['id']);
		  else $pos='';
		  if ($pos!='') $txt = $pos;  else  $txt = "None";
		  
		  
		  
        form::inpField(
            $lbls[($f = $pg["pfx"] . "Name")].":",
            $f,
            $data[$f],
            $args + array("data" => $data[$f])
        );
		
		form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-2">Positions:</label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div style="padding-top:7px">'.$txt.'</div></div></div>');
			
		$s=array();
		foreach ($_TEMPL_ALLOWED as $t){
			$s[]='<a href="javascript:void(0);" style="cursor:hand" onclick="addVar(\''.$t.'\')">'.$t.'</a>';
		}
		$s=implode(', ',$s);
		
		form::inpField(
            $lbls[($f = $pg["pfx"] . "Subject")].":",
            $f,
            $data[$f],
            $args + array("id"=>"subject","data" => $data[$f])
			  //'hint'=>'<br><b>Allowed variables</b> (click to insert).<br>'.$s)
        );
		
		form::html('<div class="fld form-group">
                    <label class="control-label col-sm-4 col-md-2"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5">
                    <div style="padding-top:7px"><span style="font-style: italic"><b>Allowed variables</b> (click to insert).<br>'
					   .$s.'</span></div></div></div>');
			
		
		echo "<script>
		      function addVar(v){ 
					insertAtCursor(document.getElementById('subject'),v);
		      }
        </script>";
		
		/***********************************/
		
		$s1=array();
		
		foreach (array_merge($_TEMPL_ALLOWED,$_TEMPL_ALLOWED_ADDON) as $t){
			$s1[]='<a href="javascript:void(0);" style="cursor:hand" onclick="addEditor(\''.$t.'\')">'.$t.'</a>';
		}
		$s1=implode(', ',$s1);
		/***
		form::html('<div class="fld form-group" >
                    <label class="control-label col-sm-4 col-md-3" ></label>
                    <div class="col-sm-8 col-md-7 col-lg-5" >
                    <div class="">
					   <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>  
			          Allowed variables (click to insert).<br>'
					   .$s1.'</div></div></div>');
		
			
		/***/
		$temps=WelcomeEmailController::getWelcomeEmailsTemplate();
		$s='<select id="temp_id"><option value="0">Select template to copy it in Message field</option>';
		
		if (!empty($temps)){
		foreach ($temps as $id=>$tname){
			$s.='<option value="'.$id.'">'.$tname.'</option>';
		}
		}
		$s.='</select>';
		
		
		form::html('<div class="fld form-group" >
                    <label class="control-label col-sm-4 col-md-2"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5" >
                    <div style="padding-top:7px"><span style="font-style: italic"><b>Allowed variables</b> (click to insert).<br>'
					   .$s1.'</span></div></div></div>');
					   
					   
		form::html('<div class="fld form-group" >
                    <label class="control-label col-sm-4 col-md-2"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5" 
					   style="padding-top:0pading-bottom:0;margin-top:0;margin-bottom:0">
					   <b>Copy from existing template</b><br>'.$s.'
					   &nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" style="cursor:hand" onclick="getTemplate()">Copy template >></a>
                    </div>
					</div>
		<script>
		function getTemplate(){
			var v = jQuery("#temp_id").val();
			if (v ==0 ) myalert("Message","Please select template name first.");
			else goGetTemplate(v);
		}
		
		function goGetTemplate(v){
			$.get( "admin/welcomeemail-details?cmd=templ&call=ajax&tid="+v, function( data ) {
			  	 var d = jQuery.parseJSON(data);
				 
				 if (d["result"]=="OK"){
					 insertHTMLAtCaret(d["text"]);
				 } 
				 myalert("Message",d["msg"]);
				
			});
		}
		</script>
		');
		/***/	
		

		form::html('<div class="fld form-group" >
                    <label class="control-label col-sm-4 col-md-2"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5" 
					   style="padding-top:0pading-bottom:0;margin-top:0;margin-bottom:0">
Press "Shift+Enter" to start a new line without empty line on top.<br>
Press "Ctrl+Shift+V" to paste text without formatting.
					   </div>
					</div>');
		
		$ckSettings = cf::defaultCkSettings();
		//print_r($ckSettings);
		$ckSettings["height"]="200px";	
       $ckSettings["fld-class"]="width-100px";	
	   //print_r($args);
	   $args1=$args;
	   $args1['wrap-class'] = 'col-sm-8 col-md-7 col-lg-9'; 
		form::inpCkEditor(
            $lbls[($f = $pg["pfx"] . "Message")].":",
            $f,
            $data[$f],
            $args1 + array("id"=>"mytext","type"=>"textarea"),
			 $ckSettings
        );
		 if (isset(form::$error['emtMessage'])){
			echo '<script>myalert("Alert","'.form::$error['emtMessage'][0].'");</script>';
		 }
		 echo '<input type="hidden" name="emtID" value="'.$data["emtID"].'">';
		 echo "<script> function addEditor(v){
			 insertAtCaret(v); 
			 return false;}
		 </script>";
		 
				
		   /****		
		  $args = self::$saveCancelArgsBase;
		 
		  form::inpSaveCancel(WelcomeEmailController::getBackUrl()["url"], $args);
		 
		  echo 
		  '<button type="button" onclick="if(confirm(\'Do you really want to delete this resource and connected data?\'))
			  { location.assign(\''.ADMIN.'welcomeemail-details?id='.$data["emtID"].'&cmd=trash\'); } " 
		  class="btn btn-danger">Delete Template</button>';
    	  
		  echo '<div class="fld form-group">
		      <div class="col-sm-8 col-md-7 col-lg-5 col-sm-offset-4 col-md-offset-2">
		     <label class="control-label col-sm-4 col-md-2"></label>
			***/ 
			
			echo' <div class="fld form-group" >
                    <label class="control-label col-sm-4 col-md-2"></label>
                    <div class="col-sm-8 col-md-7 col-lg-5" >
			 
			  <button  type="submit" name="save-btn" value="Save" class="btn btn-primary">Save</button> 
			  <button style="width:100px" type="button" onclick="location.assign(\''.ADMIN.'welcomeemail\')" class="btn btn-default">Cancel</button>
			  <span style="display:inline-block;width:100px"> </span>
			  <button style="width:100px" type="button" onclick="if(confirm(\'Do you really want to delete this email template?\'))
			  {location.assign(\''.ADMIN.
		     'welcomeemail-details?id='.$data["emtID"].'&cmd=trash'.'\')}" 
		      class="btn btn-danger">Delete</button></div></div>';
		  
		  
		  
		  form::closeTag("form");
        form::codeStyle(true, 1, true);
        form::closeTag("div", array("class" => "form-panel"));
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
	


    
}