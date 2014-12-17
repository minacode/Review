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
	
	/*
	* constructor
	*
	* @param		array		$reviews		reviews to display
	*/
	public function __construct($a_parent_obj, $a_parent_cmd, $reviews, $taxonomy, $knowledge_dimension, $expertise, $rating, $evaluation) {
		global $ilCtrl, $lng;
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->taxonomy = $taxonomy;
		$this->knowledge_dimension = $knowledge_dimension;
		$this->expertise = $expertise;
		$this->rating = $rating;
		$this->evaluation = $evaluation;
		
		$this->addColumn($lng->txt("reviews"), "", "100%");
      $this->setEnableHeader(false);
      $this->setFormAction($ilCtrl->getLinkTargetByClass("ilObjreviewGUI", "showContent"));
     	$this->setRowTemplate("tpl.output_table_row.html", ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory());
      $this->setDefaultOrderField("id");
      $this->setDefaultOrderDirection("asc");
      $this->setTopCommands(false);
      $this->addCommandButton($ilCtrl->getFormAction($this), $lng->txt("back"));

		$this->setUpData($reviews);

      $this->setTitle($lng->txt("rep_robj_xrev_review_output"));
	}

	/*
	* @param		array		$reviews		reviews to display
	*/	
	private function setUpData($reviews) {
		$data = array();
		foreach ($reviews as $review) {
			$input_form = new ilReviewInputGUI($this, "", $review, $this->taxonomy,
												$this->knowledge_dimension,
												$this->expertise,
												$this->rating,
												$this->evalutaion);
			$input_form->setReadOnly();
			$data[] = array("id" => $review["id"],
						  		 "review" => $input_form->getHtml());
		}
		$this->setData($data);
	}
	
	/*
	* Fill a single data row
	*
	* @param	array		$a_set		Data record (1 review), displayed as one table row
	*/
	protected function fillRow($a_set) {
		global $ilCtrl, $lng;
		$this->tpl->setVariable("TXT_REVIEW", $a_set["review"]);
	}
}