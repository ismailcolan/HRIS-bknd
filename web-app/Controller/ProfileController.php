<?php

/** The Order controller */
class ProfileController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;


    final static function TitleAndMetaTags()
    {
        if (!isset(AppModel::$usr["afID"])) {
            cf::$pg["title"] = "Employee login";
            cf::$pg["description"] = "Employee login";
            cf::$pg["keywords"] = "employee, login";
        } else {
            cf::$pg["title"] = "Employee profile";
            cf::$pg["description"] = "Employee profile";
            cf::$pg["keywords"] = "Employee, profile";
        }
    }

    final static function Profile()
    {   
        if (!isset(AppModel::$usr["afID"])) {
			
            if (filter_has_var(INPUT_POST, "save-btn")) {
                try {
                    if (ProfileModel::checkUserCredentials()) {
                        cf::Redirect(WP . "profile");
                    } else {
                        return HomeView::All(self::$pg, self::$rq);
//                            ProfileView::SignIn();
                    }
                } catch (Exception $e) {
                    return HomeView::All(self::$pg, self::$rq);
//                    return ProfileView::SignIn();
                }
            } else {
                //ProfileView::SignIn();
				
                cf::Redirect(WP);
            }
        } else {
            if (filter_has_var(INPUT_POST, "save-btn")) {
                if (filter_input(INPUT_POST, "save-btn") === "Save") {
                    try {
						
							 ProfileModel::DetailsValidate('server', AppModel::$usr, false, true);
						
								
							ProfileModel::SaveProfile();
                        msg::set(TXT_SAVE_OK, "success");
                        cf::Redirect(WP . "my-profile");
                    } catch(Exception $e) { 
							$exc=true;
							foreach ($_POST as $k => $v) {
								if ($k=='afStartDate') $v=val::convDate(USER_DATE,MYSQL_DATE,$v);
								if ($k=='afDateHired') $v=val::convDate(USER_DATE,MYSQL_DATE,$v);
								
								self::$data[$k] = $v;
							}
							AppModel::$usr=self::$data;
                    }
                }
				   
                ProfileModel::DetailsValidate('client', AppModel::$usr, false, true);
                ProfileView::EditProfile(AppModel::$usr, ["pfx" => "af", "key" => "afID"], ProfileModel::$fldLbl);
            } else {
				   msg::get();
                ProfileView::Profile(AccessModel::$usr + array("cvs" => CVModel::getCVsForApplication(AccessModel::$usr['afID'])), ["pfx" => "af", "key" => "afID"], self::$rq, []);
            }
        }
    }

    public static function signOut()
    {
        $fromAdmin = filter_has_var(INPUT_GET, "from-admin");
        ProfileModel::signOut(); // Log out from usr part
        unset($_SESSION['login-src-check']); // Log out from admin part
        AppModel::$usr = null;

        if($fromAdmin) {
            unset($_SESSION['webapp-admin-token']); // Log out from admin part
            AppModel::$admin = null;
        }

        cf::Redirect(WP . self::$pg["profile"]["url"]);
    }

    public static function recoverPassword()
    {
        if (filter_has_var(INPUT_GET, "key")) { 
		$db=$_GET['db'];
		db::ForceSwitchToRealByNameFast($db);
            if (ProfileModel::checkPasswordKey()) {
                if (filter_has_var(INPUT_POST, "save-btn")) {
                    $afID = ProfileModel::checkPasswordKey();
                    if ($afID) {
                        self::$data = ApplyFormModel::dataById($afID);
                    }
                    if (ProfileModel::setNewPassword()) {
                        if (empty(self::$data['afToken'])) {
                            return ProfileView::CreateAccountSuccessfully();
                        } else {
                            return ProfileView::ChangePasswordSuccessfully();
                        }
                        //cf::Redirect(WP . self::$pg["profile"]["url"]);
                    }
                }
                if (!isset($_POST['save-set-new-password'])) ProfileView::setPassword();
            } else {
                ProfileView::InvalidSetPasswordKey(htmlspecialchars(filter_input(INPUT_GET, "key")));
            }
        } else {
           
				   if (isset($_POST['save-btn'])){
					  
						if (ProfileModel::sendRecoverPasswordMail()) {
							   cf::Redirect(WP . self::$pg["profile"]["url"]);
						}
				   }
				   if (isset($_POST['save-set-new-password'])){
					  
						if (! ProfileModel::ValidateChangePassword()){
							$_GET['key']= $_POST['key'];
							self::$data = $_POST;
							ProfileView::setPassword();
							return;
						}
						else {
							$key = $_POST['key'];
							$afID =  ProfileModel::checkPasswordKey();
							$q = 'select afID from apply_forms where afActive="Hired" and afKey=' . db::EscapeStr($key, true);
							$afID= db::PrepareLoadSingleCellValue($q);
							
							ProfileModel::savePassword($afID, $_POST['pass']);
							msg::set('The password was set','success');
							cf::Redirect(WP . self::$pg["profile"]["url"]);
						}
				   }
            
            ProfileView::sendSetPasswordKey();
        }
    }
	
	public static function setNewPassword(){
		
		return ProfileModel::ValidateChangePassword();
		
	}

    public static function changePassword()
    {
        if (filter_has_var(INPUT_POST, "save-btn")) {
            if (ProfileModel::changePassword()) {
                cf::Redirect(WP . self::$pg["profile"]["url"]);
            }
        }
        ProfileView::changePassword();
    }
	
	final public static function showProfileIAmManagerFor($id){
	
		$data = EmployeeModel::GetActiveResponsibleForEmployees($id);
		$pg =Array
		(
			'item' => 'Employee',
			'items' => 'Employees',
			'hired' => Array
				(
					'url' => 'employees',
					'title' => 'Employees'
				),

			'inactive-hired' => Array
				(
					'url' => 'inactive-employees',
					'title' => 'Inactive employees'
				),
			/********
			'manager-hired' => Array
				(
					'url' => 'manager-employees',
					'title' => 'Managers'
				),
			**********/	
			'employees-terminated' => Array
				(
					'url' => 'employees-terminated',
					'title' => 'Terminated'
				),

			'details' => Array
				(
					'url' => 'employee-details',
					'title' => 'Employee'
				),

			'summary' => Array
				(
					'url' => 'employee',
					'title' => 'Employee'
				),

			'password' => Array
				(
					'url' => 'employee-password',
					'title' => 'Set employee password'
				),

			'tbl' => 'apply_forms',
			'key' => 'afID',
			'pfx' => 'af',
			'breadcrumbs-ending' => Array
				(
					'0' => 'Employees'
				),

			'title' =>'Employees'
		);
		$lbls =Array
		(
			'afJID' => 'Desired Job',
			'afPID' => 'Desired Position',
			'afHiredForPID' => 'Position',
			'afFirstName' =>'First name',
			'afLastName' =>'Last name',
			'afCellPhone' =>'Cell phone',
			'afEmcContact' =>'Emergency Contact',
			'afHomePhone' =>'Home phone',
			'afEmail' =>'E-mail',
			'afSkills' =>'Skills',
			'afBirthday' =>'Date of birth',
			'afDesiredSalary' =>'Desired salary',
			'afDesiredSalaryType' =>'Desired salary type',
			'afActive' =>'Status',
			'afAdded' =>'Applied',
			'afRating' =>'Rating',
			'afRID' =>'Rating',
			'afComments' =>'Comments',
			'afIP' =>'IP Address',
			'afBio' =>'Application CV',
			'afCurrentCV' =>'Current CV',
			'afDID' =>'Department',
			'afCOID' =>'Company office',
			'afRMID' =>'Manager',
			'afStartDate' =>'Hired',
			'afSalary' =>'Salary',
			'afSalaryType' =>'Salary type',
			'afCompany' =>'Company',
			'afResponsibleForPID' =>'Position responsible for',
			'afResponsibleForCOID' =>'Offices responsible for',
			'afWorkPhone' =>'Work phone',
			'afWorkPhoneExt' =>'Work phone extension',
			'afEmplNumber' =>'Employee number',
			'afSpecialSkills' =>'Skills and Hobbies',
			'afShortResume' =>'Short resume'

		);
		$query = 
"SELECT *, concat(afFirstName, ' ', afLastName) as afName,
(select comName from companies where comID = afCompany) as afCompanyName, 
(select pName from positions where pID=afPID) afPosition, 
(select pName from positions where pID=afHiredForPID) afHiredPosition, 
(select jName from jobs where jID=afJID) afJob, 
(select dName from departments where dID=afDID) as afDepartment, 
(select coName from company_offices where coID=afCOID) as afOffice, 
(SELECT CONCAT(af1.afFirstName, ' ', af1.afLastName) 
FROM apply_forms af1 where af1.afID=apply_forms.afRMID) AS afManager, 
(select rName from ratings where rID=afRID) afRating 
FROM apply_forms 
WHERE apply_forms.afRMID =".$id." AND`afActive`='Hired' and afHiredForPID<>9999";



	$rq='hired';
		
		
	ProfileView::listEmployeesIAmManagerFor($query,$rq,$pg,$lbls);

	}
	
	
		final public static function listProfileIAmManagerFor($id){
		
		$data = EmployeeModel::GetActiveResponsibleForEmployees($id);
		$pg =Array
		(
			'item' => 'Employee',
			'items' => 'Employees',
			'hired' => Array
				(
					'url' => 'employees',
					'title' => 'Employees'
				),

			'inactive-hired' => Array
				(
					'url' => 'inactive-employees',
					'title' => 'Inactive employees'
				),

			'manager-hired' => Array
				(
					'url' => 'manager-employees',
					'title' => 'Managers'
				),
				
			'employees-terminated' => Array
				(
					'url' => 'employees-terminated',
					'title' => 'Terminated'
				),

			'details' => Array
				(
					'url' => 'employee-details',
					'title' => 'Employee'
				),

			'summary' => Array
				(
					'url' => 'employee',
					'title' => 'Employee'
				),

			'password' => Array
				(
					'url' => 'employee-password',
					'title' => 'Set employee password'
				),

			'tbl' => 'apply_forms',
			'key' => 'afID',
			'pfx' => 'af',
			'breadcrumbs-ending' => Array
				(
					'0' => 'Employees'
				),

			'title' =>'Employees'
		);
		$lbls =Array
		(
			'afJID' => 'Desired Job',
			'afPID' => 'Desired Position',
			'afHiredForPID' => 'Position',
			'afFirstName' =>'First name',
			'afLastName' =>'Last name',
			'afCellPhone' =>'Cell phone',
			'afEmcContact' =>'Emergency Contact',
			'afHomePhone' =>'Home phone',
			'afEmail' =>'E-mail',
			'afSkills' =>'Skills',
			'afBirthday' =>'Date of birth',
			'afDesiredSalary' =>'Desired salary',
			'afDesiredSalaryType' =>'Desired salary type',
			'afActive' =>'Status',
			'afAdded' =>'Applied',
			'afRating' =>'Rating',
			'afRID' =>'Rating',
			'afComments' =>'Comments',
			'afIP' =>'IP Address',
			'afBio' =>'Application CV',
			'afCurrentCV' =>'Current CV',
			'afDID' =>'Department',
			'afCOID' =>'Company office',
			'afRMID' =>'Manager',
			'afStartDate' =>'Hired',
			'afSalary' =>'Salary',
			'afSalaryType' =>'Salary type',
			'afCompany' =>'Company',
			'afResponsibleForPID' =>'Position responsible for',
			'afResponsibleForCOID' =>'Offices responsible for',
			'afWorkPhone' =>'Work phone',
			'afWorkPhoneExt' =>'Work phone extension',
			'afEmplNumber' =>'Employee number',
			'afSpecialSkills' =>'Skills and Hobbies',
			'afShortResume' =>'Short resume'

		);
		$query = 
"SELECT *, concat(afFirstName, ' ', afLastName) as afName, 
(select pName from positions where pID=afPID) afPosition, 
(select pName from positions where pID=afHiredForPID) afHiredPosition, 
(select jName from jobs where jID=afJID) afJob, 
(select dName from departments where dID=afDID) as afDepartment, 
(select coName from company_offices where coID=afCOID) as afOffice, 
(SELECT CONCAT(af1.afFirstName, ' ', af1.afLastName) 
FROM apply_forms af1 where af1.afID=apply_forms.afRMID) AS afManager, 
(select rName from ratings where rID=afRID) afRating 
FROM apply_forms 
WHERE apply_forms.afRMID =".$id." AND`afActive`='Hired' and afHiredForPID<>9999";



	$rq='hired';
		
		
	return db::PrepareLoad($query);

	}
	
	final public static function showProfileIAmManagerForJSON($id){
		
		//$data = EmployeeModel::GetActiveResponsibleForEmployees(AppModel::$usr["afID"]);
		$pg =Array
		(
			'item' => 'Employee',
			'items' => 'Employees',
			'hired' => Array
				(
					'url' => 'employees',
					'title' => 'Employees'
				),

			'inactive-hired' => Array
				(
					'url' => 'inactive-employees',
					'title' => 'Inactive employees'
				),

			'manager-hired' => Array
				(
					'url' => 'manager-employees',
					'title' => 'Managers'
				),

			'details' => Array
				(
					'url' => 'employee-details',
					'title' => 'Employee'
				),

			'summary' => Array
				(
					'url' => 'employee',
					'title' => 'Employee'
				),

			'password' => Array
				(
					'url' => 'employee-password',
					'title' => 'Set employee password'
				),

			'tbl' => 'apply_forms',
			'key' => 'afID',
			'pfx' => 'af',
			'breadcrumbs-ending' => Array
				(
					'0' => 'Employees'
				),

			'title' =>'Employees'
		);
		$lbls =Array
		(
			'afJID' => 'Desired Job',
			'afPID' => 'Desired Position',
			'afHiredForPID' => 'Position',
			'afFirstName' =>'First name',
			'afLastName' =>'Last name',
			'afCellPhone' =>'Cell phone',
			'afEmcContact' =>'Emergency Contact',
			'afHomePhone' =>'Home phone',
			'afEmail' =>'E-mail',
			'afSkills' =>'Skills',
			'afBirthday' =>'Date of birth',
			'afDesiredSalary' =>'Desired salary',
			'afDesiredSalaryType' =>'Desired salary type',
			'afActive' =>'Status',
			'afAdded' =>'Applied',
			'afRating' =>'Rating',
			'afRID' =>'Rating',
			'afComments' =>'Comments',
			'afIP' =>'IP Address',
			'afBio' =>'Application CV',
			'afCurrentCV' =>'Current CV',
			'afDID' =>'Department',
			'afCOID' =>'Company office',
			'afRMID' =>'Manager',
			'afStartDate' =>'Hired',
			'afSalary' =>'Salary',
			'afSalaryType' =>'Salary type',
			'afCompany' =>'Company',
			'afResponsibleForPID' =>'Position responsible for',
			'afResponsibleForCOID' =>'Offices responsible for',
			'afWorkPhone' =>'Work phone',
			'afWorkPhoneExt' =>'Work phone exte.',
			'afEmplNumber' =>'Employee number',
			'afSpecialSkills' =>'Skills and Hobbies',
			'afShortResume' =>'Short resume'

		);
		$query = 
"SELECT *, concat(afFirstName, ' ', afLastName) as afName, 
(select comName from companies where comID = afCompany) as afCompanyName, 
(select pName from positions where pID=afPID) afPosition, 
(select pName from positions where pID=afHiredForPID) afHiredPosition, 
(select jName from jobs where jID=afJID) afJob, 
(select dName from departments where dID=afDID) as afDepartment, 
(select coName from company_offices where coID=afCOID) as afOffice, 
(SELECT CONCAT(af1.afFirstName, ' ', af1.afLastName) 
FROM apply_forms af1 where af1.afID=apply_forms.afRMID) AS afManager, 
(select rName from ratings where rID=afRID) afRating 
FROM apply_forms 
WHERE apply_forms.afRMID =".$id." AND`afActive`='Hired' and afHiredForPID<>9999";



	  $rq='hired';
		
		
		ProfileView::listEmployeesIAmManagerForJSON($query,$rq,$pg,$lbls);

	}
}