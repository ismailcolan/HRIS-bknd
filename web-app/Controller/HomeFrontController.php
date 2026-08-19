<?php
/** The Order controller */
class HomeFrontController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    public static function Home() {
        // Interviewer should never see home - redirect them
        if (RoleHelper::isInterviewer()) {
            cf::Redirect(ADMIN . 'for-interview-applications');
            exit;
        }
        
        // HR Manager and Admin both allowed on home - no redirect needed
        msg::get();
    
        if(filter_has_var(INPUT_GET, "autologin")) {
            $key = db::EscapeStr(filter_input(INPUT_GET, "autologin", FILTER_SANITIZE_STRING));
            $q = 'select * from apply_forms where afKey="'.$key.'"';
            $user = db::PrepareLoadSingleRow($q);

            if($user) {
                $q = 'update apply_forms set afKey=null where afID='.$user["afID"];
                db::SQL($q);

                ProfileModel::signOut();
                unset($_SESSION['login-src-check']);

                ProfileModel::SetUserSession($user);
                cf::Redirect(WP . "profile");
            }
        }

        HomeView::All(self::$pg, self::$rq);
    }

    final static function TitleAndMetaTags() {
        cf::$pg["title"] = "HRI System";
        cf::$pg["description"] = "Human Resource Information System";
        cf::$pg["keywords"] = "human, resource, information, system";
    }
}