<?php

/** The RCategories controller */
class RCategoriesController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;

    final static function All()
    {
        msg::get();
        RCategoriesView::All(self::$pg, RCategoriesModel::query('', ["rcImportant"=>"ASC", "rcName"=>"ASC"]), RCategoriesModel::$fldLbl, self::$rq);
    }

    public static function getBackUrl()
    {
        $url = (self::$data[RCategoriesModel::$dt["pfx"] . "Active"] === "Yes" ? self::$pg["list"]["url"] : self::$pg["inactive-list"]["url"]);
        $params = array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function Details($lists = array())
    {
        RCategoriesModel::Detailsvalidate("client", $lists);
        RCategoriesView::Details(self::$data, self::$pg, RCategoriesModel::$fldLbl, $lists);
    }

    final static function DetailsSave($lists = array())
    {
        try {
            RCategoriesModel::DetailsValidate("server", $lists);
        } catch (Exception $e) {
            return self::Details();
        }

        db::Save(RCategoriesModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");

        cf::Redirect(ADMIN . self::$pg["list"]["url"]);
    }

    public static function Delete()
    {

        if (filter_has_var(INPUT_POST, "move-btn")) {
            $oldID = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
            $newID = filter_input(INPUT_POST, "moveToRCID", FILTER_SANITIZE_NUMBER_INT);

            if (($oldID) && ($newID)) {
                $q = sprintf('update res_cats_link set rclRCID=%d where rclRCID=%d', $newID, $oldID);
                db::SQL($q);
                self::$data["rcResCount"] = 0;
                return self::Details();
            }
        }

        if (self::$data["rcResCount"] == 0) {
            parent::Delete();
        } else {
            // cf::Redirect(ADMIN . self::$pg["details"]["url"] . "?id=" . self::$id);
            self::Details();
        }
    }

    public static function RemoveResources() 
    {
        $resIDs = [];
        $resources = RCategoriesModel::Resources(self::$id);
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

}