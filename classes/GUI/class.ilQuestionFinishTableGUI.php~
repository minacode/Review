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

class ilQuestionFinishTableGUI extends ilTable2GUI {

	public function __construct($a_parent_obj, $a_parent_cmd, $questions) {
		global $ilCtrl, $lng;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->addColumn("", "", "1%");
		$this->addColumn($lng->txt("title"), "", "65%");
		$this->addColumn($lng->txt("author"), "", "30%");
      $this->setEnableHeader(true);
      // $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
      $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
     	$this->setRowTemplate("tpl.question_finish_table_row.html", ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory());
      $this->setDefaultOrderField("id");
      $this->setDefaultOrderDirection("asc");
      $this->setSelectAllCheckbox('q_id');
      $this->addCommandButton('updateProperties', $this->lng->txt('rep_robj_xrev_accept'));
      
      $this->setData($questions);
 
      $this->setTitle($lng->txt("rep_robj_xrev_finished_questions"));
	}
	
	/*
	* Fill a single data row
	*
	* @param	array		$a_set		Data record, displayed as one table row
	*/
	protected function fillRow($a_set) {
		global $ilCtrl, $lng;
		$ilCtrl->setParameterByClass("ilObjReviewGUI", "q_id", $a_set["question_id"]);
		$this->tpl->setVariable("CB_QUESTION_ID", $a_set["question_id"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_AUTHOR", $a_set["author"]);
	}
}
?>