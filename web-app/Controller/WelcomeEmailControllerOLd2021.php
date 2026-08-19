<?php
/** The CompanyOffice controller */
class WelcomeEmailController extends AppController {

    const __DUMP__ = false;

    public static $id, $data, $pg;
	
	 public static function deleteTemplate(){
		 
		 $id=intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
		 if($id > 0){
			 db::SQL('DELETE FROM email_template WHERE emtID='.$id);
			 db::SQL('UPDATE positions SET pWelcomeEmail=0 WHERE pWelcomeEmail='.$id);
			 msg::set('Template was deleted. All position with that template have no more assigned templates ','success');
		 }
		 else msg::set('There was s problem with template id #'.$id,'error');
		 
		 cf::Redirect(ADMIN .'/welcomeemail');
	 }
	

    public static function getEmptyWelcomeEmails()
    {  
		$res = db::LoadDataFromTable('positions', 'pID <> 0 AND pWelcomeEmail=0', '' );
		$count=count($res);
		
		$s=array();
		if (!empty($res)){
		foreach ($res as $r){
			$s[]='<a href="admin/position-details?id='.$r['pID'].'">'.$r['pName'].'</a>';
		}
		}
		$ret=array();
		$ret['count']=$count;
		$ret['positions']=implode(', ',$s);
		if ($ret['positions']=='') $ret['count']=0;
		return $ret;
		
    }
	
	 public static function getWelcomeEmailsTemplate()
    {  
		$res = db::LoadDataFromTable('email_template', '', '' );
		
		if (empty($res)) return array();
		$count=count($res);
		$s=array();
		foreach ($res as $r){
			$s[$r['emtID']]=$r['emtName'];
		}
		
		return $s;
    }
	
	
	final static function All()
    {
		if (!isset($_GET['call'])){
			$r=WelcomeEmailController::getEmptyWelcomeEmails();
			if ($r['count']!=0) msg::set("There's no Welcome Email for the following positions: ".$r['positions'],'error');
		   
			msg::get();
		}
		
       WelcomeEmailView::All(self::$pg, 
		WelcomeEmailModel::query('', array()), WelcomeEmailModel::$fldLbl, self::$rq);
    }

    public static function getBackUrl()
    {
       // $url = (self::$data[WelcomeEmailModel::$dt["pfx"] . "Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
		 $url =  self::$pg["list"]["url"];
      
        $params = array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function Details($lists = array())
    {	 ckeditor::prepare();
        WelcomeEmailModel::Detailsvalidate("client", $lists);
        WelcomeEmailView::Details(self::$data, self::$pg, WelcomeEmailModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array())
    {
        try {
            WelcomeEmailModel::DetailsValidate("server", $lists);
        } catch (Exception $e) {
			  self::$data=$_POST;
            return self::Details();
        }


        db::Save(WelcomeEmailModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? 
		    msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");

        cf::Redirect(ADMIN . self::$pg["list"]["url"]);
    }


   
}