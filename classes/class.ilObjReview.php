<?php

include_once "Services/Repository/classes/class.ilObjectPlugin.php";
include_once "QuestionManager/class.ilReviewableQuestionPluginGenerator.php";
include_once "class.ilReviewDBMapper.php";

/*
 * Application class for Review repository object.
 *
 * @var     integer     $group_id       id of the group the plugin object is in
 * @var     ilReviewDBMapper    $review_db      review plugin part of ILIAS db
 *
 * @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
 *
 * $Id$
 */

class ilObjReview extends ilObjectPlugin {
    private $group_id;
    private $review_db;

    /*
     * Constructor
     */
    public function __construct($ref_id = 0) {
        parent::__construct($ref_id);
    }


    /*
     * Set type
     */
    final function initType() {
        $this->setType("xrev");
    }

    /*
     * Create object
     */
    function doCreate() {
        global $ilDB, $ilCtrl;

        $ilDB->insert(
            "rep_robj_xrev_revobj",
            array(
                "id" => array("integer", $this->getId()),
                "group_id" => array("integer", $_GET["ref_id"])
            )
        );
    }

    /*
     * Read object data from db
     */
    function doRead() {
        global $ilDB;

        $set = $ilDB->queryF(
            "SELECT * FROM rep_robj_xrev_revobj WHERE id = %s",
            array("integer"),
            array($this->getId())
        );
        while ($rec = $ilDB->fetchObject($set)) {
            $this->obj_id = $rec->obj_id;
            $this->group_id = $rec->group_id;
        }

        $this->review_db = new ilReviewDBMapper($this->getID());
        $this->syncQuestionDB();
    }

    /*
     * Update data
     */
    function doUpdate() {
        global $ilDB;

        $ilDB->update(
            "rep_robj_xrev_revobj",
            array("group_id" => array("integer", $this->getGroupId())),
            array("id" => array("integer", $this->getId()))
        );
    }

    /*
     * Delete data from db
     */
    function doDelete() {
        // pointless, it seems this function is not called by ILIAS
    }

    /*
     * Do Cloning
     */
    function doClone($a_target_id, $a_copy_id, $new_obj) {
        $new_obj->setGroupId($this->getGroupId());
        $new_obj->update();
    }

    /*
     * Get the id of the group this object belongs to
     */
    public function getGroupId() {
        return $this->group_id;
    }

    /*
     * Set the id of the group this object belongs to
     */
    public function setGroupId($group_id) {
        $this->group_id = $group_id;
    }

    /*
     * Load all questions from the groups´ Question Pools,
     * thus updating the plugin´s question db
     */
    private function syncQuestionDB() {
        global $ilDB, $ilUser, $ilPluginAdmin;

        function cmp_rec($a, $b) {
            if ($a["question_id"] > $b["question_id"])
                return 1;
            if ($a["question_id"] < $b["question_id"])
                return -1;
            return 0;
        }

        // uncomment as soos as needed
        // $ilDB->lockTables(array("qpl_questions", "rep_robj_xrev_quest"));
        $qpl = $ilDB->queryF("SELECT qpl_questions.question_id AS question_id, tstamp FROM qpl_questions ".
                "INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi ".
                "INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id ".
                "INNER JOIN qpl_rev_qst ON qpl_rev_qst.question_id=qpl_questions.question_id ".
                "WHERE crs_items.parent_id=%s AND qpl_questions.original_id IS NULL",
                array("integer"),
                array($this->getGroupId())
        );
        $db_questions = array();
        while ($db_question = $ilDB->fetchAssoc($qpl))
            $db_questions[] = $db_question;
        $pqs = $ilDB->queryF("SELECT * FROM rep_robj_xrev_quest WHERE review_obj=%s",
                array("integer"), array($this->getId())
        );
        $pl_questions = array();
        while ($pl_question = $ilDB->fetchAssoc($pqs))
            $pl_questions[] = $pl_question;

        foreach ($db_questions as $db_question) {
            foreach ($pl_questions as $pl_question) {
                if ($db_question["question_id"] == $pl_question["question_id"]) {
                    if ($db_question["tstamp"] > $pl_question["timestamp"]) {
                        $ilDB->update("rep_robj_xrev_quest",
                                array("timestamp" => array("integer", $db_question["tstamp"])),
                                array("question_id" => array("integer", $db_question["question_id"]),
                                        "review_obj" => array("integer", $this->getId())
                                )
                        );
                        //$this->copyReviewsToHistory($db_question["question_id"]);
                        /*
                        $ilDB->update("rep_robj_xrev_revi",
                                array("state" => array("integer", 0)),
                                array("question_id" => array("integer", $db_question["question_id"]),
                                        "review_obj" => array("integer", $this->getId())
                                )
                        );
                        $this->notifyReviewersAboutChange($db_question);
                         */
                        if ($pl_question["state"] == 0) {
                            $this->proceedToNextPhase($db_question["question_id"]);
                        }
                        break;
                    }
                }
            }
        }

        foreach (array_udiff($db_questions, $pl_questions, "cmp_rec") as $new_question) {
            $ilDB->insert("rep_robj_xrev_quest", array("id" => array("integer", $ilDB->nextId("rep_robj_xrev_quest")),
                            "question_id" => array("integer", $new_question["question_id"]),
                            "timestamp" => array("integer", $new_question["tstamp"]),
                            "state" => array("integer", 0),
                            "phase" => array("integer", 0),
                            "review_obj" => array("integer", $this->getId())
                    )
            );
            $this->proceedToNextPhase($new_question["question_id"]);
            $this->notifyAdminsAboutNewQuestion($new_question);
        }

        foreach (array_udiff($pl_questions, $db_questions, "cmp_rec") as $del_question) {
            $this->notifyReviewersAboutDeletion($del_question);
            $ilDB->manipulateF("DELETE FROM rep_robj_xrev_quest WHERE question_id=%s AND review_obj=%s",
                    array("integer", "integer"),
                    array($del_question["question_id"], $this->getId())
            );
            $ilDB->manipulateF("DELETE FROM rep_robj_xrev_revi WHERE question_id=%s AND review_obj=%s",
                    array("integer", "integer"),
                    array($del_question["question_id"], $this->getId())
            );
        }

        //uncomment as soon as needed
        // $ilDB->unlockTables();
    }

