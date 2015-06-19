<?php

/*
 * GUI class for the list of reviewer allocation matrizes
 *
 * @var     ilObjReviewGUI      $parent_obj         parent object
 */
class ilReviewerAllocFormGUI extends ilPropertyFormGUI {
    private $parent_obj;

    /*
     * Constructor
     */
    public function __construct($parent_obj, $allocation) {
        global $ilCtrl;

        parent::__construct();
        $this->parent_obj = $parent_obj;

        $this->setTitle($this->parent_obj->getTxt("phases"));
        $this->setFormAction($ilCtrl->getFormAction($this));

        /*
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
            $phase_head = new ilFormSectionHeaderGUI();
            $phase_head->setTitle($lng->txt("phase") . " " . $phase->phase);
            $this->addItem($phase_head);
            $reviewer_head = new ilAspectHeadGUI($member_names);
            $this->addItem($reviewer_head);

            foreach ($members as $member) {
                $matrix_row = new ilCheckMatrixRowGUI($phase->phase, $member, $member_ids);
                $this->addItem($matrix_row);
            }
            $nr_input = new ilNumberInputGUI($lng->txt("nr_reviewers"), "nr_" . $phase->phase);
            $nr_input->setMinValue(1);
            $nr_input->setRequired(true);
            $this->addItem($nr_input);
        }
         */
        //print_r($allocation);
        foreach ($allocation as $phase => $assignments) {
            $phase_head = new ilFormSectionHeaderGUI();
            $title = $this->parent_obj->getTxt("phase") . " " . $phase + 1;
            $phase_head->setTitle($title);
            $this->addItem($phase_head);

            ksort($assignments);

            $reviewer_head = new ilAspectHeaderGUI(
                "",
                array_map(
                    function($id) { return ilObject::_lookupTitle($id); },
                    array_keys($assignments)
                )
            );
            $this->addItem($reviewer_head);

            foreach ($assignments as $author => $reviewers) {
                $row_assignment = array();
                foreach (array_keys($assignments) as $member) {
                    $row_assignment[$member] = in_array($member, $reviewers);
                }
                ksort($row_assignment);
                $row = new ilAllocationRowGUI(
                    $phase + 1,
                    $author,
                    $row_assignment
                );
                $this->addItem($row);
            }

            $nr_input = new ilNumberInputGUI(
                $this->parent_obj->getTxt("nr_reviewers"),
                "nr_" . $phase + 1
            );
            $nr_input->setMinValue(1);
            $nr_input->setRequired(true);
            $nr_input->setValue(
                $this->parent_obj->object->getReviewersPerPhase($phase + 1)
            );
            $this->addItem($nr_input);
        }

        $this->addCommandButton("saveAllocateReviewers", $this->parent_obj->getTxt("save"));
        $this->addCommandButton("addPhase", $this->parent_obj->getTxt("add_phase"));
        $this->addCommandButton("removePhase", $this->parent_obj->getTxt("remove_phase"));
    }

    /*
     * Check the form input of the user
     *
     * @return      bool            $valid          true, if everything is okay
     */
    public function checkInput() {
        global $lng;

        $valid = parent::checkInput();
        $nr_inputs = array();
        foreach ($this->getItems() as $item) {
            if ($item instanceof ilNumberInputGUI) {
                $nr_inputs[$item->getPostvar()]
                    = $this->getInput($item->getPostvar());
            }
        }
        foreach ($this->getItems() as $item) {
            if ($item instanceof ilCheckMatrixRowGUI) {
                $valid &= $item->getTickCount()
                    >= $nr_inputs["nr_" . $item->getGroupID()];
            }
        }
        if (!$valid) {
			ilUtil::sendFailure($lng->txt("too_few_reviewers_allocated"));
        }
        return $valid;
    }
}
