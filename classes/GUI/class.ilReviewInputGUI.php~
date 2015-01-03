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
include_once 'Modules/Test/classes/class.ilTestExpressPageObjectGUI.php';
include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilAspectSelectInputGUI.php";
include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilAspectHeadGUI.php";

/**
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/

class ilReviewInputGUI extends ilPropertyFormGUI {
	private $a_parent_obj;
	private $a_parent_cmd;
	
	/**
	* constructor, sets up the table
	*
	* @param		object		$a_parent_obj		GUI object that contains this object
	* @param		string		$a_parent_cmd		Command that causes construction of this object
	* @param		array			$reviews				associative arrays of displayed reviews (column => value)
	* @param		array			$quest_tax			the question´s taxonomy and knowledge dimension
	* @param		array			$taxonomy			taxonomy options to choose from
	* @param		array			$knowledge_dimension		knowlege_dimension options to choose from
	* @param		array			$expertise			expertise options to choose from
	* @param		array			$rating				rating options to choose from
	* @param		array			$evaluation			evaluation options to choose from 
	*/
	public function __construct($a_parent_obj, $a_parent_cmd, $review, $quest_tax, $taxonomy, $knowledge_dimension, $expertise, $rating, $evaluation) {
		global $ilCtrl, $lng, $ilAccess;
		parent::__construct();
		
		$this->a_parent_obj = $a_parent_obj;
		$this->a_parent_cmd = $a_parent_cmd;
		$this->review = $review;
		$this->taxonomy = $taxonomy;
		$this->knowledge_dimension = $knowledge_dimension;
		$this->expertise = $expertise;
		$this->rating = $rating;
		$this->evaluation = $evaluation;
		
		$this->quest_tax = $quest_tax;
		
		$this->setTitle($lng->txt("rep_robj_xrev_review_input"));
		$this->setFormAction($ilCtrl->getLinkTargetByClass("ilObjReviewGUI", "saveReview"));
		
		if ($this->review["lastname"] and $ilAccess->checkAccess("write", "", $a_parent_obj->a_parent_obj->object->getRefId()))
			$this->populateReviewerData();
		$this->populateReviewFormPart();
		$this->populateTaxonomyFormPart();
		$this->populateEvaluationFormPart();
		
		$this->addCommandButton($ilCtrl->getFormAction($this), $lng->txt("save"));
	}
	
	/**
	* create the output for the form part which shows the reviewer
	*/
	private function populateReviewerData() {
		global $lng;
		$reviewer = new ilNonEditableValueGUI("");
		$reviewer->setTitle($lng->txt("rep_robj_xrev_reviewer"));
		$reviewer->setValue($this->review["firstname"]." ".$this->review["lastname"]);
		$this->addItem($reviewer);
	}
	
	/**
	* create the output for the form part which allows to review certain aspects of the question
	*/
	private function populateReviewFormPart() {
		global $lng;
		$head_r = new ilFormSectionHeaderGUI();
		$head_r->setTitle($lng->txt("rep_robj_xrev_review"));
		$this->addItem($head_r);
		
		$head = new ilAspectHeadGUI(array($lng->txt("rep_robj_xrev_correctness"),
													 $lng->txt("rep_robj_xrev_relevance"),
													 $lng->txt("rep_robj_xrev_expression")));
		$this->addItem($head);

		$desc = new ilAspectSelectInputGUI($lng->txt("description"), array("dc" => array("options" => $this->evaluation,
																													"selected" => $this->review["desc_corr"]),
																								 "dr" => array("options" => $this->evaluation,
																												   "selected" => $this->review["desc_relv"]),
																								 "de" => array("options" => $this->evaluation,
																												   "selected" => $this->review["desc_expr"])),
													  false);
		$this->addItem($desc);

		$quest = new ilAspectSelectInputGUI($lng->txt("question"), array("qc" => array("options" => $this->evaluation,
																										 		 "selected" => $this->review["quest_corr"]),
																					 		  "qr" => array("options" => $this->evaluation,
																												 "selected" => $this->review["quest_relv"]),
																							  "qe" => array("options" => $this->evaluation,
																												 "selected" => $this->review["quest_expr"])),
													  false);
		$this->addItem($quest);

		$answ = new ilAspectSelectInputGUI($lng->txt("answers"), array("ac" => array("options" => $this->evaluation,
																											  "selected" => $this->review["answ_corr"]),
																							"ar" => array("options" => $this->evaluation,
																											  "selected" => $this->review["answ_relv"]),
																							"ae" => array("options" => $this->evaluation,
																											  "selected" => $this->review["answ_expr"])),
													  false);
		$this->addItem($answ);
	}
	
	/**
	* create the output for the form part which allows to input the opinion on taxonomy and knowledge dimension
	*/
	private function populateTaxonomyFormPart() {
		global $lng;
		$head_t = new ilFormSectionHeaderGUI();
		$head_t->setTitle($lng->txt("rep_robj_xrev_tax_and_know_dim"));
		$this->addItem($head_t);
		
		$head = new ilAspectHeadGUI(array($lng->txt("rep_robj_xrev_taxonomy"), $lng->txt("rep_robj_xrev_knowledge_dim")));
		$this->addItem($head);
		
		$auth = new ilAspectSelectInputGUI($lng->txt("author"),
													  array("cog_a" => array("options" => $this->taxonomy,
																					 "selected" => $this->quest_tax["taxonomy"]),
															  "kno_a" => array("options" => $this->knowledge_dimension,
																					 "selected" => $this->quest_tax["knowledge_dimension"])),
													  true);
		$this->addItem($auth);

		$revi = new ilAspectSelectInputGUI($lng->txt("rep_robj_xrev_reviewer"),
													  array("cog_r" => array("options" => $this->taxonomy,
																					 "selected" => $this->review["taxonomy"]),
															  "kno_r" => array("options" => $this->knowledge_dimension,
																					 "selected" => $this->review["knowledge_dimension"])),
													  false);
		$this->addItem($revi);
	}
	
	/**
	* create the output for the form part which allows to accept, reject or request editing of a question
	*/	
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
		$group_e->setValue($this->review["group_e"]);
		$group_e->setRequired(true);
		$this->addItem($group_e);

		$comment = new ilTextAreaInputGUI($lng->txt("rep_robj_xrev_comment"), "comment");
		$comment->setCols(70);
		$comment->setRows(10);
		$comment->setValue($this->review["eval_comment"]);
		$comment->setRequired(true);
		$this->addItem($comment);
		
		$expertise = new ilSelectInputGUI($lng->txt("rep_robj_xrev_expertise"), "exp");
		$expertise->setValue($this->review["expertise"]);
		$expertise->setOptions($this->expertise);
		$expertise->setRequired(true);
		$this->addItem($expertise);
	}
	
	/**
	* disable all input GUIs and buttons the form contains
	*/
	public function setReadOnly() {
		foreach ($this->getItems() as $item) {
			if (method_exists($item, "setDisabled"))
				$item->setDisabled(true);
			if (method_exists($item, "setRequired"))
				$item->setRequired(false);
			}
		$this->clearCommandButtons();
	}
	
	/**
	* check, if input in custom input GUIs is valid
	*
	* @return		bool		$valid		true, if input in custom input GUIs is valid
	*/
	public function checkInput() {
		global $lng;
		$valid = true;
		if (!parent::checkInput())
			$valid = false;
		$to_check = array("dc", "dr", "de", "qc", "qr", "qe", "ac", "ar", "ae", "cog_r", "kno_r");
		foreach ($to_check as $input)
			if ($this->getInput($input) == 0)
				$valid = false;
		// because not using setRequired(true) on normal input GUIs
		if ($this->getInput("exp" == 0) or $this->getInput("comment" == ""))
			$valid = false; 
		if (!$valid)
			ilUtil::sendFailure($lng->txt("form_input_not_valid"));
		return $valid;	
	}
} 

?>