    /*
     * Load all questions in the review cycle that were created by the user in all of the groups´ question pools
     *
     * @return       array           $db_questions           the questions loaded by this function as an associative array
     */
    public function loadQuestionsByUser() {
        global $ilDB, $ilUser;

        $qpl = $ilDB->queryF("SELECT qpl_questions.question_id AS id, title FROM qpl_questions ".
                "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=qpl_questions.question_id ".
                "WHERE qpl_questions.original_id IS NULL AND qpl_questions.owner=%s ".
                "AND (rep_robj_xrev_quest.state=%s OR rep_robj_xrev_quest.state=0) AND rep_robj_xrev_quest.review_obj=%s",
                array("integer", "integer", "integer"),
                array($ilUser->getId(), 1, $this->getId()));
        $db_questions = array();
        while ($db_question = $ilDB->fetchAssoc($qpl))
            $db_questions[] = $db_question;
        return $db_questions;
    }

    /*
     * Load all reviews created by the user for all questions in the groups´ question pools
     *
     * @return       array           $reviews                the reviews loaded by this function as an associative array
     */
    public function loadReviewsByUser() {
        global $ilDB, $ilUser;

        $rev = $ilDB->queryF("SELECT rep_robj_xrev_revi.id, qpl_questions.title, qpl_questions.question_id, rep_robj_xrev_revi.state FROM rep_robj_xrev_revi ".
                "INNER JOIN qpl_questions ON qpl_questions.question_id=rep_robj_xrev_revi.question_id ".
                "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=rep_robj_xrev_revi.question_id ".
                "WHERE rep_robj_xrev_revi.reviewer=%s AND rep_robj_xrev_revi.review_obj=%s AND (rep_robj_xrev_quest.state=1 OR rep_robj_xrev_quest.state=0)",
                array("integer", "integer"),
                array($ilUser->getId(), $this->getId()));
        $reviews = array();
        while ($review = $ilDB->fetchAssoc($rev))
            $reviews[] = $review;
        return $reviews;
    }

    /*
     * Load a review with a certain ID from the review database
     *
     * @param   int         $review_id          ID of the review to load
     *
     * @return  ilReviewForm    $review         review with the given ID
     */
    public function loadReviewById($review_id) {
        $reviews = $this->review_db->getReviewForms(array("id" => $review_id));
        return reset($reviews);
    }

    /*
     * Load a question with a certain ID from the review database
     *
     * @param   int         $question_id        ID of the question to load
     *
     * @return  ilCycleQuestion     $question   question with the given ID
     */
    public function loadQuestionById($question_id) {
        $questions = $this->review_db->getCycleQuestions(
            array("question_id" => $question_id)
        );
        return reset($questions);
    }

    /*
     * Load all completed reviews for a question from the review database
     *
     * @param   int         $question_id        ID of the question
     *
     * @return  array       $reviews            ilReviewForm objects
     */
    public function loadCompletedReviewsByQuestion($question_id) {
        $reviews = $this->review_db->getReviewForms(
            array("question_id" => $question_id, "state" => 1)
        );
        return $reviews;
    }

    /*
     * Update data of an existing review by form input
     *
     * @param                int             $id                     ID of the review to be updated
     * @param                array           $form_data      user input to be stored
     */
    public function storeReviewByID($id, $form_data) {
        global $ilDB;

        $ilDB->update("rep_robj_xrev_revi", array("timestamp" => array("integer", time()),
                "state" => array("integer", 1),
                "desc_corr" => array("integer", $form_data["dc"]),
                "desc_relv" => array("integer", $form_data["dr"]),
                "desc_expr" => array("integer", $form_data["de"]),
                "quest_corr" => array("integer", $form_data["qc"]),
                "quest_relv" => array("integer", $form_data["qr"]),
                "quest_expr" => array("integer", $form_data["qe"]),
                "answ_corr" => array("integer", $form_data["ac"]),
                "answ_relv" => array("integer", $form_data["ar"]),
                "answ_expr" => array("integer", $form_data["ae"]),
                "taxonomy" => array("integer", $form_data["cog_r"]),
                "knowledge_dimension" => array("integer", $form_data["kno_r"]),
                "rating" => array("integer", $form_data["group_e"]),
                "eval_comment" => array("clob", $form_data["comment"]),
                "expertise" => array("integer", $form_data["exp"])),
                array("id" => array("integer", $id)));

        $res = $ilDB->queryF(
            "SELECT question_id FROM rep_robj_xrev_revi"
            . " WHERE review_obj=%s AND id=%s",
            array("integer", "integer"),
            array($this->getID(), $id)
        );
        $q_id = $ilDB->fetchObject($res)->question_id;
        $this->checkPhaseProgress($q_id);
    }

