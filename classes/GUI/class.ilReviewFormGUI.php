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

        /* General form part */
        $this->setTitle($this->parent_obj->txt("review_input");
        $this->setFormAction($ilCtrl->getFormAction($this));
        $this->addCommandButton("saveReview", $this->parent_obj->txt("save"));
        $this->addCommandButton(
            $this->parent_obj->getStandardCmd(),
            $this->parent_obj->txt("cancel")
        );

        /* Part to review the question */
        $review_header = new ilFormSectionHeaderGUI();
        $review_header->setTitle($this->parent_obj->txt("review");
        $this->addItem($review_header);

        $review_aspect_header = new ilAspectHeaderGUI();
        $this->addItem($review_aspect_header);
        $introduction = new ilAspectSelectInpuGUI(/* TODO new parameters */);
        $this->addItem($introduction);
        $question = new ilAspectSelectInpuGUI(/* TODO new parameters */);
        $this->addItem($question);
        $answers = new ilAspectSelectInpuGUI(/* TODO new parameters */);
        $this->addItem($answers);

        /* Part to suggest the taxonomy and knowledge dimension */
        $taxknowdim_header = new ilFormSectionHeaderGUI();
        $taxknowdim_header->setTitle($this->parent_obj->txt("tax_know_dim");
        $this->addItem($taxknowdim_header);

        $taxknowdim_aspect_header = new ilAspectHeaderGUI();
        $this->addItem($taxknowdim_aspect_header);
        $author = new ilAspectSelectInpuGUI(/* TODO new parameters */);
        $this->addItem($author);
        $reviewer = new ilAspectSelectInpuGUI(/* TODO new parameters */);
        $this->addItem($reviewer);

        /* Part to evaluate the question */
        $evaluation_header = new ilFormSectionHeaderGUI();
        $evaluation_header->setTitle($this->parent_obj->txt("evaluation");
        $this->addItem($evaluation_header);

        /* TODO fill in the rest of this */
    }
}
?>
