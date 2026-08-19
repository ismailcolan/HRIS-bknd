<?php
/** Base model functions */
class AppModel extends db
{

    /** A saveQueries method setting
     * @var boolean
     */
    public static $saveQueriesCastEmptyInputToNull = true;
    /** Skip escape - mark save queries fields to not be escaped - need for static::$postOverrided field*/
    public static $postOverridedSkipEscape = null;
    /**User data for the current visitor<br />@var array */
    static $usr = null;
    /**Admin user data for the current visitor<br />@var array */
    static $admin = null;

    const ADMIN_SESSION_KEY_FOR_FRONT_UID = "admin-view-front-as-user";

    /*const SUBMITTED_DATE_LEGACY = "2016-01-01 00:00:00";*/
    const PHONE_NO_REGEXP = "/^1?[\-]?\(?\d{3}\)?[ |\-]?\d{3}?[ |\-]?\d{4,15}$|^$/";
    const EMAIL_REGEXP = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,5})+$|^$/";

    static $active = array("Yes" => "Yes", "No" => "No");


    /** Sets the cf::$rq["class"] for the given url (cf::$rq["url"])
     * @see Router::setCommonRequestClass()
     * @param type $rootInCP
     */
    static function setCommonRequestClass($rootInCP, $triggers = array())
    {
        if ($rootInCP) {
			
			$trigers['benefits']='benefits';

            $triggers["application-form"] = "application-form";
			  $triggers["job-details"] = "job-details";


            $triggers["backorders"] = "orders";
            $triggers["complete-orders"] = "orders";
            $triggers["report"] = "orders";
            $triggers["order"] = "orders";
            $triggers["order-details"] = "orders";

            $triggers["inactive-vendors"] = "vendors";
            $triggers["vendor"] = "vendors";
            $triggers["vendor-details"] = "vendors";

            $triggers["inactive-positions"] = "positions";
            $triggers["position"] = "positions";
            $triggers["position-details"] = "positions";

            $triggers["inactive-skills"] = "skills";
            $triggers["skill"] = "skills";
            $triggers["skill-details"] = "skills";

            $triggers["inactive-questions"] = "questions";
            $triggers["question"] = "questions";
            $triggers["question-details"] = "questions";
            $triggers["question-answers"] = "questions";

            $triggers["manufacturer"] = "manufacturers";
            $triggers["manufacturer-details"] = "manufacturers";

            $triggers["inactive-products"] = "products";
            $triggers["product"] = "products";
            $triggers["product-details"] = "products";

            //$triggers["inactive-categories"] = "categories";
            $triggers["category"] = "categories";
            $triggers["category-move"] = "categories";
            $triggers["category-move-products"] = "categories";
            $triggers["category-details"] = "categories";

            $triggers["inactive-employees"] = "employees";
            $triggers["manager-employees"] = "employees";
			  $triggers["employee"] = "employees";
			  $triggers["employees-terminated"] = "employees";
            $triggers["employee-details"] = "employees";
            $triggers["employee-password"] = "employees";
			  $triggers["delete-employee"] = "employees";
			  $triggers["admin-mass-task"] = "employees";
			  $triggers["view-docs"] = "employees";
			 		
			  $triggers["view-emp-iammanager"] = "view-emp-iammanager";
			  
			  $triggers["employees-of-manager"] = "employees-of-manager";
					  

            $triggers["inactive-offices"] = "offices";
            $triggers["office"] = "offices";
            $triggers["office-details"] = "offices";

            $triggers["inactive-onboarding"] = "onboarding";
            $triggers["onboarding"] = "onboarding";
            $triggers["onboarding-details"] = "onboarding";
			  $triggers["onboarding-report"] = "onboarding";
			  
			  $triggers["inactive-offboarding"] = "offboarding";
            $triggers["offboarding"] = "offboarding";
            $triggers["offboarding-details"] = "offboarding";
			  $triggers["offboarding-report"] = "offboarding";
			

            $triggers["inactive-credentialing"] = "credentialing";
            $triggers["credentialing"] = "credentialing";
            $triggers["credentialing-details"] = "credentialing";
			 $triggers["credentialing-report"] = "credentialing";

            $triggers["inactive-announcements"] = "announcements";
            $triggers["announcements"] = "announcements";
            $triggers["announcement-details"] = "announcements";

            $triggers["inactive-calendar"] = "calendar";
            $triggers["calendar"] = "calendar";
            $triggers["calendar-details"] = "calendar";

            $triggers["inactive-resources"] = "resources";
            $triggers["resources"] = "resources";
            $triggers["resource-details"] = "resources";          

            $triggers["inactive-rcategories"] = "rcategories";
            $triggers["rcategories"] = "rcategories";
            $triggers["rcategory-details"] = "rcategories";  

			$triggers["inactive-filecategories"] = "filecategories";
            $triggers["filecategories"] = "filecategories";
            $triggers["filecategories-details"] = "filecategories"; 
			$triggers["filecategories-getfile"] = "filecategories";
			
			
			$triggers["inactive-benefits_deductions_data"] = "benefits_deductions_data";
            $triggers["benefits_deductions_data"] = "benefits_deductions_data";
            $triggers["benefits_deductions_data-details"] = "benefits_deductions_data"; 
			$triggers["benefits_deductions_data-getfile"] = "benefits_deductions_data";
			
			$triggers["inactive-benefits_deductions_data"] = "benefits_deductions_data";
            $triggers["benefits_deductions_data"] = "benefits_deductions_data";
            $triggers["benefits_deductions_data-details"] = "benefits_deductions_data"; 
			$triggers["benefits_deductions_data-getfile"] = "benefits_deductions_data";
           
			  
			  $triggers["welcomeemail"] = "welcomeemail";
            $triggers["welcomeemail-details"] = "welcomeemail"; 
			  $triggers["welcomeemail-getfile"] = "welcomeemail";

            $triggers["inactive-departments"] = "departments";
            $triggers["department"] = "departments";
            $triggers["department-details"] = "departments";

            $triggers["inactive-managers"] = "managers";
            $triggers["manager"] = "managers";
            $triggers["manager-details"] = "managers";

            $triggers["inactive-jobs"] = "jobs";
            $triggers["job"] = "jobs";
            $triggers["job-details"] = "jobs";
			
			
			

            $triggers["application"] = "applications";
            $triggers["application-details"] = "applications";
            $triggers["manually-rejected-applications"] = "applications";
            $triggers["auto-rejected-applications"] = "applications";
            $triggers["for-interview-applications"] = "applications";
            $triggers["for-decision-applications"] = "applications";
            $triggers["all-applications"] = "applications";

            $triggers["password"] = "webapp-change-password";

            $triggers["settings"] = "webapp-settings";

            $triggers["admins"] = "webapp-admins";
            $triggers["admin"] = "webapp-admins";
            $triggers["admin-password"] = "webapp-admins";

            $triggers["logout"] = "login";
            $triggers["processor"] = "login";
            $url = empty(cf::$rq["url"]) ? "home" : cf::$rq["url"];
            //cf::vdd($url);
        } else { 
            $allowedFrontFiles = array(
			    "front-days-off",
			    "front-benefits",
				"front-getheader",
				"front-fileaccess",
                "front-home",
                "front-apply",
                "front-profile",
                "front-category",
                "front-data",
                "front-job",
				"job-details",
				"front-timeclock",
				"front-managertcureports_p",
                "front-employee",
				"front-file_request",
                "front-onboarding",
				"front-offboarding",
                "front-credentialing",
			    "front-my_files",
			    "front-payroll",
			    "front-my-employees",
                "cron",
                "cvs",
                "front-resources",
                "resource",
                "front-announcements",
                "front-calendar",
				"front-view-emp-iammanager"
            );
			//print_r($allowedFrontFiles);
            $url = filter_has_var(INPUT_GET, "front-file") && in_array($f = filter_input(INPUT_GET, "front-file"), $allowedFrontFiles) ? "web-app-" . $f : null;
        }
        //cf::vdd(array_key_exists($url, $triggers) ? $triggers[$url] : $url);
     //$res=array_key_exists($url, $triggers) ? $triggers[$url] : $url;
	 //echo $res;exit;

		 cf::$rq["class"] = array_key_exists($url, $triggers) ? $triggers[$url] : $url;
		 //echo $url.'>>>'.cf::$rq["class"];
        return cf::$rq["class"] ;
    }


    /**Sets the current (by url) nav array argument "class"=>"active" ($cssClass)
     * @param array $navArray
     * @param str $url
     * @param array $trigger - Fallback: try to "activate" url by a defined trigger (alias)
     * @param str $cssClass = "active"
     * @return boolean - do url is child of current nav array
     */
    static function setNavArrayChildCssClassByUrl(&$navArray, $url, $trigger = array(), $cssClass = "active")
    {
        if ($url === "application-details") {
            $url = ApplicationController::ActiveToUrl();
        }
        if (isset($navArray[$url])) {
            if (is_array($navArray[$url][1]) && count($navArray[$url]) === 2) {
                if (!isset($navArray[$url][1]["class"])) {
                    $navArray[$url][1]["class"] = $cssClass;
                } else {
                    $navArray[$url][1]["class"] .= " " . $cssClass;
                }
            } else {
                $name = is_array($navArray[$url]) ? $navArray[$url][0] : $navArray[$url];
                $navArray[$url] = array($name, array("class" => $cssClass));
            }
            return true;
        } else if (is_array($trigger) && isset($trigger[$url])) {
            return self::setNavArrayChildCssClassByUrl($navArray, $trigger[$url], null, $cssClass);
        } else {
            return false;
        }
    }


    /** Builds save queries ($qList) for an Model.
     * @param null $qList
     * @return array - the ready for save queries list
     */
    static function saveQueries($qList = array())
    {
        $controller = isset(static::$dt["controller"]) ? static::$dt["controller"] : (((substr(static::$dt["tbl"], -1) == "s") ? substr(static::$dt["tbl"], 0, -1) : static::$dt["tbl"]) . "Controller");
        $cClass = new ReflectionClass($controller);

        //Cast the empty values to Null;
        $id = !$cClass->getStaticPropertyValue("id") ? null : $cClass->getStaticPropertyValue("id");
        $data = !$cClass->getStaticPropertyValue("data") ? null : $cClass->getStaticPropertyValue("data");

        static::postVarsOverride();

        $mainTableFieldsData = isset(static::$mainTblFields) ? 
		 self::convertFieldsArrToSaveDataArr(static::$mainTblFields) : null;

        $new = is_null($id);

        if (isset(static::$dt["translations"])) {
            //webValuesModel::saveTranslations($qList, static::$dt, $data, $new, $mainTableFieldsData);
        } else {
            if (!is_null($mainTableFieldsData)) {
                $qList[static::$dt["tbl"]] = array("key" => static::$dt['key'], "data" => $mainTableFieldsData);
                //$qList[static::$dt["tbl"]] = array("key"=>static::$dt['key'], "data"=>self::convertFieldsArrToSaveDataArr($mainTableFields));
            }
            //On a new lang submission its translations (existing in other langs) has to be pre-set with the base lang (the first lang)
            if ($new === true && static::$dt["tbl"] === "web_langs") {
                static::newLangPreSetExistingTranslations($qList);
            }
        }

        //If in the child model we have defined array: $addonTablesKeysAndFields
        if (isset(static::$addonFieldsByTables)) {
            foreach (static::$addonFieldsByTables as $tbl => $tblKeyAndFieldsArr) {
                if (!isset($tblKeyAndFieldsArr["data"])) {
					   echo $tblKeyAndFieldsArr['key'].' ';
                    $qList[$tbl] = array("key" => $tblKeyAndFieldsArr['key'], "data" => self::convertFieldsArrToSaveDataArr($tblKeyAndFieldsArr["fields"]));
                } else {
					   echo $tblKeyAndFieldsArr['key'].' == ';
                    $qList[$tbl] = array("key" => $tblKeyAndFieldsArr['key'], "data" => $tblKeyAndFieldsArr["data"]);
                }
            }
        }

        /*if(static::$dt["tbl"]=="categories"){
            static::saveUpdateTableVars($qList, $new);
        }*/
//echo "<pre>";print_r($_POST);print_r($qList);exit;
        return $qList;
    }

    private final static function convertFieldsArrToSaveDataArr($dataArr, $tableData = array())
    {
		 
        foreach ($dataArr as $f) {
            if (isset(static::$postOverrided) && isset(static::$postOverrided[$f])) {
				//echo static::$postOverrided[$f].'<br>';
                $addQuotes = (bool)(!isset(self::$postOverridedSkipEscape) || !in_array($f, self::$postOverridedSkipEscape));
                $tableData[] = "`{$f}`=" . (!is_null(static::$postOverrided[$f]) ? db::EscapeStr(static::$postOverrided[$f], $addQuotes, false) : "Null");
            } else if (filter_has_var(INPUT_POST, $f)) {
				//if ($f == 'afBirthday') echo 'APP MODEL>>>>>>>>'.$_POST[$f];
               $tableData[] = "`{$f}`=" . (filter_input(INPUT_POST, $f) !== "{!--Empty--!}" ? 
			     db::EscapeStr(filter_input(INPUT_POST, $f), true, false) : "Null");
            }
        }
		//echo 'APP_MODEL:293<br>';cf::pr($tableData);exit;
        return $tableData;
    }


    /** Deletes an model item
     * @param int|arr $data
     */
    static function delete($data, $dump = false)
    {
        if (!isset(static::$dt["translations"])) {
            $id = (is_numeric($data) && abs($data) > 0) ? (int)$data :
                ((is_array($data) && isset($data[static::$dt["key"]])) ? (int)$data[static::$dt["key"]] : die(__METHOD__ . ": Invalid id."));
            $q = "DELETE FROM `" . static::$dt["tbl"] . "` WHERE `" . static::$dt["key"] . "`=" . $id;
            //cf::vdd($q);
            $var2 = null;
            db::Save($q, $id, $var2, $dump) ? msg::set(TXT_DELETE_OK, "success") : msg::set(TXT_DELETE_ERROR, "error");;
            /*if(!db::SQL($q)){ msg::set(TXT_DELETE_ERROR,"error");
            } else { msg::set(TXT_DELETE_OK,"success"); } */
        } else {
            self::deleteWithWebValues($data);
        }
    }

    /** Deletes an model item with assigned it translations
     * @param arr $data
     */
    private static function deleteWithWebValues($data)
    {
        $delT = //webValuesModel::deleteAssignedWebValuesQuery($data, array_keys(static::$dt["translations"]));
        $delQ = "DELETE FROM " . static::$dt["tbl"] . " WHERE " . static::$dt["key"] . " = " . $data[static::$dt["key"]];
        db::StartTransaction();
        if (!db::SQL($delQ) || !db::SQL($delT)) {
            msg::set(TXT_DELETE_ERROR, "error");
            db::Rollback();
        } else {
            msg::set(TXT_DELETE_OK, "success");
            db::Commit();
        }
    }


    /** Checks for Model::$postOverrided array and allows the data for the listed vars to be amended during the
     * db::saveQueries() execution. Useful to cast empty values to null or to cast to some other values (for checboxes)*/
    protected static function postVarsOverride()
    {
        if (!isset(static::$postOverrided)) {
            cf::vdd(__METHOD__ . " Error: The model::\$postOverrided var is required in the model.");
        }
    }

    static function query_SkipAddons()
    {
        $dataAddons = static::$dataAddons;
        static::$dataAddons = array();
        $q = static::query();
        static::$dataAddons = $dataAddons;
        return $q;
    }

    /** Description: The method checks do the request is submitted from our server - a session var
     *  is used for validation. Redirect triggered on validation failure */
    final static function SetOrValidateRequestSrc()
    {
        if (!isset($_SESSION["login-src-check"])) {
            $_SESSION["login-src-check"] = rand();
        } else {
            $checkString = cf::hashTokenByPassAndSalt($_SESSION["login-src-check"], "login-page-check");
            unset($_SESSION["login-src-check"]);

            $validateSource = filter_input(INPUT_GET, "q");

            if (empty($validateSource) || !cf::timingSafeCompare($checkString, $validateSource)) {
                cf::Redirect(WP . (!empty(cf::$rq["dir-list"]) ? implode(WS, cf::$rq["dir-list"]) . WS : "") . cf::$rq["url"]);
            } else {
                //resetting the session
                self::SetOrValidateRequestSrc();
            }
        }
    }
}