    /*
     * Copy all review entries belonging to a question into the history table
     *
     * @param       integer         $q_id           $question_id
     */
    public function copyReviewsToHistory($q_id) {
        global $ilDB;

        $hist_res = $ilDB->queryF(
            "SELECT * FROM rep_robj_xrev_revi"
            . " WHERE question_id=%s AND state=%s",
            array("integer", "integer"),
            array($q_id, 1)
        );
        while ($review = $ilDB->fetchAssoc($hist_res)) {
            $ilDB->insert(
                "rep_robj_xrev_hist",
                array(
                    "timestamp" => array("integer", $review["timestamp"]),
                    "desc_corr" => array("integer", $review["desc_corr"]),
                    "desc_relv" => array("integer", $review["desc_relv"]),
                    "desc_expr" => array("integer", $review["desc_expr"]),
                    "quest_corr" => array("integer", $review["quest_corr"]),
                    "quest_relv" => array("integer", $review["quest_relv"]),
                    "quest_expr" => array("integer", $review["quest_expr"]),
                    "answ_corr" => array("integer", $review["answ_corr"]),
                    "answ_relv" => array("integer", $review["answ_relv"]),
                    "answ_expr" => array("integer", $review["answ_expr"]),
                    "taxonomy" => array("integer", $review["taxonomy"]),
                    "knowledge_dimension" => array("integer", $review["knowledge_dimension"]),
                    "rating" => array("integer", $review["rating"]),
                    "eval_comment" => array("clob", $review["eval_comment"]),
                    "expertise" => array("integer", $review["expertise"]),
                    "question_id" => array("integer", $review["question_id"]),
                    "id" => array("integer", $review["id"]),
                    "reviewer" => array("integer", $review["reviewer"])
                )
            );
        }
    }

    /*
     * Check if all reviews for a question are completed and evaluate them
     *
     * @param       integer         $q_id           question id
     */
    public function checkPhaseProgress($q_id) {
        global $ilDB;

        $res = $ilDB->queryF(
            "SELECT rating, state FROM rep_robj_xrev_revi"
            . " WHERE review_obj=%s AND question_id=%s",
            array("integer", "integer"),
            array($this->getID(), $q_id)
        );
        $reviews = array();
        while ($review = $ilDB->fetchObject($res)) {
            $reviews[] = $review;
        }
        $accepted = true;
        $refused = true;
        foreach ($reviews as $review) {
            if ($review->state == 0) {
                return;
            }
            $accepted &= $review->rating == 1;
            $refused &= $review->rating == 3;
        }
        if ($accepted) {
            $this->proceedToNextPhase($q_id);
        }
        else if ($refused) {
            $this->markQuestionAsRefused($q_id);
        }
        else /* to edit */ {
            $this->notifyAuthorAboutNeedToEdit($q_id);
            $ilDB->update(
                "rep_robj_xrev_quest",
                array("state" => array("integer", 0)),
                array(
                    "question_id" => array("integer", $q_id),
                    "review_obj" => array("integer", $this->getID())
                )
            );
        }
    }

    /*
     * Load all members of a group
     *
     * @return       array           $members       ids, names of the members
     */
    public function loadMembers() {
        global $ilDB;

        $res = $ilDB->queryF("SELECT usr_data.usr_id AS id, firstname, lastname FROM usr_data ".
                "INNER JOIN rbac_ua ON rbac_ua.usr_id=usr_data.usr_id ".
                "INNER JOIN object_data ON object_data.obj_id=rbac_ua.rol_id ".
                "WHERE object_data.title='il_grp_admin_%s' OR object_data.title='il_grp_member_%s'",
                array("integer", "integer"),
                array($this->getGroupId(), $this->getGroupId()));
        $members = array();
        while ($member = $ilDB->fetchObject($res))
            $members[] = $member;
        return $members;
    }

    /*
     * Load all review cycle phases
     *
     * @return      array           $phases         'phases' table row objects
     */
    public function loadPhases() {
        global $ilDB;

        $res = $ilDB->queryF("SELECT phase, nr_reviewers "
                . "FROM rep_robj_xrev_phases "
                . "WHERE review_obj = %s",
                array("integer"),
                array($this->getId()));

        $phases = array();
        while ($phase = $ilDB->fetchObject($res)) {
            $phases[] = $phase;
        }
        return $phases;
    }

    public function proceedToNextPhase($q_id) {
        global $ilDB;

        $max_phase = 0;
        foreach ($this->loadPhases() as $phase) {
            if ($phase->phase > $max_phase) {
                $max_phase = $phase->phase;
            }
        }
        $current_phase = $this->getCurrentPhase($q_id)->phase;

        for ($step = 1; $step + $current_phase <= $max_phase; $step++) {
            foreach ($this->loadPhases() as $phase) {
                if ($current_phase + $step == $phase->phase
                    && $phase->nr_reviewers > 0) {
                    $ilDB->update(
                        "rep_robj_xrev_quest",
                        array(
                            "phase" => array("integer", $current_phase + $step),
                            "state" => array("integer", 1)
                        ),
                        array(
                            "question_id" => array("integer", $q_id),
                            "review_obj" => array("integer", $this->getID())
                        )
                    );
                    $this->copyReviewsToHistory($q_id);
                    $this->clearAllocatedReviews($q_id);
                    $this->allocateReviews($q_id);
                    return;
                }
            }
        }
        $this->finishQuestion($q_id);
    }

