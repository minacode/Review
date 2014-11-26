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

include_once("./Services/Repository/classes/class.ilObjectPlugin.php");

/**
* Application class for Review repository object.
*
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*/
class ilObjReview extends ilObjectPlugin {
	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct($a_ref_id = 0) {
		parent::__construct($a_ref_id);
	}
	

	/**
	* Get type.
	*/
	final function initType() {
		$this->setType("xrev");
	}
	
	/**
	* Create object
	*/
	function doCreate() {
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO rep_robj_xrev_revobj ".
			"(id) VALUES (".
			$ilDB->quote($this->getId(), "integer").
			")");
	}
	
	/**
	* Read data from db
	*/
	function doRead() {
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM rep_robj_xrev_revobj ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set)) {
		}
	}
	
	/**
	* Update data
	*/
	function doUpdate() {
		global $ilDB;
	}
	
	/**
	* Delete data from db
	*/
	function doDelete() {
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM rep_robj_xrev_revobj WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		
	}
	
	/**
	* Do Cloning
	*/
	function doClone($a_target_id,$a_copy_id,$new_obj) {
		global $ilDB;
		
		$new_obj->update();
	}	
}
?>
