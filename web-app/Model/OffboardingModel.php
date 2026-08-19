<?php
class OffboardingModel extends OnboardingModel
{
	static function init(){
		self::$dt["controller"] = "OffboardingController";
		self::$type='off';
	}
}
OffboardingModel::init();