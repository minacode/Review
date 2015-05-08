<?php
/*
 * Encapsulated data of a question in the review cycle
 *
 * @var     integer     $id             unique object identifier
 * @var     integer     $review_obj     id of the calling review object
 * @var     integer     $question_id    id of the corresponding ILIAS question
 * @var     integer     $state          current state
 * @var     integer     $phase          current phase the question is in
 * @var     integer     $timestamp      timestamp of the last change
 * @var     assQuestion $question       the actual question object (read only)
 * @var     ilDB        $db             local reference to the ILIAS database
 *
 * TODO     replace integer constants with enums
 */
class ilCycleQuestion {
    private $id;
    private $review_obj;
    private $question_id;
    private $state;
    private $phase;
    private $timestamp;
    private $question;
    private $db;

    /*
     * Constructor
     */
    public function __construct(
        $id = "",
        $review_obj = "",
        $question_id = "",
        $state = "",
        $phase = "",
        $timestamp = "",
        $question = ""
    ) {
        global $ilDB;

        $this->id = $id;
        $this->review_obj = $review_obj;
        $this->question_id = $question_id;
        $this->state = $state;
        $this->phase = $phase;
        $this->timestamp = $timestamp;
        $this->question = $question;
        $this->db = $ilDB;
    }

    /*
     * Load the data of a question object from the database
     *
     * @param   integer     $id             id of the question in the review
     * cycle (NOT the ILIAS object id!)
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function loadFromDB($id) {
        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_quest WHERE id = %s",
            array("integer"),
            array($id)
        );

        if ($result->numRows() == 1) {
            $record = $this->db->fetchObject($result);
            $this->id = $record->id;
            $this->review_obj = $record->review_obj;
            $this->question_id = $record->question_id;
            $this->state = $record->state;
            $this->phase = $record->phase;
            $this->timestamp = $record->timestamp;
            $this->question
                = assQuestion::_instantiateQuestion($this->question_id);
            return true;
        } else {
            return false;
        }
    }

    /*
     * Store the data of a question object into the database
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function storeToDB() {
        if ($this->id == "") {
            return false;
        }

        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_quest WHERE id = %s",
            array("integer"),
            array($this->id)
        );

        if ($result->numRows() == 0) {
            $this->db->insert(
                "rep_robj_xrev_quest",
                array(
                    "id" => array("integer", $this->id),
                    "review_obj" => array("integer", $this->review_obj),
                    "question_id" => array("integer", $this->question_id),
                    "state" => array("integer", $this->state),
                    "phase" => array("integer", $this->phase),
                    "timestamp" => array("integer", $this->timestamp)
                )
            );
        } else {
            $this->db->update(
                "rep_robj_xrev_quest",
                array(
                    "review_obj" => array("integer", $this->review_obj),
                    "question_id" => array("integer", $this->question_id),
                    "state" => array("integer", $this->state),
                    "phase" => array("integer", $this->phase),
                    "timestamp" => array("integer", $this->timestamp)
                ),
                array("id" => array("integer", $this->id))
            );
        }
        /*
         * Do not save the question object to the database here, since it is
         * for read only!
         */
        return true;
    }

    /*
     * Delete a cycle question object from the database (not the actual
     * question)
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function deleteFromDB() {
        if ($this->id == "") {
            return false;
        }

        $this->db->manipulateF(
            "DELETE FROM rep_robj_xrev_quest WHERE id = %s",
            array("integer"),
            array($this->id)
        );
        return true;
    }

    /*
     * Set the state
     *
     * @param   integer     $state          state
     */
    public function setState($state) {
        $this->state = $state;
    }

    /*
     * Set the timestamp
     *
     * @param   integer     $timestamp      timestamp
     */
    public function setTimestamp($timestamp) {
        $this->timestamp = $timestamp;
    }

    /*
     * Set the phase
     *
     * @param   integer     $phase          phase
     */
    public function setPhase($phase) {
        $this->phase = $phase;
    }

    // TODO     ca. 1000000 getters
}
?>
