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

include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewOutputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewInputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilQuestionTableGUI.php");

/**
* User Interface class for Review repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjReviewGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReviewGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilReviewOutputGUI, ilReviewInputGUI
*
*/
class ilObjReviewGUI extends ilObjectPluginGUI {
	/**
	* Initialisation
	*/
	protected function afterConstructor() {
		// anything needed after object has been constructed
		// - example: append my_id GET parameter to each request
		//   $ilCtrl->saveParameter($this, array("my_id"));
	}
	
	/**
	* Get type.
	*/
	final function getType() {
		return "xrev";
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*
	* @param string		command		command to be performed by this class
	*/
	function performCommand($cmd) {
		switch ($cmd) {
			case "editProperties":		// list all commands that need write permission here
			case "updateProperties":
			//case "...":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":			// list all commands that need read permission here
			//case "...":
			//case "...":
				$this->checkPermission("read");
				$this->$cmd();
				break;
				
			case "inputReview":
			case "showReviews":
			//Write Access für User prüfen
			 	$this->$cmd();
				break;
		}
	}

	/**
	* After object has been created -> jump to this command
	*/
	function getAfterCreationCmd() {
		return "showContent";
	}

	/**
	* Get standard command
	*/
	function getStandardCmd() {
		return "showContent";
	}
	
	/**
	* Set tabs
	*/
	function setTabs() {
		global $ilTabs, $ilCtrl, $ilAccess;
		
		// tab for the "show content" command
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
			$ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
		}

		// standard info screen tab
		$this->addInfoTab();

		// a "properties" tab
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}

	/**
	* Edit plugin object properties and reviewer allocation
	*/
	function editProperties() {
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$this->initReviewAllocForm();
		$this->alloc_form->setValuesByPost();
		$tpl->setContent($this->form->getHTML() . "<br><hr><br>" . $this->alloc_form->getHTML());
	}

	/**
	* Init form for reviewer allocation
	*/
	public function initReviewAllocForm() {
		global $ilCtrl;
		$this->alloc_form = new ilPropertyFormGUI();
		$this->alloc_form->setTitle($this->txt("reviewer_allocation"));
		$this->alloc_form->setFormAction($ilCtrl->getFormAction($this));
		
		$q1 = new ilSelectInputGUI($this->txt("q1"), "q1");
		$q1->setRequired(true);
		$q1->setValue(0);
		$q1->setOptions( array(
								0=> "",
								1=> "Dummy-Reviewer 1",
								2=> "Dummy-Reviewer 2",
								3=> "Dummy-Reviewer 3",
							)
		);
		$this->alloc_form->addItem($q1);
		
		$q2 = new ilSelectInputGUI($this->txt("q2"), "q2");
		$q2->setRequired(true);
		$q2->setValue(0);
		$q2->setOptions( array(
								0=> "",
								1=> "Dummy-Reviewer 1",
								2=> "Dummy-Reviewer 2",
								3=> "Dummy-Reviewer 3",
							)
		);
		
		$this->alloc_form->addItem($q2);
		$q3 = new ilSelectInputGUI($this->txt("q3"), "q3");
		$q3->setRequired(true);
		$q3->setValue(0);
		$q3->setOptions( array(
								0=> "",
								1=> "Dummy-Reviewer 1",
								2=> "Dummy-Reviewer 2",
								3=> "Dummy-Reviewer 3",
							)
		);
		$this->alloc_form->addItem($q3);
		$this->alloc_form->addCommandButton("updateProperties", $this->txt("request"));
	}
	
	/**
	* Init  form for editing plugin object properties
	*/
	public function initPropertiesForm() {
		global $ilCtrl;

		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
		$this->form->addItem($ta);

		$this->form->addCommandButton("updateProperties", $this->txt("save"));
	                
		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues() {
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Update properties
	*/
	public function updateProperties() {
		global $tpl, $lng, $ilCtrl;
		$submit = false;
	
		$this->initPropertiesForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->update();
			$submit = true;
		}
		$this->form->setValuesByPost();
		
		$this->initReviewAllocForm();
		if ($this->alloc_form->checkInput()) {
			if (!($_POST["q1"] || $_POST["q2"] || $_POST["q3"])) die();
			/*
			/ Wir haben nur Dummy-Daten, nichts wird gespeichert
			$this->object->setValue($this->alloc_form->getValue("q1"));
			$this->object->setValue($this->alloc_form->getValue("q2"));
			$this->object->setValue($this->alloc_form->getValue("q3"));
			$this->object->update();
			*/
			$submit = true;
		}
		$this->alloc_form->setValuesByPost();
		if ($submit) {
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}
		$tpl->setContent($this->form->getHtml() . "<br><hr><br>" . $this->alloc_form->getHTML());
	}

	/**
	* Show plugin content (question and review table)
	*/
	protected function showContent() {
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("content");
		
		$table_q = new ilQuestionTableGUI($this, "showContent", $this->object->loadQuestionsByUser());
		$table_r = new ilReviewTableGUI($this, "showContent", $this->object->loadReviewsByUser());
		$tpl->setContent($table_q->getHtml() . "<br><hr><br>" . $table_r->getHtml());
	}

	/**
	* Display review input form
	*/
	public function inputReview() {
		global $tpl, $ilTabs;		
		$ilTabs->activateTab("content");
		$form = new ilReviewInputGUI($this, "showContent");
		$tpl->setContent($form->getHTML());
		echo $_GET["r_id"];
	}

	/**
	* Output reviews
	*/
	public function showReviews() {
		global $tpl, $ilTabs;		
		$ilTabs->activateTab("content");
		$tbl = new ilReviewOutputGUI($this, "showReviews");
		$tpl->setContent($tbl->getHtml());
	}
}
?>