    /*
     * Delete all review form objects allocated to a question
     *
     * @param       integer         $q_id           question id
     */
    public function clearAllocatedReviews($q_id) {
        global $ilDB;

        $ilDB->manipulateF(
            "DELETE FROM rep_robj_xrev_revi"
            . " WHERE review_obj=%s AND question_id=%s",
            array("integer", "integer"),
            array($this->getID(), $q_id)
        );
    }

    /*
     * Get the current cycle phase of a question
     *
     * @param       integer         $q_id           question id
     *
     * @return      object          $phase          phase object from record
     */
    public function getCurrentPhase($q_id) {
        global $ilDB;

        $res = $ilDB->queryF(
            "SELECT rep_robj_xrev_phases.phase,"
            . " rep_robj_xrev_phases.nr_reviewers"
            . " FROM rep_robj_xrev_phases"
            . " INNER JOIN rep_robj_xrev_quest"
            . " ON rep_robj_xrev_quest.phase=rep_robj_xrev_phases.phase"
            . " WHERE rep_robj_xrev_phases.review_obj=%s"
            . " AND rep_robj_xrev_quest.question_id=%s",
            array("integer", "integer"),
            array($this->getID(), $q_id)
        );
        return $ilDB->fetchObject($res);
    }

    /*
     * Load all questions that currently have no reviewer allocated to them
     *
     * @return       array           $questions              the question loaded by this function as an associative array
     */
    public function  loadUnallocatedQuestions() {
        global $ilDB, $ilUser;

        $qpl = $ilDB->queryF("SELECT qpl_questions.question_id AS id, title, owner FROM qpl_questions ".
                "INNER JOIN rep_robj_xrev_quest ON rep_robj_xrev_quest.question_id=qpl_questions.question_id ".
                "WHERE qpl_questions.original_id IS NULL AND ".
                "rep_robj_xrev_quest.state=0 AND rep_robj_xrev_quest.review_obj=%s",
                array("integer"),
                array($this->getId()));
        $questions = array();
        while ($question = $ilDB->fetchAssoc($qpl))
            $questions[] = $question;
        return $questions;
    }

    /*
     * Load all reviewers allocated to the author of a question in a certain
     * phase ordered by the amount of reviews they currently have to complete
     *
     * @param       integer         $q_id           question id
     * @param       integer         $phase_nr       cycle phase
     *
     * @return      array           $reviewer_pool  objects of allocated
     *                                              reviewers
     */
    public function loadAllocatedReviewers($q_id, $phase_nr) {
        global $ilDB;

        $res = $ilDB->queryF(
            "SELECT qpl_questions.owner FROM qpl_questions"
            . " WHERE qpl_questions.question_id=%s",
            array("integer"),
            array($q_id)
        );
        $author = $ilDB->fetchObject($res)->owner;

        $res = $ilDB->queryF(
            "SELECT rep_robj_xrev_alloc.reviewer,"
            . " COUNT(DISTINCT rep_robj_xrev_revi.id)"
            . " FROM rep_robj_xrev_alloc"
            . " LEFT JOIN rep_robj_xrev_revi"
            . " ON rep_robj_xrev_revi.reviewer=rep_robj_xrev_alloc.reviewer"
            . " WHERE rep_robj_xrev_alloc.review_obj=%s"
            . " AND rep_robj_xrev_alloc.author=%s"
            . " AND rep_robj_xrev_alloc.phase=%s"
            . " GROUP BY rep_robj_xrev_alloc.reviewer"
            . " ORDER BY COUNT(DISTINCT rep_robj_xrev_revi.id)",
            array("integer", "integer", "integer"),
            array($this->getID(), $author, $phase_nr)
        );
        $reviewer_pool = array();
        while ($reviewer = $ilDB->fetchObject($res)) {
            $reviewer_pool[] = $reviewer;
        }
        return $reviewer_pool;
    }

    /*
     * Create review form objects for a question, one for each allocated
     * reviewer
     *
     * @param       integer         $q_id           question id
     */
    public function allocateReviews($q_id) {
        global $ilDB;

        $current_phase = $this->getCurrentPhase($q_id);
        $reviewer_pool = $this->loadAllocatedReviewers(
            $q_id,
            $current_phase->phase
        );
        $max_reviewers = $current_phase->nr_reviewers;
        foreach ($reviewer_pool as $reviewer) {
            if (--$max_reviewers < 0) {
                break;
            }
            $ilDB->insert(
                "rep_robj_xrev_revi",
                array(
                    "id" => array(
                        "integer",
                        $ilDB->nextID("rep_robj_xrev_revi")
                    ),
                    "timestamp" => array("integer", time()),
                    "reviewer" => array("integer", $reviewer->reviewer),
                    "question_id" => array("integer", $q_id),
                    "state" => array("integer", 0),
                    "desc_corr" => array("integer", 0),
                    "desc_relv" => array("integer", 0),
                    "desc_expr" => array("integer", 0),
                    "quest_corr" => array("integer", 0),
                    "quest_relv" => array("integer", 0),
                    "quest_expr" => array("integer", 0),
                    "answ_corr" => array("integer", 0),
                    "answ_relv" => array("integer", 0),
                    "answ_expr" => array("integer", 0),
                    "taxonomy" => array("integer", 0),
                    "knowledge_dimension" => array("integer", 0),
                    "rating" => array("integer", 0),
                    "eval_comment" => array("clob", ''),
                    "expertise" => array("integer", 0),
                    "review_obj" => array("integer", $this->getId())
                )
            );
            $this->notifyReviewerAboutAllocation($reviewer->reviewer);
        }
    }

