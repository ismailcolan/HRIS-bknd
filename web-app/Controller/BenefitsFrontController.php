<?php
class BenefitsFrontController extends AppController{

    const __DUMP__ = false;

    public static $id, $data, $pg;
	
	public static function InitBenefits(){
	
		BenefitsFrontView::InitBenefits();
	}
	
	public static function HeaderBenefits(){
		BenefitsFrontView::HeaderBenefits();
	}
	
	
}