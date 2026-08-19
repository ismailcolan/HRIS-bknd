<?php
/** The CompanyOffice controller */
class PayrollController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function Payroll(){
        msg::get();
        PayrollView::Payroll();
    }

}