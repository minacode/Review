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


include_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/

class ilQuestionTableGUI extends ilTable2GUI {

	public function __construct($a_parent_obj, $a_parent_cmd) {
		global $ilCtrl, $lng;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->addColumn("Titel", "", "30%");
		$this->addColumn("Fragestellung", "", "40%");
      $this->addColumn("Aktion", "", "30%");
      $this->setEnableHeader(true);
      $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
     	$this->setRowTemplate("tpl.question_table_row.html", ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory());
      $this->setDefaultOrderField("id");
      $this->setDefaultOrderDirection("asc");
      
      $this->simulateData();
 
      $this->setTitle("Meine Fragen");
	}
	
	private function simulateData() {
		$data = array(
			array("id" => 0, "title" => "Frage 1 [Neu erstellt]", "question" => "Ist dies eine Dummy-Frage?", "state" => 0),
			array("id" => 1, "title" => "Frage 2 [Reviews angefordert]", "question" => "Handelt es sich hierbeit um eine Dummy-Frage?", "state" => 1),
			array("id" => 2, "title" => "Frage 3 [Bearbeitet]", "question" => "Könnte es sein, das dies eine Dummy-Frage ist?", "state" => 0)				
		);
		$this->setData($data);
	}
	
	/*
	* Fill a single data row
	*/
	protected function fillRow($a_set) {
		global $ilCtrl, $lng;
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_QUESTION", $a_set["question"]);
		$this->tpl->setVariable("TXT_ACTION", "Reviews einsehen");
		$this->tpl->setVariable("LINK_ACTION", $ilCtrl->getLinkTargetByClass("ilObjReviewGUI", "showReviews"));
	}
}

?>