<?php
/*
 * Encapsulated data of a question in the review cycle
 *
 * @var     integer     $cycle_id             unique object identifier
 * @var     integer     $review_obj     id of the calling review object
 * @var     integer     $question_id    id of the corresponding ILIAS question
 * @var     integer     $state          current state
 * @var     integer     $phase          current phase the question is in
 * @var     integer     $timestamp      timestamp of the last change
 * @var     assQuestion $question       the actual question object (read only)
 * @var     ilDB        $db             local reference to the ILIAS database
 * @var     ilReviewDBMapper    $mapper     local reference to the mapper
 *
 * TODO     replace integer constants with enums
 */
class ilCycleQuestion {
    private $cycle_id;
    private $review_obj;
    private $question_id;
    private $state;
    private $phase;
    private $timestamp;
    private $question;
    private $db;
    private $mapper;

    /*
     * Constructor
     */
    public function __construct(
        $db,
        $mapper,
        $review_obj = "",
        $question_id = "",
        $state = "",
        $phase = "",
        $timestamp = "",
        $question = "",
        $cycle_id = ""
    ) {
        $this->db = $db;
        $this->mapper = $mapper;
        $this->review_obj = $review_obj;
        $this->question_id = $question_id;
        $this->state = $state;
        $this->phase = $phase;
        $this->timestamp = $timestamp;
        $this->question = $question;
        $this->cycle_id = $cycle_id;
    }

    /*
     * Load the data of a question object from the database
     *
     * @param   integer     $cycle_id             id of the question in the review
     * cycle (NOT the ILIAS object id!)
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function loadFromDB($cycle_id) {
        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_quest WHERE id = %s",
            array("integer"),
            array($cycle_id)
        );

        if ($result->numRows() == 1) {
            $record = $this->db->fetchObject($result);
            $this->cycle_id = $record->id;
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
        if ($this->cycle_id == "") {
            return false;
        }

        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_quest WHERE id = %s",
            array("integer"),
            array($this->cycle_id)
        );

        if ($result->numRows() == 0) {
            $this->db->insert(
                "rep_robj_xrev_quest",
                array(
                    "id" => array("integer", $this->cycle_id),
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
                array("id" => array("integer", $this->cycle_id))
            );
        }
        /*
         * Do not save the question object to the database here, since it is
         * for read only!
         */
        $this->mapper->notifyAboutChanges("cycle_questions");
        return true;
    }

    /*
     * Delete a cycle question object from the database (not the actual
     * question)
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function deleteFromDB() {
        if ($this->cycle_id == "") {
            return false;
        }

        $this->db->manipulateF(
            "DELETE FROM rep_robj_xrev_quest WHERE id = %s",
            array("integer"),
            array($this->cycle_id)
        );
        $this->mapper->notifyAboutChanges("cycle_questions");
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

    /*
     * Get the question ID
     *
     * @return  integer     $id             id
     */
    public function getID() {
        return $this->question_id;
    }

    /*
     * Get the review object
     *
     * @return  integer     $review_object  review object
     */
    public function getReviewObject() {
        return $this->review_object;
    }

    /*
     * Get the cycle ID
     *
     * @return  integer     $cycle          id in the review cycle
     */
    public function getCycleID() {
        return $this->cycle_id;
    }

    /*
     * Get the state
     *
     * @return  integer     $state          state
     */
    public function getState() {
        return $this->state;
    }

    /*
     * Get the phase
     *
     * @return  integer     $phase          phase
     */
    public function getPhase() {
        return $this->phase;
    }

    /*
     * Get the timestamp
     *
     * @return  integer     $timestamp      timestamp
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /*
     * Get the taxonomy
     *
     * @return  integer     $taxonomy       taxonomy
     */
    public function getTaxonomy() {
        return $this->question->getTaxonomy();
    }

    /*
     * Get the knowledge dimension
     *
     * @return  integer     $knowledge_dimension    knowledge dimension
     */
    public function getKnowledgeDimension() {
        return $this->question->getKnowledgeDimension();
    }

    /*
     * Get the learning outcome
     *
     * @return  integer     $learning_outcome       learning outcome
     */
    public function getLearningOutcome() {
        return $this->question->getLearningOutcome();
    }

    /*
     * Get the topic
     *
     * @return  integer     $topic          topic
     */
    public function getTopic() {
        return $this->question->getTopic();
    }

    /*
     * Get the question pool
     *
     * @return  integer     $obj_fi         question pool
     */
    public function getQuestionPool() {
        return $this->question->getObjID();
    }

    /*
     * Get the title
     *
     * @return  string      $title          title
     */
    public function getTitle() {
        return $this->question->getTitle();
    }

    /*
     * Get the author
     *
     * @return  string      $author         author
     */
    public function getAuthor() {
        return $this->question->getAuthor();
    }

    /*
     * Get the owner
     *
     * @return  integer     $owner          owner
     */
    public function getOwner() {
        return $this->question->getOwner();
    }

    /*
     * Get the question type
     *
     * @return  string      $question_type  question type
     */
    public function getQuestionType() {
        return $this->question->getQuestionType();
    }
}
?>
