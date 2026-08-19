<?php
/** The Vendor view */
class HomeView extends AppView {
    
    final static function All($pg, $rq){
        form::openDiv(array('class'=>"content bg-white"));
        form::openDiv(array('class'=>"row"));

		/*******
        form::openDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
        form::tag("Welcome to Maiden Lane Medical", "h3", ["class"=>"text-center"]);
        form::closeDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
		*****/

        form::openDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
        form::openDiv(array("class"=>"row"));
//        Sign in card
        form::openDiv(array("class"=>"col-sm-12 col-md-6 col-lg-6"));
        form::html('
            <div class="card" style="height: 460px;">
                <div class="card-header">Employee Area</div>
                <div class="card-body text-primary"><br/>');
				echo "If you're an employee of Maiden Lane Medical or KAL Health:<br>
<ul>
<li>Please use this address to login from now on: <a href=\"c/mlm\">https://humanroad.ferndaleroad.com/c/mlm</a></li>
<li>When logging in, please enter \"MLM\" as \"Customer\" (this will be pre-filled if you use the above link)</li></ul>";
				
        ProfileView::SignIn();
        form::html('
                </div>
            </div>
        ');
        form::closeDiv();

//        Application form card
		
        form::openDiv(array("class"=>"col-sm-12 col-md-6 col-lg-6"));
        form::html('<div class="card" style="height: 460px;">
						<div class="card-header">Open Jobs</div>');
		
				if (db::SwitchToRealByCompany()) {
					echo '<div class="card-body text-primary"><br/><a href="'.WP.'jobs">VIEW OPEN JOBS >></a><hr/>';
					ApplyFormView::Positions(false);
				}
				
				else {
					echo '<br><div class="alert alert-warning"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> 
					The company you entered <b>'.@$_SESSION['company'].'</b> is not registered on this site.<br>
					Please check url you entered again.
					</div>';
				}
			
	    form::html('</div></div>');
        form::closeDiv();
		
        form::closeDiv(array("class"=>"row"));
        form::closeDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));




        form::openDiv(array("class"=>"row"));
        form::openDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));


        /*form::openDiv(array("class"=>"row"));
        form::openDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
        form::tag("", "hr");
        form::tag("<strong>OPEN JOBS</strong>", "div", ["class"=>"text-center"]);
        form::tag("", "hr");
        form::closeDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
        form::closeDiv(array("class"=>"row"));

        self::RenderLast4Jobs();

        form::closeDiv(array("class"=>"col-sm-12 col-md-6 col-lg-6"));
        form::closeDiv(array("class"=>"row"));

        form::openDiv(array("class"=>"row"));
        form::openDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
        form::tag("", "hr");
        form::tag("<a href='/jobs' class='right'>View all open jobs >></a>", "div", ['class'=>"text-center"]);
        form::closeDiv(array("class"=>"col-sm-12 col-md-12 col-lg-12"));
        form::closeDiv(array("class"=>"row"));*/




//        form::openDiv(array("class"=>"col-sm-12 col-md-6 col-lg-6"));
//        form::tag("Last 10 Employees", "h3");
//        self::RenderLast10Employees();
//        form::tag("<br/><a href='/employees' class='right'>View all employees >></a> | <a href='/employees' class='right'>Find an employee >></a>", "div", array('class'=>"col card-footer"));
//        form::closeDiv(array("class"=>"col-sm-12 col-md-6 col-lg-6"));

//        form::openDiv(array("class"=>"row middle"));
//        form::tag("<br/><hr/><a href='/jobs'>View all jobs >></a>", "div", array('class'=>"col-md-2 col-md-offset-5 btn "));
//        form::closeDiv(array("class"=>"row"));

        form::closeDiv(array('class'=>"row"));
        form::closeDiv(array('class'=>"content bg-white"));
    }

    protected static function RenderLast4Jobs() {
        $jobs = JobModel::data('jActive="Yes"', ["jAdded" => "DESC"], 4);
        if(empty($jobs)) {
            form::html('<strong>There are currently no open positions</strong>');
        } else {
            foreach ($jobs as $job) {
                form::html('
<div class="col-md-6" >
                <div class="card">
                <div class="card-header">'.$job["jName"].'</div>
                <div class="card-body text-primary">
                  Position: <span class="card-title">'.htmlspecialchars($job["jPosition"]).'</span>
                  <div class="card-text">'.nl2br(htmlspecialchars($job["jDescription"])).'</div>
                  <div class="card-footer right"><a href="/application-form?jID='.$job["jID"].'&pID='.$job["jPID"].'">Apply >></a></div>
                </div>
              </div>
</div>');
            }
        }
    }

    public static function RenderLast5Employees() {
		$today = new DateTime();
       $emplList = ApplyFormModel::data('afActive="Hired" AND afID >1 AND
		   (afDateLeaving IS NULL OR afDateLeaving > "'.$today->format('Y-m-d').'") ',
			["afDateHired" => "DESC"], 
			5);
        if(empty($emplList)) {
            form::html('<strong>There are currently no hired employees</strong>');
        } else {
            foreach ($emplList as $empl) {
				//cf::pr($empl);
                $imgFile = ApplyFormModel::IMG_PATH . str_replace("{---LiveID---}", $empl["afID"], ApplyFormModel::IMG_FILENAME);
                if (file_exists($imgFile)) {
                    $imgSrc = WP . $imgFile . '?r='.rand(1000, 9999);
                } else {
                    $imgSrc = ADD_IMG . 'employee-photo.png' ;
                }
				   $today = new DateTime();
				   //echo '>>>>>>>'.$empl['afDateLeaving'];
					if (isset($empl['afDateLeaving']) &&  ($today->format('Y-m-d')< $empl['afDateLeaving'])) {
						$add_txt='<br><span style="font-size:0.95em;">(in leaving notice, leaving '.val::convDate(MYSQL_DATE,USER_DATE,$empl['afDateLeaving'])
						.')</span>';
					}
					else $add_txt='';
					$add_txt='';// no more show leaving
					
					$away=MyEmployeesController::isEmployeeAwayToday($empl["afID"]);
					
					if (!empty($away))
						$add_txt="<br>Off-work today {$away['vrType']} ";
                form::html('
					<div class="col" >
                <div class="card">
                <div class="card-header" style="height:auto;"><a href="/employees?ID='.$empl["afID"].'">'.$empl["afFirstName"]. ' ' .$empl["afLastName"].'</a>
				   '.$add_txt.'</div>
                <div class="card-body text-primary">
                <img src="'.$imgSrc.'" class="card-photo">
                  Position: <span class="card-title">'.htmlspecialchars($empl["afHiredPosition"]).'</span><br/>
                  Office: <span class="card-title">'.htmlspecialchars($empl["afOffice"]).'</span><br/>
                  
                </div>
                <div class="card-footer right"><a href="/employees?ID='.$empl["afID"].'">Details >></a></div>
              </div>
</div>');
            }
        }
    }

}