    /*
     * Save matrix input as author - reviewer allocation
     *
     * @param       array       $alloc_matrix       black magic
     */
    public function allocateReviewers($alloc_matrix) {
        global $ilDB;

        $ilDB->manipulateF("DELETE FROM rep_robj_xrev_alloc " .
                    "WHERE review_obj=%s",
                    array("integer"),
                    array($this->getId()));

        foreach ($alloc_matrix as $row) {
            foreach ($row["reviewers"] as $reviewer_id => $checked) {
                if (!$checked) {
                    continue;
                }
                $ilDB->insert("rep_robj_xrev_alloc", array(
                        "phase" => array("integer", explode("_", $reviewer_id)[1]),
                        "reviewer" => array("integer", explode("_", $reviewer_id)[3]),
                        "author" => array("integer", $row["q_id"]),
                        "review_obj" => array("integer", $this->getId())));
            }
        }
    }

    /*
     * Change the number of reviewers for a cycle phase
     *
     * @param       integer     $phase              phase to be changed
     * @param       integer     $nr_reviewers       new number of reviewers
     */
    public function updateCyclePhase($phase, $nr_reviewers) {
        global $ilDB;

        $ilDB->update("rep_robj_xrev_phases",
                array("nr_reviewers" => array("integer", $nr_reviewers)),
                array("phase" => array("integer", $phase),
                "review_obj" => array("integer", $this->getID())));
    }

    /*
     * Load the whole review cycle allocation
     *
     * @return      array       $allocation         postvars and their values
     */
    public function loadReviewerAllocation() {
        global $ilDB;

        $allocation = array();

        $res = $ilDB->queryF("SELECT phase, reviewer, author "
                . "FROM rep_robj_xrev_alloc "
                . "WHERE review_obj=%s",
                array("integer"),
                array($this->getID()));

        while ($alloc = $ilDB->fetchObject($res)) {
            $allocation[sprintf("id_%s_%s_%s", $alloc->phase,
                    $alloc->author, $alloc->reviewer)]
                = true;
        }

        $res = $ilDB->queryF("SELECT nr_reviewers, phase "
                . "FROM rep_robj_xrev_phases "
                . "WHERE review_obj=%s",
                array("integer"),
                array($this->getID()));

        while ($phase = $ilDB->fetchObject($res)) {
            $allocation[sprintf("nr_%s", $phase->phase)] = $phase->nr_reviewers;
        }

        return $allocation;
    }

    /*
     * Remove a question from the review cycle by marking it as finished
     *
     * @param       integer         $q_id           question id
     */
    public function finishQuestion($q_id) {
        global $ilDB;
        $ilDB->update(
            "rep_robj_xrev_quest",
            array("state" => array("integer", 2)),
            array(
                "question_id" => array("integer", $q_id),
                "review_obj" => array("integer", $this->getId())
            )
        );
        $this->copyReviewsToHistory($q_id);
        $this->clearAllocatedReviews($q_id);
        $this->copyQuestionToReviewedPool($q_id);
        $this->notifyAuthorsAboutAcceptance(array($q_id));
    }

    /*
     * Create a new question pool that stores reviewed questions
     *
     * @param   string      $old_id     id of the corresponding question pool
     * @param   string      $title      title of the new question pool
     *
     * @return  integer     $new_id     object id of the new question pool
     */
    public function createPoolForReviewedQuestions($old_id, $title) {
        global $ilDB;
        include_once 'Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
        include_once 'Services/Object/classes/class.ilObjectActivation.php';

        $res = $ilDB->queryF(
            "SELECT ref_id FROM object_reference WHERE obj_id = %s",
            array("integer"),
            array($old_id)
        );
        $id = -1;
        while ($ref_pool = $ilDB->fetchObject($res)) {
            $pool = new ilObjQuestionPool($ref_id);
            //$pool->create();
            ///*
            $new_pool = $pool->cloneObject($this->getGroupID());
            foreach ($new_pool->getAllQuestions() as $question_id) {
                $new_pool->deleteQuestion($question_id);
            }
            //$ilDB->nextID("qpl_questions");
            $new_pool->setTitle($title);
            $new_pool->update();
            ilObjectActivation::getItem($new_pool->getRefID());
            $id = $new_pool->getID();
            //*/
        }
        return $id;
    }

    public function checkTable($id) {
        global $ilDB;
        $res = $ilDB->queryF(
            "SELECT * FROM qpl_rev_qst WHERE question_id = %s",
            array("integer"),
            array($id)
        );
        return ($res->numRows() > 0) ? "true" . $id : "real";
    }

    public function fooTestBar($q_id) {
        return $this->copyQuestionToReviewedPool($q_id);

    }

