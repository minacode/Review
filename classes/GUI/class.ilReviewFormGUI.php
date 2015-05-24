<?php
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
        parent::__construct;
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

        $this->setTitle($this->parent_obj->txt("review_input");
        $this->setFormAction($ilCtrl->getFormAction($this));
        $this->addCommandButton("saveReview", $this->parent_obj->txt("save"));
        $this->addCommandButton(
            $this->parent_obj->getStandardCmd(),
            $this->parent_obj->txt("cancel")
        );

        $this->populateReviewPart();
        $this->populateTaxonomyPart();
        $this->populateEvaluationPart();
    }

    /*
     * Fill in the part to review the question
     */
    private function populateReviewPart() {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_obj->txt("review");
        $this->addItem($header);

        $aspect_header = new ilAspectHeaderGUI(
            "",
            array(
                $this->parent_obj->txt("correctness"),
                $this->parent_obj->txt("relevance"),
                $this->parent_obj->txt("expression")
            )
        );
        $this->addItem($aspect_header);
        $introduction = new ilAspectSelectInpuGUI(
            $this->parent_obj->txt("introduction"),
            array(
                array(
                    "postvar" => "dc",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getDescCorr(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "dr",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getDescRelv(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "de",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getDescExpr(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($introduction);
        $question = new ilAspectSelectInpuGUI(
            $this->parent_obj->txt("question"),
            array(
                array(
                    "postvar" => "qc",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getQuestCorr(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "qr",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getQuestRelv(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "qe",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getQuestExpr(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($question);
        $answers = new ilAspectSelectInpuGUI(
            $this->parent_obj->txt("answers"),
            array(
                array(
                    "postvar" => "ac",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getAnswCorr(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "ar",
                    "options" => $this->parent_obj->getEnum("rating"),
                    "value" => $this->review->getAnswRelv(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "ae",
                    "options" => $this->parent_obj->getEnum("rating"),
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
        $header->setTitle($this->parent_obj->txt("tax_know_dim");
        $this->addItem($header);

        $aspect_header = new ilAspectHeaderGUI(
            "",
            array(
                $this->parent_obj->txt("taxonomy"),
                $this->parent_obj->txt("knowledge_dim")
            )
        );
        $this->addItem($aspect_header);
        $author = new ilAspectSelectInpuGUI(
            $this->parent_obj->txt("author"),
            array(
                array(
                    "postvar" => "cog_a",
                    "options" => $this->parent_obj->getEnum("taxonomy"),
                    "value" => $this->question->getTaxonomy(),
                    "disabled" => true
                ),
                array(
                    "postvar" => "kno_a",
                    "options" => $this->parent_obj->getEnum(
                        "knowledge_dimension"
                    ),
                    "value" => $this->question->getKnowledgeDimension(),
                    "disabled" => true
                )
            )
        );
        $this->addItem($author);
        $reviewer = new ilAspectSelectInpuGUI(
            $this->parent_obj->txt("reviewer"),
            array(
                array(
                    "postvar" => "cog_r",
                    "options" => $this->parent_obj->getEnum("taxonomy"),
                    "value" => $this->review->getTaxonomy(),
                    "disabled" => $this->readonly
                ),
                array(
                    "postvar" => "kno_r",
                    "options" => $this->parent_obj->getEnum(
                        "knowledge_dimension"
                    ),
                    "value" => $this->review->getKnowledgeDimension(),
                    "disabled" => $this->readonly
                )
            )
        );
        $this->addItem($reviewer);
    }

    /*
     * Fill in the part to evaluate the question
     */
    private function populateEvaluationPart() {
        $header = new ilFormSectionHeaderGUI();
        $header->setTitle($this->parent_obj->txt("evaluation");
        $this->addItem($header);
        /* TODO fill in the rest of this */
    }
}
?>
