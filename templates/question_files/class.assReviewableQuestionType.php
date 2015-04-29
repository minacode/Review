<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @extends ass<qtype>
 *
 * @ingroup     ModulesTestQuestionPool
 */

require_once('<qpath>classes/class.ass<qtype>.php');
require_once('export/qti12/class.assReviewable<qtype>Export.php');
require_once('import/qti12/class.assReviewable<qtype>Import.php');

class assReviewable<qtype> extends ass<qtype> {

    protected $taxonomy;
    protected $knowledge_dimension;


    /**
     * assReviewable<qtype> constructor
     *
     * The constructor takes possible arguments an creates an instance of the assReviewable<qtype> object.
     *
     * @param string     $title                 A title string to describe the question
     * @param string     $comment               A comment string to describe the question
     * @param string     $author                A string containing the name of the questions author
     * @param integer    $owner                 A numerical ID to identify the owner/creator
     * @param string     $question              The question string of the <qtype> question
     * @param int|string $output_type           The output order of the <qtype> answers
     * @param int        $taxonomy              The taxonomy of the question
     * @param int        $knowledge_dimension   The knowledge dimension of the question
     * @param int        $learning_outcome      The learning outcome of the question
     * @param int        $topic                 The topic of the question
     */
    function _construct(
        $title = "",
        $comment = "",
        $author = "",
        $owner = -1,
        $question = "",
        $output_type = OUTPUT_ORDER,
        $taxonomy = "",
        $knowledge_dimension = "",
        $learning_outcome = "",
        $topic = ""
    ) {
        parent::_construct($title, $comment, $author, $owner, $question, $output_type);
        $this->taxonomy = $taxonomy;
        $this->knowledge_dimension = $knowledge_dimension;
        $this->learning_outcome = $learning_outcome;
        $this->topic = $topic;
    }

    /*
     * @return string
     */
    public function getQuestionType() {
        return "assReviewable<qtype>";
    }

    /*
     * @return int $learning_outcome
     */
    public function getLearningOutcome() {
        return $this->learning_outcome;
    }

    /*
     * @param int $learning_outcome
     */
    public function setLearningOutcome($learning_outcome) {
        $this->learning_outcome = $learning_outcome;
    }

    /*
     * @return int $topic
     */
    public function getTopic() {
        return $this->topic;
    }

    /*
     * @param int $a_taxonomy
     */
    public function setTopic($topic) {
        $this->topic = $topic;
    }

    /*
     * @return int $taxonomy
     */
    public function getTaxonomy() {
        return $this->taxonomy;
    }

    /*
     * @param int $a_taxonomy
     */
    public function setTaxonomy($a_taxonomy) {
        $this->taxonomy = $a_taxonomy;
    }

    /*
     * @return int $knowledge_dimension
     */
    public function getKnowledgeDimension() {
        return $this->knowledge_dimension;
    }
    
    /*
     * @param int $a_knowledge_dimension
     */
    public function setKnowledgeDimension($a_knowledge_dimension) {
        $this->knowledge_dimension = $a_knowledge_dimension;
    }
    
    /**
     * Function to save taxonomy and knowledge dimension to the database
     * 
     * @param string $original_id
     */
    private function saveReviewDataToDb($original_id = "") {
        global $ilDB;
        
        $result = $ilDB->queryF(
            "SELECT * 
            FROM qpl_rev_qst 
            WHERE question_id = %s",
            array("integer"),
            array( $this->getId() ) 
        );
        
        if ($result->numRows() <= 0) {
            $affectedRows = $ilDB->insert(
                "qpl_rev_qst",
                array(
                    "question_id"         => array( "integer"    , $this->getId()                 ),
                    "taxonomy"            => array( "integer"    , $this->getTaxonomy()           ),
                    "knowledge_dimension" => array( "integer"    , $this->getKnowledgeDimension() ),
                    "learning_outcome" => array( "clob"    , $this->getLearningOutcome() ),
                    "topic" => array( "text"    , $this->getTopic() )
                )
            );
        } else {
            $affectedRows = $ilDB->update(
                "qpl_rev_qst",
                array(
                    "taxonomy"            => array( "integer"    , $this->getTaxonomy()           ),
                    "knowledge_dimension" => array( "integer"    , $this->getKnowledgeDimension() ),
                    "learning_outcome" => array( "clob"    , $this->getLearningOutcome() ),
                    "topic" => array( "text"    , $this->getTopic() )
                ),
                array(
                    "question_id"         => array( "integer" , $this->getId()                 )
                )
            );
        }
    }
    
    /**
     * Overwritten function to save question to the database
     * 
     * @param string $original_id
     */
    public function saveToDb($original_id = "") {
        $this->saveReviewDataToDb($original_id);
        parent::saveToDb($original_id);
        $this->createDescription();
    }


    /*
     * Generate the description of a question (question pool title + topic)
     */
    public function createDescription() {
        global $ilDB;

        $res = $ilDB->queryF(
            "SELECT object_data.title"
            . " FROM object_data"
            . " INNER JOIN qpl_questions"
            . " ON object_data.obj_id = qpl_questions.obj_fi"
            . " WHERE qpl_questions.question_id = %s",
            array("integer"),
            array($this->getID())
        );

        $pool = $ilDB->fetchAssoc($res)["title"];

        $ilDB->update(
            "qpl_questions",
            array("description" => array(
                "text",
                $pool . "/" . $this->getTopic())
            ),
            array("question_id" => array("integer", $this->getID()))
        );
    }
    
    /**
     * Function to load taxonomy and knowledge dimension from the database
     * 
     * @param string $question_id   The id of target question
     */
    private function loadReviewDataFromDb($question_id = "") {
        global $ilDB;
        
        $result = $ilDB->queryF(
            "SELECT * FROM qpl_rev_qst WHERE question_id = %s",
            array("integer"),
            array($this->getId())
        );
        
        if($result->numRows() == 1) {
            $data = $ilDB->fetchAssoc($result);
            $this->setTaxonomy( $data['taxonomy'] );
            $this->setKnowledgeDimension( $data['knowledge_dimension'] );
            $this->setLearningOutcome( $data['learning_outcome'] );
            $this->setTopic( $data['topic'] );
        }
    }
    
    /**
     * Function to load data from the database
     * 
     * @param string $question_id   The id of target question
     */
    public function loadFromDb($question_id) {
        parent::loadFromDb($question_id);
        $this->loadReviewDataFromDb($original_id);
    }
    
    /**
     * Function to delete the question
     * 
     * @param string $question_id   The id of target question
     */
    function delete($question_id) {
        global $ilDB;
        
        $affectedRows = $ilDB->manipulate( "DELETE FROM qpl_rev_qst WHERE question_id = " . $question_id );
        
        parent::delete( $question_id );
    }
    
    /**
     * Function to convert the question to JSONn
     */
    function toJSON() {
        $result = json_decode( parent::toJson() );
        
        $result['taxonomy'] = $this->getTaxonomy();
        $result['knowlegde_dimension'] = $this->getKnowlegdgeDimension();
        $result['learning_outcome'] = $this->getLearningOutcome();
        $result['topic'] = $this->getTopic();
        
        return json_encode($result);
    }

}
