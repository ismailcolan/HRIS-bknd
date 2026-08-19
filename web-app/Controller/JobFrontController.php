<?php
/** The CompanyOffice controller */
class JobFrontController extends AppController {

    const __DUMP__ = false;
     
    public static $id, $data, $pg;

    final static function Jobs(){
        JobView::FrontListAll(self::$pg, self::$rq);
    }
	
	final static function JobDetails(){
		$id=filter_input(INPUT_GET, "jID", FILTER_SANITIZE_NUMBER_INT);
		if($id !=''){
			$data = JobModel::dataById($id);
			JobView::JobDetails($data);
		}
    }

}