    /*
     * Copy a question that has finished the review cycle to a special question
     * pool for use in tests
     *
     * @param   integer     $q_id       question id
     */
    public function copyQuestionToReviewedPool($q_id) {
        /*
        include_once "Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
        $new_pool = new ilObjQuestionPool();
        $new_pool->setTitle("foo");
        $new_pool->create();
        $new_pool->save();
         */
        $debugs = array();

        global $ilDB;

        $res = $ilDB->queryF(
            "SELECT object_data.title, object_data.obj_id
             FROM object_data
             INNER JOIN qpl_questions
             ON qpl_questions.obj_fi = object_data.obj_id
             WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($q_id)
        );

        $fetch_pool = $ilDB->fetchObject($res);
        $old_id = $fetch_pool->obj_id;
        $target_pool = $fetch_pool->title . " [Reviewed]";

        $res = $ilDB->queryF(
            "SELECT obj_id FROM object_data
             WHERE title = %s AND type = 'qpl'",
            array("text"),
            array($target_pool)
        );

        if (($qpl = $ilDB->fetchObject($res)) != null) {
            $qpl_id = $qpl->obj_id;
        } else {
            $qpl_id = $this->createPoolForReviewedQuestions($old_id, $target_pool);
        }

        /*
        $new_id = $ilDB->nextID("qpl_questions");

        $res = $ilDB->queryF(
            "SELECT * FROM qpl_questions WHERE question_id = %s",
            array("integer"),
            array($q_id)
        );
        $qst = $ilDB->fetchObject($res);
        $ilDB->insert(
            "qpl_questions",
            array(
                "question_id" => array("integer", $new_id),
                "question_type_fi" => array("integer", $qst->question_type_fi),
                "obj_fi" => array("integer", $qpl_id),
                "title" => array("text", $qst->title),
                "description" => array("text", $qst->description),
                "author" => array("text", $qst->author),
                "owner" => array("integer", $qst->owner),
                "working_time" => array("text", $qst->working_time),
                "points" => array("double", $qst->points),
                "complete" => array("text", $qst->complete),
                "original_id" => array("integer", $qst->original_id),
                "tstamp" => array("integer", $qst->tstamp),
                "created" => array("integer", $qst->created),
                "nr_of_tries" => array("integer", $qst->nr_of_tries),
                "question_text" => array("clob", $qst->question_text),
                "add_cont_edit_mode" => array("text", $qst->add_cont_edit_mode),
                "external_id" => array("text", $qst->external_id)
            )
        );
         */
        $question = assQuestion::_instantiateQuestion($q_id);
        $new_id = $question->copyObject($qpl_id);
        $debugs["after_copy"] = $this->checkTable($new_id);
        for ($i = 1; $i < 1000000; $i+=1) {
        }

        $res = $ilDB->queryF(
            "SELECT * FROM qpl_rev_qst WHERE question_id = %s",
            array("integer"),
            array($q_id)
        );
        $qst = $ilDB->fetchObject($res);
        $ilDB->insert(
            "qpl_rev_qst",
            array(
                "question_id" => array("integer", $new_id),
                "taxonomy" => array("integer", $qst->taxonomy),
                "knowledge_dimension" => array("integer", $qst->knowledge_dimension),
                "learning_outcome" => array("clob", $qst->learning_outcome),
                "topic" => array("text", $qst->topic),
            )
        );

        $res = $ilDB->queryF(
            "SELECT * FROM rep_robj_xrev_quest WHERE question_id = %s",
            array("integer"),
            array($q_id)
        );
        $qst = $ilDB->fetchObject($res);
        $ilDB->insert(
            "rep_robj_xrev_quest",
            array(
                "question_id" => array("integer", $new_id),
                "id" => array("integer", $ilDB->nextID("rep_robj_xrev_quest")),
                "timestamp" => array("integer", $qst->timestamp),
                "state" => array("integer", $qst->state),
                "phase" => array("integer", $qst->phase),
                "review_obj" => array("integer", $qst->review_obj),
            )
        );
        $debugs["after_insert"] = $this->checkTable($new_id);
        return $debugs;
    }

    /*
     * Remove a question from the review cycle by marking it as refused
     *
     * @param       integer         $q_id           question id
     */
    public function markQuestionAsRefused($q_id) {
        global $ilDB;
        $ilDB->update(
            "rep_robj_xrev_quest",
            array("state" => array("integer", -1)),
            array(
                "question_id" => array("integer", $q_id),
                "review_obj" => array("integer", $this->getId())
            )
        );
        $this->copyReviewsToHistory($q_id);
        $this->clearAllocatedReviews($q_id);
        $this->notifyAuthorsAboutRefusal(array($q_id));
    }

    /*
     * Load metadata of a question
     *
     * @param                int             $q_id                   question id
     *
     * @return       array           $question       $question metadata as an associative array
     */
    public function loadQuestionMetaData($q_id) {
        global $ilDB;
        $req = $ilDB->queryF(
            "SELECT qpl_questions.title, qpl_rev_qst.learning_outcome, usr_data.firstname, usr_data.lastname ".
            "FROM qpl_questions ".
            "INNER JOIN usr_data ON usr_data.usr_id=qpl_questions.owner ".
            "INNER JOIN qpl_rev_qst ON qpl_rev_qst.question_id=qpl_questions.question_id ".
            "WHERE qpl_questions.question_id=%s",
            array("integer"),
            array($q_id)
        );
        return $ilDB->fetchAssoc($req);
    }

    /*
     * Load taxonomy and knowledge dimension of a question
     *
     * @param                int             $q_id                   question id
     *
     * @return       array           $question       $question taxonomy data as an associative array
     */
    public function loadQuestionTaxonomyData($q_id) {
        global $ilDB;
        $req = $ilDB->queryF("SELECT qpl_rev_qst.taxonomy, qpl_rev_qst.knowledge_dimension ".
                "FROM qpl_rev_qst ".
                "WHERE qpl_rev_qst.question_id=%s",
                array("integer"),
                array($q_id)
        );
        return $ilDB->fetchAssoc($req);
    }

