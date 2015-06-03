<?php

include_once 'Services/Table/classes/class.ilTable2GUI.php';
include_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

/*
 * Table GUI for questions to convert
 */
class ilConvertQuestionTableGUI extends ilTable2GUI {

	/*
	 * Constructor, configures GUI output
	 *
	 * @param		object		$a_parent_obj		GUI object that contains this object
	 * @param		string		$a_parent_cmd		Command that causes construction of this object
	 * @param		array			$questions			associative arrays of displayed data (column => value)
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $questions) {
		global $ilCtrl, $lng;
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->addColumn($lng->txt("title"), "", "30%");
		$this->addColumn($lng->txt("author"), "", "25%");
		$this->addColumn($lng->txt("type"), "", "25%");
		$this->addColumn($lng->txt("action"), "", "20%");
        $this->setEnableHeader(true);

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
        $this->setRowTemplate("tpl.convert_question_table_row.html", ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory());
        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");

		$ilCtrl->saveParameterByClass("ilObjReviewGUI", array("q_id"));

        $this->setData($questions);

        $this->setTitle($lng->txt("rep_robj_xrev_nonrev_questions"));
	}

	/*
	 * Fill a single data row
	 *
	 * @param	array		$set		Data record, displayed as one table row
	 */
	protected function fillRow($a_set) {
		global $ilCtrl, $lng;
		$ilCtrl->saveParameterByClass("ilObjReviewGUI", array("q_id"));
		$ilCtrl->setParameterByClass("ilObjReviewGUI", "q_id", $a_set["question_id"]);
		$this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_AUTHOR", $a_set["author"]);
        $this->tpl->setVariable("TXT_TYPE", assQuestion::_getQuestionTypeName($a_set["type_tag"]));
		$this->tpl->setVariable("TXT_ACTION", $lng->txt("rep_robj_xrev_convert"));
		$this->tpl->setVariable("LINK_ACTION", $ilCtrl->getLinkTargetByClass("ilObjReviewGUI", "performConvertQuestion"));
    }
}
?>
