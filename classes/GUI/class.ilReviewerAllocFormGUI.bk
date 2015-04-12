<?php

/*
 * GUI class for the List of reviewer allocation matrizes
 */
class ilReviewerAllocFormGUI extends ilCustomInputGUI {

    public function __construct($members, $phases, $parent) {
        global $lng, $ilCtrl;

        $html = "";

        $member_names = array();
        foreach ($members as $member) {
            $member->name = $member->firstname . ' ' . $member->lastname;
            $member_names[] = $member->name;
        }
        $member_ids = array();
        foreach ($members as $member) {
            $member_ids[] = $member->id;
        }

        foreach ($phases as $phase) {
            $alloc_form = new ilPropertyFormGUI();
            $alloc_form->setTitle($lng->txt("phase") . " " . $phase->phase);
            $alloc_form->setFormAction($ilCtrl->getFormAction($this));

            $reviewer_head = new ilAspectHeadGUI($member_names);
            $alloc_form->addItem($reviewer_head);

            foreach ($members as $member) {
                $matrix_row = new ilCheckMatrixRowGUI($member, $member_ids);
                $alloc_form->addItem($matrix_row);
            }
            $nr_input = new ilNumberInputGUI($lng->txt("nr_reviewers"), "nr_reviewers");
            $nr_input->setMinValue(1);
            $nr_input->setRequired(true);
            $alloc_form->addItem($nr_input);

            $alloc_form->addCommandButton("saveAllocateReviewers", $lng->txt("save"));

            $html .= $alloc_form->getHTML();
        }

        $bf = new ilPropertyFormGUI();
        $bf->addCommandButton("addPhase", $lng->txt("add_phase"));
        $bf->addCommandButton("removePhase", $lng->txt("remove_phase"));
        $bf->setFormAction($ilCtrl->getFormActionByClass("ilObjReviewGUI", "allocateReviewers"));
        $this->setHTML($html . $bf->getHTML());
    }
}
