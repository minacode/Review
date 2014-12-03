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


include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
include_once 'Services/Table/classes/class.ilTable2GUI.php';
include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilAspectSelectInputGUI.php";
include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilAspectHeadGUI.php";
include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilAspectListGUI.php";

/**
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/

class ilReviewInputGUI extends ilPropertyFormGUI {
	private $a_parent_obj;
	private $a_parent_cmd;
	
	public function __construct($a_parent_obj, $a_parent_cmd, $review) {
		global $ilCtrl, $lng;
		parent::__construct();
		
		$this->a_parent_obj = $a_parent_obj;
		$this->a_parent_cmd = $a_parent_cmd;
		$this->review = $review;
		
		$this->setTitle($lng->txt("rep_robj_xrev_review_input"));
		$this->setFormAction($ilCtrl->getLinkTargetByClass("ilObjReviewGUI", "saveReview"));
		
		$this->populateQuestionFormPart();
		$this->populateReviewFormPart();
		$this->populateTaxonomyFormPart();
		$this->populateEvaluationFormPart();
		$this->populateAdditionalData();		
		
		$this->addCommandButton($ilCtrl->getFormAction($this), $lng->txt("save"));
	}
	
	private function populateQuestionFormPart() {
		global $lng;
		$head_q = new ilFormSectionHeaderGUI();
		$head_q->setTitle($lng->txt("question"));
		$this->addItem($head_q);
		
		$title = new ilNonEditableValueGUI($lng->txt("title"));
		$title->setValue($this->simulateData()["title"]);
		$this->addItem($title);
		
		$description = new ilNonEditableValueGUI($lng->txt("description"));
		$description->setValue($this->simulateData()["description"]);
		$this->addItem($description);
		
		$question = new ilNonEditableValueGUI($lng->txt("question"));
		$question->setValue($this->simulateData()["question"]);
		$this->addItem($question);

		$answers = new ilAspectListGUI($lng->txt("answers"), $this->simulateData()["answers"]);
		$this->addItem($answers);
	}
	
	private function populateReviewFormPart() {
		global $lng;
		$head_r = new ilFormSectionHeaderGUI();
		$head_r->setTitle($lng->txt("rep_robj_xrev_review"));
		$this->addItem($head_r);
		
		$head = new ilAspectHeadGUI(array($lng->txt("rep_robj_xrev_correctness"),
													 $lng->txt("rep_robj_xrev_relevance"),
													 $lng->txt("rep_robj_xrev_expression")));
		$this->addItem($head);
		
		$desc = new ilAspectSelectInputGUI($lng->txt("description"), array("dc" => array("options" => $this->rating(),
																													"selected" => $this->review["desc_corr"]),
																								 "dr" => array("options" => $this->rating(),
																												   "selected" => $this->review["desc_relv"]),
																								 "de" => array("options" => $this->rating(),
																												   "selected" => $this->review["desc_expr"])),
													  false);
		$this->addItem($desc);
		
		$quest = new ilAspectSelectInputGUI($lng->txt("question"), array("qc" => array("options" => $this->rating(),
																										 		 "selected" => $this->review["quest_corr"]),
																					 		  "qr" => array("options" => $this->rating(),
																												 "selected" => $this->review["quest_relv"]),
																							  "qe" => array("options" => $this->rating(),
																												 "selected" => $this->review["quest_expr"])),
													  false);
		$this->addItem($quest);
		
		$answ = new ilAspectSelectInputGUI($lng->txt("answers"), array("ac" => array("options" => $this->rating(),
																											  "selected" => $this->review["answ_corr"]),
																							"ar" => array("options" => $this->rating(),
																											  "selected" => $this->review["answ_relv"]),
																							"ae" => array("options" => $this->rating(),
																											  "selected" => $this->review["answ_expr"])),
													  false);
		$this->addItem($answ);
	}
	
	private function populateTaxonomyFormPart() {
		global $lng;
		$head_t = new ilFormSectionHeaderGUI();
		$head_t->setTitle($lng->txt("rep_robj_xrev_tax_and_know_dim"));
		$this->addItem($head_t);
		
		$head = new ilAspectHeadGUI(array($lng->txt("rep_robj_xrev_taxonomy"), $lng->txt("rep_robj_xrev_knowledge_dim")));
		$this->addItem($head);
		
		$auth = new ilAspectSelectInputGUI($lng->txt("author"),
													  array("cog_a" => array("options" => $this->cognitiveProcess(),
																					 "selected" => $this->simulateData()["cog"]),
															  "kno_a" => array("options" => $this->knowledge(),
																					 "selected" => $this->simulateData()["kno"])),
													  true);
		$this->addItem($auth);
		
		$revi = new ilAspectSelectInputGUI($lng->txt("rep_robj_xrev_reviewer"),
													  array("cog_r" => array("options" => $this->cognitiveProcess(),
																					 "selected" => $this->review["taxonomy"]),
															  "kno_r" => array("options" => $this->knowledge(),
																					 "selected" => $this->review["knowledge_dimension"])),
													  false);
		$this->addItem($revi);
		
	}
		
	private function populateEvaluationFormPart() {
		global $lng;
		$head_e = new ilFormSectionHeaderGUI();
		$head_e->setTitle($lng->txt("rep_robj_xrev_evaluation"));
		$this->addItem($head_e);

		$group_e = new ilRadioGroupInputGUI($lng->txt("rep_robj_xrev_rating"),"group_e");
		$op_a = new ilRadioOption($lng->txt("rep_robj_xrev_quest_accept"), "1", "");
		$group_e->addOption($op_a);
		$op_e = new ilRadioOption($lng->txt("rep_robj_xrev_quest_edit"), "2", "");
		$group_e->addOption($op_e);
		$op_d = new ilRadioOption($lng->txt("rep_robj_xrev_quest_refuse"), "3", "");
		$group_e->addOption($op_d);
		$this->addItem($group_e);

		$comment = new ilTextAreaInputGUI($lng->txt("rep_robj_xrev_comment"), "comment");
		$comment->setCols(70);
		$comment->setRows(10);
		$comment->setValue($this->review["eval_comment"]);
		$this->addItem($comment);
		
		$expertise = new ilSelectInputGUI($lng->txt("rep_robj_xrev_expertise"), "exp");
		$expertise->setValue($this->review["expertise"]);
		$expertise->setOptions($this->expertise());
		$this->addItem($expertise);
	}
		
	private function populateAdditionalData() {
		global $lng;
		$head_a = new ilFormSectionHeaderGUI();
		$head_a->setTitle($lng->txt("rep_robj_xrev_add_info"));
		$this->addItem($head_a);
		
		$author = new ilNonEditableValueGUI($lng->txt("rep_robj_xrev_auth_quest"));
		$author->setValue($this->simulateData()["author"]);
		$this->addItem($author);
	}

	private function simulateData() {
		$data = array("answers" => array(
													array("id" => 0, "answer" => "42", "correct" => 1),
													array("id" => 1, "answer" => "zweiundvierzig", "correct" => 0),
													array("id" => 2, "answer" => "forty two", "correct" => 0)
												  ),
						  "title" => "Dummy-Titel",
						  "question" => "Ist diese Dummy-Frage eine Dummy-Frage?",
						  "description" => "Dummy-Beschreibung der zu diesem Dummy-Review gehörigen Dummy-Frage",
						  "author" => "Dummy Autor",
						  "cog" => 2,
						  "kno" => 3
						 );
		return $data;
	}
	
	private function cognitiveProcess() {
		return array(0 => "",
						 1 => "Remember",
						 2 => "Understand",
						 3 => "Apply",
						 4 => "Analyze",
						 5 => "Evaluate",
						 6 => "Create",
						);
	}
	
	private function knowledge() {
		return array(0 => "",
						 1 => "Conceptual",
						 2 => "Factual",
						 3 => "Procedural",
						 4 => "Metacognitive",
						);
	}
	
	private function rating() {
		return array(0 => "",
						 1 => "gut",
						 2 => "Korrektur",
						 3 => "ungeeignet",
						);
	}
	
	private function expertise() {
		return array(0 => "",
						 1 => "No familiarity",
						 2 => "Some familiarity",
						 3 => "Knowledgeable",
						 4 => "Expert"
						);
	}
	
	public function setReadOnly() {
		foreach ($this->getItems() as $item)
			if (method_exists($item, "setDisabled"))
				$item->setDisabled(true);
		$this->clearCommandButtons();
	}
} 

?>