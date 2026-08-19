<?php
/** The CompanyOffice controller */
class FileCategoriesController extends AppController {

    const __DUMP__ = false;

    public static $id, $data, $pg;

    final static function All()
    {
       msg::get();
       FileCategoriesView::All(self::$pg, 
			FileCategoriesModel::query('', array()), FileCategoriesModel::$fldLbl, self::$rq);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[FileCategoriesModel::$dt["pfx"] . "Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function Details($lists = array())
    {
        FileCategoriesModel::Detailsvalidate("client", $lists);
        FileCategoriesView::Details(self::$data, self::$pg, FileCategoriesModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array())
    {
        try {
            FileCategoriesModel::DetailsValidate("server", $lists);
        } catch (Exception $e) {
            return self::Details();
        }


        db::Save(FileCategoriesModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? 
		    msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");

        cf::Redirect(ADMIN . self::$pg["list"]["url"]);
    }

    public static function Delete()
    {

        if (filter_has_var(INPUT_POST, "move-btn")) {
            $oldID = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
            $newID = filter_input(INPUT_POST, "moveToRCID", FILTER_SANITIZE_NUMBER_INT);

            if (($oldID) && ($newID)) {
                $q = sprintf('update files_cats_link set fclRCID=%d where fclRCID=%d', $newID, $oldID);
                db::SQL($q);
                self::$data["fcResCount"] = 0;
                return self::Details();
            }
        }

        if (self::$data["fcResCount"] == 0) {
            parent::Delete();
        } else {
            // cf::Redirect(ADMIN . self::$pg["details"]["url"] . "?id=" . self::$id);
            self::Details();
        }
    }

    public static function RemoveResources() 
    {
        $resIDs = [];
        $resources = FileCategoriesModel::Resources(self::$id);
        foreach($resources as $res) {

            $files = ResourcesModel::getResourceItems($res["resID"]);

            foreach($files as $file) {
                $fn = "../add/uploads/resources/file_".$file["riID"];
                if(file_exists($fn)) {
                    unlink($fn);
                }
            }        

            $resIDs[] = $res["resID"];
        }

        $idList = implode(", ", $resIDs);
        $q = "delete from res_cats_link where rclRID in ({$idList})";
        db::SQL($q);

        $q = "delete from resource_items where riRID in ({$idList})";
        db::SQL($q);

        $q = "delete from resources where resID in ({$idList})";
        db::SQL($q);

        msg::put("The resources were successfully deleted.");

        cf::Redirect(ADMIN . self::$pg["details"]["url"]."?id=".self::$id);
    }
	
	
	public static function downloadFile(){
        $id = intval(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT));
        
        $rec = db::PrepareLoadSingleRow("select * from  fcfilesbyuser where 	fcfbuID={$id}");
        if($rec === false) {
            $rec = [];
        } 
        if(empty($rec)) {
            die("No acces to such file");
        } else {
            $filePath = FS_ROOT."add/customer".$_SESSION['customerID']."/uploads/files/".$rec['fcfbuFileSavedAs'];//'.'.$rec['fcfbuFileЕxt'];
			//cf::pr($rec);echo '>>>'.$filePath;exit;
            if(file_exists($filePath)) { 
			    $name=$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'];
                header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
                header("Cache-Control: public");
                header("Content-Type: application/binary");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length:".filesize($filePath));
                header('Content-Disposition: attachment; filename="'.$rec['fcfbuFileName'].'.'.$rec['fcfbuFileЕxt'].'"');
                readfile($filePath);
                die();
            } else {            
                die("No such file");
            }
        }
    }
	
	
	public static function getVisibleFilesForEmployee(){
		
		$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
		$data=db::PrepareLoad(
		'SELECT * FROM  fcfilesbyuser LEFT JOIN fcategories ON   fcID = fcfbuFileCatID  
		WHERE fcfbuVisible = "visible" AND fcfbuUserID='.$id.' AND fcActive="Yes"  ORDER BY fcName ASC,fcfbuFileName ASC');
		
		$r=array();
		if (!empty($data)) {
		foreach ($data as $k=>$d){
			$r[$d['fcName']][] = 
			array('id'=>$d['fcfbuID'],'saved_as'=>$d['fcfbuFileSavedAs'],
			'name'=>$d['fcfbuFileName'], 'ext'=>$d['fcfbuFileЕxt'],
			'state'=>$d['fcfbuVisible']);
		}
		}
		return $r;
	}
	
	public static function getFilesForEmployee(){
		
		$id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
		$data=db::PrepareLoad(
		'SELECT * FROM  fcfilesbyuser LEFT JOIN fcategories ON   fcID = fcfbuFileCatID  WHERE fcfbuUserID='.$id.' AND fcActive="Yes"  ORDER BY fcName ASC,fcfbuFileName ASC');
		
		$r=array();
		if (!empty($data)) {
		foreach ($data as $k=>$d){
			$r[$d['fcName']][] = 
			array('id'=>$d['fcfbuID'],'saved_as'=>$d['fcfbuFileSavedAs'],
			'name'=>$d['fcfbuFileName'], 'ext'=>$d['fcfbuFileЕxt'],
			'state'=>$d['fcfbuVisible']);
		}
		}
		return $r;
	}
	
	public static function moveFilesToCategory(){
		//print_r($_POST);exit;
		$cat_id=intval(filter_input(INPUT_POST, "moveToRCID", FILTER_SANITIZE_NUMBER_INT));
		$old_cat_id = intval(filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT));
		if ($cat_id == 0 || $old_cat_id == 0 ){
			msg::set('Please select category','error');
		}
	   else {
		   db::SQL('UPDATE fcfilesbyuser SET fcfbuFileCatID ='.$cat_id.'  WHERE fcfbuFileCatID = '.$old_cat_id);
			msg::set('Files were moved to new category','success');
	   }

		cf::redirect('/admin/filecategories-details?id='.$old_cat_id );
	}
	
	public static function deleteFilesFromCategory(){
		$old_cat_id = intval(filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT));
		if ($old_cat_id == 0 ){
			msg::set('Invalid category','error');
		}
		else {
			db::SQL('DELETE FROM  fcfilesbyuser  WHERE fcfbuFileCatID = '.$old_cat_id);
			
			msg::set('Files in category were deleted','success');
			$cat_id=$_POST['cat_id'];
		}
		cf::redirect('/admin/filecategories-details?id='.$old_cat_id);
	}

}