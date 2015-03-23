<?php
require_once "./Services/Form/classes/class.ilPropertyFormGUI.php";
require_once "./Modules/Test/classes/inc.AssessmentConstants.php";
require_once "<qpath>classes/class.ass<qtype>GUI.php";

/**
 * GUI class for Reviewable<qtype>
 *
 * @author  Julius Felchow <julius.felchow@mailbox.tu-dresden.de>
 * @author  Max Friedrich <max.friedrich@mailbox.tu-dresden.de>
 * @version $Id$
 *
 * @ingroup ModulesTestQuestionPool
 *
 * @ilctrl_iscalledby assReviewable<qtype>GUI: ilObjQuestionPoolGUI, ilObjTestGUI, ilQuestionEditGUI, ilTestExpressPageObjectGUI
 */

  
 
class assReviewable<qtype>GUI extends ass<qtype>GUI{

    var $plugin = null;
    var $object = null;
    
    /**
    * Constructor
    *
    * @param integer $id The database id of a question object
    * @access public
    */

    public function __construct($id = -1) {
        parent::__construct();
        include_once "./Services/Component/classes/class.ilPlugin.php";
        $this->plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, "TestQuestionPool", "qst", "assReviewable<qtype>");
        $this->plugin->includeClass("class.assReviewable<qtype>.php");
        $this->object = new assReviewable<qtype>();
        if ($id >= 0)
        {
            $this->object->loadFromDb($id);
        }
    }

    private function writeReviewData($always = false)
    {
        $this->object->setTaxonomy($_POST["taxonomy"]);
        $this->object->setKnowledgeDimension($_POST["knowledge_dimension"]);
    }

    /**
     * Evaluates a posted edit form and writes the form data in the question object
     *
     * @param bool $always
     *
     * @return integer A positive value, if one of the required fields wasn't set, else 0
     */
    public function writePostData($always = false) {
        $hasErrors = (!$always) ? $this->editQuestion(true) : false;
        if (!$hasErrors)
        {
            parent::writePostData($always);
            $this->writeReviewData();
            return 0;
        }
        return 1;
    }

    // ...
    public function addQuestionFormCommandButtons( $form ) {
        parent::addQuestionFormCommandButtons( $form );
        $this->populateTaxonomyFormPart( $form );
    }

    /**
     * Creates the output of the taxonomy and knowledgeDimension for the question
     *
     * @param object $form (ilPropertyFormGUI())
     *
     * @return object (ilPropertyFormGUI())
     */
    
    private function populateTaxonomyFormPart($form){
        global $lng;
        global $ilPluginAdmin;
        if($ilPluginAdmin->isActive(IL_COMP_SERVICE, "Repository", "robj", "Review")){
            include_once ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                 "/classes/class.ilObjReview.php";
            $head_cog = new ilSelectInputGUI("", "taxonomy");
            $head_cog->setTitle($lng->txt("qpl_qst_<id>_cognitive_process"));
            $head_cog->setValue($this->getDefaultTaxonomy($this->object->getId()));
            $head_cog->setOptions(ilObjReview::getEnum("taxonomy"));
            $head_cog->setRequired(true);
            $form->addItem($head_cog);

            $head_kno = new ilSelectInputGUI("", "knowledge_dimension");
            $head_kno->setTitle($lng->txt("qpl_qst_<id>_knowledge_dimension"));
            $head_kno->setValue($this->getDefaultKnowledgeDimension($this->object->getId()));
            $head_kno->setOptions(ilObjReview::getEnum("knowledge dimension"));
            $head_kno->setRequired(true);
            $form->addItem($head_kno);
        }
    }
    /**
     * Returns the input for the ilSelectInputGUI (taxonomy)
     *
     * @param int $question_id
     *
     * @return int array
     */
    private function getDefaultTaxonomy($question_id)
    {
        global $ilDB;
        $result = $ilDB->queryF(
                "SELECT taxonomy FROM qpl_rev_qst WHERE question_id = %s", 
                array("integer"),
                array($question_id));
        if($result->numRows() <= 0)
        {
            return 0;
        }else 
        {
            $first_row = $ilDB->fetchAssoc($result);
            return $first_row["taxonomy"];
        }
    }
    /**
     * Returns the input for the ilSelectInputGUI (knowledgeDimension)
     *
     * @param int $question_id
     *
     * @return int array
     */
    private function getDefaultKnowledgeDimension($question_id)
    {
        global $ilDB;
        $result = $ilDB->queryF(
                "SELECT knowledge_dimension FROM qpl_rev_qst WHERE question_id = %s", 
                array("integer"),
                array($question_id));
        if($result->numRows() <= 0)
        {
            return 0;
        }else 
        {
            $first_row = $ilDB->fetchAssoc($result);
            return $first_row["knowledge_dimension"];
        }   
    }
    
    /**
     * Checks for taxonomy and knowledge_dimensions to be set
     *
     * @return bool
     */
    public function checkAddInput(){
        global $lng;
        $valid = true;
        if ($_POST["taxonomy"]==0 || $_POST["knowledge_dimension"]==0)
            $valid = false;
        if (!$valid)
            ilUtil::sendFailure($lng->txt("form_input_not_valid"));
        return $valid;  
        
    }
}

?>