    /*
     * Prepare message output to inform a reviewer about
     * their allocation to a certain question
     *
     * @param       integer         $reviewer           reviewer id
     */
    public function notifyReviewerAboutAllocation($reviewer) {
        $this->performNotification(array($reviewer), "msg_review_requested");
    }

    /*
     * Prepare message output to inform authors about
     * the acceptance of a certain question by the reviewers
     *
     * @param                array                   $question_ids                   array of the ids of the accepted question
     */
    public function notifyAuthorsAboutAcceptance($question_ids) {
        global $ilDB;
        $receivers = array();
        foreach ($question_ids as $id)
            $receivers[] = $ilDB->fetchAssoc($ilDB->queryF("SELECT owner FROM qpl_questions WHERE question_id=%s",
                    array("integer"), array($id)))["owner"];
        $this->performNotification($receivers, "msg_question_accepted");
    }

    /*
     * Prepare message output to inform authors about
     * the refusal of a certain question by the reviewers
     *
     * @param                array                   $question_ids                   array of the ids of the accepted question
     */
    public function notifyAuthorsAboutRefusal($question_ids) {
        global $ilDB;
        $receivers = array();
        foreach ($question_ids as $id)
            $receivers[] = $ilDB->fetchAssoc($ilDB->queryF("SELECT owner FROM qpl_questions WHERE question_id=%s",
                    array("integer"), array($id)))["owner"];
        $this->performNotification($receivers, "msg_question_refused");
    }

    /*
     * Prepare message output to inform an author that the reviewers want him
     * to edit one of his questions
     *
     * @param       integer         $q_id           $question_id
     */
    public function notifyAuthorAboutNeedToEdit($q_id) {
        global $ilDB;

        $res = $ilDB->queryF(
            "SELECT owner FROM qpl_questions WHERE question_id=%s",
            array("integer"),
            array($q_id)
        );
        $receiver = $ilDB->fetchAssoc($res)["owner"];
        $this->performNotification(array($receiver), "msg_question_rework");
    }

    /*
     * Prepare message output to inform reviewers about
     * a change of a certain question they have to review
     *
     * @param                array                   $question                       question data as an associative array
     */
    public function notifyReviewersAboutChange($question) {
        global $ilDB;
        $res = $ilDB->queryF("SELECT reviewer FROM rep_robj_xrev_revi ".
                "WHERE review_obj=%s AND question_id=%s",
                array("integer", "integer"),
                array($this->getId(), $question["question_id"])
        );
        $receivers = array();
        while ($receiver = $ilDB->fetchAssoc($res))
            $receivers[] = $receiver["reviewer"];
        $this->performNotification($receivers, "msg_question_edited");
    }

    /*
     * Prepare message output to inform the group´s admins about
     * the creation of a new question
     *
     * @param                array                   $question                       question data as an associative array
     */
    public function notifyAdminsAboutNewQuestion($question) {
        global $ilDB;
        $res = $ilDB->queryF("SELECT usr_id FROM rbac_ua ".
                "INNER JOIN object_data ON object_data.obj_id=rbac_ua.rol_id ".
                "WHERE object_data.title='il_grp_admin_%s'",
                array("integer"),
                array($this->getGroupId())
        );
        $receivers = array();
        while ($receiver = $ilDB->fetchAssoc($res))
            $receivers[] = $receiver["usr_id"];
        $this->performNotification($receivers, "msg_question_created");
    }

    /*
     * Prepare message output to inform reviewers about
     * the deletion of a question they had to review
     *
     * @param                array                   $question                       question data as an associative array
     */
    public function notifyReviewersAboutDeletion($question) {
        global $ilDB;
        $res = $ilDB->queryF("SELECT reviewer FROM rep_robj_xrev_revi ".
                "WHERE review_obj=%s AND question_id=%s",
                array("integer", "integer"),
                array($this->getId(), $question["question_id"])
        );
        $receivers = array();
        while ($receiver = $ilDB->fetchAssoc($res))
            $receivers[] = $receiver["reviewer"];
        $this->performNotification($receivers, "msg_question_deleted");
    }

    /*
     * Created and send an ILIAS message based on data prepared by this object´s notify... methods
     *
     * @param                array                   $receivers                      array of user ids corresponding to the receivers of the message
     * @param                string          $message_type           the kind of information to be sent
     */
    private function performNotification($receivers, $message_type) {
        include_once "./Services/Notification/classes/class.ilSystemNotification.php";
        $ntf = new ilSystemNotification();
        $ntf->setObjId($this->getId());
        $ntf->setLangModules(array("rep_robj_xrev"));
        $ntf->setSubjectLangId("rep_robj_xrev_".$message_type."_subj");
        $ntf->setIntroductionLangId("rep_robj_xrev_".$message_type."_intr");
        $ntf->setGotoLangId("rep_robj_xrev_obj_xrev");

        $ntf->sendMail($receivers);
    }

    /*
     * Load all questions that are not reviewable
     *
     * @return   array       $questions      associative array of question data
     */
    public function loadNonReviewableQuestions() {
        global $ilDB, $ilUser;

        $res = $ilDB->queryF(
            "SELECT question_id, type_tag, title, author FROM qpl_questions"
            . " INNER JOIN object_reference ON object_reference.obj_id=qpl_questions.obj_fi"
            . " INNER JOIN crs_items ON crs_items.obj_id=object_reference.ref_id"
            . " INNER JOIN qpl_qst_type ON qpl_qst_type.question_type_id=qpl_questions.question_type_fi"
            . " WHERE crs_items.parent_id=%s AND qpl_questions.owner=%s",
            array("integer", "integer"),
            array($this->getGroupId(), $ilUser->getID())
        );

        $questions = array();
        while ($question = $ilDB->fetchAssoc($res))
            $questions[] = $question;
        foreach ($questions as $index => $question) {
            if (strpos($question["type_tag"], "assReviewable") !== FALSE)
                unset($questions[$index]);
        }
        return $questions;
    }

