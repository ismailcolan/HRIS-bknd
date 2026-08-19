<?php

/** The WebappAdmin controller */

class WebappAdminController extends AppController
{



	const __DUMP__ = false;

	public static $id, $data, $pg;



	final static function All($lists = array())
	{

		msg::get();

		$lists["roles"] = WebappAdminModel::getEnumFieldValues(WebappAdminModel::$dt["tbl"], "waaRole");

		unset($lists["roles"]["Admin"]);
		array_unshift($lists["roles"], "Admin"); //Move the Admin in the top position

		WebappAdminModel::$dataAddons = array("waaRegDateFormatted");

		WebappAdminView::All(self::$pg, WebappAdminModel::query(), $lists);
	}



	final static function Details($lists = array())
	{

		WebappAdminModel::ValidateDetails("client", $lists);

		WebappAdminView::Details(self::$data, self::$pg, $lists);
	}

	final static function DetailsSave($lists = array())
	{

		try {
			WebappAdminModel::ValidateDetails("server", $lists);
		} catch (Exception $e) {
			return self::Details();
		}



		db::Save(WebappAdminModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__) ? msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");



		cf::Redirect(self::$pg["list"]["url"]);
	}



	static function ForgotPasswordForm()
	{ //cf::pr($_SESSION);exit;

		msg::get();

		WebappAdminView::ForgotPasswordForm();
	}



	static function ForceLogin()
	{

		$customer = filter_input(INPUT_GET, "customer", FILTER_SANITIZE_STRING);

		$customer = strtoupper(trim(preg_replace('/\s+/S', " ", $customer)));



		$hash = filter_input(INPUT_GET, "hash", FILTER_SANITIZE_STRING);



		db::SwitchToRealUsingName($customer);



		$q = "SELECT * FROM  webapp_admins_tokens WHERE waatHash ='{$hash}' AND waatID=1";



		$res = db::PrepareLoadSingleRow($q);



		if ($res == false) {

			//$_POST['customer'] = $customer;

			msg::set('You are not loged in. Probably this link was used before.', 'error');

			cf::Redirect(WP . 'admin/login');
		} else {

			// ========= go to login ============

			//$salt = db::PrepareLoadSingleCellValue("SELECT waatSalt FROM webapp_admins_tokens WHERE waatID=(SELECT waaID FROM webapp_admins WHERE waaAFID is null and waaUsername=" . self::EscapeStr($user, true) . ")");

			//$user = 'admin';



			//$token = cf::hashTokenByPassAndSalt($pass, $salt);

			$sql = "SELECT * FROM webapp_admins WHERE waaUsername = 'admin'";

			$usr = db::PrepareLoadSingleRow($sql);





			if ($usr) {

				WebappAdminModel::setLoginToken($usr);

				msg::set('You are automaticaly loged in. Please use change password to set new one.', 'success');

				$_SESSION['NO_ASK_OLD_PASS'] = true;

				$q = "UPDATE webapp_admins_tokens SET waatHash='' WHERE waatID = 1";

				db::SQL($q);

				cf::Redirect(WP . 'admin/password');
			} else {

				msg::set('You are not loged in.There was a problem to autolog it.Please contact site admin.', 'error');

				cf::Redirect(WP . 'admin/login');
			}
		}
	}



	static function SendLinkForgotPassword()
	{

		//cf::pr($_POST);

		$customer = filter_input(INPUT_POST, "customer", FILTER_SANITIZE_STRING);

		$customer = strtoupper(trim(preg_replace('/\s+/S', " ", $customer)));



		$email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_STRING);

		$q = "SELECT * FROM customers WHERE (UPPER(cCustomerName) = '{$customer}' OR UPPER(cCustomerShortName) = '{$customer}') AND cActive = 'yes' ";



		db::SwitchToMain();

		//echo $q;exit;

		$res = db::PrepareLoadSingleRow($q);

