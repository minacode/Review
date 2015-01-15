<?php

/**
* Test class for ilObjReview.php
*
* @author Peter Merseburger <Peter.Merseburger@mailbox.tu-dresden.de>
*
* $Id$
*/

class ilObjReviewTest extends PHPUnit_Framework_TestCase {
		
		protected $backupGlobals = FALSE;
 
		protected function setUp() {
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
			include_once("Customizing/global/plugins/Services/Repository/RepositoryObject/Review/classes/class.ilObjReview.php");
		}
		
		public function testDoCreate() {
			global $ilDB;
			
			$test_id = 1337;
			$obj = new ilObjReview();
			$obj->setId($test_id);
			$obj->doCreate();
			$result = $ilDB->query("SELECT * FROM rep_robj_xrev_revobj WHERE id=$test_id");
			$record = $ilDB->fetchAssoc($result);
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id = $test_id");
			$target = array(id => $test_id, group_id => 0);
			
			$this->assertEquals($target, $record);
		}
		
		public function testDoUpdate() {
			global $ilDB;
			 
			$test_id = 1337;
			$test_group_id = 1338;
			$obj = new ilObjReview();
			$obj->setId($test_id);
			$obj->doCreate();
			
			$result1 = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revobj WHERE id =%s ",array("integer"),array($test_id));
			$record1 = $ilDB->fetchAssoc($result1);
			
			$obj->setGroupId($test_group_id);
			$obj->doUpdate();
			
			$result2 = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revobj WHERE id =%s ",array("integer"),array($test_id));
			$record2 = $ilDB->fetchAssoc($result2);
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id= $test_id");			
			
			$this->assertFalse($record1 == $record2);
		}
		
		public function testDoRead() {
			global $ilDB;
			
			$test_id1 = 2300;
			$test_id2 = 4600;
			$obj = new ilObjReview();
			
			$obj->setId($test_id1);
			$ilDB->insert("rep_robj_xrev_revobj", array("id" => array("integer", $test_id1), "group_id" => array("integer", $test_id2)));
			
			$obj->doRead();
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id= $test_id1");
			
			$this->assertEquals($test_id2, $obj->getGroupId());
		}
		
		public function testDoClone() {
			global $ilDB;
			
			$test_id1 = 1339;
			$test_id2 = 1340;
			$test_group_id = 42;
			$obj1 = new ilObjReview();
			$obj1->setId($test_id1);
			$obj1->setGroupId($test_group_id);
			$obj2 = new ilObjReview();
			$obj1->setId($test_id2);
			$obj1->doClone($test_id2, $test_id1, $obj2);
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id= $test_id1");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id= $test_id2");
			
