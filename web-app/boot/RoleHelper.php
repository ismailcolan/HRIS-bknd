<?php
class RoleHelper
{
    public static function currentRole()
    {
        if (isset($_SESSION['role'])) {
            return strtolower($_SESSION['role']);
        }
        return '';
    }

    public static function isAdmin()
    {
        return self::currentRole() === 'admin';
    }

    public static function isHRManager()
    {
        return self::currentRole() === 'hr_manager';
    }

    public static function isInterviewer()
    {
        return self::currentRole() === 'interviewer';
    }

    public static function canAccessSettings()
    {
        return self::isAdmin();
    }

    public static function canAddEmployee()
    {
        return self::isAdmin();
    }

    public static function canViewHiredEmployees()
    {
        return self::isAdmin();
    }

    public static function canViewApplications()
    {
        return self::isAdmin() || self::isHRManager() || self::isInterviewer();
    }

    public static function canEditApplications()
    {
        return self::isAdmin() || self::isHRManager();
    }

    public static function canChangeApplicationStatus()
    {
        return self::isAdmin() || self::isHRManager();
    }

    public static function canAssignInterviewers()
    {
        return self::isAdmin() || self::isHRManager();
    }

    public static function canAddInterviewNotes()
    {
        return self::isAdmin() || self::isHRManager() || self::isInterviewer();
    }

    public static function isAssignedToApplication($applicationId)
    {
        if (self::isAdmin() || self::isHRManager()) {
            return true;
        }

        if (self::isInterviewer()) {
            $interviewerId = (int)$_SESSION['admin_id'];
            if ($interviewerId === 0) return false;

            $count = db::PrepareLoadSingleCellValue(
                "SELECT COUNT(*) FROM application_interviewers 
                 WHERE application_id = " . (int)$applicationId . " 
                 AND interviewer_id = " . $interviewerId
            );
            return (int)$count > 0;
        }

        return false;
    }

    public static function denyAccess()
    {
        msg::set('You do not have permission to access this page.', 'error');
        cf::Redirect(ADMIN . 'home');
        exit;
    }

    public static function requireAdmin()
    {
        if (!self::isAdmin()) {
            self::denyAccess();
        }
    }

    public static function requireAdminOrHR()
    {
        if (!self::isAdmin() && !self::isHRManager()) {
            self::denyAccess();
        }
    }

    public static function requireAnyRole()
    {
        if (empty(self::currentRole())) {
            self::denyAccess();
        }
    }
    
    
public static function canAccessModule($controller)
{
    $controller = strtolower($controller);

    // ✅ Admin can access everything
    if (self::isAdmin()) {
        return true;
    }

    // ✅ HR Manager allowed modules
    if (self::isHRManager()) {
        return true;
    }

    // ✅ Interviewer
    if (self::isInterviewer()) {
        return in_array($controller, [
            'application'
        ]);
    }

    return false;
}
}

?>