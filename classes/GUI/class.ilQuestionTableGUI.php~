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

	/**
	* Constructor, configures GUI output
	*
	* @param		object		$a_parent_obj		GUI object that contains this object
	* @param		string		$a_parent_cmd		Command that causes construction of this object
	* @param		array			$questions			associative arrays of displayed data (column => value)
	*/
	public function __construct($a_parent_obj, $a_parent_cmd, $questions) {
		global $ilCtrl, $lng;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->addColumn($lng->txt("title"), "", "80%");
      $this->addColumn($lng->txt("action"), "", "20%");
      $this->setEnableHeader(true);
      $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
     	$this->setRowTemplate("tpl.question_table_row.html", ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory());
      $this->setDefaultOrderField("id");
      $this->setDefaultOrderDirection("asc");
      
      $ilCtrl->saveParameterByClass("ilObjReviewGUI", array("q_id", "origin"));
      
      $this->setData($questions);
 
      $this->setTitle($lng->txt("rep_robj_xrev_my_questions"));
	}
	
	/*
	* Fill a single data row
	*
	* @param	array		$a_set		Data record, displayed as one table row
	*/
	protected function fillRow($a_set) {
		global $ilCtrl, $lng; 
		$ilCtrl->setParameterByClass("ilObjReviewGUI", "q_id", $a_set["id"]);
		$ilCtrl->setParameterByClass("ilObjReviewGUI", "origin", "question");
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("rep_robj_xrev_view"));
		$this->tpl->setVariable("LINK_ACTION", $ilCtrl->getLinkTargetByClass("ilObjReviewGUI", "showReviews"));
	}
}
?>