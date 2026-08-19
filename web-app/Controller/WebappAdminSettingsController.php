<?php

class WebappAdminSettingsController extends AppController {

    

    const __DUMP__ = false;

    public static $id, $data, $pg;

    

    static function Settings(){
        RoleHelper::requireAdmin();
        msg::get();

        WebappAdminSettingsModel::ValidateSettings("client");

        WebappAdminSettingsView::Settings(self::$data, self::$pg);

    }

    static function SettingsSave(){
        RoleHelper::requireAdmin();
        try{ WebappAdminSettingsModel::ValidateSettings("server"); } catch (Exception $e) {return self::Settings();}

        

        db::Save(WebappAdminSettingsModel::saveQueries(), self::$id, null, self::__DUMP__) ? msg::set(TXT_SAVE_OK,"success") : msg::set(TXT_SAVE_ERROR,"error");

        

        cf::Redirect(self::$pg["settings"]["url"]);

    }

}