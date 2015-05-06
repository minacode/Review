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
 * @var     ilDB        $db             local reference to the ILIAS database
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
    private $db;

    /*
     * Constructor
     */
    public function __construct(
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
        $rating = ""
    ) {
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
        $this->knowledge_dimension = $taxonomy;
        $this->eval_comment = $eval_comment;
        $this->rating = $rating;
    }

    /*
     * Load the data of a review form object from the database
     *
     * @param   integer     $review_id          id of the review
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
            $this->knowledge_dimension = $record->taxonomy;
            $this->eval_comment = $record->eval_comment;
            $this->rating = $record->rating;
        }
    }
}
?>
