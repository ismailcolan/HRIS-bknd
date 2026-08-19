<?php

/** The CompanyOffice controller */
class ResourcesController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;

    final static function All()
    {
        msg::get();
        $cond = "`resActive`=\"" . (self::$rq === "list" ? "Yes" : "No") . "\"";
        ResourcesView::All(self::$pg, ResourcesModel::query($cond, ["resImportant" => "ASC", "resTitle" => "ASC"]), ResourcesModel::$fldLbl, self::$rq);
    }

    static function Summary()
    {
        msg::get();
        ResourcesView::Summary(self::$data, self::$pg, ResourcesModel::$fldLbl);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[ResourcesModel::$dt["pfx"] . "Active"] != "No" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function Details($lists = array())
    {
        ResourcesModel::Detailsvalidate("client", $lists);
        self::$data["files"] = ResourcesModel::getResourceItems(self::$id, "File");
        self::$data["links"] = ResourcesModel::getResourceItems(self::$id, "Link");

        ResourcesView::Details(self::$data, self::$pg, ResourcesModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array())
    {
        $oldId = self::$id;
        try {
            ResourcesModel::DetailsValidate("server", $lists);
        } catch (Exception $e) {
            return self::Details();
        }
        if (db::Save(ResourcesModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__)) {

            $q = "delete from res_cats_link where rclRID=" . self::$id;
            db::SQL($q);

            $cats = filter_input(INPUT_POST, "resCategories", FILTER_DEFAULT, FILTER_FORCE_ARRAY);
            if (is_array($cats)) {
                if (!empty($cats)) {
                    foreach ($cats as $catID) {
                        $q = sprintf("insert into res_cats_link (rclRID, rclRCID) values (%d, %d)", self::$id, $catID);
                        db::SQL($q);
                    }
                }
            }

            msg::set(TXT_SAVE_OK, "success");
        } else {
            msg::set(TXT_SAVE_ERROR, "error");
        }

        if (!empty($oldId)) {
            cf::Redirect(ADMIN . self::getBackUrl()["url"]);
        } else {
            cf::Redirect(self::$pg["details"]["url"] . "?id=" . self::$id);
        }
    }

    public static function uploadResourceFile()
    {
        $error = "Please select a file!";

        if (isset($_FILES["newResourceFile"])) {
            if (!empty($_FILES["newResourceFile"]) and (!empty($_FILES["newResourceFile"]["name"]))) {
                $error = "";
                $allowed = array('pdf');
                
                //$filename = $_FILES["newResourceFile"]['name'];
                $filename = str_replace(" ", "_", $_FILES["newResourceFile"]['name']);
                
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                if (!in_array($ext, $allowed)) {
                    $error = 'Invalid file format!';
                } else if ($_FILES["newResourceFile"]["size"] > 20971520) {
                    $error = "File size exceeds 20MB!";
                }
            }
        }

        if (empty($error)) {
            $q = sprintf(
                "insert into resource_items(riRID, riType, riName, riActive, riAdded, riUpdated) 
                          values(%d, 'File', '%s', 'Yes', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                self::$id,
                 $filename
            );
            if (db::SQL($q)) {
                $fileID = db::LastID();
                $resDir = "../add/uploads/resources/file_" . $fileID;
                move_uploaded_file($_FILES["newResourceFile"]["tmp_name"], $resDir);
                $result = ["result" => "OK", "html" => ResourcesView::renderFilesList(ResourcesModel::getResourceItems(self::$id, "File"))];
            } else {
                $result = ["result" => "error", "html" => "Failed to save file!"];
            }
        } else {
            $result = ["result" => "error", "html" => $error];
        }

        echo json_encode($result);
        die();
    }

    public static function removeResourceFile()
    {
        $riID = intval(filter_input(INPUT_GET, "riID", FILTER_SANITIZE_NUMBER_INT));
        $file = "../add/uploads/resources/file_" . $riID;

        db::SQL("delete from resource_items where riID=" . $riID . " and riType='File'");
        if (file_exists($file)) {
            unlink($file);
        }

        echo ResourcesView::renderFilesList(ResourcesModel::getResourceItems(self::$id, "File"));
        die();
    }


    public static function addResourceLink()
    {
        $error = "Please enter a link!";
        if (filter_has_var(INPUT_POST, "newResourceLink")) {
            $link = trim(filter_input(INPUT_POST, "newResourceLink", FILTER_SANITIZE_STRING));
            if (!empty($link)) {
                $error = "";
            }
            $text = trim(filter_input(INPUT_POST, "newResourceLinkText", FILTER_SANITIZE_STRING));
        }

        if (empty($error)) {
            $q = sprintf(
                "INSERT INTO resource_items(riRID, riType, riName, riText, riActive, riAdded, riUpdated) 
                          values(%d, 'Link', '%s','%s', 'Yes', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                self::$id,
                $link,
                $text
            );
            if (db::SQL($q)) {
                $result = ["result" => "OK", "html" => ResourcesView::renderLinksList(ResourcesModel::getResourceItems(self::$id, "Link"))];
                //$result = $result + ["URL" => "OK","html" => ResourcesView::renderLinksList(ResourcesModel::getResourceItems(self::$id, "URL"))];//<<<PV
            } else {
                $result = ["result" => "error", "html" => "Failed to add link!".$q];
            }
        } else {
            $result = ["result" => "error", "html" => $error];
        }

        echo json_encode($result);
        die();
    }

    public static function removeResourceLink()
    {
        $riID = intval(filter_input(INPUT_GET, "riID", FILTER_SANITIZE_NUMBER_INT));

        db::SQL("delete from resource_items where riID=" . $riID . " and riType='Link'");

        echo ResourcesView::renderLinksList(ResourcesModel::getResourceItems(self::$id, "Link"));
        die();
    }

    public static function Resources()
    {
        $catID = intval(filter_input(INPUT_GET, "cat", FILTER_SANITIZE_NUMBER_INT));
        $data = RCategoriesModel::Resources($catID);
        if (empty($data)) {
            $page = new PageFront();
            $page->web404();
            die();
        }
        $data["category"] = RCategoriesModel::dataById($catID);
        ResourcesView::Resources($data);
    }

    public static function Categories()
    {
        $data = RCategoriesModel::AllActiveData();
        RCategoriesView::Categories($data);
    }

    
}