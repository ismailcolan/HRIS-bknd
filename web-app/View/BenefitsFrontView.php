<?php
class BenefitsFrontView extends AppView{
	
	
	public static function InitBenefits(){
		include __DIR__ ."/../webapp-html/start-benefit(header).html";
		echo '<body>';
		
		echo 
		'
		<div style="background-color: #243342;">
		<iframe id="myiframe" frameborder=0  scrolling="no"  src="https://boonchapman.benselect.com/nypelvicpain"
		style="width:100%;min-width: 100%;height:10000px;padding:0; margin:0;background-color:#FFF;border-top:1px solid #DDD;">
		</iframe>
		</div>';
		/**********/
		echo "
		";
		/*******/
		include __DIR__ ."/../webapp-html/stop-benefits(footer).html";

	}	
}