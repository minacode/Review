<?php

include_once("./Services/Repository/classes/class.ilObjectPluginAccess.php");

/*
 * Access/Condition checking for Review object
 *
 * @author 		Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
 * @version $Id$
 */
class ilObjReviewAccess extends ilObjectPluginAccess {

	/*
	 * Checks wether a user may invoke a command or not
	 * (this method is called by ilAccessHandler::checkAccess)
	 *
	 * @param	string		$cmd			command (not permission!)
 	 * @param	string		$permission 	permission
	 * @param	int			$ref_id 		reference id
	 * @param	int			$obj_id 		object id
	 * @param	int			$user_id		user id
	 *
	 * @return	boolean		true, if everything is ok
	 */
    function _checkAccess(
        $cmd,
        $permission,
        $ref_id,
        $obj_id,
        $user_id = ""
    ) {
		global $ilUser, $ilAccess;

		if ($user_id == "") {
			$user_id = $ilUser->getId();
		}

		switch ($permission) {
			case "read":
                if (!ilObjReviewAccess::checkOnline($obj_id)
                    && !$ilAccess->checkAccessOfUser(
                        $user_id,
                        "write",
                        "",
                        $ref_id
                    )
                ) {
					return false;
                }
				break;
		}

		return true;
	}

	/*
	 * Check, if user is allowed to edit or view specific reviews
	 *
     * @param   ilObjReview $review_plugin  the review plugin object
	 * @param	int			$obj_id 		object id
	 * @param	int			$user_id		user id
	 * @param	string		$cmd			command
	 * @param   string		$obj_type		type of object (review or question)
	 *
	 * @return	boolean 	$_              true, if user gets access
	 */
    static function checkAccessToObject(
        $review_plugin,
        $obj_id,
        $user_id,
        $cmd,
        $obj_type
    ) {
		global $ilDB, $ilUser;

		if ($user_id == "") {
			$user_id = $ilUser->getId();
		}

		switch ($cmd) {
			case "inputReview":
			case "saveReview":
                if (count($review_plugin->review_db->getReviewForms(
                    array("id" => $obj_id, "reviewer" => $user_id)
                )) == 1) {
                    return true;
                }
                /*
                $res = $ilDB->queryF(
                    "SELECT COUNT(id) FROM rep_robj_xrev_revi "
                    . "WHERE id=%s AND reviewer=%s",
                    array("integer", "integer"),
                    array($obj_id, $user_id)
                );
				if ($ilDB->fetchAssoc($res)["count(id)"] == 1) {
					return true;
                }
                */
				break;
			case "showReviews":
                if ($obj_type == "review") {
                    if (count($review_plugin->review_db->getReviewForms(
                        array("question_id" => $obj_id, "reviewer" => $user_id)
                    )) == 1) {
                        return true;
                    }
                }
                if ($obj_type == "question") {
                    if (count($review_plugin->review_db->getCycleQuestions(
                        array("question_id" => $obj_id, "owner" => $user_id)
                    )) == 1) {
                        return true;
                    }
                }
                /*
				if ($obj_type == "review") {
                    $res = $ilDB->queryF(
                        "SELECT COUNT(id) FROM rep_robj_xrev_revi "
                        . "WHERE id=%s AND reviewer=%s",
                        array("integer", "integer"),
                        array($obj_id, $user_id)
                    );
					if ($ilDB->fetchAssoc($res)["count(id)"] == 1) {
						return true;
                    }
				}
				if ($obj_type == "question") {
                    $res = $ilDB->queryF(
                        "SELECT COUNT(question_id) FROM qpl_questions "
                        . "WHERE question_id=%s AND owner=%s",
                        array("integer", "integer"),
                        array($obj_id, $user_id)
                    );
					if ($ilDB->fetchAssoc($res)["count(question_id)"] == 1) {
						return true;
                    }
				}
                */
				break;
		}
		return false;
	}

	/*
	 * Must return true due to ILIAS magic
	 *
	 * @param	integer     $id                 id
	 */
	public static function checkOnline($id) {
		return true;
	}
}
?>
