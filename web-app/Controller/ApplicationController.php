<?php

/** The CompanyOffice controller */
class ApplicationController extends AppController
{

    const __DUMP__ = false;

    public static $id, $data, $pg;




    final static function All()
    {
        msg::get();
        $cond = ApplicationModel::StatusCond(self::$rq);

        if (RoleHelper::isInterviewer()) {
            $interviewerId = (int)$_SESSION['admin_id'];
            $assignedCond = "afID IN (
            SELECT application_id FROM application_interviewers 
            WHERE interviewer_id = $interviewerId
        )";
            if (empty($cond)) {
                $cond = $assignedCond;
            } else {
                $cond = "($cond) AND $assignedCond";
            }
        }

        ApplicationView::All(
            self::$pg,
            ApplicationModel::query($cond),
            ApplicationModel::$fldLbl,
            self::$rq
        );
    }

    static function Summary()
    {
        msg::get();
        ApplicationView::Summary(self::$data, self::$pg, ApplicationModel::$fldLbl);
    }

    public static function ActiveToUrl()
    {
        if (self::$data === null) {
            self::$data = EmployeeController::$data;
        }
        //print_r(self::$data);
        $url = self::$data[ApplicationModel::$dt["pfx"] . "Active"];
        switch ($url) {
            case 'For Interview':
                $url = "for-interview-applications";
                break;
            case 'Payroll':
                $url = "payroll";
                break;
            case 'For Decision':
                $url = "for-decision-applications";
                break;
            case 'Manually Rejected':
                $url = "manually-rejected-applications";
                break;
            case 'Automatically Rejected':
                $url = "auto-rejected-applications";
                break;
            case 'Hired':
                $url = "employees";
                break;
            case 'Inactive':
                $url = "inactive-employees";
                break;
            case 'Manager':
                $url = "manager-employees";
                break;
            case 'Terminated':
                $url = "employees-terminated";
                break;
            case 'Password':
                $url = "inactive-password";
                break;
            default:
                $url = "applications";
        }

        return $url;
    }

    public static function getBackUrl()
    {
        if (isset(AppModel::$admin["waaAFID"])) {
            $url = self::$pg["all"]["url"];
        } else {
            $url = self::ActiveToUrl();
        }
        $params = array("name" => "Back to " . strtolower(self::$pg["items"]), "url" => $url, "icon" => "fa fa-chevron-circle-left", "args" => array("class" => "btn btn-info"));
        return $params;
    }

    final static function Details($lists = array())
    {
        // Handle all CMD actions first
        if (filter_has_var(INPUT_GET, "cmd")) {
            $cmd = filter_input(INPUT_GET, "cmd", FILTER_SANITIZE_STRING);
            $appId = (int)self::$id;
            $interviewerId = (int)$_SESSION['admin_id'];

            // Save new note
            if ($cmd === 'save-note' && filter_has_var(INPUT_POST, 'note')) {
                $note = db::EscapeStr(
                    filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING)
                );

                $noteRound = filter_input(INPUT_POST, 'note_round', FILTER_SANITIZE_NUMBER_INT);
                $noteRoundVal = (!empty($noteRound) && (int)$noteRound > 0)
                    ? (int)$noteRound
                    : 'NULL';

                // If interviewer - verify they are assigned to this round
                if (RoleHelper::isInterviewer()) {
                    $check = db::PrepareLoadSingleCellValue(
                        "SELECT COUNT(*) FROM application_interviewers 
             WHERE application_id = $appId 
             AND interviewer_id = $interviewerId 
             AND `round` = $noteRoundVal"
                    );
                    if ((int)$check === 0) {
                        msg::set('You are not assigned to this round.', 'error');
                        cf::Redirect(ADMIN . 'application-details?id=' . $appId);
                        exit;
                    }

                    // Check if note already submitted for this round
                    $existing = db::PrepareLoadSingleCellValue(
                        "SELECT COUNT(*) FROM interview_notes 
             WHERE inApplicationID = $appId 
             AND inInterviewerID = $interviewerId 
             AND inRound = $noteRoundVal"
                    );
                    if ((int)$existing > 0) {
                        msg::set('You have already submitted a note for this round. Please edit the existing note.', 'error');
                        cf::Redirect(ADMIN . 'application-details?id=' . $appId);
                        exit;
                    }
                }

                db::SQL(
                    "INSERT INTO interview_notes 
         (inApplicationID, inInterviewerID, inNote, inRound) 
         VALUES ($appId, $interviewerId, '$note', $noteRoundVal)"
                );

                self::sendNoteNotificationToAdmin($appId, $interviewerId, $noteRoundVal);
                msg::set('Note saved successfully.', 'success');
                cf::Redirect(ADMIN . 'application-details?id=' . $appId);
                exit;
            }

            // Update note
            if ($cmd === 'update-note' && filter_has_var(INPUT_POST, 'note')) {
                $noteId = (int)filter_input(INPUT_POST, 'note_id', FILTER_SANITIZE_NUMBER_INT);
                $note = db::EscapeStr(
                    filter_input(INPUT_POST, 'note', FILTER_SANITIZE_STRING)
                );
                $where = RoleHelper::isAdmin()
                    ? "inID = $noteId"
                    : "inID = $noteId AND inInterviewerID = $interviewerId";
                db::SQL("UPDATE interview_notes SET inNote='$note', inUpdatedAt=NOW() WHERE $where");
                self::sendNoteNotificationToAdmin($appId, $interviewerId, 'Updated');
                msg::set('Note updated.', 'success');
                cf::Redirect(ADMIN . 'application-details?id=' . $appId);
                exit;
            }

            // Delete note
            if ($cmd === 'delete-note') {
                $noteId = (int)filter_input(INPUT_GET, 'note_id', FILTER_SANITIZE_NUMBER_INT);
                $where = RoleHelper::isAdmin()
                    ? "inID = $noteId"
                    : "inID = $noteId AND inInterviewerID = $interviewerId";
                db::SQL("DELETE FROM interview_notes WHERE $where");
                msg::set('Note deleted.', 'success');
                cf::Redirect(ADMIN . 'application-details?id=' . $appId);
                exit;
            }

            // Assign interviewers
            // Assign interviewers - multi-round
            if ($cmd === 'assign-interviewers') {
                RoleHelper::requireAdminOrHR();
                $assignedBy = (int)$_SESSION['admin_id'];

                // Delete all existing assignments for this application
                db::SQL("DELETE FROM application_interviewers WHERE application_id = $appId");

                // Round 1
                $round1 = (int)filter_input(INPUT_POST, 'round_1', FILTER_SANITIZE_NUMBER_INT);
                if ($round1 > 0) {
                    db::SQL(
                        "INSERT INTO application_interviewers 
             (application_id, interviewer_id, round, assigned_by)
             VALUES ($appId, $round1, 1, $assignedBy)"
                    );
                    self::sendInterviewerAssignmentEmail($appId, $round1, 1);
                }

                // Round 2
                $round2 = (int)filter_input(INPUT_POST, 'round_2', FILTER_SANITIZE_NUMBER_INT);
                if ($round2 > 0) {
                    db::SQL(
                        "INSERT INTO application_interviewers 
             (application_id, interviewer_id, round, assigned_by)
             VALUES ($appId, $round2, 2, $assignedBy)"
                    );
                    self::sendInterviewerAssignmentEmail($appId, $round2, 2); 
                }

                // Round 3
                $round3 = (int)filter_input(INPUT_POST, 'round_3', FILTER_SANITIZE_NUMBER_INT);
                if ($round3 > 0) {
                    db::SQL(
                        "INSERT INTO application_interviewers 
             (application_id, interviewer_id, round, assigned_by)
             VALUES ($appId, $round3, 3, $assignedBy)"
                    );
                    self::sendInterviewerAssignmentEmail($appId, $round3, 3); 
                }

                msg::set('Interviewers assigned successfully.', 'success');
                cf::Redirect(ADMIN . 'application-details?id=' . $appId);
                exit;
            }
        }

        // Block interviewer from unassigned applications
        if (RoleHelper::isInterviewer()) {
            $appId = (int)self::$id;
            $interviewerId = (int)$_SESSION['admin_id'];
            $count = db::PrepareLoadSingleCellValue(
                "SELECT COUNT(*) FROM application_interviewers 
             WHERE application_id = $appId 
             AND interviewer_id = $interviewerId"
            );
            if ((int)$count === 0) {
                msg::set('You do not have access to this application.', 'error');
                cf::Redirect(ADMIN . 'applications');
                exit;
            }
        }
        msg::get();

        if (empty($lists)) {
            $lists = ["positions" => PositionModel::AllActiveKeyName(), "jobs" => JobModel::AllIdNameArr()];
        }
        $lists["position"] = ApplyFormController::LoadPosition(self::$data["afPID"]);

        if (empty($_POST))
            self::$data["afBirthday"] = val::convDate(MYSQL_DATE_TIME, USER_DATE, self::$data["afBirthday"]);

        if (empty(self::$data["afStartDate"]) || (self::$data["afStartDate"] === "0000-00-00") || (self::$data['afActive'] !== 'Hired')) {
            $today = new DateTime();
            self::$data["afStartDate"] = $today->format(USER_DATE);
        } else {
            if (empty($_POST)) {
                self::$data["afStartDate"] = val::convDate(MYSQL_DATE, USER_DATE, self::$data["afStartDate"]);
            }
        }

        if (empty(self::$data["afSalaryType"])) {
            if (filter_has_var(INPUT_POST, "afSalaryType")) {
                self::$data["afSalaryType"] = filter_input(INPUT_POST, "afSalaryType", FILTER_SANITIZE_STRING);
            } else {
                self::$data["afSalaryType"] = $lists["position"]["pSalaryType"];
            }
        }

        self::$data["data"] = self::$data;
        self::$data["cvs"] = CVModel::getCVsForApplication(self::$id);
        ApplicationModel::Detailsvalidate("client", $lists, true);
        ApplicationView::Details(self::$data, self::$pg, ApplicationModel::$fldLbl, $lists);
    }

    public static function removeBioFile($filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    final static function DetailsSave($lists = array())
    {
        $isEmployee = isset(self::$data["afApplyAsEmployeeID"]);
        $lists["position"] = ApplyFormController::LoadPosition(self::$data["afPID"]);

        $lists["data"] = self::$data;

        try {

            ApplyFormModel::DetailsValidate("server", $lists, true);

            CVModel::validateUploadedFiles();

            if (filter_input(INPUT_POST, "afActive", FILTER_SANITIZE_STRING) === "Hired") {
                $mail = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
                $q = "select count(afID) as cnt from apply_forms where afID<>%d and afActive='Hired' and afEmail='%s'";
                $q = sprintf($q, self::$id, $mail);

                if ($isEmployee) {
                    $afID = intval(self::$data["afApplyAsEmployeeID"]);
                    $q .= ' and afID <> ' . $afID;
                }

                $cnt = db::PrepareLoadSingleCellValue($q);

                if ($cnt > 0) {
                    val::setError("afEmail", "There's already an employee with this email address.");
                    val::throwServerRunError();
                }

                if (!filter_has_var(INPUT_POST, "skip-name-check")) {
                    $firstName = filter_input(INPUT_POST, "afFirstName", FILTER_SANITIZE_STRING);
                    $lastName = filter_input(INPUT_POST, "afLastName", FILTER_SANITIZE_STRING);
                    $q = "select count(afID) as cnt from apply_forms where afID<>%d and afActive='Hired' and afFirstName='%s' and afLastName='%s'";
                    if ($isEmployee) {
                        $afID = intval(self::$data["afApplyAsEmployeeID"]);
                        $q .= ' and afID <> ' . $afID;
                    }
                    $q = sprintf($q, self::$id, $firstName, $lastName);
                    $cnt = db::PrepareLoadSingleCellValue($q);

                    if ($cnt > 0 && !isset(self::$data['afApplyAsEmployeeID'])) {
                        self::$data["check-name-failed"] = true;
                        val::setError("check-name-failed", "There's  already an employee with this name.");
                        val::throwServerRunError();
                    } else {
                        self::$data["check-name-failed"] = false;
                    }
                }
            }

            val::throwServerRunError();
        } catch (Exception $e) {

            foreach ($_POST as $k => $v) {
                self::$data[$k] = $v;
            }
            return self::Details();
        }

        if ($isEmployee) {
            if (filter_input(INPUT_POST, "afActive", FILTER_SANITIZE_STRING) === "Hired") {
                db::Save(ApplyFormModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__);
                $q = 'update apply_forms set afActive="Change Position" where afID=' . self::$id;
                db::SQL($q);

                self::$id = self::$data["afApplyAsEmployeeID"];
                self::$data = ApplyFormModel::dataById(self::$id);
                $q = 'update apply_forms set afApplyAsEmployeeID=null where afID=' . self::$id;
                db::SQL($q);
            }
        }

        if (db::Save(ApplyFormModel::saveQueries(), self::$id, self::$pg['tbl'], self::__DUMP__)) {

            $id = self::$data[self::$pg["key"]];

            // --- Process interviewer assignments if submitted alongside the main form ---
            if ((RoleHelper::isAdmin() || RoleHelper::isHRManager())
                && (filter_has_var(INPUT_POST, 'round_1')
                    || filter_has_var(INPUT_POST, 'round_2')
                    || filter_has_var(INPUT_POST, 'round_3'))
            ) {
                $assignedBy = (int)$_SESSION['admin_id'];
                db::SQL("DELETE FROM application_interviewers WHERE application_id = $id");

                $round1 = (int)filter_input(INPUT_POST, 'round_1', FILTER_SANITIZE_NUMBER_INT);
                if ($round1 > 0) {
                    db::SQL("INSERT INTO application_interviewers (application_id, interviewer_id, round, assigned_by) VALUES ($id, $round1, 1, $assignedBy)");
                    self::sendInterviewerAssignmentEmail($id, $round1, 1);
                }
                $round2 = (int)filter_input(INPUT_POST, 'round_2', FILTER_SANITIZE_NUMBER_INT);
                if ($round2 > 0) {
                    db::SQL("INSERT INTO application_interviewers (application_id, interviewer_id, round, assigned_by) VALUES ($id, $round2, 2, $assignedBy)");
                    self::sendInterviewerAssignmentEmail($id, $round2, 2);
                }
                $round3 = (int)filter_input(INPUT_POST, 'round_3', FILTER_SANITIZE_NUMBER_INT);
                if ($round3 > 0) {
                    db::SQL("INSERT INTO application_interviewers (application_id, interviewer_id, round, assigned_by) VALUES ($id, $round3, 3, $assignedBy)");
                    self::sendInterviewerAssignmentEmail($id, $round3, 3); 
                }
            }

            $fileName = str_replace("{---LiveID---}", $id, ApplyFormModel::IMG_FILENAME);
            if (filter_has_var(INPUT_POST, "photoCmd") && (filter_input(INPUT_POST, "photoCmd", FILTER_SANITIZE_STRING) === "remove")) {
                $photoFile = "../" . ApplyFormModel::IMG_PATH . $fileName;
                if (file_exists($photoFile)) {
                    unlink($photoFile);
                }
            }
            img::move_images_from_temp("afPhoto", true, $fileName);

            CVModel::processUploadedFiles($id);

            if (filter_input(INPUT_POST, "afActive", FILTER_SANITIZE_STRING) === "Hired") {
                $mail = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
                $key = ProfileModel::generateKey();


                $url = WP . "login-recover?key=" . $key . '&db=' . str_replace(DB_PREFIX, '', $_SESSION['DBNAME']);
                $msg = nl2br(sprintf(
                    MAIL_PASSWORD_CREATE_MSG,
                    self::$data["afFirstName"],
                    self::$data["afLastName"],
                    form::linkOut($url, ltrim($url, "//"))
                ));

                $CreatePaswordLink = '<br> ' . form::linkOut($url, ltrim($url, "//")) . ' <br>';
                if (self::sendWelcomeMail($CreatePaswordLink)) {
                    //cf::sendMail($mail, MAIL_PASSWORD_CREATE_SUBJ, $msg, EMAIL_MAILER_NAME, EMAIL_MAILER_ADDRESS)) {
                    $q = 'update apply_forms set afKey="' . $key . '" where afID=' . self::$id;
                    db::SQL($q);
                }

                $hiredFor = filter_input(INPUT_POST, "afHiredForPID", FILTER_SANITIZE_NUMBER_INT);
                if ($hiredFor === "9999") {
                    $ids = (filter_has_var(INPUT_POST, "afResponsibleForPID")) ? array_values(filter_input(INPUT_POST, "afResponsibleForPID", FILTER_DEFAULT, FILTER_FORCE_ARRAY)) : [];
                    ManagerPositionsModel::SaveManagerPositions(self::$id, $ids);

                    $ids = (filter_has_var(INPUT_POST, "afResponsibleForCOID")) ? array_values(filter_input(INPUT_POST, "afResponsibleForCOID", FILTER_DEFAULT, FILTER_FORCE_ARRAY)) : [];
                    ManagerOfficesModel::SaveManagerOffices(self::$id, $ids);
                }

                $_date = filter_input(INPUT_POST, "afStartDate", FILTER_SANITIZE_STRING);

                $startDate = DateTime::createFromFormat(USER_DATE, $_date);

                EmployeeOnboardingModel::AssignEmployeeOnboardingTasks(self::$id, $hiredFor, $startDate);

                EmployeeCredentialingModel::AssignEmployeeCredentialings(self::$id, $hiredFor, $startDate);

                $position = PositionModel::dataById($hiredFor);
                $salary = filter_input(INPUT_POST, "afSalary", FILTER_SANITIZE_STRING);
                $salaryType = filter_input(INPUT_POST, "afSalaryType", FILTER_SANITIZE_STRING);
                $department = DepartmentModel::dataById(filter_input(INPUT_POST, "afDID", FILTER_SANITIZE_NUMBER_INT));
                $office = CompanyOfficeModel::dataById(filter_input(INPUT_POST, "afCOID", FILTER_SANITIZE_NUMBER_INT));

                FieldHistoryModel::AddHistory("department", $id, $department["dName"], $startDate);
                FieldHistoryModel::AddHistory("office", $id, $office["coName"], $startDate);
                FieldHistoryModel::AddHistory("position", $id, $position["pName"], $startDate);
                FieldHistoryModel::AddHistory("salary", $id, PositionModel::formatSalary($salary) . " " . strtolower($salaryType), $startDate);

                if ($isEmployee) {
                    msg::set("The employee position <strong>HAS BEEN CHANGED</strong>. You can view his/her details in the menu Employees.", "success");
                } else {
                    msg::set("The applicant <strong>HAS BEEN HIRED</strong>. You can view his/her details in the menu Employees.", "success");
                }
            } else {
                msg::set(TXT_SAVE_OK, "success");
            }

            // ApplyFormModel::UpdateAdminRecord($id, self::$data["afHiredForPID"], self::$data["afEmail"], null);
            $newHiredForPID = filter_input(INPUT_POST, "afHiredForPID", FILTER_SANITIZE_NUMBER_INT);
            $newEmail = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
            ApplyFormModel::UpdateAdminRecord($id, $newHiredForPID, $newEmail, null);

            // ============================================
            // AUTO-CREATE ADMIN ACCOUNT FOR HR/INTERVIEWER
            // ============================================
            self::autoCreateAdminForPosition($id, $hiredFor);
            // ===========================================

            if (filter_input(INPUT_POST, "afActive", FILTER_SANITIZE_STRING) === "Manually Rejected") {
                $mail = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_EMAIL);
                $firstName = filter_input(INPUT_POST, "afFirstName", FILTER_SANITIZE_STRING);
                $lastName = filter_input(INPUT_POST, "afLastName", FILTER_SANITIZE_STRING);
                $msg = nl2br(sprintf(MAIL_REJECTED_APPLICATION_MSG, $firstName, $lastName));
                cf::sendMail($mail, MAIL_REJECTED_APPLICATION_SUBJ, $msg, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS']);
            }
        } else {
            msg::set(TXT_SAVE_ERROR, "error");
        }

        if (filter_input(INPUT_POST, "afActive", FILTER_SANITIZE_STRING) === "Hired") cf::Redirect('employee-details?id=' . self::$data['afID']);
        else cf::Redirect(self::getBackUrl()["url"]);
    }

    private static function sendInterviewerAssignmentEmail($applicationId, $interviewerId, $round)
    {
        // GET DATA
        $q = "
        SELECT 
            af.afFirstName,
            af.afLastName,
            p.pName AS jobTitle,
            wa.waaEmail,
            wa.waaUsername
        FROM apply_forms af
        LEFT JOIN positions p ON p.pID = af.afPID
        LEFT JOIN webapp_admins wa ON wa.waaID = $interviewerId
        WHERE af.afID = $applicationId
    ";

        $data = db::PrepareLoadSingleRow($q);

        if (!$data) return;

        $candidateName = $data['afFirstName'] . ' ' . $data['afLastName'];
        $jobTitle = $data['jobTitle'];
        $email = $data['waaEmail'];
        $interviewerName = $data['waaUsername'];

        $link = ADMIN . "application-details?id=" . $applicationId;

        // SUBJECT
        $subject = "Interview Assigned - Round $round";

        // MESSAGE
        $message = "
        <p>Hi $interviewerName,</p>

        <p>You have been assigned to conduct an interview.</p>

        <p><strong>Candidate:</strong> $candidateName</p>
        <p><strong>Job Title:</strong> $jobTitle</p>
        <p><strong>Round:</strong> $round</p>

        <p>
            <a href='$link' target='_blank'>View Application</a>
        </p>

        <br>
        <p>Regards,<br>Admin Team</p>
    ";

        // SEND MAIL
        cf::sendMail(
            $email,
            $subject,
            $message,
            $GLOBALS['EMAIL_MAILER_NAME'],
            $GLOBALS['EMAIL_MAILER_ADDRESS']
        );
    }

    private static function sendNoteNotificationToAdmin($appId, $interviewerId, $round)
    {
        $q = "
        SELECT 
            af.afFirstName,
            af.afLastName,
            p.pName AS jobTitle,
            wa.waaUsername AS interviewerName
        FROM apply_forms af
        LEFT JOIN positions p ON p.pID = af.afPID
        LEFT JOIN webapp_admins wa ON wa.waaID = $interviewerId
        WHERE af.afID = $appId
    ";

        $data = db::PrepareLoadSingleRow($q);
        if (!$data) return;

        $candidateName = $data['afFirstName'] . ' ' . $data['afLastName'];
        $jobTitle = $data['jobTitle'];
        $interviewerName = $data['interviewerName'];

        $link = ADMIN . "application-details?id=" . $appId;

        $roundText = is_numeric($round) ? "Round $round" : $round;

        $subject = "Interview Note Activity - $roundText";

        $message = "
        <p>Hello Admin,</p>
        <p>An interviewer has submitted/updated notes.</p>

        <p><strong>Candidate:</strong> $candidateName</p>
        <p><strong>Job Title:</strong> $jobTitle</p>
        <p><strong>Round:</strong> $roundText</p>
        <p><strong>Interviewer:</strong> $interviewerName</p>

        <p><a href='$link' target='_blank'>View Application</a></p>

        <br>
        <p>Regards,<br>Human Road System</p>
    ";

        // 🔥 GET MULTIPLE EMAILS FROM CONSTANT
        // $emails = explode(',', EMAIL_ADMIN);
        $row = db::PrepareLoadSingleRow("SELECT asValue FROM webapp_admin_settings WHERE asKey='ADMIN_MAILS'");

$emails = explode("\n", $row['asValue']);

        foreach ($emails as $email) {
            $email = trim($email);

            if (!empty($email)) {
                cf::sendMail(
                    $email,
                    $subject,
                    $message,
                    $GLOBALS['EMAIL_MAILER_NAME'],
                    $GLOBALS['EMAIL_MAILER_ADDRESS']
                );
            }
        }
    }

    private static function autoCreateAdminForPosition($employeeId, $positionId)
    {
        // Get position admin role flag
        $position = db::PrepareLoadSingleRow(
            "SELECT pID, pName, pAdminRole FROM positions WHERE pID = " . (int)$positionId
        );

        if (empty($position['pAdminRole'])) return; // Not an admin role position

        $adminRole = $position['pAdminRole']; // 'hr_manager' or 'interviewer'

        // Get role_id
        $roleRow = db::PrepareLoadSingleRow(
            "SELECT id FROM roles WHERE role_name = '" . db::EscapeStr($adminRole) . "'"
        );
        if (!$roleRow) return;
        $roleId = (int)$roleRow['id'];

        // Get employee data
        $employee = db::PrepareLoadSingleRow(
            "SELECT afID, afFirstName, afLastName, afEmail FROM apply_forms WHERE afID = " . (int)$employeeId
        );
        if (!$employee) return;

        $email = $employee['afEmail'];
        $firstName = $employee['afFirstName'];
        $lastName = $employee['afLastName'];

        // Check if admin account already exists for this employee
        $existingAdmin = db::PrepareLoadSingleRow(
            "SELECT waaID FROM webapp_admins WHERE waaAFID = " . (int)$employeeId
        );

        if ($existingAdmin) {
            // Update existing account role if position changed
            db::SQL(
                "UPDATE webapp_admins SET 
             role_id = $roleId,
             role = '" . db::EscapeStr($adminRole) . "',
             waaEmail = '" . db::EscapeStr($email) . "'
             WHERE waaAFID = " . (int)$employeeId
            );
            return;
        }

        // Generate unique username from email
        $username = explode('@', $email)[0];
        $usernameCheck = (int)db::PrepareLoadSingleCellValue(
            "SELECT COUNT(*) FROM webapp_admins WHERE waaUsername = '" . db::EscapeStr($username) . "'"
        );
        if ($usernameCheck > 0) {
            $username = $username . '_' . $employeeId;
        }

        // Generate salt
        $salt = cf::saltAnWord($email);

        // Insert admin record
        db::SQL(
            "INSERT INTO webapp_admins 
         (waaUsername, waaEmail, waaRole, waaAFID, waaRegDate, role_id, role) 
         VALUES (
            '" . db::EscapeStr($username) . "',
            '" . db::EscapeStr($email) . "',
            'Admin',
            " . (int)$employeeId . ",
            NOW(),
            $roleId,
            '" . db::EscapeStr($adminRole) . "'
         )"
        );

        $newAdminId = db::$mysqli->insert_id;

        // Insert token row with salt - token will be set when password is assigned
        db::SQL(
            "INSERT INTO webapp_admins_tokens (waatID, waatSalt, waatToken) 
         VALUES ($newAdminId, '" . db::EscapeStr($salt) . "', '')"
        );
    }

    public static function AllForManager()
    {
        $afID = intval(AppModel::$admin["waaAFID"]);
        $positions = implode(", ", array_keys(ManagerPositionsModel::LoadAsKeyValueArray($afID)));
        //        $offices = implode(", ", array_keys(ManagerOfficesModel::LoadAsKeyValueArray($afID)));
        //        var_dump($positions, $offices);

        switch (cf::$rq["url"]) {
            case "manager-for-interview-applications":
                $active = "For Interview";
                break;
            case "manager-for-decision-applications":
                $active = "For Decision";
                break;
            case "manager-manually-rejected-applications":
                $active = "Manually Rejected";
                break;
            default:
                $active = "Pending";
                break;
        }

        $cond = 'afActive="' . $active . '" and afPID in (' . $positions . ') ';
        $q = ApplicationModel::query($cond);
        ApplicationView::All(self::$pg, $q, ApplicationModel::$fldLbl, self::$rq);
    }

    public static function DetailsForManager()
    {
        $afID = intval(AppModel::$admin["waaAFID"]);
        $positions = array_keys(ManagerPositionsModel::LoadAsKeyValueArray($afID));

        if (in_array(self::$data["afActive"], ["Pending", "For Interview", "For Decision", "Manually Rejected"])) {
            if (in_array(self::$data["afPID"], $positions)) {
                self::Details([]);
            } else {
                cf::Redirect(self::$pg["all"]["url"]);
            }
        } else {
            cf::Redirect(self::$pg["all"]["url"]);
        }
    }

    public static function DetailsSaveForManager()
    {
        $afID = intval(AppModel::$admin["waaAFID"]);
        $positions = array_keys(ManagerPositionsModel::LoadAsKeyValueArray($afID));

        if (in_array(self::$data["afActive"], ["Pending", "For Interview", "For Decision", "Manually Rejected"])) {
            if (in_array(self::$data["afPID"], $positions)) {
                self::DetailsSave([]);
            } else {
                cf::Redirect(self::$pg["all"]["url"]);
            }
        } else {
            cf::Redirect(self::$pg["all"]["url"]);
        }
    }

    public static function replaceNames($text, $f)
    {
        global $_TEMPL_ALLOWED, $_TEMPL_ALLOWED_ADDON;
        $templ = array_merge($_TEMPL_ALLOWED, $_TEMPL_ALLOWED_ADDON);
        foreach ($templ as $tname) {
            $name = val::get_string_between($tname, '[', ']');
            if (isset($f[$name]))
                $text = str_replace($tname, $f[$name], $text);
        }
        return $text;
    }

    public static function  sendWelcomeMail($_link = '')
    {

        $hiredFor = filter_input(INPUT_POST, "afHiredForPID", FILTER_SANITIZE_NUMBER_INT);
        $r = db::PrepareLoadSingleRow('SELECT * FROM positions WHERE pID=' . $hiredFor);

        $templID = $r['pWelcomeEmail'];
        $position = $r['pName'];
        if ($templID == 0) {
            msg::set('There is no assigned template for ' . $r['pName'] . ', welcome message was not sent.', 'error');
            return true; //no assigned template
        }

        $r = db::PrepareLoadSingleRow('SELECT * FROM email_template WHERE 	emtID=' . $templID);
        $subject = $r['emtSubject'];
        $message = $r['emtMessage'];
        $f = array();
        $f['FirstName'] = filter_input(INPUT_POST, "afFirstName", FILTER_SANITIZE_STRING);
        $f['LastName'] = filter_input(INPUT_POST, "afLastName", FILTER_SANITIZE_STRING);
        $f['StartDate'] = filter_input(INPUT_POST, "afStartDate", FILTER_SANITIZE_STRING);
        $f['Position'] = $position;
        $manID = filter_input(INPUT_POST, "afRMID",  FILTER_SANITIZE_NUMBER_INT);
        $r = db::PrepareLoadSingleRow('SELECT * FROM apply_forms WHERE afID=' . $manID);
        $f['ManagerName'] = $r['afFirstName'] . ' ' . $r['afLastName'];
        $compID = filter_input(INPUT_POST, "afCompany", FILTER_SANITIZE_STRING);
        $companies = CompaniesModel::GetActiveCompaniesLong();
        $f['CompanyName'] = $companies[$compID];

        $offID = filter_input(INPUT_POST, "afCOID", FILTER_SANITIZE_NUMBER_INT);
        $r = db::PrepareLoadSingleRow('SELECT * FROM company_offices WHERE coID=' . $offID);
        $f['Office'] = $r['coName'];
        $f['CreatePaswordLink'] = $_link;

        $mail = filter_input(INPUT_POST, "afEmail", FILTER_SANITIZE_STRING);
        $subject = self::replaceNames($subject, $f);
        $message = self::replaceNames($message, $f);

        if (! cf::sendMail($mail, $subject, $message, $GLOBALS['EMAIL_MAILER_NAME'], $GLOBALS['EMAIL_MAILER_ADDRESS'])) {
            msg::set('There was a problem to send welcome email.', 'error');
            return false;
        }

        return true;
    }
}
