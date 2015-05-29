<?php

include_once 'Services/Table/classes/class.ilTable2GUI.php';

/*
 * Table GUI for reviews
 *
 * @var     string      read_cmd        command for links to passive GUIs
 * @var     string      write_cmd       command for links to active GUIs
 */
class ilReviewTableGUI extends ilTable2GUI {
    private $read_cmd;
    private $write_cmd;

	/*
	 * Constructor, configures GUI output
	 *
	 * @param	object		$parent_obj		    GUI that contains this object
	 * @param	string		$parent_cmd		    calling command
	 * @param	array		$reviews		    ilReviewForm objects
	 */
    public function __construct(
        $parent_obj,
        $parent_cmd,
        $reviews,
        $read_cmd = "",
        $write_cmd = ""
    ) {
        global $ilCtrl;

        parent::__construct($parent_obj, $parent_cmd);
        $this->read_cmd = $read_cmd;
        $this->write_cmd = $write_cmd;

        $this->addColumn(
            $this->getParentObject()->getTxt("rep_robj_xrev_title_quest"),
            "",
            "80%"
        );
        $this->addColumn(
            $this->getParentObject()->getTxt("action"),
            "",
            "20%"
        );
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction(
            $this->getParentObject(),
            $this->getParentCmd()
        ));
        $this->setRowTemplate(
            "tpl.review_table_row.html",
            ilPlugin::getPluginObject(
                IL_COMP_SERVICE,
                'Repository',
                'robj',
                'Review'
            )->getDirectory()
        );
        $this->setDefaultOrderField("id");
        $this->setDefaultOrderDirection("asc");

        $ilCtrl->saveParameterByClass(
            "ilObjReviewGUI",
            array("r_id", "q_id", "origin")
        );

        $this->prepareData($reviews);
	}

    /*
     * Make associative array from the review objects
     *
     * @param   array       $reviews            ilReviewForm objects
     */
    private function prepareData($reviews) {
        $data = array();
        foreach ($reviews as $review) {
            $data[] = array(
                "id" => $review->getID(),
                "question_id" => $review->getQuestionID(),
                "title" => $this->getParentObject()->getObject()
                    ->loadQuestionById($review->getQuestionID())->getTitle(),
                "state" => $review->getState()
            );
        }
        $this->setData($data);
    }
	/*
	 * Fill a single data row
	 *
	 * @param	array       $row                review data record
	 */
	protected function fillRow($row) {
		global $ilCtrl;

        $ilCtrl->setParameterByClass("ilObjReviewGUI", "r_id", $row["id"]);
        $ilCtrl->setParameterByClass(
            "ilObjReviewGUI",
            "q_id",
            $row["question_id"]
        );
		$ilCtrl->setParameterByClass("ilObjReviewGUI", "origin", "review");

        $this->tpl->setVariable("TXT_TITLE", $row["title"]);
        if ($row["state"] != 0) {
            $this->tpl->setVariable(
                "TXT_ACTION",
                $this->getParentObject()->getTxt("view")
            );
            $this->tpl->setVariable(
                "LINK_ACTION",
                $ilCtrl->getLinkTargetByClass(
                    "ilObjReviewGUI",
                    $this->read_cmd
                )
            );
		} else {
            $this->tpl->setVariable(
                "TXT_ACTION",
                $this->getParentObject()->getTxt("create")
            );
            $this->tpl->setVariable(
                "LINK_ACTION",
                $ilCtrl->getLinkTargetByClass(
                    "ilObjReviewGUI",
                    $this->write_cmd
                )
            );
		}
	}
}
?>
