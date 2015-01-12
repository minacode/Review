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

/**
* GUI to display question data
*
* @var		string		$html		rendered HTML equivalent to this object
*
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/
class ilQuestionOverviewGUI {
	private $html;	
	
	/**
	* Constructor, configures GUI output
	*
	* @param		object		$parent			GUI object that contains this object
	* @param		string		$inner_part		HTML string of the actual question and the answers
	* @param		array			$metadata		associative array of the question´s title, description and author => corresponding value
	*/
	public function __construct($parent, $inner_part, $metadata) {
		global $tpl, $ilAccess, $lng;
		$path_to_il_tpl = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory();
		$template = new ilTemplate("tpl.question_overview.html", true, true, $path_to_il_tpl);
		$template->setVariable("TXT_TITLE", $metadata["title"]);
		if ($ilAccess->checkAccess("write", "", $parent->object->getRefId()))
				$template->setVariable("TXT_AUTHOR", $metadata["firstname"]." ".$metadata["lastname"]);
		$template->setVariable("TXT_DESCRIPTION", $metadata["description"]);
		
		$template->setVariable("QUESTION", $lng->txt("rep_robj_xrev_question"));
		$template->setVariable("TITLE", $lng->txt("rep_robj_xrev_title"));
		if ($ilAccess->checkAccess("write", "", $parent->object->getRefId()))
			$template->setVariable("AUTHOR", $lng->txt("rep_robj_xrev_auth_quest"));
		$template->setVariable("DESCRIPTION", $lng->txt("rep_robj_xrev_description"));
		$template->setVariable("COMPL_QUESTION", $lng->txt("rep_robj_xrev_compl_question"));
		
		
		
		$template->setVariable("TXT_INNER_PART", $inner_part);
		$this->html = $template->get();
		
		
	}
	
	/**
	* Get the HTML string describing the look of this object
	*
	* @return		$this->html		rendered HTML string
	*/
	public function getHTML() {
		return $this->html;
	}
}
?>