		if ($res == false) {

			msg::set("<b>Forgotten password</b>: No such customer. Please contact site admin.", 'error');
		} else {

			db::SwitchToRealUsingName($customer);

			$q = "SELECT * FROM webapp_admin_settings WHERE asKey='MAIN_ADMIN_EMAIL' AND asValue='{$email}'";

			$_r = db::PrepareLoadSingleRow($q);

			if ($_r == false) {

				msg::set("<b>Forgotten password</b>:Registered admin mail differs from the entered one. Please contact site admin.", 'error');
			} else {





				$hash = hash('md5', date($customer . 'Y-m-d H:i:s'));

				$link = WP . "admin/login?cmd=force_login&customer={$customer}&hash={$hash}";

				$q = "UPDATE webapp_admins_tokens SET waatHash ='{$hash}' WHERE waatID=1";

				//echo $q;exit;

				$r = db::SQL($q);

				if ($r) {



					$subject = "Change password request for Human Road.";



					$mail_body =

						"You requested access to admin of Human Road - {$customer}.<br>Please follow next link to autologin and change password.

				<br><a href='" . $link . "'>{$link}</a> .

				<br>

				" . nl2br($GLOBALS['SYSTEM_MAILS_SIGNATURE']);



					$_res = cf::sendMail(
						$email,

						$subject,

						$mail_body,

						$GLOBALS['EMAIL_MAILER_NAME'],

						$GLOBALS['EMAIL_MAILER_ADDRESS']

					);



					if ($_res == true)

						msg::set("<b>Forgotten password</b>: Mail to <b>{$_POST['email']}</b> was send. Please follow link from mail in order to autologin and change admin passowrd", 'success');

					else

						msg::set("<b>Forgotten password</b>: Mail to <b>{$_POST['email']}</b> was not send. Please contact site admin.", 'error');
				} else msg::set('There was a problem to send forgotten link. Please, contact admin of site.', 'error');
			}
		}

