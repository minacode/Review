<?php
include_once 'class.ilAspectHeaderGUI.php';
include_once 'class.ilAspectSelectInputGUI.php';

/*
 * Form to input or display review data
 *
 * @var     ilReviewForm    $review         displayed review form
 * @var     ilCycleQuestion $question       question associated to the review
 * @var     ilObjReviewGUI  $parent_obj     calling GUI object
 * @var     boolean         $readonly       set content non-editable
 */
class ilReviewFormGUI extends ilPropertyFormGUI {
    private $review;
    private $question;
    private $parent_obj;
    private $readonly;

    public function __construct($review, $question, $parent_obj, $readonly) {
        parent::__construct();
        $this->review = $review;
        $this->question = $question;
        $this->parent_obj = $parent_obj;
        $this->readonly = $readonly;
        $this->populateForm();
    }

    /*
     * Create the GUI items displayed in the form
     */
    private function populateForm() {
        global $ilCtrl;

        if (!$this->readonly) {
            $this->setTitle($this->parent_obj->getTxt("review_input"));
            $this->setFormAction($ilCtrl->getFormAction($this));
            $this->addCommandButton("saveReview", $this->parent_obj->getTxt("save"));
            $this->addCommandButton(
                $this->parent_obj->getStandardCmd(),
                $this->parent_obj->getTxt("cancel")
            );
        }
        $this->populateReviewPart();
        $this->populateTaxonomyPart();
        $this->populateRatingPart();
    }

    /*
     * Fill in the part to review the question
     */
    private function populateReviewPart() {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_obj->getTxt("review"));
        $this->addItem($header);

        $aspect_header = new ilAspectHeaderGUI(
            "",
            array(
                $this->parent_obj->getTxt("correctness"),
                $this->parent_obj->getTxt("relevance"),
                $this->parent_obj->getTxt("expression")
            )
        );
        $this->addItem($aspect_header);
        $introduction = new ilAspectSelectInputGUI(
            $this->parent_obj->getTxt("introduction"),
            array(
                array(
                    "postvar" => "dc",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getDescCorr(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "dr",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getDescRelv(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "de",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getDescExpr(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($introduction);
        $question = new ilAspectSelectInputGUI(
            $this->parent_obj->getTxt("question"),
            array(
                array(
                    "postvar" => "qc",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getQuestCorr(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "qr",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getQuestRelv(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "qe",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getQuestExpr(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($question);
        $answers = new ilAspectSelectInputGUI(
            $this->parent_obj->getTxt("answers"),
            array(
                array(
                    "postvar" => "ac",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getAnswCorr(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "ar",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getAnswRelv(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "ae",
                    "options" => ilObjReview::getEnum("evaluation"),
                    "value" => $this->review->getAnswExpr(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($answers);
    }

    /*
     * Fill in the part to suggest the taxonomy and knowledge dimension
     */
    private function populateTaxonomyPart() {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_obj->getTxt("tax_know_dim"));
        $this->addItem($header);

        $aspect_header = new ilAspectHeaderGUI(
            "",
            array(
                $this->parent_obj->getTxt("taxonomy"),
                $this->parent_obj->getTxt("knowledge_dim")
            )
        );
        $this->addItem($aspect_header);
        $author = new ilAspectSelectInputGUI(
            $this->parent_obj->getTxt("auth_quest"),
            array(
                array(
                    "postvar" => "cog_a",
                    "options" => ilObjReview::getEnum("taxonomy"),
                    "value" => $this->question->getTaxonomy(),
                    "disabled" => true
                ),
                array(
                    "postvar" => "kno_a",
                    "options" => ilObjReview::getEnum(
                        "knowledge dimension"
                    ),
                    "value" => $this->question->getKnowledgeDimension(),
                    "disabled" => true
                )
            )
        );
        $this->addItem($author);
        $reviewer = new ilAspectSelectInputGUI(
            $this->parent_obj->getTxt("reviewer"),
            array(
                array(
                    "postvar" => "cog_r",
                    "options" => ilObjReview::getEnum("taxonomy"),
                    "value" => $this->review->getTaxonomy(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "kno_r",
                    "options" => ilObjReview::getEnum(
                        "knowledge dimension"
                    ),
                    "value" => $this->review->getKnowledgeDimension(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($reviewer);
    }

    /*
     * Fill in the part to rate the question
     */
    private function populateRatingPart() {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_obj->getTxt("evaluation"));
        $this->addItem($header);

        $evaluation = new ilRadioGroupInputGUI(
            $this->parent_obj->getTxt("rating"),
            "evaluation"
        );
        $eva_1 = new ilRadioOption(
            $this->parent_obj->getTxt("quest_accept"),
            "1",
            ""
        );
		$evaluation->addOption($eva_1);
        $eva_2 = new ilRadioOption(
            $this->parent_obj->getTxt("quest_edit"),
            "2",
            ""
        );
		$evaluation->addOption($eva_2);
        $eva_3 = new ilRadioOption(
            $this->parent_obj->getTxt("quest_refuse"),
            "3",
            ""
        );
		$evaluation->addOption($eva_3);
		$evaluation->setValue($this->review->getRating());
		$evaluation->setRequired(true);
        $evaluation->setDisabled($this->readonly);
		$this->addItem($evaluation);

        $comment = new ilTextAreaInputGUI(
            $this->parent_obj->getTxt("comment"),
            "comment"
        );
		$comment->setCols(70);
		$comment->setRows(10);
		$comment->setValue($this->review->getEvalComment());
		$comment->setRequired(true);
        $comment->setDisabled($this->readonly);
		$this->addItem($comment);

        $expertise = new ilSelectInputGUI(
            $this->parent_obj->getTxt("expertise"),
            "exp"
        );
		$expertise->setValue($this->review->getExpertise());
		$expertise->setOptions(ilObjReview::getEnum("expertise"));
		$expertise->setRequired(true);
        $expertise->setDisabled($this->readonly);
		$this->addItem($expertise);
    }
}
?>