			$this->assertEquals($obj1->getGroupId(), $obj2->getGroupId());
		}
		
		public function testLoadQuestionsByUser() {
			global $ilDB, $ilUser;
			
			$obj = new ilObjReview();
			
			$test_id1 = $ilDB->nextID("qpl_questions");
			$test_id2 = $ilDB->nextID("rep_robj_xrev_quest");
			$test_title = "Test Title";
		
			$ilDB->insert("qpl_questions",
							  array("question_id" => array("integer", $test_id1),
							  		  "title" => array("text", $test_title),
							  		  "owner" => array("integer", $ilUser->getId())
							  )
			);
			$ilDB->insert("rep_robj_xrev_quest",
							  array("id" => array("integer", $test_id2),
							  		  "question_id" => array("integer", $test_id1),
							  		  "review_obj" => array("integer", $obj->getId()),
							  		  "state" => array("integer", 1)
							  )
			);			
			
			$record = $obj->loadQuestionsByUser()[0];
			$target = array("id" => $test_id1, "title" => $test_title);
			
			$this->assertEquals($target, $record);
			
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = $test_id1");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = $test_id2");
		}
	
		public function testLoadReviewsByUser() {
			global $ilDB, $ilUser;
			
			$test_id1 = $ilDB->nextID("rep_robj_xrev_revi");
			$test_id2 = $ilDB->nextID("qpl_questions");
			$test_title = "Test Title";
			
			$obj = new ilObjReview();
			
			$ilDB->insert("qpl_questions",
							  array("question_id" => array("integer", $test_id2),
							  		  "title" => array("text", $test_title)
							  )
			);
			$ilDB->insert("rep_robj_xrev_quest",
							  array("question_id" => array("integer", $test_id2),
							  		  "state" => array("integer", 1),
							  )
			);
			$ilDB->insert("rep_robj_xrev_revi",
							  array("id" => array("integer", $test_id1),
							  		  "review_obj" => array("integer", $obj->getId()),
							  		  "reviewer" => array("integer", $ilUser->getId()),
							  		  "state" => array("integer", 0),
							  		  "question_id" => array("integer", $test_id2)
							  )
			);
			
			$record = $obj->loadReviewsByUser()[0];
			
			$target = array("id" => $test_id1,
								 "question_id" => $test_id2,
								 "title" => $test_title,
							  	 "state" => 0
			);
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revi WHERE id=$test_id1");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE question_id=$test_id2");
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id=$test_id2");
			
			$this->assertEquals($target, $record);
		}
		
		public function testLoadReviewById() {
			global $ilDB;
			
			$test_id = $ilDB->nextId("rep_robj_xrev_revi");
			
			$ilDB->insert("rep_robj_xrev_revi", array("id" => array("integer", $test_id)));
			
			$obj = new ilObjReview();
			
			$record = $obj->loadReviewById($test_id);
			$target_res = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi WHERE id=%s", array("integer"), array($test_id));
			$target = $ilDB->fetchAssoc($target_res);
			
			$this->assertEquals($target, $record);
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revi WHERE id=$test_id");
		}		
		
		public function testLoadReviewsByQuestion() {
			global $ilDB;
			
			$test_id1 = $ilDB->nextID("rep_robj_xrev_revi");
			$test_id2 = $ilDB->nextID("qpl_questions");
			
			$obj = new ilObjReview();
			
			$ilDB->manipulateF("INSERT INTO rep_robj_xrev_revi (id, question_id, review_obj) VALUES (%s, %s, %s)",
									 array("integer", "integer", "integer"),
									 array($test_id1, $test_id2, $obj->getId())
			);
			$record = $obj->loadReviewsByQuestion($test_id2)[0];
			$sel = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi WHERE id=%s AND question_id=%s",
										array("integer", "integer"),
										array($test_id1, $test_id2)
			);
			$target = $ilDB->fetchAssoc($sel); 
			
			$ilDB->manipulateF("DELETE FROM rep_robj_xrev_revi WHERE id = %s", array("integer"), array($test_id1));
			
			$this->assertEquals($target, $record);
		}
		

		public function testLoadReviewers() {
			global $ilDB;
			
			$test_id1 = 42;
			$test_id2 = 1337;
			$test_id3 = 23;
			$test_id4 = 77;
			$test_fname = "Max";
			$test_lname = "Mustermann";
			$obj = new ilObjReview();
			$obj->setGroupId($test_id3);
			
			$ilDB->insert("usr_data", array("usr_id" => array("integer", $test_id2),
													  "firstname" => array("text", $test_fname),
													  "lastname" => array("text", $test_lname)
											  )
			);
			$ilDB->insert("rbac_ua", array("usr_id" => array("integer", $test_id2),
													 "rol_id" => array("text", $test_id4)
											  )
			);
			$ilDB->insert("object_data", array("obj_id" => array("integer", $test_id4),
													  	  "title" => array("text", "il_grp_member_" . $obj->getGroupId())
											  )
			);
			$sel = $ilDB->queryF("SELECT usr_id, firstname, lastname FROM usr_data WHERE usr_id=%s",
										array("integer"),
										array($test_id2)
					 );
			$target = $ilDB->fetchAssoc($sel);
			$record = $obj->loadReviewers()[0];
			
			$ilDB->manipulate("DELETE FROM usr_data WHERE usr_id = $test_id2");
			$ilDB->manipulate("DELETE FROM rbac_ua WHERE usr_id = $test_id2");
			$ilDB->manipulate("DELETE FROM object_data WHERE obj_id = $test_id4");
			
			$this->assertEquals($target, $record);
		}

		public function testLoadUnallocatedQuestions() {
			global $ilDB;
			
			$test_id1 = $ilDB->nextId("qpl_questions");
			$test_id2 = $ilDB->nextId("rep_robj_xrev_quest");
			$test_title = "Test Title";
			$test_owner = 1337;
			
			$obj = new ilObjReview();
			
			$ilDB->insert("qpl_questions", array("question_id" => array("integer", $test_id1),
															 "title" => array("text", $test_title),
															 "owner" => array("integer", $test_owner)
													 )
			);
			$ilDB->insert("rep_robj_xrev_quest", array("id" => array("integer", $test_id2),
																	 "question_id" => array("integer", $test_id1),
															 		 "state" => array("integer", 0),
															 		 "review_obj" => array("integer", $obj->getId())
													 		 )
			);
			$target = array("id" => $test_id1, "title" => $test_title, "owner" => $test_owner);
			
			$record = $obj->loadUnallocatedQuestions()[0];
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = $test_id1");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = $test_id2");
			
			$this->assertEquals($target, $record);
		}

		public function testLoadReviewedQuestions() {
			global $ilDB;
			
			$test_id1 = $ilDB->nextID("qpl_questions");
			$test_id2 = $ilDB->nextID("rep_robj_xrev_quest");
			$test_id3 = $ilDB->nextID("rep_robj_xrev_revi");
			$test_title = "Test Title";
			$test_author = "Max Mustermann";
			$obj = new ilObjReview();
			
			$ilDB->insert("qpl_questions",
							  array("question_id" => array("integer", $test_id1),
							  		  "title" => array("text", $test_title),
							  		  "author" => array("text", $test_author)
							  )
			);
			$ilDB->insert("rep_robj_xrev_quest",
							  array("id" => array("integer", $test_id2),
							  		  "state" => array("integer", 1),
							  		  "review_obj" => array("integer", $obj->getId()),
							  		  "question_id" => array("integer", $test_id1)
							  )
			);
			$ilDB->insert("rep_robj_xrev_revi",
							  array("id" => array("integer", $test_id3),
							  		  "question_id" => array("integer", $test_id1),
							  		  "state" => array("integer", 1)
							  )
			);
			
			$sel = $ilDB->queryF("SELECT question_id, title, author FROM qpl_questions WHERE question_id=%s",
										array("integer"), array($test_id1));
			$target = $ilDB->fetchAssoc($sel);
			$record = $obj->loadReviewedQuestions()[0];
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = $test_id1");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = $test_id2");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revi WHERE id = $test_id3");
			
			$this->assertEquals($target, $record);
		}
		
		public function testStoreReviewById() {
			global $ilDB;
			$test_id = $ilDB->nextId("rep_robj_xrev_revi");
			$obj = new ilObjReview();
			
			$data = array("dc" => 2, "dr" => 2, "de" => 2, "qc" => 1, "qr" => 1, "qe" => 1, "ac" => 3, "ar" => 3, "ae" => 3,
							  "kno_r" => 4, "cog_r" => 5, "group_e" => 1, "comment" => "foo", "exp" => 4);
			$ilDB->insert("rep_robj_xrev_revi", array("id" => array("integer", $test_id), "state" => array("integer", 0)));
			$obj->storeReviewByID($test_id, $data);
			$result = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi WHERE id=%s", array("integer"), array($test_id));
			$record = $ilDB->fetchAssoc($result);
			$target = array("reviewer" => $record["reviewer"],
								 "question_id" => $record["question_id"],
								 "state" => 1,
								 "desc_corr" => 2,
								 "desc_relv" => 2,
								 "desc_expr" => 2,
								 "quest_corr" => 1,
								 "quest_relv" => 1,
								 "quest_expr" => 1,
								 "answ_corr" => 3,
								 "answ_relv" => 3,
								 "answ_expr" => 3,
								 "taxonomy" => 5,
								 "knowledge_dimension" => 4,
								 "rating" => 1,
								 "eval_comment" => 'foo',
								 "expertise" => 4,
								 "review_obj" => $obj->getId(),
								 "id" => $record["id"],
								 "timestamp" => $record["timestamp"]
								 );
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revi WHERE id = $test_id");
			
			$this->assertEquals($target, $record);
		}
		
		public function testAllocateReviews() {
			global $ilDB;
			
			$test_id1 = $ilDB->nextId("rep_robj_xrev_quest");
			$test_id2 = $ilDB->nextId("qpl_questions");
			$test_id3 = 23;
			$obj = new ilObjReview();
			
			$ilDB->insert("rep_robj_xrev_quest", array("id" => array("integer", $test_id1),
																	 "question_id" => array("integer", $test_id2),
																	 "review_obj" => array("integer", $obj->getId()),
																	 "state" => array("integer", 0)
															 )
			);
			$matrix = array(array("reviewers" => array("id_".$test_id2."_".$test_id3 => true), "q_id" => $test_id2));
			$obj->allocateReviews($matrix);
			$result = $ilDB->queryF("SELECT state FROM rep_robj_xrev_quest WHERE id=%s", array("integer"), array($test_id1));
			$state = $ilDB->fetchAssoc($result)["state"];
			$result = $ilDB->queryF("SELECT * FROM rep_robj_xrev_revi WHERE question_id=%s AND reviewer=%s",
											array("integer", "integer"), array($test_id2, $test_id3));
			$record = $ilDB->fetchAssoc($result);
			$target = array("reviewer" => $test_id3,
								 "question_id" => $test_id2,
								 "state" => 0,
								 "desc_corr" => 0,
								 "desc_relv" => 0,
								 "desc_expr" => 0,
								 "quest_corr" => 0,
								 "quest_relv" => 0,
								 "quest_expr" => 0,
								 "answ_corr" => 0,
								 "answ_relv" => 0,
								 "answ_expr" => 0,
								 "taxonomy" => 0,
								 "knowledge_dimension" => 0,
								 "rating" => 0,
								 "eval_comment" => '',
								 "expertise" => 0,
								 "review_obj" => $obj->getId()
								 );
			$target["id"] = $record["id"];
			$target["timestamp"] = $record["timestamp"];
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = $test_id1");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revi WHERE question_id = $test_id2 AND reviewer = $test_id3");
			
			$this->assertEquals(1, $state);
			$this->assertEquals($target, $record);
		}
		
		public function testFinishQuestions() {
			global $ilDB;
			
			$test_id1 = $ilDB->nextID("rep_robj_xrev_quest");
			$test_id2 = 42;
			
			$obj = new ilObjReview();
			$ilDB->insert("rep_robj_xrev_quest",
							  array("id" => array("integer", $test_id1),
							  		  "question_id" => array("integer", $test_id2),
							  		  "state" => array("integer", 1),
							  		  "review_obj" => array("integer", $obj->getID())
							  )
			);
			$questions = array($test_id2);
			$obj->finishQuestions($questions);
			
			$result = $ilDB->queryF("SELECT state FROM rep_robj_xrev_quest WHERE question_id=%s", array("integer"), array($test_id2));
			$record = $ilDB->fetchAssoc($result);
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = $test_id1");
			
			$this->assertEquals(2, $record["state"]);
		}

		/*
		* taxonomy(), knowledgeDimension(), expertise(), rating() and evaluation()
		* work like getter methods and do not need to be tested.
		* This is just an example test of taxonomy().
		*/
		public function testTaxonomy() {
			global $ilDB, $lng;
			
			$obj = new ilObjReview();
			$test_tax = array(0 => $lng->txt("rep_robj_xrev_select"),
									1 => $lng->txt("rep_robj_xrev_taxon_remem"),
									2 => $lng->txt("rep_robj_xrev_taxon_underst"),
									3 => $lng->txt("rep_robj_xrev_taxon_apply"),
									4 => $lng->txt("rep_robj_xrev_taxon_anal"),
									5 => $lng->txt("rep_robj_xrev_taxon_eval"),
									6 => $lng->txt("rep_robj_xrev_taxon_create")
							);
			
			$this->assertEquals($test_tax, $obj->taxonomy());
		}

		public function testLoadQuestionMetaData() {
			global $ilDB, $ilUser;
			
			$test_id = $ilDB->nextId("qpl_questions");
			$test_title = "Test Title";
			$test_descr = "Test Description";
			$test_fname = "root";
			$test_lname = "user";
			
			$obj = new ilObjReview();
			
			$ilDB->insert("qpl_questions",
							  array("question_id" => array("integer", $test_id),
							  		  "title" => array("text", $test_title),
							  		  "description" => array("text", $test_descr),
							  		  "owner" => array("integer", $ilUser->getId())
							  )
			);
			$target = array("title" => $test_title,
								 "description" => $test_descr,
								 "firstname" => $test_fname,
								 "lastname" => $test_lname
						 );
			$record = $obj->loadQuestionMetaData($test_id);
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = $test_id");
			
			$this->assertEquals($target, $record);
		}

		public function testLoadQuestionTaxonomyData() {
			global $ilDB;
			
			$test_id = $ilDB->nextId("qpl_questions");
			$test_tax = 2;
			$test_knd = 3;
			
			$obj = new ilObjReview();
			
			$ilDB->insert("qpl_rev_qst",
							  array("question_id" => array("integer", $test_id),
							  		  "taxonomy" => array("integer", $test_tax),
							  		  "knowledge_dimension" => array("integer", $test_knd)
							  )
			);
			$target = array("taxonomy" => $test_tax,
								 "knowledge_dimension" => $test_knd);				
			$record = $obj->loadQuestionTaxonomyData($test_id);
			
			$ilDB->manipulate("DELETE FROM qpl_rev_qst WHERE question_id = $test_id");
			
			$this->assertEquals($target, $record);
		}
}


?>