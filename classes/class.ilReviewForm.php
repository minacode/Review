<?php
/*
 * Encapsulated data of a review form
 *
 * @var     integer     $id             unique object identifier
 * @var     integer     $review_obj     id of the calling review object
 * @var     integer     $question_id    id of the reviewed question
 * @var     integer     $state          current state
 * @var     integer     $reviewer       id of the user editing the review form
 * @var     integer     $timestamp      timestamp of the last change
 * @var     integer     $desc_corr      technical correctness of description
 * @var     integer     $desc_relv      relevancy of description
 * @var     integer     $desc_expr      expression of description
 * @var     integer     $quest_corr     technical correctness of question
 * @var     integer     $quest_relv     relevancy of question
 * @var     integer     $quest_expr     expression of question
 * @var     integer     $answ_corr      technical correctness of answer
 * @var     integer     $answ_relv      relevancy of answer
 * @var     integer     $answ_expr      expression of answer
 * @var     integer     $taxonomy       taxonomy assumption by the reviewer
 * @var     integer     $knowledge_dimension       knowl. dim. assumption
 * @var     integer     $eval_comment   comment for evaluation
 * @var     integer     $rating         rating of the question
 * @var     integer     $expertise      expertise of the reviewer
 * @var     ilDB        $db             local reference to the ILIAS database
 * @var     ilReviewDBMapper    $mapper     local reference to the mapper
 *
 * TODO     replace integer constants with enums
 */
class ilReviewForm {
    private $id;
    private $review_obj;
    private $question_id;
    private $state;
    private $reviewer;
    private $timestamp;
    private $desc_corr;
    private $desc_relv;
    private $desc_expr;
    private $quest_corr;
    private $quest_relv;
    private $quest_expr;
    private $answ_corr;
    private $answ_relv;
    private $answ_expr;
    private $taxonomy;
    private $knowledge_dimension;
    private $eval_comment;
    private $rating;
    private $expertise;
    private $db;
    private $mapper;

    /*
     * Constructor
     */
    public function __construct(
        $db,
        $mapper,
        $id = "",
        $review_obj = "",
        $question_id = "",
        $state = "",
        $reviewer = "",
        $timestamp = "",
        $desc_corr = "",
        $desc_relv = "",
        $desc_expr = "",
        $quest_corr = "",
        $quest_relv = "",
        $quest_expr = "",
        $answ_corr = "",
        $answ_relv = "",
        $answ_expr = "",
        $taxonomy = "",
        $knowledge_dimension = "",
        $eval_comment = "",
        $expertise = "",
        $rating = ""
    ) {
        $this->db = $db;
        $this->mapper = $mapper;
        $this->id = $id;
        $this->review_obj = $review_obj;
        $this->question_id = $question_id;
        $this->state = $state;
        $this->reviewer = $reviewer;
        $this->timestamp = $timestamp;
        $this->desc_corr = $desc_corr;
        $this->desc_relv = $desc_relv;
        $this->desc_expr = $desc_expr;
        $this->quest_corr = $quest_corr;
        $this->quest_relv = $quest_relv;
        $this->quest_expr = $quest_expr;
        $this->answ_corr = $answ_corr;
        $this->answ_relv = $answ_relv;
        $this->answ_expr = $answ_expr;
        $this->taxonomy = $taxonomy;
        $this->knowledge_dimension = $knowledge_dimension;
        $this->eval_comment = $eval_comment;
        $this->rating = $rating;
        $this->expertise = $expertise;
    }

