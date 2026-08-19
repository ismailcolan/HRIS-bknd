<?php
/*
How TimeClock Logic works:

There's a cron job running every 5 minutes which gets (from
TimeClockPlus database) whether an employee is at work or not.
If the latest report for an employee is In - then the employee is at
work, if it's Out or no report - then he/she is not at work.

There is another a cron job running once a day. It fetches all
reports of the CURRENT week from TimeClockPlus.
The SELECT query filters out all incorrect reports - ones of
non-hourly employees, ones with incorrect date, etc.
Then we delete all existing reports in HRIS for the current week and
import the new data from TimeClockPlus i.e. we update the entire week in HRIS.
If there are wrong reports detected, an email is sent to the admin.
This way every day we've got up-to-date reports from TimeClockPlus in
HRIS. If there's a change in TimeClockPlus after that, it will be
updated with the next day's update.

Only changes from current week are copied. If a change has been made
in a prior week, it won't be copied over to HRIS.

When the week is over, on Monday morning we run another cron job
which does the same but for the just ended week.
This way we guarantee all changes between the last update on Sunday
and Sunday 11:59 PM are copied too.
Once that script runs, no more data is retrieved from TimeClockPlus
for the just ended week. If any change is made for the ended week
from now on in TimeClockPlus, it won't be copied over to HRIS.

Managers sign off reports only after the Monday's script runs (not earlier).
*/

class TimeClock extends PageFront {

    const CONTROLLER = "TimeClockFrontController";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);

    }

    protected function inHead($C = self::CONTROLLER) {
         msg::prepare();
		 table::prepare();
    }



}

$page = new TimeClock();

$page->HTMLHeader();

TimeClockFrontController::ListWorkingHours();


$page->HTMLFooter();
