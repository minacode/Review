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
	
	public function __construct($a_parent_obj, $a_parent_cmd) {
		global $ilCtrl;
		parent::__construct();
		
		$this->a_parent_obj = $a_parent_obj;
		$this->a_parent_cmd = $a_parent_cmd;
		
		$this->setTitle("Review-Eingabeformular");
		$this->setFormAction($ilCtrl->getLinkTargetByClass("ilObjreviewGUI", "showContent"));
		
		$this->populateQuestionFormPart();
		$this->populateReviewFormPart();
		$this->populateTaxonomyFormPart();
		$this->populateEvaluationFormPart();
		$this->populateAdditionalData();		
		
		$this->addCommandButton($ilCtrl->getFormAction($this), "Abbrechen");
		//$this->addCommandButton($ilCtrl->getLinkTargetByClass($a_parent_obj, $a_parent_cmd), "Abbrechen");
	}
	
	private function populateQuestionFormPart() {
		$head_q = new ilFormSectionHeaderGUI();
		$head_q->setTitle("Frage");
		$this->addItem($head_q);
		
		$title = new ilNonEditableValueGUI("Titel");
		$title->setValue($this->simulateData()["title"]);
		$this->addItem($title);
		
		$description = new ilNonEditableValueGUI("Beschreibung");
		$description->setValue($this->simulateData()["description"]);
		$this->addItem($description);
		
		$question = new ilNonEditableValueGUI("Fragestellung");
		$question->setValue($this->simulateData()["question"]);
		$this->addItem($question);

		$answers = new ilAspectListGUI("Antworten", $this->simulateData()["answers"]);
		$this->addItem($answers);
	}
	
	private function populateReviewFormPart() {
		$head_r = new ilFormSectionHeaderGUI();
		$head_r->setTitle("Review");
		$this->addItem($head_r);
		
		$head = new ilAspectHeadGUI(array("Fachl. Richtigkeit", "Relevanz", "Formulierung"));
		$this->addItem($head);
		
		$desc = new ilAspectSelectInputGUI("Beschreibung", array("dc" => array("options" => $this->rating(),
																									  "selected" => 0),
																					"dr" => array("options" => $this->rating(),
																									  "selected" => 0),
																					"de" => array("options" => $this->rating(),
																									  "selected" => 0)),
													  false);
		$this->addItem($desc);
		
		$quest = new ilAspectSelectInputGUI("Fragestellung", array("qc" => array("options" => $this->rating(),
																										 "selected" => 0),
																					  "qr" => array("options" => $this->rating(),
																										 "selected" => 0),
																					  "qe" => array("options" => $this->rating(),
																										 "selected" => 0)),
													  false);
		$this->addItem($quest);
		
		$answ = new ilAspectSelectInputGUI("Antworten", array("ac" => array("options" => $this->rating(),
																								  "selected" => 0),
																				"ar" => array("options" => $this->rating(),
																								  "selected" => 0),
																				"ae" => array("options" => $this->rating(),
																								  "selected" => 0)),
													  false);
		$this->addItem($answ);
	}
	
	private function populateTaxonomyFormPart() {
		$head_t = new ilFormSectionHeaderGUI();
		$head_t->setTitle("Taxonomiestufe und Wissensdimension");
		$this->addItem($head_t);
		
		$head = new ilAspectHeadGUI(array("Taxonomiestufe", "Wissensdimension"));
		$this->addItem($head);
		
		$auth = new ilAspectSelectInputGUI("Autor", array("cog_a" => array("options" => $this->cognitiveProcess(),
																								 "selected" => $this->simulateData()["cog"]),
																		  "kno_a" => array("options" => $this->knowledge(),
																								 "selected" => $this->simulateData()["kno"])),
													  true);
		$this->addItem($auth);
		
		$revi = new ilAspectSelectInputGUI("Reviewer", array("cog_r" => array("options" => $this->cognitiveProcess(),
																									 "selected" => 0),
																		  	  "kno_r" => array("options" => $this->knowledge(),
																									 "selected" => 0)),
													  false);
		$this->addItem($revi);
		
	}
		
	private function populateEvaluationFormPart() {
		$head_e = new ilFormSectionHeaderGUI();
		$head_e->setTitle("Bewertung");
		$this->addItem($head_e);

		$group_e = new ilRadioGroupInputGUI("Urteil" ,"group_e");
		$op_a = new ilRadioOption("Frage akzeptiert", "1", "");
		$group_e->addOption($op_a);
		$op_e = new ilRadioOption("Frage überarbeiten", "2", "");
		$group_e->addOption($op_e);
		$op_d = new ilRadioOption("Frage abgelehnt", "3", "");
		$group_e->addOption($op_d);
		$this->addItem($group_e);

		$comment = new ilTextAreaInputGUI("Bemerkungen", "comment");
		$comment->setCols(70);
		$comment->setRows(10);
		$this->addItem($comment);
		
		$expertise = new ilSelectInputGUI("Expertise", "exp");
		$expertise->setValue(0);
		$expertise->setOptions($this->expertise());
		$this->addItem($expertise);
	}
		
	private function populateAdditionalData() {
		$head_a = new ilFormSectionHeaderGUI();
		$head_a->setTitle("Weitere Informationen");
		$this->addItem($head_a);
		
		$author = new ilNonEditableValueGUI("Autor der Frage");
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