    /*
     * Load the data of a review form object from the database
     *
     * @param   integer     $review_id      id of the review
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function loadFromDB($review_id) {
        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_revi WHERE id = %s",
            array("integer"),
            array($review_id)
        );

        if ($result->numRows() == 1) {
            $record = $this->db->fetchObject($result);
            $this->id = $record->id;
            $this->review_obj = $record->review_obj;
            $this->question_id = $record->question_id;
            $this->state = $record->state;
            $this->reviewer = $record->reviewer;
            $this->timestamp = $record->timestamp;
            $this->desc_corr = $record->desc_corr;
            $this->desc_relv = $record->desc_relv;
            $this->desc_expr = $record->desc_expr;
            $this->quest_corr = $record->quest_corr;
            $this->quest_relv = $record->quest_relv;
            $this->quest_expr = $record->quest_expr;
            $this->answ_corr = $record->answ_corr;
            $this->answ_relv = $record->answ_relv;
            $this->answ_expr = $record->answ_expr;
            $this->taxonomy = $record->taxonomy;
            $this->knowledge_dimension = $record->knowledge_dimension;
            $this->eval_comment = $record->eval_comment;
            $this->rating = $record->rating;
            $this->expertise = $record->expertise;
            return true;
        } else {
            return false;
        }
    }

    /*
     * Store the data of a review form object into the database
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function storeToDB() {
        if ($this->id == "") {
            return false;
        }

        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_revi WHERE id = %s",
            array("integer"),
            array($this->id)
        );

        if ($result->numRows() == 0) {
            $this->db->insert(
                "rep_robj_xrev_revi",
                array(
                    "id" => array("integer", $this->id),
                    "review_obj" => array("integer", $this->review_obj),
                    "question_id" => array("integer", $this->question_id),
                    "state" => array("integer", $this->state),
                    "reviewer" => array("integer", $this->reviewer),
                    "timestamp" => array("integer", $this->timestamp),
                    "desc_corr" => array("integer", $this->desc_corr),
                    "desc_relv" => array("integer", $this->desc_relv),
                    "desc_expr" => array("integer", $this->desc_expr),
                    "quest_corr" => array("integer", $this->quest_corr),
                    "quest_relv" => array("integer", $this->quest_relv),
                    "quest_expr" => array("integer", $this->quest_expr),
                    "answ_corr" => array("integer", $this->answ_corr),
                    "answ_relv" => array("integer", $this->answ_relv),
                    "answ_expr" => array("integer", $this->answ_expr),
                    "taxonomy" => array("integer", $this->taxonomy),
                    "knowledge_dimension" => array(
                        "integer",
                        $this->knowledge_dimension
                    ),
                    "eval_comment" => array("clob", $this->eval_comment),
                    "rating" => array("integer", $this->rating),
                    "expertise" => array("integer", $this->expertise)
                )
            );
        } else {
            $this->db->update(
                "rep_robj_xrev_revi",
                array(
                    "review_obj" => array("integer", $this->review_obj),
                    "question_id" => array("integer", $this->question_id),
                    "state" => array("integer", $this->state),
                    "reviewer" => array("integer", $this->reviewer),
                    "timestamp" => array("integer", $this->timestamp),
                    "desc_corr" => array("integer", $this->desc_corr),
                    "desc_relv" => array("integer", $this->desc_relv),
                    "desc_expr" => array("integer", $this->desc_expr),
                    "quest_corr" => array("integer", $this->quest_corr),
                    "quest_relv" => array("integer", $this->quest_relv),
                    "quest_expr" => array("integer", $this->quest_expr),
                    "answ_corr" => array("integer", $this->answ_corr),
                    "answ_relv" => array("integer", $this->answ_relv),
                    "answ_expr" => array("integer", $this->answ_expr),
                    "taxonomy" => array("integer", $this->taxonomy),
                    "knowledge_dimension" => array(
                        "integer",
                        $this->knowledge_dimension
                    ),
                    "eval_comment" => array("clob", $this->eval_comment),
                    "rating" => array("integer", $this->rating),
                    "expertise" => array("integer", $this->expertise)
                ),
                array("id" => array("integer", $this->id))
            );
        }
        $this->mapper->notifyAboutChanges("review_forms");
        return true;
    }

    /*
     * Store the review form data in the history table of the database
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function copyToHistory() {
        if ($this->id == "") {
            return false;
        }

        $this->db->insert(
            "rep_robj_xrev_hist",
            array(
                "id" => array("integer", $this->id),
                "question_id" => array("integer", $this->question_id),
                "reviewer" => array("integer", $this->reviewer),
                "timestamp" => array("integer", $this->timestamp),
                "desc_corr" => array("integer", $this->desc_corr),
                "desc_relv" => array("integer", $this->desc_relv),
                "desc_expr" => array("integer", $this->desc_expr),
                "quest_corr" => array("integer", $this->quest_corr),
                "quest_relv" => array("integer", $this->quest_relv),
                "quest_expr" => array("integer", $this->quest_expr),
                "answ_corr" => array("integer", $this->answ_corr),
                "answ_relv" => array("integer", $this->answ_relv),
                "answ_expr" => array("integer", $this->answ_expr),
                "taxonomy" => array("integer", $this->taxonomy),
                "knowledge_dimension" => array(
                    "integer",
                    $this->knowledge_dimension
                ),
                "eval_comment" => array("clob", $this->eval_comment),
                "rating" => array("integer", $this->rating),
                "expertise" => array("integer", $this->expertise)
            )
        );
        return true;
    }

    /*
     * Delete a review object from the database
     *
     * @return  boolean     $success        true, if operation was performed
     */
    public function deleteFromDB() {
        if ($this->id == "") {
            return false;
        }

        $this->db->manipulateF(
            "DELETE FROM rep_robj_xrev_revi WHERE id = %s",
            array("integer"),
            array($this->id)
        );
        $this->mapper->notifyAboutChanges("review_forms");
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
     * Set the description correctness
     *
     * @param   integer     $desc_corr      description correctness
     */
    public function setDescCorr($desc_corr) {
        $this->desc_corr = $desc_corr;
    }

    /*
     * Set the description relevancy
     *
     * @param    integer     $desc_relv     description relevancy
     */
    public function setDescRelv($desc_relv) {
        $this->desc_relv = $desc_relv;
    }

    /*
     * Set the description expression
     *
     * @param    integer     $desc_expr     description expression
     */
    public function setDescExpr($desc_expr) {
        $this->desc_expr = $desc_expr;
    }

    /*
     * Set the answer correctness
     *
     * @param    integer     $answ_corr     answer correctness
     */
    public function setAnswCorr($answ_corr) {
        $this->answ_corr = $answ_corr;
    }

    /*
     * Set the answer relevancy
     *
     * @param    integer     $answ_relv     answer relevancy
     */
    public function setAnswRelv($answ_relv) {
        $this->answ_relv = $answ_relv;
    }

    /*
     * Set the answer expression
     *
     * @param    integer     $answ_expr     answer expression
     */
    public function setAnswExpr($answ_expr) {
        $this->answ_expr = $answ_expr;
    }

    /*
     * Set the question correctness
     *
     * @param    integer     $quest_corr    question correctness
     */
    public function setQuestCorr($quest_corr) {
        $this->quest_corr = $quest_corr;
    }

    /*
     * Set the question relevancy
     *
     * @param    integer     $quest_relv    question relevancy
     */
    public function setQuestRelv($quest_relv) {
        $this->quest_relv = $quest_relv;
    }

    /*
     * Set the question expression
     *
     * @param    integer     $quest_expr    question expression
     */
    public function setQuestExpr($quest_expr) {
        $this->quest_expr = $quest_expr;
    }

    /*
     * Set the taxonomy
     *
     * @param   integer     $taxonomy       taxonomy
     */
    public function setTaxonomy($taxonomy) {
        $this->taxonomy = $taxonomy;
    }

    /*
     * Set the knowledge dimension
     *
     * @param   integer     $knowledge_dimension    knowledge dimension
     */
    public function setKnowledgeDimension($knowledge_dimension) {
        $this->knowledge_dimension = $knowledge_dimension;
    }

    /*
     * Set the question rating
     *
     * @param   integer     $rating         question rating
     */
    public function setRating($rating) {
        $this->rating = $rating;
    }

    /*
     * Set the evaluation comment
     *
     * @param   string      $eval_comment   evaluation comment
     */
    public function setEvalComment($eval_comment) {
        $this->eval_comment = $eval_comment;
    }

    /*
     * Set the reviewer expertise
     *
     * @param   integer     $expertise      reviewer expertise
     */
    public function setExpertise($expertise) {
        $this->expertise = $expertise;
    }

    /*
     * Get the id
     *
     * @return  integer     $id             id
     */
    public function getId() {
        return $this->id;
    }

    /*
     * Get the review object
     *
     * @return  integer     $review_obj     review object
     */
    public function getReviewObj() {
        return $this->review_obj;
    }

    /*
     * Get the question id
     *
     * @return  integer     $question_id    question id
     */
    public function getQuestionID() {
        return $this->question_id;
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
     * Get the timestamp
     *
     * @return  integer     $timestamp      timestamp
     */
    public function getTimestamp() {
        return $this->timestamp;
    }

    /*
     * Get the description correctness
     *
     * @return  integer     $desc_corr      description correctness
     */
    public function getDescCorr() {
        return $this->desc_corr;
    }

    /*
     * Get the description relevancy
     *
     * @return   integer     $desc_relv     description relevancy
     */
    public function getDescRelv() {
        return $this->desc_relv;
    }

    /*
     * Get the description expression
     *
     * @return   integer     $desc_expr     description expression
     */
    public function getDescExpr() {
        return $this->desc_expr;
    }

    /*
     * Get the answer correctness
     *
     * @return   integer     $answ_corr     answer correctness
     */
    public function getAnswCorr() {
        return $this->answ_corr;
    }

    /*
     * Get the answer relevancy
     *
     * @return   integer     $answ_relv     answer relevancy
     */
    public function getAnswRelv() {
        return $this->answ_relv;
    }

    /*
     * Get the answer expression
     *
     * @return   integer     $answ_expr     answer expression
     */
    public function getAnswExpr() {
        return $this->answ_expr;
    }

    /*
     * Get the question correctness
     *
     * @return   integer     $quest_corr    question correctness
     */
    public function getQuestCorr() {
        return $this->quest_corr;
    }

    /*
     * Get the question relevancy
     *
     * @return   integer     $quest_relv    question relevancy
     */
    public function getQuestRelv() {
        return $this->quest_relv;
    }

    /*
     * Get the question expression
     *
     * @return   integer     $quest_expr    question expression
     */
    public function getQuestExpr() {
        return $this->quest_expr;
    }

    /*
     * Get the taxonomy
     *
     * @return  integer     $taxonomy       taxonomy
     */
    public function getTaxonomy() {
        return $this->taxonomy;
    }

    /*
     * Get the knowledge dimension
     *
     * @return  integer     $knowledge_dimension    knowledge dimension
     */
    public function getKnowledgeDimension() {
        return $this->knowledge_dimension;
    }

    /*
     * Get the question rating
     *
     * @return  integer     $rating         question rating
     */
    public function getRating() {
        return $this->rating;
    }

    /*
     * Get the evaluation comment
     *
     * @return  string      $eval_comment   evaluation comment
     */
    public function getEvalComment() {
        return $this->eval_comment;
    }

    /*
     * Get the reviewer expertise
     *
     * @return  integer     $expertise      reviewer expertise
     */
    public function getExpertise() {
        return $this->expertise;
    }

    /*
     * Get the user id of the reviewer
     *
     * @return  integer     $reviewer       user id of the reviewer
     */
    public function getReviewer() {
        return $this->reviewer;
    }
}
?>
