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
		$this->syncQuestionDB();
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
		
	/**
	* Load all questions from the groups´ Question Pools,
	* thus updating the plugin´s question db
	*/
	private function syncQuestionDB() {
		global $ilDB;
		
		$qpl = $ilDB->query("SELECT question_id, tstamp FROM qpl_questions ".
								  /*"INNER JOIN qpl_questionpool ON qpl_questionpool.obj_fi=qpl_questions.obj_fi ".*/
								  "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
								  "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
								  "WHERE crs_items.parent_id=66 AND qpl_questions.original_id IS NULL"
			);

		while ($db_question = $ilDB->fetchAssoc($qpl)) {
			$contained = false;
			$pqs = $ilDB->query("SELECT * FROM rep_robj_xrev_quest");
			while ($pl_question = $ilDB->fetchAssoc($pqs)) {
				if ($db_question["question_id"] == $pl_question["id"])
					$contained = true;
				if ($db_question["tstamp"] > $pl_question["timestamp"]) {
					$ilDB->manipulateF("UPDATE rep_robj_xrev_quest SET timestamp=%s WHERE id=%s",
											 array("timestamp", "integer"),
											 array($db_question["tstamp"], $db_question["question_id"]));
					// TODO update reviews
					break;
				}
			}
			if (!$contained) {
				$ilDB->manipulateF("INSERT INTO rep_robj_xrev_quest (id, timestamp) VALUES (%s, %s)",
										 array("integer", "timestamp"),
										 array($db_question["question_id"], $db_question["tstamp"]));
				//TODO update reviews
				break;
			}
		}
	}
}
?>
