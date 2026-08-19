<?php
/** The cron model class */
class CronModel extends AppModel  {
    
    const LOCAL_RUN_INTERVAL_HOURS = 24;
    const LOCAL_RUN_COOKIE_NAME = "local-cron";
    
    static $dataAddons = array(), $dt = array("tbl"=>"crons", "key"=>"crID", "pfx"=>"cr", "controller"=>"CronController");
    static $mainTblFields = array("crType", "crRequest", "crData", "crExecuted");
    static $postOverrided = array();
    static $lastRuns = array("daily"=>null, "weekly"=>null);
    
    static function query($conditions = '', array $orderby = array(), $limit = 0){
        $query = "SELECT * FROM " . self::$dt["tbl"];
        $q = self::PrepareLoadQuery($query, $conditions, $orderby, $limit);

        $optionalAddons = array();
            $optionalAddons["crExecutedFormatted"] = "DATE_FORMAT(crExecuted, '".GLOBAL_MYSQL_DATETIME."') AS `crExecutedFormatted`";
        $addons = array();
        
        foreach(self::$dataAddons as $k){
            $addons[] = (in_array($k, array_keys($optionalAddons)) ? $optionalAddons[$k] : die(__METHOD__.": Incorrect addon request call: ".$k));
        }
		
        return str_replace("*", (empty($addons) ? "*" : ("*,\n".implode(",\n",$addons))), $q);
    }
    
    /**Each run (daily|weekely) has to completed only on own dedicated CronController::$rq method
     * to ensure no missing execution both methods are executed sccessfully we use self::LastRuns()
     * Then non-dedicated runs just will be skipped if they return existing execution data */
    static function Run(){
        self::LastRuns();

        $C = self::$dt["controller"];
        $msgs = array();
        
        if(is_null(self::$lastRuns["daily"]) && in_array($C::$rq, array("daily", "local"))){
            $msgs[] = self::Daily();
        }
        
        if(is_null(self::$lastRuns["weekly"]) && in_array($C::$rq, array("weekly", "local"))){
            $msgs[] = self::Weekly();
        }
        return $msgs;
    }
    private static function LastRuns($applyLimit = true){
        $limit = array();
        $limit["daily"]  = !$applyLimit ? null : " AND `crExecuted` > DATE_SUB(NOW(), INTERVAL ".(int)self::LOCAL_RUN_INTERVAL_HOURS." HOUR)";
        $limit["weekly"] = !$applyLimit ? null : " AND `crExecuted` > DATE_SUB(NOW(), INTERVAL ".((int)self::LOCAL_RUN_INTERVAL_HOURS*7)." HOUR)";
        $dailyQ = "SELECT *, \"daily\" AS `crCurrent` FROM `".self::$dt["tbl"]."` WHERE `".self::$dt["key"]."`=(SELECT MAX(`".self::$dt["key"]."`) FROM `".self::$dt["tbl"]."` WHERE `crType`=\"Daily\"".$limit["daily"].")";
        $weekly = "SELECT *, \"weekly\" AS `crCurrent` FROM `".self::$dt["tbl"]."` WHERE `".self::$dt["key"]."`=(SELECT MAX(`".self::$dt["key"]."`) FROM `".self::$dt["tbl"]."` WHERE `crType`=\"Weekly\"".$limit["weekly"].")";
        //cf::vdd("SELECT * FROM (\n({$dailyQ})\n UNION \n({$weekly})\n) crons ORDER BY `crCurrent` ASC");
        $res = self::PrepareLoad("SELECT * FROM (\n({$dailyQ})\n UNION \n({$weekly})\n) crons ORDER BY `crCurrent` ASC");
        if($res){
            foreach($res as $row){
                self::$lastRuns[$row["crCurrent"]] = $row;
            }
        }
    }
    
    private static function Daily(){
        return "daily-not-set"; 
        /*self::$postOverrided = array("crType"=>"Daily", "crRequest"=>ucfirst(CronController::$rq));
        CronController::$id = null;
        db::Save(self::saveQueries(), CronController::$id, self::$dt["tbl"], false);*/
        
    }
    private static function Weekly(){
        $cronLog = array();
        if(!is_null(($notCheapestPriceActiveProducts = ProductModel::NotCheapeastPriceActiveProducts()))){
            $productsList = array();
            foreach($notCheapestPriceActiveProducts as $p){
                $ppfx = ProductModel::$dt["pfx"];
                $fldLbl = ProductModel::$fldLbl;
                $productsList[] = form::linkOut(ADMIN."product?id=".$p[$ppfx."ID"], $p[$ppfx."Name"])."<br />".
                                  $fldLbl[($f = $ppfx."Path")].": ".$p[$f]."<br />".
                                  $fldLbl[($f = $ppfx."Manufacturer")].": ".$p[$f]." (#".$p[$ppfx."MTag"].")<br />".
                                  "Current price: ".cf::price($p[$ppfx."Price"])." (".$fldLbl[($f = $ppfx."Vendor")].": ".$p[$ppfx."Vendor"].") <br />".
                                  "Lowest price: ".cf::price($p[$ppfx."PriceMin"])." (".$fldLbl[($f = $ppfx."Vendor")].": ".$p[$ppfx."VendorMin"].")";
            }
            $productsText = form::tagOut(implode("</p><hr /><p>", $productsList),"p");
            $msg = sprintf(nl2br(MAIL_ADMIN_CRON_WEEKLY_REMINDER__NOT_CHEAPEST_PRICE_ACTIVE_PRDUCTS_MSG), $productsText);
            cf::sendMail(EMAIL_ADMIN, MAIL_ADMIN_CRON_WEEKLY_REMINDER__NOT_CHEAPEST_PRICE_ACTIVE_PRDUCTS_SUBJ, $msg, SITE_NAME, EMAIL_ADMIN);
            //cf::sendMail(DEV_EMAIL_ADMIN, MAIL_ADMIN_CRON_WEEKLY_REMINDER__NOT_CHEAPEST_PRICE_ACTIVE_PRDUCTS_SUBJ, $msg, SITE_NAME, DEV_EMAIL_ADMIN);
            $cronLog["issent-notcheepestproducts-reminder"] = true;
        } else {
            $cronLog["issent-notcheepestproducts-reminder"] = false;
        }
        
        CronController::$id = null;
        self::$postOverrided = array("crType"=>"Weekly", "crRequest"=>ucfirst(CronController::$rq), "crData"=>json_encode($cronLog));
        
        db::Save(self::saveQueries(), CronController::$id, self::$dt["tbl"], false);

        /*will return date with the cron type:  0000-00-00 00:00:00 (Weekly)*/
        return self::PrepareLoadSingleCellValue("SELECT CONCAT(`crExecuted`,\" (\",`crType`,\")\") FROM `".self::$dt["tbl"]."` WHERE `".self::$dt["key"]."`=".CronController::$id);
    }
}