    /*
     * Update a former non reviewable question
     *
     * @param   int         $id         id of the question to update
     * @param   int         $tax        taxonomy -""-
     * @param   int         $knowd      knowledge dimension -""-
     * @param   int         $loutc      learning outcome -""-
     * @param   int         $topic      topic -""-
     */
    public function saveQuestionConversion($id, $tax, $knowd, $loutc, $topic) {
        global $ilDB;

        $res = $ilDB->queryF("SELECT type_tag FROM qpl_qst_type " .
                             "INNER JOIN qpl_questions ON qpl_questions.question_type_fi=qpl_qst_type.question_type_id " .
                             "WHERE question_id=%s",
                             array("integer"),
                             array($id)
               );
        $old_type = $ilDB->fetchAssoc($res)["type_tag"];
        $new_type = sprintf("assReviewable%s", substr($old_type, 3));
        $res = $ilDB->queryF("SELECT question_type_id FROM qpl_qst_type " .
                             "WHERE type_tag=%s",
                             array("text"),
                             array($new_type)
               );
        $type_id = $ilDB->fetchAssoc($res)["question_type_id"];
        $res = $ilDB->queryF(
            "SELECT object_data.title"
            . " FROM object_data"
            . " INNER JOIN qpl_questions"
            . " ON object_data.obj_id = qpl_questions.obj_fi"
            . " WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($id)
        );
        $pool = $ilDB->fetchAssoc($res)["title"];
        $ilDB->update("qpl_questions",
            array(
                "question_type_fi" => array("integer", $type_id),
                "description" => array("text", $pool . "/" . $topic)
            ),
            array("question_id" => array("integer", $id))
        );
        $ilDB->insert("qpl_rev_qst",
            array("question_id" => array("integer", $id),
                "taxonomy" => array("integer", $tax),
                "knowledge_dimension" => array("integer", $knowd),
                "learning_outcome" => array("clob", $loutc),
                "topic" => array("text", $topic)
            )
        );
    }

    /*
     * Get a review plugin specific enumeration
     *
     * @param       string      $identifier     name of the enumeration
     *
     * @return      array       $enum           enum entry id => term
     */
    public static function getEnum($identifier) {
        global $ilDB, $lng;
        switch ($identifier) {
            case "taxonomy": $table = "taxon"; break;
            case "knowledge dimension": $table = "knowd"; break;
            case "evaluation": $table = "eval"; break;
            case "rating": $table = "rate"; break;
            case "expertise": $table = "expert"; break;
            default: return null;
        }
        $res = $ilDB->query("SELECT * FROM rep_robj_xrev_$table");
        $enum = array();
        while ($entry = $ilDB->fetchAssoc($res))
            $enum[$entry["id"]] = $lng->txt("rep_robj_xrev_".$entry["term"]);
        return $enum;
    }

    public function addPhaseToCycle() {
        global $ilDB;

        $res = $ilDB->queryF("SELECT MAX(phase) AS maxphase "
                . "FROM rep_robj_xrev_phases "
                . "WHERE review_obj=%s",
                array("integer"),
                array($this->getID()));

        $maxphase = $ilDB->fetchAssoc($res)["maxphase"];
        $ilDB->insert("rep_robj_xrev_phases",
            array("phase" => array("integer", $maxphase + 1),
                "review_obj" => array("integer", $this->getID()),
                "nr_reviewers" => array("integer", 0)));
    }

    public function removePhaseFromCycle() {
        global $ilDB;

        $res = $ilDB->queryF("SELECT MAX(phase) AS maxphase "
                . "FROM rep_robj_xrev_phases "
                . "WHERE review_obj=%s",
                array("integer"),
                array($this->getID()));

        $maxphase = $ilDB->fetchAssoc($res)["maxphase"];
        $ilDB->manipulateF("DELETE FROM rep_robj_xrev_phases "
                . "WHERE phase=%s AND review_obj=%s",
                array("integer", "integer"),
                array($maxphase, $this->getID()));

        $ilDB->manipulateF("DELETE FROM rep_robj_xrev_alloc "
                . "WHERE phase=%s AND review_obj=%s",
                array("integer", "integer"),
                array($maxphase, $this->getID()));
    }

    function getQuestionTypesWithNoReviewablePlugin() {
            global $ilDB;

            $return_values = array();

            $not_reviewable_types = array();
            $result = $ilDB->query('SELECT type_tag FROM qpl_qst_type WHERE type_tag NOT LIKE "assReviewable%"');
            while ( $data = $ilDB->fetchAssoc( $result ) ) {
                array_push($not_reviewable_types, $data['type_tag']);
            }

            $reviewable_types = array();
            $result = $ilDB->query('SELECT name FROM il_plugin WHERE name LIKE "assReviewable%"');
            while ( $data = $ilDB->fetchAssoc( $result ) ) {
                array_push($reviewable_types, $data['name']);
            }

            foreach ( $not_reviewable_types as $nr_type ) {
                if ( !in_array( 'assReviewable'. substr($nr_type, 3), $reviewable_types ) ) {
                    array_push( $return_values, $nr_type );
                }
            }
            return $return_values;
        }
}
?>
