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
include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewInputGUI.php";

class ilReviewOutputGUI extends ilTable2GUI {
	
	public function __construct($a_parent_obj, $a_parent_cmd) {
		global $ilCtrl, $lng;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->addColumn("Reviews", "", "100%");
      $this->setEnableHeader(false);
      $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
     	$this->setRowTemplate("tpl.output_table_row.html", ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory());
      $this->setDefaultOrderField("id");
      $this->setDefaultOrderDirection("asc");
      
      $this->simulateData();
 
      $this->setTitle("Reviews zu dieser Frage");
	}
	
	private function simulateData() {
		$data = array();
		$rev1 = new ilReviewInputGUI($this, "");
		$rev1->setReadOnly();
		$data[] = array("id" => 0, "review" => $rev1->getHTML());
		$rev2 = new ilReviewInputGUI($this, "");
		$rev2->setReadOnly();
		$data[] = array("id" => 1, "review" => $rev2->getHTML());
		$this->setData($data);
	}
	
	/*
	* Fill a single data row
	*/
	protected function fillRow($a_set) {
		global $ilCtrl, $lng;
		$this->tpl->setVariable("TXT_REVIEW", $a_set["review"]);
	}
}