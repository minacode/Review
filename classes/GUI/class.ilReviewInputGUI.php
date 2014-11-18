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
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		
		$group_q = new ilRadioGroupInputGUI("Frage" ,"group_q");
		$title = new ilNonEditableValueGUI("Titel:");
		$title->setValue("Dummy-Titel");
		$group_q->addSubItem($title);
		$description = new ilNonEditableValueGUI("Beschreibung:");
		$description->setValue("Dummy-Beschreibung der zu diesem Dummy-Review gehörigen Dummy-Frage");
		$group_q->addSubItem($description);
		$question = new ilNonEditableValueGUI("Fragestellung:");
		$question->setValue("Ist diese Dummy-Frage eine Dummy-Frage?");
		$group_q->addSubItem($question);
		$dir = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory();
		include_once("$dir/classes/GUI/class.ilAnswerTableGUI.php");
		$answers = new ilNonEditableValueGUI("Antwortoptionen:");
		$ao = "";
		foreach ($this->simulateData() as $answer)
			$ao .= "                                                                                " . $answer["answer"];
		$answers->setValue($question);
		$group_q->addSubItem($answers);
		$this->addItem($group_q);
		
		$group_r = new ilRadioGroupInputGUI("Review" ,"group_r");
		
		$group_t = new ilRadioGroupInputGUI("Taxonomiestufe und Wissensdimension" ,"group_t");
		
		$group_e = new ilRadioGroupInputGUI("Urteil" ,"group_e");
		$op_a = new ilRadioOption("Frage akzeptiert", "1", "");
		$group_e->addOption($op_a);
		$op_e = new ilRadioOption("Frage überarbeiten", "2", "");
		$group_e->addOption($op_e);
		$op_d = new ilRadioOption("Frage abgelehnt", "3", "");
		$group_e->addOption($op_d);
		$this->addItem($group_e);
				
		$group_c = new ilRadioGroupInputGUI("Bemerkungen" ,"group_c");
		$comment = new ilTextAreaInputGUI("Bemerkungen:", "comment");
		$group_c->addSubItem($comment);
		$this->addItem($group_c);
				
		$group_a = new ilRadioGroupInputGUI("Autor" ,"group_a");
		$author = new ilNonEditableValueGUI("Autor:");
		$author->setValue("Dummy-Autor");
		$group_a->addSubItem($author);
		$this->addItem($group_a);
		
		//$this->addCommandButton($ilCtrl->getLinkTargetByClass($a_parent_obj, $a_parent_cmd), "Absenden");
		//$this->addCommandButton($ilCtrl->getLinkTargetByClass($a_parent_obj, $a_parent_cmd), "Abbrechen");
	}
	
	private function simulateData() {
		$data = array(
			array("id" => 0, "answer" => "42"),
			array("id" => 1, "answer" => "zweiundvierzig"),
			array("id" => 2, "answer" => "forty two")				
		);
		return $data;
	}

} 

?>