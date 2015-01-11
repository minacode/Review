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
		}
		
		/*
		public function testTest() {
		  $a = 1337;
		  
		  if($a == 1337) {
		    assertTrue(true,"");
		  }
		  else {
		    assertTrue(false,"testTest() failed!");
		  }
		}
		*/
		

		public function testLoadUnallocatedQuestions() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1336);
			//$obj->setRefId($a_id);
			// how to set a parent_id???
			
			$ilDB->manipulate("INSERT INTO qpl_questions (question_id,title) VALUES (99,'testTitle')");
			$ilDB->manipulate("INSERT INTO object_reference (ref_id,obj_id,deleted) VALUES (9999,9998,NULL)");
			$ilDB->manipulate("INSERT INTO crs_items (parent_id,obj_id) VALUES (999,998)");
			$ilDB->manipulate("INSERT INTO rep_robj_xrev_quest (id,timestamp,state,review_obj,question_id) VALUES (90,NULL,0,997,99)");
			
			$result = $obj->testLoadUnallocatedQuestions();
			$target = array(id => 99, title => "testTitle");
			
			if ($result == $target) {
				$this->assertTrue(true,"");
			} 
			else {
				$this->assertTrue(false,"loadUnallocatedQuestions() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = 99");
			$ilDB->manipulate("DELETE FROM object_reference WHERE ref_id = 9999");
			$ilDB->manipulate("DELETE FROM crs_items WHERE");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = 90");
		}
		
		
		public function testDoCreate() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1337);
			$obj->doCreate();
			
			$result = $ilDB->manipulate("SELECT * FROM rep_robj_xrev_revobj WHERE id = 1337");
			$target = array(id => 1337, group_id => NULL);
			
			if($result == $target) {
				$this->assertTrue(true,"");
			}
			else {
				$this->assertTrue(false,"doCreate() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id = 1337");
		}

		// method has been removed
		/*
		public function testDoDelete() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1338);
			$obj->doDelete();
			
			$result = $ilDB->manipulate("SELECT * FROM rep_robj_xrev_revobj WHERE id = 1338");
			
			if($result == NULL) {
				$this->assertTrue(true,"");
			}
			else {
				$this->assertTrue(false,"doDelete() failed!");
			}
		}
		*/
		
		
		public function testDoUpdate() {
			global $ilDB;
			  
			$obj = new ilObjReview();
			$obj->setId(1338);
			$obj->doCreate();
			
			$result1 = $ilDB->manipulateF("SELECT * FROM rep_robj_xrev_revobj WHERE id =%s ",array("integer"),array($obj->getId()));
			
			$obj->setId(4242);
			$obj->doUpdate();
			
			$result2 = $ilDB->manipulateF("SELECT * FROM rep_robj_xrev_revobj WHERE id =%s ",array("integer"),array($obj->getId()));
			
			if($result1 != $result2) {
				$this->assertTrue(true,"");
			}
			else {
				$this->assertTrue(false,"doUpdate() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE id= 4242");
		}
		
		
		public function testDoClone() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1339);
			doClone(1340,1339,$obj2);
			
			$obj2->setId(1339);
			
			if($obj1->getId() == $obj2->getId()) {
				$this->assertTrue(true,"");
			} 
			else {
				$this->assertTrue(false,"doClone() failed!");
			}	
		}
		
		// possibly not working
		public function testLoadQuestionsByUser() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1342);
			
			$ilDB->manipulate("INSERT INTO qpl_questions (question_id,title) VALUES (99,'testTitle')");
			$ilDB->manipulate("INSERT INTO object_reference (ref_id,obj_id,deleted) VALUES (9999,9998,NULL)");
			$ilDB->manipulate("INSERT INTO crs_items (parent_id,obj_id) VALUES (999,998)");
			$ilDB->manipulateF("INSERT INTO rep_robj_xrev_quest (id,timestamp,state,review_obj,question_id) VALUES (90,NULL,0,%s,99)",array("integer"),$obj->getId());
			
			$result = $obj->loadQuestionsByUser();
			$target = array(id => 42,title => 'testTitle');
			
			if($result == $target) {
				$this->assertTrue(true,"");
			} 
			else {
				$this->assertTrue(false,"loadQuestionsByUser() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = 99");
			$ilDB->manipulate("DELETE FROM object_reference WHERE ref_id = 9999");
			$ilDB->manipulate("DELETE FROM crs_items WHERE");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE id = 90");
		}
		
		
		public function testLoadReviewsByUser() {
			global $ilDB;
			
			$ilDB->manipulate("INSERT INTO qpl_questions (id,title,question_id,state) VALUES (42,'testtitle',42,1)");
			
			$obj = new ilObjReview();
			$obj->setId(1343);
			
			$result = $obj->loadReviewsByUser();
			$target = array(id => 42, title => 'testtitle',question_id => 42,state => 1);
			
			if($result === $target) {
				$this->assertTrue(true,"");
			} 
			else {
				$this->assertTrue(false,"loadQuestionsByUser() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE id=42");
		}
		
		
		public function testLoadReviewById() {
			global $ilDB;
			
			$ilDB->manipulate("INSERT INTO rep_robj_xrev_revi (id) VALUES (42)");
			
			$obj = new ilObjReview();
			$obj->setId(1344);
			
			$result = $obj->loadReviewById();
			$target = $ilDB->manipulate("SELECT * FROM rep_robj_xrev_revi WHERE id=42");
			
			if($result == $target) {
				$this->assertTrue(true,"");
			} 
			else {
				$this->assertTrue(false,"loadQuestionsByUser() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_revi WHERE id=42");
		}		
		
		
		public function testLoadReviewsByQuestion() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1345);
			
			$ilDB->manipulateF("INSERT INTO rep_robj_xrev_revi (id,question_id) VALUES (%s,43)",array("integer"),$obj->getId());
			$sel = $ilDB->manipulateF("SELECT FROM rep_robj_xrev_revi WHERE id=%s AND question_id=43",array("integer"),$obj->getId());
			$target = $ilDB->fetchAssoc($sel); 
			
			if ($target == $obj->loadReviewsByQuestion(43)) {
				assertTrue(true,"");
			} 
			else {
				assertTrue(false,"loadReviewsByQuestion() failed!");
			}
			
			$ilDB->manipulateF("DELETE FROM rep_robj_xrev_revi WHERE id = %s", array("integer"),$obj->getId());
		}
		
		
		public function testLoadReviewers() {
			global $ilDB;
			
			$obj = new ilObjReview();
			
			$ilDB->manipulate("INSERT INTO usr_data (usr_id,firstname,lastname) VALUES (1346,'justin','sane')");
			$sel = $ilDB->manipulate("SELECT FROM usr_data WHERE usr_id=1346");
			$target = $ilDB->fetchAssoc($sel);
			
			if ($target == $obj->loadReviewers()) {
				assertTrue(true,"");
			} 
			else {
				assertTrue(false,"loadReviewers() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM usr_data WHERE usr_id = 1346");
		}
		
		public function testLoadUnallocatedQuestions() {
			global $ilDB;
			
			$obj = new ilObjReview();
			
			$ilDB->manipulate("INSERT INTO qpl_questions (id,title) VALUES (12,'Banana question')");
			$sel = $ilDB->manipulate("SELECT FROM qpl_questions WHERE id=12");
			$target = $ilDB->fetchAssoc($sel);
			
			if ($target == $obj->loadUnallocatedQuestions()) {
				assertTrue(true,"");
			} 
			else {
				assertTrue(false,"loadUnallocatedQuestions() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE id = 12");
		}
		
		
		public function testLoadReviewedQuestions() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$obj->setId(1350);
			
			$ilDB->manipulate("INSERT INTO qpl_questions (question_id,title,author) VALUES (12,'Banana question')");
			$ilDB->manipulate("INSERT INTO rep_robj_xrev_quest (question_id,review_obj,state) VALUES (12,1350,1)");
			$sel = $ilDB->manipulate("SELECT FROM qpl_questions WHERE id=12");
			
			$target = $ilDB->fetchAssoc($sel);
			
			if ($target == $obj->loadReviewedQuestions()) {
				assertTrue(true,"");
			} 
			else {
				assertTrue(false,"loadReviewedQuestions() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = 12");
			$ilDB->manipulate("DELETE FROM rep_robj_xrev_quest WHERE question_id = 12");
		}
		
		public function testFinishQuestions() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$ilDB->manipulate("INSERT INTO rep_robj_xrev_quest (question_id,state) VALUES (1212,0)");
			
			$questions = array(1212);
			$obj->finishQuestions($questions);
			
			$question = $ilDB->manipulate("SELECT state FROM rep_robj_xrev_quest WHERE question_id=1212");
			
			if($question == 2) {
				assertTrue(true,"");
			}
			else {
				assertTrue(false,"finishQuestions() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = 1212");
		}
		
		
		// could be deleted, taxonomy() is basically just a getter
		public function testTaxonomy() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$testtax = array(	0 => "select"
								1 => "taxon_remem"
								2 => "taxon_underst"
								3 => "taxon_apply"
								4 => "taxon_anal"
								5 => "taxon_eval"
								6 => "taxon_create"
							);
			
			if ($testtax == $obj->taxonomy()) {
				assertTrue(true,"");
			}
			else {
				assertTrue(false,"taxonomy() failed!");
			}
		}
		
		// could be deleted, knowledgeDimension() is basically just a getter
		public function testKnowledgeDimension() {
			global $ilDB;
			
			$obj = new ilObjReview();
			$testtax = array(	0 => "select"
								1 => "knowd_concp"
								2 => "knowd_fact"
								3 => "knowd_proc"
								4 => "knowd_meta"
							);
			
			if ($testtax == $obj->knowledgeDimension()) {
				assertTrue(true,"");
			}
			else {
				assertTrue(false,"taxonomy() failed!");
			}
		}
		
		
		// same as taxonomy() and knowledgeDimension()
		public function testExpertise() {
		}
		// same as taxonomy() and knowledgeDimension()
		public function testRating() {
		}
		// same as taxonomy() and knowledgeDimension()
		public function testEvaluation() {
		}
		
		
		public function testLoadQuestionMetaData() {
			global $ilDB;
			
			$obj = new ilObjReview();
			
			$ilDB->manipulate("INSERT INTO qpl_questions (question_id,title,description,owner) VALUES (1213,'Test Animal Question',NULL,6)");
			$target = array("title" 	=> "Test Animal Question"
					"description" => NULL
					"firstname" => "root"
					"lastname" => "user");
			$result = $obj->loadQuestionMetaData(1213);
			
			if($target == $result){
				assertTrue(true,"");
			}
			else{
				assertTrue(false,"loadQuestionsMetaData() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_questions WHERE question_id = 1213");
			
		}
		
		public function testLoadQuestionTaxonomyData() {
			global $ilDB;
			
			$obj = new ilObjReview();
			
			$ilDB->manipulate("INSERT INTO qpl_rev_qst (question_id,taxonomy,knowledge_dimension) VALUES (1214,'testtaxonomy','testknowledgedimension')");
			$target = array("taxonomy" => "testtaxonomy"
							"knowledge_dimension" => "testknowledgedimension");
							
			$result = $obj->loadQuestionTaxonomyData(1214);
			
			if($target == $result){
				assertTrue(true,"");
			}
			else{
				assertTrue(false,"loadQuestionsTaxonomyData() failed!");
			}
			
			$ilDB->manipulate("DELETE FROM qpl_rev_qst WHERE question_id = 1214");
		}
}


?>