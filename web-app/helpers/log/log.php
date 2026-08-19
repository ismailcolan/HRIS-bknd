<?php 
/** Description of log */
class log {
    
    private static $logFileName = "php-log.log";
    
    public static function write($msg, $fileName=null) {
        $callers=debug_backtrace();
		 $sLog = val::currentDate("Y-m-d H:i:s") 
		 . " | Class method: "  .  $callers[1]["class"] . "::" . $callers[1]["function"] . " | " . $callers[0]["file"] . " (line: " . $callers[0]["line"] . ") | Message:\n" . $msg . "\n".  str_repeat(". ", 60)."\n";
        $fName = is_null($fileName) ? self::$logFileName : $fileName;
        $fp = fopen((APP_LOGS . $fName), "a");
        fwrite($fp, $sLog);
        fclose($fp);
    }
    /*
    public static function showLog() {
        $options = array(
          'start_time' => (time() - (24 * 60 * 60)) * 1e6,
          'end_time' => time() * 1e6,
          'include_app_logs' => true,
        );

        $logs = LogService::fetch($options);

        foreach ($logs as $log) {
            echo '<br/ ><br /> REQUEST LOG';
            echo '<br /> IP: ' . $log->getIp() .
                 '<br /> Status: ' . $log->getStatus() .
                 '<br /> Method: ' . $log->getMethod() .
                 '<br /> Resource: ' . $log->getResource() .
                 '<br />';
            $end_date_time = $log->getEndDateTime();
            echo 'Date: ' . $end_date_time->format('c') . '<br />';

            $app_logs = $log->getAppLogs();
            foreach ($app_logs as $app_log) {
                echo '<br/ ><br /> APP LOG';
                echo '<br /> Message: ' . $app_log->getMessage() . '<br />';
                $app_log_date_time = $app_log->getDateTime();
                echo 'Date: ' . $app_log_date_time->format('c') . '<br />';
            }
        }
        
    }
    */
}

