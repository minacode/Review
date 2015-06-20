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
    public $review_db;

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
    function doClone($target_id, $copy_id, $new_obj) {
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
     * Synchronize the plugin question database with the ILIAS question pools
     */
    private function syncQuestionDB() {
        global $ilDB;

        $qpl_questions = $this->getAllCourseQuestions();
        $plg_questions = $this->review_db->getCycleQuestions(array());

        $cmp_qst = function($question_a, $question_b) {
            if ($question_a->getID() > $question_b->getID()) {
                return 1;
            }
            if ($question_a->getID() < $question_b->getID()) {
                return -1;
            }
            return 0;
        };
        $new_questions = array_udiff($qpl_questions, $plg_questions, $cmp_qst);
        $del_questions = array_udiff($plg_questions, $qpl_questions, $cmp_qst);

        foreach ($new_questions as $new_question) {
            $new_cycle_question = new ilCycleQuestion(
                $ilDB,
                $this->review_db,
                $this->getID(),
                $new_question->getID(),
                0,
                0,
                self::getQuestionTimestamp($new_question->getID()),
                $new_question,
                $ilDB->nextID("rep_robj_xrev_quest")
            );
            $new_cycle_question->storeToDB();
            $this->proceedToNextPhase($new_cycle_question);
            $this->notifyAdminsAboutNewQuestion($new_cycle_question);
        }

        foreach ($del_questions as $del_question) {
            $this->notifyReviewersAboutDeletion(
                // TODO change notify function to use the object
                array($del_question->getID())
            );
            $reviews = $this->review_db->getReviewForms(
                array("question_id" => $del_question->getID())
            );
            foreach ($reviews as $review) {
                $review->copyToHistory();
                $review->deleteFromDB();
            }
            $del_question->deleteFromDB();
        }

        $qpl_questions = $this->getAllCourseQuestions();
        $plg_questions = $this->review_db->getCycleQuestions(array());

        foreach ($qpl_questions as $qpl_question) {
            foreach ($plg_questions as $plg_question) {
                if (
                    $qpl_question->getID()
                    == $plg_question->getID()
                    && self::getQuestionTimestamp($qpl_question->getID())
                    > $plg_question->getTimestamp()
                ) {
                    $plg_question->setTimestamp(
                        self::getQuestionTimestamp($qpl_question->getID())
                    );
                    $plg_question->storeToDB();
                    if ($plg_question->getState() == 0) {
                        $this->proceedToNextPhase($plg_question);
                    }
                    break;
                }
            }
        }
    }

    /*
     * Load all questions in the review cycle created by the current user
     *
     * @return  array       $questions          ilCycleQuestion objects
     */
    public function loadQuestionsByUser() {
        global $ilUser;

        $questions = $this->review_db->getCycleQuestions(
            array("owner" => $ilUser->getID(), "state" => 1)
        );
        return $questions;
    }

    /*
     * Load all reviews the current user has to complete
     *
     * @return  array       $reviews            ilReviewFormObjects
     */
    public function loadReviewsByUser() {
        global $ilUser;

        $reviews = $this->review_db->getReviewForms(
            array("reviewer" => $ilUser->getID())
        );
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
            array("id" => $question_id)
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
     * Check if all reviews for a question are completed and evaluate them
     *
     * @param       ilCycleQuestion     $question       question
     */
    public function checkPhaseProgress($question) {
        $reviews = $this->review_db->getReviewForms(
            array("question_id" => $question->getID())
        );
        $accepted = true;
        $refused = true;
        foreach ($reviews as $review) {
            if ($review->getState() == 0) {
                return;
            }
            $accepted &= $review->getRating() == 1;
            $refused &= $review->getRating() == 3;
        }
        if ($accepted) {
            $this->proceedToNextPhase($question);
        }
        else if ($refused) {
            $this->markQuestionAsRefused($question);
        }
        else /* to edit */ {
            // TODO change notify function to use the object
            $this->notifyAuthorAboutNeedToEdit($question->getID());
            $question->setState(0);
            $question->storeToDB();
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

    /*
     * Move a question to the next phase in the review cycle or finish it, if
     * it passed the last phase
     *
     * @param   ilCycleQuestion     $question       question
     */
    public function proceedToNextPhase($question) {
        for ($next_phase = $question->getPhase() + 1; ; $next_phase++) {
            $phase = $this->review_db->getCyclePhases(
                array("phase_nr" => $next_phase)
            );
            $allocation = $this->review_db->getReviewerAllocations(
                array(
                    "phase_nr" => $next_phase,
                    "author" => $question->getOwner()
                )
            );
            if (count($phases) != 1 || count($allocation) != 1) {
                break;
            }
            if (reset($phase)->getNumReviewers > 0
                && reset($phase)->getNumReviewers
                >= count(reset($allocation)->getReviewers())
            ) {
                $question->setPhase($next_phase);
                $question->setState(1);
                $question->storeToDB();
                $reviews = $this->review_db->getReviewForms(
                    array("question_id" => $question->getID())
                );
                foreach ($reviews as $review) {
                    $review->copyToHistory();
                    $review->delete();
                }
                $this->allocateReviews($question);
            }
        }
        $this->finishQuestion($question);
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
     * @param   ilCycleQuestion     $question       question
     */
    public function allocateReviews($question) {
        global $ilDB;

        $num_reviewers = reset($this->review_db->getCyclePhases(
            array("phase_nr" => $question->getPhase())
        ))->getNumReviewers();
        $allocation = reset($this->reviewer_db->getReviewerAllocations(
            array(
                "phase_nr" => $question->getPhase(),
                "author" => $question->getOwner()
            )
        ))->getReviewers();
        shuffle($allocation);
        while ($num_reviewers-- > 0) {
            $reviewer = array_shift($allocation);
            $review_form = new ilReviewForm(
                $ilDB,
                $this->review_db,
                $ilDB->nextID("rep_robj_xrev_revi"),
                $this->getID(),
                $question->getID(),
                0,
                $reviewer,
                time(),
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                "",
                0,
                0
            );
            $review_form->storeToDB();
            $this->notifyReviewerAboutAllocation($reviewer);
        }
    }

    /*
     * Save matrix input as author - reviewer allocation
     *
     * @param   array       $allocation         $phase => $author => $reviewer
     */
    public function allocateReviewers($allocation) {
        global $ilDB;

        /*
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
         */
        foreach ($this->review_db->getReviewerAllocations(array()) as $alloc) {
            $alloc->deleteFromDB();
        }
        foreach ($allocation as $phase => $assignment) {
            foreach ($assignment as $author => $reviewers) {
                $matches = $this->review_db->getReviewerAllocations(
                    array("phase_nr" => $phase, "author" => $author)
                );
                if (count($matches) == 1) {
                    $alloc_obj = reset($matches);
                    $alloc_obj->setReviewers($reviewers);
                } else {
                    $alloc_obj = new ilReviewerAllocation(
                        $ilDB,
                        $this->review_db,
                        $phase,
                        $author,
                        $reviewers,
                        $this->getID()
                    );
                }
                $alloc_obj->storeToDB();
            }
        }
    }

    /*
     * Change the number of reviewers for a cycle phase
     *
     * @param       integer     $phase              phase to be changed
     * @param       integer     $num_reviewers      new number of reviewers
     */
    public function updateCyclePhase($phase, $num_reviewers) {
        $phase = reset(
            $this->review_db->getCyclePhases(array("phase_nr" => $phase))
        );
        $phase->setNumReviewers($num_reviewers);
        $phase->storeToDB();
    }

    /*
     * Load the whole review cycle allocation
     *
     * @return      array       $allocation     phases => authors => reviewers
     */
    public function loadReviewerAllocation() {
        /*
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
         */
        $group = ilGroupParticipants::_getInstanceByObjID(
            ilObject::_lookupObjectID($this->getGroupID())
        );
        $authors = $group->getParticipants();
        $allocation = array();
        foreach ($this->review_db->getCyclePhases(array()) as $phase) {
            $phase_alloc = array();
            foreach ($authors as $author) {
                $matches = $this->review_db->getReviewerAllocations(
                    array(
                        "author" => $author,
                        "phase_nr" => $phase->getPhaseNr()
                    )
                );
                if (count($matches) == 1) {
                    $phase_alloc[$author] = reset($matches)->getReviewers();
                } else {
                    $phase_alloc[$author] = array();
                }
            }
            $allocation[] = $phase_alloc;
        }
        return $allocation;
    }

    /*
     * Remove a question from the review cycle by marking it as finished
     *
     * @param   ilCycleQuestion     $question       question
     */
    public function finishQuestion($question) {
        $question->setState(2);
        $question->storeToDB();
        $reviews = $this->review_db->getReviewForms(
            array("question_id" => $question->getID())
        );
        foreach ($reviews as $review) {
            $review->copyToHistory();
            $review->delete();
        }
        $this->copyQuestionToReviewedPool($question);
        // TODO change the notify function to use the object
        $this->notifyAuthorsAboutAcceptance(array($question->getID()));
    }

    /*
     * Create a new question pool that stores reviewed questions
     *
     * @param   string      $old_id     id of the corresponding question pool
     *
     * @return  integer     $new_id     object id of the new question pool
     */
    public function createPoolForReviewedQuestions($old_id) {
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
            $new_pool = $pool->cloneObject($this->getGroupID());
            foreach ($new_pool->getAllQuestions() as $question_id) {
                $new_pool->deleteQuestion($question_id);
            }
            //$ilDB->nextID("qpl_questions");
            $new_pool->setTitle($title);
            $new_pool->setOnline(true);
            $group =
                ilGroupParticipants::_getInstanceByObjID(
                    ilObject::_lookupObjectID($this->getGroupID())
                );
            $new_pool->setOwner(reset($group->getAdmins()));
            $new_pool->update();
            ilObjectActivation::getItem($new_pool->getRefID());
            $new_id = $new_pool->getID();
            $ilDB->insert(
                "rep_robj_xrev_poolmap",
                array(
                    "question_pool" => array("integer", $old_id),
                    "pool_for_tests" => array("integer", $new_id),
                    "review_obj" => array("integer", $this->getID())
                )
            );
        }
        return $new_id;
    }

    /*
     * Copy a question that has finished the review cycle to a special question
     * pool for use in tests
     *
     * @param   ilCycleQuestion     $question       question
     */
    public function copyQuestionToReviewedPool($question) {
        global $ilDB;

        $target_pool = $ilDB->queryF(
            "SELECT pool_for_tests FROM rep_robj_xrev_poolmap "
            . "WHERE question_pool = %s AND review_obj = %s",
            array("integer", "integer"),
            array($question->getObjID(), $this->getID())
        );
        if ($target_pool->numRows() == 0) {
            $pool_id =
                $this->createPoolForReviewedQuestions($question->getObjID());
        } else {
            $pool_id = $ilDB->fetchObject($target_pool)->pool_for_tests;
        }

        $question = assQuestion::_instantiateQuestion($question->getID());
        $new_qst = $question->copyObject($pool_id);

        $cycle_question = new ilCycleQuestion(
            $ilDB,
            $this->review_db,
            $this->getID(),
            $new_qst,
            2,
            $question->getPhase(),
            time(),
            assQuestion::_instantiateQuestion($new_qst),
            $ilDB->nextID("rep_robj_xrev_quest")
        );
        $cycle_question->storeToDB();
        /* Maybe copy reviewed question data */
    }

    /*
     * Remove a question from the review cycle by marking it as refused
     *
     * @param   ilCycleQuestion     $question       question
     */
    public function markQuestionAsRefused($question) {
        $question->setState(-1);
        $question->storeToDB();
        $reviews = $this->review_db->getReviewForms(
            array("question_id" => $question->getID())
        );
        foreach ($reviews as $review) {
            $review->copyToHistory();
            $review->delete();
        }
        // TODO change notify function to use the object
        $this->notifyAuthorsAboutRefusal(array($question->getID()));
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
     * Get all questions from all question pools of the group
     *
     * @return  array       $questions          assQuestion objects
     */
    public function getAllCourseQuestions() {
        global $ilDB;

        $questions = array();
        // TODO use ILIAS objects for this search
        $result = $ilDB->queryF(
            "SELECT question_id AS id FROM qpl_questions "
            . "INNER JOIN object_reference "
            . "ON object_reference.obj_id = qpl_questions.obj_fi "
            . "INNER JOIN crs_items "
            . "ON crs_items.obj_id = object_reference.ref_id "
            . "WHERE crs_items.parent_id = %s "
            . "AND qpl_questions.original_id IS NULL",
            array("integer"),
            array($this->getGroupID())
        );
        while ($record = $ilDB->fetchAssoc($result)) {
            $questions[] = assQuestion::_instantiateQuestion($record["id"]);
        }
        return $questions;
    }

    /*
     * Load all questions of a user that are not reviewable
     *
     * @return  array       $questions          assQuestion objects
     */
    public function loadNonReviewableQuestionsByUser() {
        global $ilUser;

        $questions = $this->getAllCourseQuestions();
        return array_filter(
            $questions,
            function($question) use ($ilUser) {
                return $question->getOwner() == $ilUser->getID()
                    && strpos(
                        $question->getQuestionType(),
                        "Reviewable"
                    ) === FALSE;
            }
        );
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

        $question = assQuestion::_instantiateQuestion($id);
        $type_tag = sprintf(
            "assReviewable%s",
            substr($question->getQuestionType(), 3)
        );
        $type_id = $ilDB->fetchAssoc($ilDB->queryF(
            "SELECT question_type_id FROM qpl_qst_type WHERE type_tag = %s",
            array("text"),
            array($type_tag)
        ))["question_type_id"];
        $pool = ilObject::_lookupTitle($question->getObjID());
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

    /*
     * Look up the timestamp of a question in the ILIAS database
     *
     * @param   integer     $question_id        question id
     *
     * @return  integer     $timestamp          timestamp
     */
    static function getQuestionTimestamp($question_id) {
        global $ilDB;

        $result = $ilDB->queryF(
            "SELECT tstamp FROM qpl_questions WHERE question_id = %s",
            array("integer"),
            array($question_id)
        );
        $record = $ilDB->fetchObject($result);
        return $record->tstamp;
    }

    /*
     * Return the number of reviewers set for a phase
     *
     * @param   integer         $phase          phase
     *
     * @return  integer         $_              number ob reviewers
     */
    function getReviewersPerPhase($phase) {
        $phases =
            $this->review_db->getCyclePhases(array("phase_nr" => $phase));
        return reset($phases)->getNumReviewers();
    }
}
?>
