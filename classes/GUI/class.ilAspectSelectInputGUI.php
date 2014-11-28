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
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/

class ilAspectSelectInputGUI extends ilCustomInputGUI {
	
	/**
	* Constructor for table-like display of ilSelectInputGUIs
	*
	* @param	string	$title			title of the aspect
	* @param array		$select_ipnuts	associative array of all selects,
	*											$_POST variable => array of all options, pre-selected option
	* @param	bool		$disabled		true if selects are read-only
	*/
	public function __construct($title, $select_inputs, $disabled) {
		parent::__construct();
		$path_to_il_tpl = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory();
		$custom_tpl = new ilTemplate("tpl.aspect_row.html", true, true, $path_to_il_tpl);
		foreach ($select_inputs as $postvar => $values) {
			$select = new ilSelectInputGUI("", $postvar);
			$select->setValue($values["selected"]);
			$select->setOptions($values["options"]);
			$select->setDisabled($disabled);
			$select->insert($custom_tpl);
		}
		$this->setTitle($title);
		$this->setHTML($custom_tpl->get());	
	}
}