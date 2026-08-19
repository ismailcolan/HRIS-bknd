<?php
class OffboardingFrontController extends OnboardingFrontController
{
 public static function OffBoardingTasks(){
	 self::OnBoardingTasks();
 }

 public static function init(){
	 self::$type = 'off';
 }
 
 
 
}
OffboardingFrontController::init();