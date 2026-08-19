<?php
/** The CompanyOffice controller */
class OffboardingController extends OnboardingController {

 static function init(){
	 self::$type='off';
 }
}
OffboardingController::init();