		cf::Redirect(WP . 'admin/login');
	}





	static function ChangePassword($oldPass = false)
	{ //cf::pr($_SESSION);

		msg::get();

		WebappAdminModel::ValidateChangePassword("client", $oldPass);

		WebappAdminView::ChangePassword(self::$pg, $oldPass);
	}



	static function ChangePasswordProcess($oldPass = false)
	{

		try {
			WebappAdminModel::ValidateChangePassword("server", $oldPass);
		} catch (Exception $e) {
			return self::ChangePassword();
		}



		$q = "UPDATE webapp_admins_tokens SET waatToken=AES_ENCRYPT(\"" . self::$data["token"] . "\", \"" . WEBAPP_SQL_SECRET_KEY . "\") WHERE waatID = " . self::$id;

		$r = db::Save($q, self::$id, null, self::__DUMP__) ? msg::set(TXT_SAVE_OK, "success") : msg::set(TXT_SAVE_ERROR, "error");



		//  ======== UPDATE MAIN ===========

		//$pass = $_POST['waaPass'];

		//db::SwitchToMain();

		//$q = "UPDATE customers SET cPass = '{$pass}' WHERE cID = '{$_SESSION['customerID']}'";

		//db::SQL($q);

		//db::ForceSwitchToRealByName($_SESSION['DBNAME']);



		$url = array_key_exists("list", self::$pg) ? self::$pg["list"]["url"] : self::$pg["password"]["url"];

		/********

		if($r) {

			msg::set('Password was changed with new one.','success');

		}

		else msg::set('Password was NOT changed with new one.','error');

		 **********/

		cf::Redirect($url);
	}



	static function Login($goto = null)
	{

		msg::get();

		WebappAdminModel::ValidateLogin("client");

		if (is_null($goto)) {

			$goto = filter_has_var(INPUT_GET, "goto") ? filter_input(INPUT_GET, "goto", FILTER_SANITIZE_URL) : null;
		}

		WebappAdminView::Login($goto);
	}

	// static function LoginProcess(){ //cf::pr($_GET);

	// 	$customer = filter_input(INPUT_POST, "customer", FILTER_SANITIZE_STRING);



	// 	//unset($_COOKIE['customer']);

	// 	//setcookie("customer", null, -1, '/');

	// 	//setcookie("customer", $customer,time()+3600*30*24);



	// 	if (!db::SwitchToReal()) { 

	// 		form::$error["customer"][] = "No such customer registered."; 

	//         $goto = filter_has_var(INPUT_POST, "goto") ?  filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL) : null;

	// 		db::SwitchToMain();

	//         return self::Login($goto);

	// 	};



	//     try{ WebappAdminModel::ValidateLogin("server"); } catch (Exception $e) {return self::Login();}



	//     if ( WebappAdminModel::LoginProcess("Admin") ) { 

	//         $url = filter_has_var(INPUT_POST, "goto") ?  filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL) : "home";



	//         cf::Redirect(ADMIN.$url);

	//     } else { 

	//         form::$error["pass"][] = "The login data you entered is incorrect."; 

	//         $goto = filter_has_var(INPUT_POST, "goto") ?  filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL) : null;

	//         return self::Login($goto);

	//     }

	// }


	static function LoginProcess()
	{
		$customer = filter_input(INPUT_POST, "customer", FILTER_SANITIZE_STRING);

		if (!db::SwitchToReal()) {
			form::$error["customer"][] = "No such customer registered.";
			$goto = filter_has_var(INPUT_POST, "goto") ? filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL) : null;
			db::SwitchToMain();
			return self::Login($goto);
		}

		try {
			WebappAdminModel::ValidateLogin("server");
		} catch (Exception $e) {
			return self::Login();
		}

		if (WebappAdminModel::LoginProcess("Admin")) {

			// ✅ STEP 1: Get role from DB
			$admin = db::PrepareLoadSingleRow(
				"SELECT role, waaRole FROM webapp_admins WHERE waaID = " . (int)$_SESSION['admin_id']
			);

			// ✅ STEP 2: Set role in session
			if ($admin) {
				if (!empty($admin['role'])) {
					$_SESSION['role'] = strtolower($admin['role']); // interviewer / hr_manager
				} else {
					$_SESSION['role'] = strtolower(str_replace(' ', '_', $admin['waaRole']));
				}
			}

			// ✅ STEP 3: Redirect based on role
			$goto = null;

			// check POST
			if (filter_has_var(INPUT_POST, "goto")) {
				$goto = filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL);
			}

			// check GET (IMPORTANT FIX)
			elseif (filter_has_var(INPUT_GET, "go")) {
				$goto = filter_input(INPUT_GET, "go", FILTER_SANITIZE_URL);
			}

			if (RoleHelper::isInterviewer()) {
				cf::Redirect(ADMIN . 'for-interview-applications');
				exit;
			}

			if (RoleHelper::isHRManager()) {
				cf::Redirect(ADMIN . 'applications');
				exit;
			}

			// fallback
			if (!empty($goto)) {
				cf::Redirect(ADMIN . $goto);
				exit;
			}

			cf::Redirect(ADMIN . 'home');
			exit;
		} else {
			form::$error["pass"][] = "The login data you entered is incorrect.";
			$goto = filter_has_var(INPUT_POST, "goto") ? filter_input(INPUT_POST, "goto", FILTER_SANITIZE_URL) : null;
			return self::Login($goto);
		}
	}

	final static function Logout()
	{

		WebappAdminModel::unsetLoginToken("Admin");

		if (isset($_SESSION['NO_ASK_OLD_PASS']))  unset($_SESSION['NO_ASK_OLD_PASS']);

		cf::Redirect(ADMIN . "login");
	}



	final static function Delete()
	{

		WebappAdminModel::delete(self::$data);

		cf::Redirect(self::$pg["list"]["url"]);
	}
}
