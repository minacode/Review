<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for Review repository object.
*
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/
class ilObjReview extends ilObjectPlugin {
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}
	

	/**
	* Get type.
	*/
	final function initType() {
		$this->setType("xrev");
	}
	
	/**
	* Create object
	*/
	function doCreate() {
		global $ilDB;
		
		$group_res = $ilDB->queryF("SELECT parent FROM tree WHERE child=%s",
											array("integer"),
											$this->getId());
											
		while ($group_id = $ilDB->fetchAssoc($group_res)["parent"]);
		
		$ilDB->manipulate("INSERT INTO rep_robj_xrev_revobj ".
			"(id, group_id) VALUES (".
			$ilDB->quote($this->getId(), "integer"). ", ". $ilDB->quote($group_id, "integer").
			")");
	}
	
	/**
	* Read data from db
	*/
	function doRead() {
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xrev_revobj ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set)) {
			$this->obj_id = $rec["obj_id"];
			$this->group_id = $rec["group_id"];
		}
		
		$this->syncQuestionDB();
	}
	
	/**
	* Update data
	*/
	function doUpdate() {
		global $ilDB;
	}
	
	/**
	* Delete data from db
	*/
	function doDelete() {
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		
	}
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj) {
		global $ilDB;
		
		$new_obj->update();
	}
		
	/**
	* Load all questions from the groups´ Question Pools,
	* thus updating the plugin´s question db
	*/
	private function syncQuestionDB() {
		global $ilDB, $ilUser;		
		
		function cmp_rec($a, $b) {
			if ($a["id"] > $b["id"])
				return 1;
			if ($a["id"] < $b["id"])
				return -1;
			return 0;
		}
		
		// uncomment as soos as needed
		// $ilDB->lockTables(array("qpl_questions", "rep_robj_xrev_quest"));
		
		$qpl = $ilDB->queryF("SELECT question_id AS id, tstamp FROM qpl_questions ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL",
								   array("integer"),
								   $this->getId());
		$db_questions = array();
		while ($db_question = $ilDB->fetchAssoc($qpl))
			$db_questions[] = $db_question;
		$pqs = $ilDB->query("SELECT * FROM rep_robj_xrev_quest");
		$pl_questions = array();
		while ($pl_question = $ilDB->fetchAssoc($pqs))
			$pl_questions[] = $pl_question;
		
		foreach ($db_questions as $db_question) {
			foreach ($pl_questions as $pl_question) {
				if ($db_question["id"] == $pl_question["id"]) {
					if ($db_question["tstamp"] > $pl_question["timestamp"]) {
						$ilDB->manipulateF("UPDATE rep_robj_xrev_quest SET timestamp=%s WHERE id=%s",
												 array("integer", "integer"),
												 array($db_question["tstamp"], $db_question["id"]));
					$ilDB->manipulateF("UPDATE rep_robj_xrev_revi SET state=0 WHERE question_id=%s",
											 array("integer"),
											 array($db_question["id"]));
					break;
					}
				}
			}
		}
		
		$new_questions = array_udiff($db_questions, $pl_questions, "cmp_rec");
		foreach ($new_questions as $new_question)
			$ilDB->manipulateF("INSERT INTO rep_robj_xrev_quest (id, timestamp) VALUES (%s, %s)",
									 array("integer", "integer"),
									 array($new_question["id"], $new_question["tstamp"]));			
		
		$del_questions = array_udiff($pl_questions, $db_questions, "cmp_rec");
		foreach ($del_questions as $del_question) {
			$ilDB->manipulateF("DELETE FROM rep_robj_xrev_quest WHERE id=%s",
									 array("integer"),
									 array($del_question["id"]));
			$ilDB->manipulateF("DELETE FROM rep_robj_xrev_revi WHERE question_id=%s",
									 array("integer"),
									 array($del_question["id"]));
		}
		
		//uncomment as soon as needed
		// $ilDB->unlockTables();
	}
	
	/*
	* Load all questions created by the user in all of the groups´ question pools
	*
	* @return	array		$db_questions		the questions loaded by this function as an associative array
	*/ 
	public function loadQuestionsByUser() {
		global $ilDB, $ilUser;

		$qpl = $ilDB->queryF("SELECT question_id AS id, title FROM qpl_questions ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL AND qpl_questions.owner=%s",
								   array("integer", "integer"),
								   array($this->getId(), $ilUser->getId()));
		$db_questions = array();
		while ($db_question = $ilDB->fetchAssoc($qpl))
			$db_questions[] = $db_question;
		return $db_questions;
	}
	
	/*
	* Load all reviews created by the user for all questions in the groups´ question pools
	*
	* @return	array		$reviews		the reviews loaded by this function as an associative array
	*/ 
	public function loadReviewsByUser() {
		global $ilDB, $ilUser;

		$rev = $ilDB->queryF("SELECT rep_robj_xrev_revi.id, qpl_questions.title, qpl_questions.question_id, rep_robj_xrev_revi.state FROM rep_robj_xrev_revi ".
									"INNER JOIN qpl_questions ON qpl_questions.question_id=rep_robj_xrev_revi.question_id ".
									"INNER JOIN rep_robj_xrev_revobj ON rep_robj_xrev_revobj.id=rep_robj_xrev_revi.review_obj ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "WHERE crs_items.parent_id=%s AND rep_robj_xrev_revi.reviewer=%s",
								   array("integer", "integer"),
								   array($this->getId(), $ilUser->getId()));
		$reviews = array();
		while ($review = $ilDB->fetchAssoc($rev))
			$reviews[] = $review;
		return $reviews;
	}

	/*
	* Load a review with a certain ID from the Review Database
	*
	* @param		int		$a_id		ID of the review to load
	*
	* @return	array		$reviews	all reviews with the given ID (exactly one or none)
	*/ 
	public function loadReviewById($a_id) {
		global $ilDB;
		
		$rev = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi WHERE id=%s",
									array("integer"),
									array($a_id));
		
		$reviews = array();
		while ($review = $ilDB->fetchAssoc($rev))
			$reviews[] = $review;
		return $reviews[0];
	}
	
	/*
	* Update data of an existing review by form input
	*
	* @param		$id			ID of the review to be updated
	* @param		$form_data	user input to be stored
	*/
	public function storeReviewByID($id, $form_data) {
		global $ilDB;
		
		$ilDB->update("rep_robj_xrev_revi", array("timestamp" => array("integer", time()),
																"state" => array("integer", 1),
																"desc_corr" => array("integer", $form_data["dc"]),
																"desc_relv" => array("integer", $form_data["dr"]),
																"desc_expr" => array("integer", $form_data["de"]),
																"quest_corr" => array("integer", $form_data["qc"]),
																"quest_relv" => array("integer", $form_data["qr"]),
																"quest_expr" => array("integer", $form_data["qe"]),
																"answ_corr" => array("integer", $form_data["ac"]),
																"answ_relv" => array("integer", $form_data["ar"]),
																"answ_expr" => array("integer", $form_data["ae"]),
																"taxonomy" => array("integer", $form_data["cog_r"]),
																"knowledge_dimension" => array("integer", $form_data["kno_r"]),
																"rating" => array("integer", $form_data["group_e"]),
																"eval_comment" => array("clob", $form_data["comment"]),
																"expertise" => array("integer", $form_data["exp"])),
						  array("id" => array("integer", $id)));
	}
	
	/*
	* Load a review with a certain ID from the Review Database
	*
	* @param		int		$a_id		ID of the review to load
	*
	* @return	array		$reviews	all reviews with the given ID (exactly one or none)
	*/ 
	public function loadReviewsByQuestion($q_id) {
		global $ilDB;
		
		$rev = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi WHERE question_id=%s",
									array("integer"),
									array($q_id));
		$reviews = array();
		while ($review = $ilDB->fetchAssoc($rev))
			$reviews[] = $review;
			
		return $reviews;
	}
	
	/*
	* Load all members of a group
	*
	* @return	array		$reviewers	ids and names of the group members
	*/ 
	public function loadReviewers() {
		global $ilDB;
		
		$res = $ilDB->queryF("SELECT usr_data.usr_id AS usr_id, firstname, lastname FROM usr_data ".
									"INNER JOIN rbac_ua ON rbac_ua.usr_id=usr_data.usr_id ".
								   "INNER JOIN object_data ON object_data.obj_id=rbac_ua.rol_id ".
								   "WHERE object_data.title='il_grp_admin_%s' OR object_data.title='il_grp_member_%s'",
								   array("integer", "integer"),
								   /*array($_GET["ref_id"], $_GET["ref_id"])*/array(66, 66));
		$reviewers = array();
		while ($reviewer = $ilDB->fetchAssoc($res))
			$reviewers[] = $reviewer;
		return $reviewers;
	}
	
	/*
	* Load all questions that currently have no reviewer allocated to them
	*
	* @return	array		$questions		the question loaded by this function as an associative array
	*/ 
	public function  loadUnallocatedQuestions() {
		global $ilDB, $ilUser;

		$qpl = $ilDB->queryF("SELECT question_id AS id, title FROM qpl_questions ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.id=qpl_questions.question_id ".
								   "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL AND rep_robj_xrev_quest.state=0",
								   array("integer"),
								   $this->getId()));
		$questions = array();
		while ($question = $ilDB->fetchAssoc($qpl))
			$questions[] = $question;
		return $questions;
	}
	
	/*
	* Save matrix input as review entities containing the allocated reviewer
	*
	* @param		array		$alloc_matrix		array of arrays of reviewers
	*/
	public function allocateReviews($alloc_matrix) {
		global $ilDB;
		
		$entities = array();
		foreach ($alloc_matrix as $row) {
			foreach ($row["reviewers"] as $reviewer_id => $checked) {
				if (!$checked)
					continue;
				$ilDB->manipulateF("INSERT INTO rep_robj_xrev_revi (id, ".
																					 "timestamp, ".
																					 "reviewer, ".
																					 "question_id, ".
																					 "state, ".
																					 "desc_corr, ".
																					 "desc_relv, ".
																					 "desc_expr, ".
																					 "quest_corr, ".
																					 "quest_relv, ".
																					 "quest_expr, ".
																					 "answ_corr, ".
																					 "answ_relv, ".
																					 "answ_expr, ".
																					 "taxonomy, ".
																					 "knowledge_dimension, ".
																					 "rating, ".
																					 "eval_comment, ".
																					 "expertise, ".
																					 "review_obj) ".
									 "VALUES (%s, %s, %s, %s, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, %s)",
									 array("integer", "integer", "integer", "integer", "integer"),
									 array($ilDB->nextID("rep_robj_xrev_revi"),
									 		 time(), 
									 		 explode("_", $reviewer_id)[2], 
									 		 $row["q_id"], 
									 		 $this->getId()));
				$ilDB->update("rep_robj_xrev_quest", array("state" => array("integer", 1)), array("id" => array("integer", $row["q_id"])));
			}
		}
	}
}
?>
