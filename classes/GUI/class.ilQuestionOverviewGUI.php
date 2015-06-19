<?php

/*
 * GUI to display question data
 *
 * @var		string		$html		rendered HTML equivalent to this object
 */
class ilQuestionOverviewGUI {
	private $html;

	/*
	 * Constructor, configures GUI output
	 *
	 * @param	ilObjReviewGUI		$parent_obj		parent GUI object
	 * @param	ilCycleQuestion		$question		displayed question
	 */
	public function __construct($parent_obj, $question) {
		global $tpl, $ilAccess;

        $path_to_il_tpl = ilPlugin::getPluginObject(IL_COMP_SERVICE,
            'Repository',
            'robj',
            'Review'
        )->getDirectory();
        $template = new ilTemplate(
            "tpl.question_overview.html",
            true,
            true,
            $path_to_il_tpl
        );
		$template->setVariable("TXT_TITLE", $question->getTitle());
		$template->setVariable("TXT_LOUTC", $question->getLearningOutcome());
        if ($ilAccess->checkAccess(
            "write",
            "",
            $parent_obj->object->getRefId())
        ) {
            $template->setVariable("TXT_AUTHOR", $question->getAuthor());
        }
		$template->setVariable("QUESTION", $parent_obj->getTxt("question"));
		$template->setVariable("TITLE", $parent_obj->getTxt("title"));
        if ($ilAccess->checkAccess(
            "write",
            "",
            $parent_obj->object->getRefId())
        ) {
            $template->setVariable(
                "AUTHOR",
                $parent_obj->getTxt("auth_quest")
            );
        }
        $template->setVariable(
            "LOUTC",
            $parent_obj->getTxt("learning_outcome")
        );
        $template->setVariable(
            "COMPL_QUESTION",
            $parent_obj->getTxt("compl_question")
        );

        $template->setVariable(
            "TXT_INNER_PART",
            assQuestionGUI::_getQuestionGUI(
                "",
                $question->getID()
            )->getSolutionOutput(0)
        );
		$this->html = $template->get();
	}

	/*
	 * Get the HTML string describing the look of this object
	 *
	 * @return		$this->html		rendered HTML string
	 */
	public function getHTML() {
		return $this->html;
	}
}
?>
