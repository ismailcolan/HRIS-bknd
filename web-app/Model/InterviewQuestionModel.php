<?php
class InterviewQuestionModel
{
    const TBL = 'interview_questions';

    /**
     * Get all questions for a position, grouped by round
     * Returns: array( 1 => [rows], 2 => [rows], 3 => [rows] )
     */
    public static function getByPosition($posID)
    {
        $posID = (int)$posID;
        $rows = db::PrepareLoad(
            "SELECT * FROM " . self::TBL . "
             WHERE iqPosID = {$posID}
             ORDER BY iqRound ASC, iqID ASC"
        );
        $grouped = array(1 => array(), 2 => array(), 3 => array());
        if ($rows) {
            foreach ($rows as $r) {
                $grouped[(int)$r['iqRound']][] = $r;
            }
        }
        return $grouped;
    }

    /**
     * Get questions for a specific position + round
     */
    public static function getByPositionAndRound($posID, $round)
    {
        $posID = (int)$posID;
        $round = (int)$round;
        $rows = db::PrepareLoad(
            "SELECT * FROM " . self::TBL . "
             WHERE iqPosID = {$posID} AND iqRound = {$round}
             ORDER BY iqID ASC"
        );
        return $rows ? $rows : array();
    }

    /**
     * Save all questions for a position from POST data.
     * Expects $_POST['iq_questions'][round][n] = 'question text'
     * Deletes removed ones, inserts new ones.
     */
    public static function saveFromPost($posID)
    {
        $posID = (int)$posID;
        if (!isset($_POST['iq_questions']) || !is_array($_POST['iq_questions'])) {
            // No questions posted — delete all for this position
            db::SQL("DELETE FROM " . self::TBL . " WHERE iqPosID = {$posID}");
            return;
        }

        $adminId = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;

        // Collect submitted IDs to know which to delete
        $submittedIDs = array();

        foreach (array(1, 2, 3) as $round) {
            if (!isset($_POST['iq_questions'][$round])) continue;
            foreach ($_POST['iq_questions'][$round] as $key => $text) {
                $text = trim($text);
                if ($text === '') continue;

                $escapedText = db::EscapeStr($text);

                // Check if it has an existing ID
                $existingID = 0;
                if (isset($_POST['iq_ids'][$round][$key])) {
                    $existingID = (int)$_POST['iq_ids'][$round][$key];
                }

                if ($existingID > 0) {
                    // Update existing
                    db::SQL(
                        "UPDATE " . self::TBL . "
                         SET iqQuestion = '{$escapedText}'
                         WHERE iqID = {$existingID} AND iqPosID = {$posID}"
                    );
                    $submittedIDs[] = $existingID;
                } else {
                    // Insert new
                    db::SQL(
                        "INSERT INTO " . self::TBL . "
                         (iqPosID, iqRound, iqQuestion, iqCreatedBy, iqCreatedAt)
                         VALUES ({$posID}, {$round}, '{$escapedText}', {$adminId}, NOW())"
                    );
                    $submittedIDs[] = db::LastID();
                }
            }
        }

        // Delete questions that were removed in the UI
        if (!empty($submittedIDs)) {
            $idList = implode(',', $submittedIDs);
            db::SQL(
                "DELETE FROM " . self::TBL . "
                 WHERE iqPosID = {$posID} AND iqID NOT IN ({$idList})"
            );
        } else {
            db::SQL("DELETE FROM " . self::TBL . " WHERE iqPosID = {$posID}");
        }
    }

    /**
     * Delete all questions for a position (call on position delete)
     */
    public static function deleteByPosition($posID)
    {
        db::SQL("DELETE FROM " . self::TBL . " WHERE iqPosID = " . (int)$posID);
    }
}