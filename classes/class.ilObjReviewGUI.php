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

include_once 'Modules/TestQuestionPool/classes/class.assQuestionGUI.php';
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
include_once './Services/Form/classes/class.ilCustomInputGUI.php';
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewOutputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewInputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilReviewTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilQuestionTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilCheckMatrixRowGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilQuestionFinishTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
				 "/classes/GUI/class.ilQuestionOverviewGUI.php");

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
* @ilCtrl_Calls ilObjReviewGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilReviewOutputGUI, ilReviewInputGUI, assQuestionGUI
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
	* @param string		$cmd		command to be performed by this class
	*/
	function performCommand($cmd) {
		switch ($cmd) {
			case "editProperties":
			case "updateProperties":
			case "allocateReviews":
			case "saveAllocateReviews":
			case "finishQuestions":
			case "saveFinishQuestions":
				$this->checkPermission("write");
				$this->$cmd();
				break;
			
			case "showContent":
			case "inputReview":
			case "showReviews":
			case "saveReview":
				$this->checkPermission("read");
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

		// tabs to edit properties and run the review cycle
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
			$ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
			$ilTabs->addTab("allocation", $this->txt("reviewer_allocation"), $ilCtrl->getLinkTarget($this, "allocateReviews"));
			$ilTabs->addTab("finish", $this->txt("finished_questions"), $ilCtrl->getLinkTarget($this, "finishQuestions"));
		}

		// standard epermission tab
		$this->addPermissionTab();
	}

	/**
	* Edit plugin object properties
	*/
	function editProperties() {
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		
		$this->initQuestionFinishForm();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* Input reviewer allocation
	*/
	function allocateReviews() {
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("allocation");
		if (count($this->object->loadUnallocatedQuestions()) > 0) {
			$this->initReviewAllocForm();
			$this->alloc_form->setValuesByPost();
			$tpl->setContent($this->alloc_form->getHTML());
		}
		else 
			$tpl->setContent($this->txt("no_alloc"));
	}
	
	/**
	* Check and save reviewer allocation
	*/
	function saveAllocateReviews() {
		global $tpl, $ilTabs, $lng, $ilCtrl;
		
		$ilTabs->activateTab("allocation");
		$this->initReviewAllocForm();
		if ($this->alloc_form->checkInput()) {
			$rows = array();
			foreach ($this->alloc_form->getItems() as $item) {
				if (!method_exists($item, "getPostVars"))
					continue;
				$row_postvars = $item->getPostVars();
				$row_values = array();
				foreach ($row_postvars as $row_postvar)
					$row_values[$row_postvar] = $this->alloc_form->getInput($row_postvar);
				$rows[] = array("q_id" => $item->getQuestionId(), "reviewers" => $row_values);
			}
			$this->object->allocateReviews($rows);
			$this->object->notifyReviewersAboutAllocation($rows);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "allocateReviews");
		}
		$this->alloc_form->setValuesByPost();
		$tpl->setContent($this->alloc_form->getHTML());
	}
	
	/**
	* Accept allocation
	*/
	public function finishQuestions() {
		global $tpl, $ilTabs;
		
		$ilTabs->activateTab("finish");
		$this->initQuestionFinishForm();
		$tpl->setContent($this->finish_form->getHTML());
	}
	
	/**
	* Check and perform finishing of questions
	*/
	public function saveFinishQuestions() {
		global $tpl, $ilTabs, $lng, $ilCtrl;
		
		$ilTabs->activateTab("finish");
		$this->initQuestionFinishForm();
		if (count($_POST["q_id"] > 0)) {
			$this->object->finishQuestions($_POST["q_id"]);
			$this->object->notifyAuthorsAboutAcceptance($_POST["q_id"]);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "finishQuestions");
		}
		$tpl->setContent($this->finish_form->getHTML());
	}

	/**
	* Init form for reviewer allocation
	*/
	public function initReviewAllocForm() {
		global $ilCtrl;
		
		$this->alloc_form = new ilPropertyFormGUI();
		$this->alloc_form->setTitle($this->txt("reviewer_allocation"));
		$this->alloc_form->setFormAction($ilCtrl->getFormAction($this));
		
		$reviewers = $this->object->loadReviewers();
		$reviewer_names = array();
		foreach ($reviewers as $reviewer)
			$reviewer_names[] = $reviewer['firstname'] . ' ' . $reviewer['lastname'];
		$reviewer_ids = array();
		foreach ($reviewers as $reviewer)
			$reviewer_ids[] = $reviewer["usr_id"];
			
		$reviewer_head = new ilAspectHeadGUI($reviewer_names);
		$this->alloc_form->addItem($reviewer_head);
		
		foreach ($this->object->loadUnallocatedQuestions() as $question) {
			$matrix_row = new ilCheckMatrixRowGUI($question, $reviewer_ids);
			$this->alloc_form->addItem($matrix_row);
		}
		
		$this->alloc_form->addCommandButton("saveAllocateReviews", $this->txt("request"));
	}
	
	/**
	* init form for finishing questions (removing them from the review cycle)
	*/
	public function initQuestionFinishForm() {
		global $ilCtrl;
		
		$this->finish_form = new ilQuestionFinishTableGUI($this, "saveFinishQuestions", $this->object->loadReviewedQuestions());
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

		$this->initPropertiesForm();
		if ($this->form->checkInput()) {
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setDescription($this->form->getInput("desc"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
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
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilPluginAdmin;		
		$ilTabs->activateTab("content");
		if (!ilObjReviewAccess::checkAccessToObject($_GET["r_id"], "", "inputReview", "review")) {
			ilUtil::sendFailure($lng->txt("rep_robj_xrev_no_access"), true);
			$ilCtrl->redirect($this, "showContent");
		}
		$ilCtrl->setParameter($this, "r_id", $_GET["r_id"]);
		$ilCtrl->setParameter($this, "q_id", $_GET["q_id"]);
		$input = new ilReviewInputGUI($this, "showContent", $this->object->loadReviewById($_GET["r_id"]),
												$this->object->loadQuestionTaxonomyData($_GET["q_id"]),
												$this->object->taxonomy(),
												$this->object->knowledgeDimension(),
												$this->object->expertise(),
												$this->object->rating(),
												$this->object->evaluation()
						 );
		if ($ilPluginAdmin->isActive(IL_COMP_MODULE, "TestQuestionPool", "qst", "assReviewableMultipleChoice")) {
			$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			$quest = new ilQuestionOverviewGUI($this, $q_gui->getPreview(true), $this->object->loadQuestionMetaData($_GET["q_id"]));
			$tpl->setContent($quest->getHTML().$input->getHtml());
		}
		else 
			$tpl->setContent($input->getHtml());
	}
	
	/*
	* Save review input
	*/
	public function saveReview() {
		global $tpl, $ilTabs, $lng, $ilCtrl, $ilPluginAdmin;
		$ilTabs->activateTab("content");
		if (!ilObjReviewAccess::checkAccessToObject($_GET["r_id"], "", "saveReview", "review")) {
			ilUtil::sendFailure($lng->txt("rep_robj_xrev_no_access"), true);
			$ilCtrl->redirect($this, "showContent");
		}
		$ilCtrl->setParameter($this, "r_id", $_GET["r_id"]);
		$ilCtrl->setParameter($this, "q_id", $_GET["q_id"]);
		$input = new ilReviewInputGUI($this, "showContent", $this->object->loadReviewById($_GET["r_id"]),
												$this->object->loadQuestionTaxonomyData($_GET["q_id"]),
												$this->object->taxonomy(),
												$this->object->knowledgeDimension(),
												$this->object->expertise(),
												$this->object->rating(),
												$this->object->evaluation()
						 );
		if ($input->checkInput()) {
			$form_data = array();
			$post_vars = array("dc", "dr", "de", "qc", "qr", "qe", "ac", "ar", "ae", "cog_r", "kno_r", "group_e", "comment", "exp");
			foreach ($post_vars as $post_var)
				$form_data[$post_var] = $input->getInput($post_var);
			$this->object->storeReviewById($_GET["r_id"], $form_data);
			$this->object->notifyAuthorAboutCompletion($_GET["r_id"]);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "showContent");
		}
		else
			$ilCtrl->setParameter($this, "r_id", $_GET["r_id"]);
		$input->setValuesByPost();
		if ($ilPluginAdmin->isActive(IL_COMP_MODULE, "TestQuestionPool", "qst", "assReviewableMultipleChoice")) {
			$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			$quest = new ilQuestionOverviewGUI($this, $q_gui->getPreview(true), $this->object->loadQuestionMetaData($_GET["q_id"]));
			$tpl->setContent($quest->getHTML().$input->getHtml());
		}
		else 
			$tpl->setContent($input->getHtml());
	}

	/**
	* Output reviews
	*/
	public function showReviews() {
		global $tpl, $ilTabs, $ilCtrl, $lng, $ilPluginAdmin;
		if (!ilObjReviewAccess::checkAccessToObject($_GET[substr($_GET["origin"], 0, 1)."_id"], "", "showReviews", $_GET["origin"])) {
			ilUtil::sendFailure($lng->txt("rep_robj_xrev_no_access"), true);
			$ilCtrl->redirect($this, "showContent");
		}		
		$ilTabs->activateTab("content");
		$tbl = new ilReviewOutputGUI($this, "showReviews", $this->object->loadReviewsByQuestion($_GET["q_id"]),
											  $this->object->loadQuestionTaxonomyData($_GET["q_id"]),
					  						  $this->object->taxonomy(),
											  $this->object->knowledgeDimension(),
											  $this->object->expertise(),
											  $this->object->rating(),
											  $this->object->evaluation()
					  );
		if ($ilPluginAdmin->isActive(IL_COMP_MODULE, "TestQuestionPool", "qst", "assReviewableMultipleChoice")) {
			$q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
			$quest = new ilQuestionOverviewGUI($this, $q_gui->getPreview(true), $this->object->loadQuestionMetaData($_GET["q_id"]));
			$tpl->setContent($quest->getHTML().$tbl->getHtml());
		}
		else 
			$tpl->setContent($tbl->getHtml());
	}
}
?>
