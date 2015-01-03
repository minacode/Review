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
	*/
	public function __construct($a_ref_id = 0) {
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
		global $ilDB, $ilCtrl;
		
		$ilDB->insert("rep_robj_xrev_revobj",
						  array("id" => array("integer", $this->getId()),
								  "group_id" => array("integer", $ilCtrl->getParameterArrayByClass("ilrepositorygui")["ref_id"])
						  )
		);
	}
	
	/**
	* Read data from db
	*/
	function doRead() {
		global $ilDB;
		
		$set = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revobj WHERE id=%s",
									array("integer"),
									array($this->getId())
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
		
		$ilDB->update("rep_robj_xrev_revobj",
						  array("group_id" => array("integer", $this->getGroupId())),
						  array("id" => array("integer", $this->getId()))
		);
	}
	
	/**
	* Delete data from db
	*/
	function doDelete() {
		// pointless, it seems this function is not called by ILIAS
	}
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id, $a_copy_id, $new_obj) {
		$new_obj->setGroupId($this->getGroupId());
		$new_obj->update();
	}
	
	
	/**
	* Get the id of the group this object belongs to
	*/
	public function getGroupId() {
		return $this->group_id;
	}
	
	/**
	* Set the id of the group this object belongs to
	*/
	public function setGroupId($group_id) {
		$this->group_id = $group_id;
	}
		
	/**
	* Load all questions from the groups´ Question Pools,
	* thus updating the plugin´s question db
	*/
	private function syncQuestionDB() {
		global $ilDB, $ilUser;		
		
		function cmp_rec($a, $b) {
			if ($a["question_id"] > $b["question_id"])
				return 1;
			if ($a["question_id"] < $b["question_id"])
				return -1;
			return 0;
		}
		
		// uncomment as soos as needed
		// $ilDB->lockTables(array("qpl_questions", "rep_robj_xrev_quest"));
		
		$qpl = $ilDB->queryF("SELECT qpl_questions.question_id AS question_id, tstamp FROM qpl_questions ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL",
								   array("integer"),
								   array($this->getGroupId()));
		$db_questions = array();
		while ($db_question = $ilDB->fetchAssoc($qpl))
			$db_questions[] = $db_question;
		$pqs = $ilDB->queryF("SELECT * FROM rep_robj_xrev_quest WHERE review_obj=%s",
									array("integer"), array($this->getId()));
		$pl_questions = array();
		while ($pl_question = $ilDB->fetchAssoc($pqs))
			$pl_questions[] = $pl_question;
		
		foreach ($db_questions as $db_question) {
			foreach ($pl_questions as $pl_question) {
				if ($db_question["question_id"] == $pl_question["question_id"]) {
					if ($db_question["tstamp"] > $pl_question["timestamp"]) {
						$ilDB->update("rep_robj_xrev_quest",
										  array("timestamp" => array("integer", $db_question["tstamp"])),
										  array("question_id" => array("integer", $db_question["question_id"]),
										  		  "review_obj" => array("integer", $this->getId())
										  )
						);
						$ilDB->update("rep_robj_xrev_revi",
										  array("state" => array("integer", 0)),
										  array("question_id" => array("integer", $db_question["question_id"]),
										  		  "review_obj" => array("integer", $this->getId())
										  )
						);
						$this->notifyReviewersAboutChange($db_question);
						break;
					}
				}
			}
		}
		
		foreach (array_udiff($db_questions, $pl_questions, "cmp_rec") as $new_question) {
			$ilDB->insert("rep_robj_xrev_quest", array("id" => array("integer", $ilDB->nextId("rep_robj_xrev_quest")),
															 		 "question_id" => array("integer", $new_question["question_id"]),
															 		 "timestamp" => array("integer", $new_question["tstamp"]),
															 		 "state" => array("integer", 0),
															  		 "review_obj" => array("integer", $this->getId())
															 )
			);
			$this->notifyAdminsAboutNewQuestion($new_question);
		}

		foreach (array_udiff($pl_questions, $db_questions, "cmp_rec") as $del_question) {
			$this->notifyReviewersAboutDeletion($del_question);
			$ilDB->manipulateF("DELETE FROM rep_robj_xrev_quest WHERE question_id=%s AND review_obj=%s",
									 array("integer", "integer"),
									 array($del_question["question_id"], $this->getId()));
			$ilDB->manipulateF("DELETE FROM rep_robj_xrev_revi WHERE question_id=%s AND review_obj=%s",
									 array("integer", "integer"),
									 array($del_question["question_id"], $this->getId()));
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

		$qpl = $ilDB->queryF("SELECT qpl_questions.question_id AS id, title FROM qpl_questions ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=qpl_questions.question_id ".
								   "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL ".
								   "AND qpl_questions.owner=%s AND rep_robj_xrev_quest.state!=%s AND rep_robj_xrev_quest.review_obj=%s",
								   array("integer", "integer", "integer", "integer"),
								   array($this->getGroupId(), $ilUser->getId(), 2, $this->getId()));
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
								   "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=qpl_questions.question_id ".
								   "WHERE crs_items.parent_id=%s AND rep_robj_xrev_revi.reviewer=%s AND rep_robj_xrev_revi.review_obj=%s ".
								   "AND rep_robj_xrev_quest.state=1",
								   array("integer", "integer", "integer"),
								   array($this->getGroupId(), $ilUser->getId(), $this->getId()));
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
	* @param		int		$id			ID of the review to be updated
	* @param		array		$form_data	user input to be stored
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
		
		$rev = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi ".
									"INNER JOIN usr_data ON usr_data.usr_id=rep_robj_xrev_revi.reviewer ".
									"WHERE question_id=%s AND review_obj=%s",
									array("integer", "integer"),
									array($q_id, $this->getId()));
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
								   array($this->getGroupId(), $this->getGroupId()));
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

		$qpl = $ilDB->queryF("SELECT qpl_questions.question_id AS id, title FROM qpl_questions ".
								   "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								   "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								   "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=qpl_questions.question_id ".
								   "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL AND ".
								   "rep_robj_xrev_quest.state=0 AND rep_robj_xrev_quest.review_obj=%s",
								   array("integer", "integer"),
								   array($this->getGroupId(), $this->getId()));
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
				$ilDB->insert("rep_robj_xrev_revi", array("id" => array("integer", $ilDB->nextID("rep_robj_xrev_revi")),
																		"timestamp" => array("integer", time()),
																		"reviewer" => array("integer", explode("_", $reviewer_id)[2]),
																		"question_id" => array("integer", $row["q_id"]),
																		"state" => array("integer", 0),
																		"desc_corr" => array("integer", 0),
																		"desc_relv" => array("integer", 0),
																		"desc_expr" => array("integer", 0),
																		"quest_corr" => array("integer", 0),
																		"quest_relv" => array("integer", 0),
																		"quest_expr" => array("integer", 0),
																		"answ_corr" => array("integer", 0),
																		"answ_relv" => array("integer", 0),
																		"answ_expr" => array("integer", 0),
																		"taxonomy" => array("integer", 0),
																		"knowledge_dimension" => array("integer", 0),
																		"rating" => array("integer", 0),
																		"eval_comment" => array("clob", ''),
																		"expertise" => array("integer", 0),
																		"review_obj" => array("integer", $this->getId())
								  								)
				);
				$ilDB->update("rep_robj_xrev_quest", array("state" => array("integer", 1)),
								  array("question_id" => array("integer", $row["q_id"]), "review_obj" => array("integer", $this->getId())));
			}
		}
	}
	
	/**
	* Load all questions for which all reviews have been completed
	*
	* @return	array		$questions		array of associative arrays of questions
	*/
	public function loadReviewedQuestions() {
		global $ilDB;
		
		$req = $ilDB->queryF("SELECT qpl_questions.question_id, qpl_questions.title, qpl_questions.author ".
									"FROM qpl_questions ".
									"INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=qpl_questions.question_id ".
									"WHERE rep_robj_xrev_quest.review_obj=%s AND rep_robj_xrev_quest.state=1",
									array("integer"),
									array($this->getId()));
		$questions = array();							
		while ($question = $ilDB->fetchAssoc($req)) {
			$rev = $ilDB->queryF("SELECT id FROM rep_robj_xrev_revi ".
										"WHERE state=0 AND question_id=%s AND review_obj=%s",
										array("integer", "integer"),
										array($question["question_id"], $this->getId()));
			if ($ilDB->fetchAssoc($rev) == 0)
				$questions[] = $question;
		}
		return $questions;
	}
	
	/**
	* Remove questions from the review cycle by marking them as finished
	*
	* @param		array		$questions		array of question_ids
	*/
	public function finishQuestions($questions) {
		global $ilDB;
		foreach ($questions as $question_id) {
			$ilDB->update("rep_robj_xrev_quest",
							  array("state" => array("integer", 2)),
							  array("question_id" => array("integer", $question_id),
							  		  "review_obj" => array("integer", $this->getId())
							  )
			);
		}
	}
	
	/*
	* Get taxonomy data from DB
	*
	* @return		array			$taxonomies		associative array of taxonomy value id => term
	*/
	public static function taxonomy() {
		global $ilDB, $lng;
		$res = $ilDB->query("SELECT * FROM rep_robj_xrev_taxon");
		$taxonomies = array();
		while ($taxonomy = $ilDB->fetchAssoc($res))
			$taxonomies[$taxonomy["id"]] = $lng->txt("rep_robj_xrev_".$taxonomy["term"]);
		return $taxonomies;
	}
		
	/*
	* Get knowledge dimension data from DB
	*
	* @return		array			$know_dims		associative array of knowledge dimension value id => term
	*/
	public static function knowledgeDimension() {
		global $ilDB, $lng;
		$res = $ilDB->query("SELECT * FROM rep_robj_xrev_knowd");
		$know_dims = array();
		while ($know_dim = $ilDB->fetchAssoc($res))
			$know_dims[$know_dim["id"]] = $lng->txt("rep_robj_xrev_".$know_dim["term"]);
		return $know_dims;
	}
		
	/*
	* Get expertise data from DB
	*
	* @return		array			$expertises		associative array of expertise value id => term
	*/
	public static function expertise() {
		global $ilDB, $lng;
		$res = $ilDB->query("SELECT * FROM rep_robj_xrev_expert");
		$expertises = array();
		while ($expertise = $ilDB->fetchAssoc($res))
			$expertises[$expertise["id"]] = $lng->txt("rep_robj_xrev_".$expertise["term"]);
		return $expertises;
	}	
	
	/*
	* Get rating data from DB
	*
	* @return		array			$ratings		associative array of rating value id => term
	*/
	public static function rating() {
		global $ilDB, $lng;
		$res = $ilDB->query("SELECT * FROM rep_robj_xrev_rate");
		$ratings = array();
		while ($rating = $ilDB->fetchAssoc($res))
			$ratings[$rating["id"]] = $lng->txt("rep_robj_xrev_".$rating["term"]);
		return $ratings;
	}
		
	/*
	* Get evaluation data from DB
	*
	* @return		array			$evaluations		associative array of evaluation value id => term
	*/
	public static function evaluation() {
		global $ilDB, $lng;
		$res = $ilDB->query("SELECT * FROM rep_robj_xrev_eval");
		$evaluations = array();
		while ($evaluation = $ilDB->fetchAssoc($res))
			$evaluations[$evaluation["id"]] = $lng->txt("rep_robj_xrev_".$evaluation["term"]);
		return $evaluations;
	}
	
	/**
	* Load metadata of a question
	*
	* @param		int		$q_id			question id
	*
	* @return	array		$question	$question metadata as an associative array
	*/
	public function loadQuestionMetaData($q_id) {
		global $ilDB;
		$req = $ilDB->queryF("SELECT qpl_questions.title, qpl_questions.description, usr_data.firstname, usr_data.lastname ".
									"FROM qpl_questions ".
									"INNER JOIN usr_data ON usr_data.usr_id=qpl_questions.owner ".
									"WHERE qpl_questions.question_id=%s",
									array("integer"),
									array($q_id)
						  );
		return $ilDB->fetchAssoc($req);
	}
	
	/**
	* Load taxonomy and knowledge dimension of a question
	*
	* @param		int		$q_id			question id
	*
	* @return	array		$question	$question taxonomy data as an associative array
	*/
	public function loadQuestionTaxonomyData($q_id) {
		global $ilDB;
		$req = $ilDB->queryF("SELECT qpl_rev_qst.taxonomy, qpl_rev_qst.knowledge_dimension ".
									"FROM qpl_rev_qst ".
									"WHERE qpl_rev_qst.question_id=%s",
									array("integer"),
									array($q_id)
						  );
		return $ilDB->fetchAssoc($req);
	}
	
	/**
	* Prepare message output to inform reviewers about
	* their allocation to a certain question
	*
	* @param		array			$alloc_matrix			array of arrays of reviewers
	*/
	public function notifyReviewersAboutAllocation($alloc_matrix) {
		$receivers = array();
		foreach ($alloc_matrix as $row)
			foreach ($row["reviewers"] as $reviewer_id => $checked)
				if ($checked)
					$receivers[] = explode("_", $reviewer_id)[2];
		$this->performNotification($receivers, "msg_review_requested");
	}
	
	/**
	* Prepare message output to inform authors about
	* the acceptance of a certain question by the group´s admin
	*
	* @param		array			$question_ids			array of the ids of the accepted question
	*/
	public function notifyAuthorsAboutAcceptance($question_ids) {
		global $ilDB;
		$receivers = array();
		foreach ($question_ids as $id)
			$receivers[] = $ilDB->fetchAssoc($ilDB->queryF("SELECT owner FROM qpl_questions WHERE question_id=%s",
																		  array("integer"),
																		  array($id)
																 )
										 )["owner"];
		$this->performNotification($receivers, "msg_question_accepted");
	}
	
	/**
	* Prepare message output to inform an author about
	* the completion of a review on a certain question
	*
	* @param		integer			$review_id			id of the completed review
	*/
	public function notifyAuthorAboutCompletion($review_id) {
		global $ilDB;
		$rev = $ilDB->queryF("SELECT reviewer FROM rep_robj_xrev_revi WHERE id=%s",
									array("integer"),
									array($review_id)
						  );
		$receivers = array();
		while ($receiver = $ilDB->fetchAssoc($rev))
			$receivers[] = $receiver["reviewer"];
		$this->performNotification($receivers, "msg_review_completed");
	}
	
	/**
	* Prepare message output to inform reviewers about
	* a change of a certain question they have to review
	*
	* @param		array			$question			question data as an associative array
	*/
	public function notifyReviewersAboutChange($question) {
		global $ilDB;
		$res = $ilDB->queryF("SELECT reviewer FROM rep_robj_xrev_revi ".
									"WHERE review_obj=%s AND question_id=%s",
									array("integer", "integer"),
									array($this->getId(), $question["question_id"])
						  );
		$receivers = array();
		while ($receiver = $ilDB->fetchAssoc($res))
			$receivers[] = $receiver["reviewer"];
		$this->performNotification($receivers, "msg_question_edited");
	}
	
	/**
	* Prepare message output to inform the group´s admins about
	* the creation of a new question
	*
	* @param		array			$question			question data as an associative array
	*/
	public function notifyAdminsAboutNewQuestion($question) {
		global $ilDB;
		$res = $ilDB->queryF("SELECT usr_id FROM rbac_ua ".
									"INNER JOIN object_data ON object_data.obj_id=rbac_ua.rol_id ".
								   "WHERE object_data.title='il_grp_admin_%s'",
								   array("integer"),
								   array($this->getGroupId())
						  );
		$receivers = array();
		while ($receiver = $ilDB->fetchAssoc($res))
			$receivers[] = $receiver["usr_id"];
		$this->performNotification($receivers, "msg_question_created");
	}
	
	/**
	* Prepare message output to inform reviewers about
	* the deletion of a question they had to review
	*
	* @param		array			$question			question data as an associative array
	*/
	public function notifyReviewersAboutDeletion($question) {
		global $ilDB;
		$res = $ilDB->queryF("SELECT reviewer FROM rep_robj_xrev_revi ".
									"WHERE review_obj=%s AND question_id=%s",
									array("integer", "integer"),
									array($this->getId(), $question["question_id"])
						  );
		$receivers = array();
		while ($receiver = $ilDB->fetchAssoc($res))
			$receivers[] = $receiver["reviewer"];
		$this->performNotification($receivers, "msg_question_deleted");
	}
	
	/**
	* Created and send an ILIAS message based on data prepared by this object´s notify... methods
	*
	* @param		array			$receivers			array of user ids corresponding to the receivers of the message
	* @param		string		$message_type		the kind of information to be sent
	*/
	private function performNotification($receivers, $message_type) {
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();
		$ntf->setObjId($this->getId());
		$ntf->setLangModules(array("rep_robj_xrev"));
		$ntf->setSubjectLangId("rep_robj_xrev_".$message_type."_subj");
		$ntf->setIntroductionLangId("rep_robj_xrev_".$message_type."_intr");
		
		$ntf->sendMail($receivers);
	}
}
?>
