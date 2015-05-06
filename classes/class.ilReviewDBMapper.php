<?php
/*
 * Database abstraction class for access of ilDB
 *
 * @var     ilDB        $db             local reference to the ILIAS database
 * @var     integer     $obj_id         id of the calling review object
 * @var     array       $review_forms   review form objects
 */
class ilReviewDBMapper {
    private $obj_id;
    private $db;
    private $review_forms;

    /*
     * Constructor
     */
    public function __construct($review_obj) {
        global $ilDB;

        $this->obj_id = $obj_id;
        $this->db = $ilDB;
        $this->review_forms = array();
        $this->loadReviewForms;
    }

    /*
     * Load all review form objects that belong to the calling review object
     */
    private function loadReviewForms() {
        $result = $this->db->queryF(
            "SELECT id FROM rep_robj_xrev_revi WHERE review_obj = %s",
            array("integer"),
            array($this->obj_id)
        );
        while ($record = $ilDB->fetchObject($result)) {
            $review_form = new ilReviewForm($review_form->id);
            $review_form->loadFromDB($record->id);
            $this->review_forms[] = $review_form;
        }
    }
}
?>
