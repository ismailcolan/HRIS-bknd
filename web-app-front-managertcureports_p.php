<?php

class ManagerRtcureports extends PageFront {

    const CONTROLLER = "ManagerRtcureportsFrontController_p";

    public function __construct($C = self::CONTROLLER) {
        parent::__construct($C);

    }

    protected function inHead($C = self::CONTROLLER) {
        msg::prepare();
		table::prepare();
    }



}


                     
//error_reporting(0);
@session_start();

if(@$_GET["querytype"]=="admin_set_hours") {
	ManagerRtcureportsFrontController_p::SaveAdminHours();
	exit;
}

if(@$_GET["querytype"]=="")
    {
        ManagerRtcureportsFrontController_p::ListUnsignedReports();
    }
    else if(@$_GET["querytype"]=="update")
              {
                  $status=ManagerRtcureportsFrontController_p::UpdateReportstatus($_GET["rstatus"],$_GET["id"]);
                  if($status=="Error")
                      {
                        $_SESSION["error"] = "An error has been occur. please try again later";
                        cf::Redirect(WP."managertcureports");
                      }
                      else
                          {
                            $_SESSION["success"] = "Report has been signed successfully";
                            cf::Redirect(WP."managertcureports");
                          }
              }
              else if(@$_GET["querytype"]=="updatefromdetail")
                        {

                            $status=ManagerRtcureportsFrontController_p::UpdateReportstatus($_GET["rstatus"],$_GET["id"]);
                            if($status=="Error")
                                {
                                  $_SESSION["error"] = "An error has been occur. please try again later";
                                  cf::Redirect(WP.'managertcureports?querytype=listattendence&empnumber='.$_GET["empnumber"].'&startdate='.$_GET["startdate"]);
                                }
                                else
                                    { 
								      if($_GET['rstatus'] == 'Yes')
                                      $_SESSION["success"] = " Weekly report signed.";
									  else  $_SESSION["success"] = " Weekly report unsigned.";
									  
                                      cf::Redirect(WP.'managertcureports?querytype=listattendence&empnumber='.$_GET["empnumber"].'&startdate='.$_GET["startdate"]);
                                    }
                        }
              else if(@$_GET["querytype"]=="listattendence")
                      {   
						 // $page = new ManagerRtcureports();
						 // $page->HTMLHeader();
                          ManagerRtcureportsFrontController_p::ListWorkingHours(@$_GET["empnumber"], @$_GET["startdate"]);
						 // $page->HTMLFooter();
						 // exit;
                      }
					  
			 else if(@$_GET["querytype"]=="admin_del_hours" )
                      {   
						 // $page = new ManagerRtcureports();
						 // $page->HTMLHeader();
                          ManagerRtcureportsFrontController_p::delReport();
						 // $page->HTMLFooter();
						 // exit;
                      }
					  
					  else
                          {
                                cf::Redirect(WP."404");
                          }

?>
