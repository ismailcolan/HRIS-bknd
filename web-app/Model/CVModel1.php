<?php
/** The Orders model */
class CVModel extends AppModel {
    
    static $dataAddons = array(), $dt = array("tbl"=>"cvs", "key"=>"cvID", "pfx"=>"cv", "controller"=>"");
    static $mainTblFields = array("cvafID", "cvFileType", "cvFileName");
    static $fldLbl = array();
    static $addonFieldsByTables;
    static $postOverrided = array();
    $cust = $_SESSION['customerID'];
	define ( "UPLOAD_PATH" , "add/customer{$cust}/uploads/");
    const APPLICATION_CV = "application_cv";
    const CURRENT_CV = "current_cv";

    public static function dataById($id = 0, $allLangs = false) {
        $q = 'select * from cvs where cvID='.$id;
        $rec = self::processRecord(db::PrepareLoadSingleRow($q));
        return $rec;
    }

    public static function getCVsForApplication($afID){
        $afid = intval($afID);
        $result = ["application_cv" => "", "current_cv" => ""];
        $q = 'select * from `'.self::$dt["tbl"].'` where `cvafID` = '.$afid;
        $data = db::PrepareLoad($q);
        if(is_array($data)) {
            foreach ($data as $row) {
                $result[$row["cvFileType"]] = self::processRecord($row);
            }
        }
        return $result;
    }

    protected static function addRecord($afID, $fileType, $fileName) {
        $q = 'INSERT INTO `cvs` (cvafID, cvFileType, cvFileName) VALUES (%d, "%s", "%s")';
        $q = sprintf($q, $afID, $fileType, db::EscapeStr($fileName));
        if(db::SQL($q)) {
            return db::LastID();
        } else {
            return false;
        }
    }

    protected static function processRecord($rec){
        if(empty($rec)) {
            $rec = "";
        } else {
            $rec['cvFileName'] = str_replace(' ', '_', $rec['cvFileName']);
            $rec['filePath'] = (cf::$rq['admin-section'] ? '..' . DIRECTORY_SEPARATOR : '') . self::UPLOAD_PATH . $rec["cvFileType"] . $rec['cvID'] . '.file';
            $rec['fileURL'] = WP . 'cvs/'.$rec['cvID'].'/'.$rec['cvFileName'];
        }
        return $rec;
    }

    public static function loadFileRecord($afID, $fileType) {
        $q = 'select * from cvs where cvafID=%d and cvFileType="%s"';
        $q = sprintf($q, $afID, $fileType);
        $rec = self::processRecord(db::PrepareLoadSingleRow($q));
        return $rec;
    }

    public static function removeFileAndRecord($rec) {
        if(!empty($rec)) {
            if(file_exists($rec['filePath'])) unlink($rec['filePath']);
            $q = "delete from cvs where cvID=".$rec['cvID'];
            db::SQL($q);
        }
    }


    public static function validateUploadedFiles(){
        if(filter_has_var(INPUT_POST, 'bioCmd')) {
            if(filter_input(INPUT_POST, 'bioCmd', FILTER_SANITIZE_STRING) === "upload") {
                ApplyFormModel::validateBio('afBio', false);
            }
        } else {
            if (isset($_FILES['afBio']['error']) && !is_array($_FILES['afBio']['error'])) {
                if ($_FILES['afBio']['error'] === UPLOAD_ERR_OK) {
                    ApplyFormModel::validateBio('afBio', false);
                }
            }
        }

        if(filter_has_var(INPUT_POST, 'cvCmd')) {
            if(filter_input(INPUT_POST, 'cvCmd', FILTER_SANITIZE_STRING) === "upload") {
                ApplyFormModel::validateBio('afCurrentCV', false);
            }
        } else {
            if (isset($_FILES['afCurrentCV']['error']) && !is_array($_FILES['afCurrentCV']['error'])) {
                if ($_FILES['afCurrentCV']['error'] === UPLOAD_ERR_OK) {
                    ApplyFormModel::validateBio('afCurrentCV', false);
                }
            }
        }
    }

    public static function processUploadedFiles($afID){
        $oldFiles = self::getCVsForApplication($afID);



        $saveBio = false;
        if(filter_has_var(INPUT_POST, 'bioCmd')) {
            switch (filter_input(INPUT_POST, 'bioCmd', FILTER_SANITIZE_STRING)) {
                case "remove" :
                $rec = self::loadFileRecord($afID, self::APPLICATION_CV);
                self::removeFileAndRecord($rec);
                break;
                case "upload" :
                    $saveBio = true;
                    break;
            }
        } else {
            $saveBio = true;
        }

        if($saveBio) {
            if (isset($_FILES['afBio']['error']) && !is_array($_FILES['afBio']['error'])) {
                if($_FILES['afBio']['error'] === UPLOAD_ERR_OK) {
                    $fileID = self::addRecord($afID, self::APPLICATION_CV, $_FILES['afBio']['name'] );
                    if($fileID) {
                        self::removeFileAndRecord($oldFiles[self::APPLICATION_CV]);
                        $rec = self::loadFileRecord($afID, self::APPLICATION_CV);
                        move_uploaded_file($_FILES["afBio"]["tmp_name"], $rec["filePath"]);
                    }
                }
            }
        }


        $saveCV = false;
        if(filter_has_var(INPUT_POST, 'cvCmd')) {
            switch (filter_input(INPUT_POST, 'cvCmd', FILTER_SANITIZE_STRING)) {
                case 'remove':
                $rec = self::loadFileRecord($afID, self::CURRENT_CV);
                self::removeFileAndRecord($rec);
                break;
                case 'upload':
                    $saveCV = true;
                    break;
            }
        } else {
            $saveCV = true;
        }

        if($saveCV) {
            if (isset($_FILES['afCurrentCV']['error']) && !is_array($_FILES['afCurrentCV']['error'])) {
                if($_FILES['afCurrentCV']['error'] === UPLOAD_ERR_OK) {
                    $fileID = self::addRecord($afID, self::CURRENT_CV, $_FILES['afCurrentCV']['name'] );
                    if($fileID) {
                        self::removeFileAndRecord($oldFiles[self::CURRENT_CV]);
                        $rec = self::loadFileRecord($afID, self::CURRENT_CV);
                        move_uploaded_file($_FILES["afCurrentCV"]["tmp_name"], $rec["filePath"]);
                    }
                }
            }
        }


    }

}
