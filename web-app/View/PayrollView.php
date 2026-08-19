<?php
/** The CompanyOffice view */
class PayrollView extends AppView {
   
    /** Shows a controller details view
     * @param type $data - controller::$data
     * @param type $lbls - model::$fldLbl 
     * @param type $lists - lists generated in the model */
    final static function Payroll(){
			echo "
			<div class='col-sm-12' style='background-color:white'>
			<br>
			<a target='_blank' class='btn btn-primary' 
			href='https://workforcenow.adp.com/workforcenow/login.html?TYPE=33554433&REALMOID=06-dc2a3c54-b061-101f-ae21-844af8f80cb3&GUID=&SMAUTHREASON=0&METHOD=GET&SMAGENTNAME=-SM-HTPqgOarGz%2b4Qz5KZXibVabQA8Iyhr1m2SpSov%2bOQz11zFsMcZvl2ldo2O94pp7C&TARGET=-SM-https%3a%2f%2fworkforcenow%2eadp%2ecom%2ftheme%2fadmin%2ehtml#'>
			Go to ADP</a>
			<br>
			<br>
			<div class='alert alert-warning'>
			<i class='fa fa-exclamation-triangle' aria-hidden='true'></i>
			<b>DISCLAIMER:</b> By clicking on this you are leaving the HRIS website and are going to a website that is not operated by KALHealth, LLC. We are not responsible for the content or availability of linked sites. 
KALHealth, LLC is not responsible for the content of linked third party websites. We are not an agent for these third parties nor do we endorse or guarantee their products. We make no representation or warranty regarding the accuracy of the information contained in the linked sites. We suggest that you always verify the information obtained from linked websites before acting upon this information.
<br>
Also, please be aware that the security and privacy policies on these sites may be different than KALHealth's policies, so please read third party privacy and security policies closely.
<br>
If you have any questions or concerns about the products and services offered on linked third party websites, please contact the third party directly.		
</div></div>";
    }
}
