<?php
/*
+-----------------------------------------------------------------------------+
| ILIAS open source                                                           |
+-----------------------------------------------------------------------------+
| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
|                                                                             |
| This program is free software; you can redistribute it and/or               |
| modify it under the terms of the GNU General Public License                 |
| as published by the Free Software Foundation; either version 2              |
| of the License, or (at your option) any later version.                      |
|                                                                             |
| This program is distributed in the hope that it will be useful,             |
| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
| GNU General Public License for more details.                                |
|                                                                             |
| You should have received a copy of the GNU General Public License           |
| along with this program; if not, write to the Free Software                 |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
+-----------------------------------------------------------------------------+
*/

include_once "Modules/TestQuestionPool/classes/class.assQuestionGUI.php";
include_once "Services/Repository/classes/class.ilObjectPluginGUI.php";
include_once("QuestionManager/class.ilReviewableQuestionPluginGenerator.php");
include_once("Modules/TestQuestionPool/classes/class.assQuestionGUI.php");
include_once("./Services/Repository/classes/class.ilObjectPluginGUI.php");
include_once("./Services/Form/classes/class.ilCustomInputGUI.php");
include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilReviewOutputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilReviewInputGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilReviewTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilQuestionTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilCheckMatrixRowGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilQuestionOverviewGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                 "/classes/GUI/class.ilConvertQuestionTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilReviewerAllocFormGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilQuestionOverviewGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilConvertQuestionTableGUI.php");
include_once(ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory() .
                                 "/classes/GUI/class.ilGenerateQuestionTypesGUI.php");

/**
* User Interface class for Review repository object.
*
* User interface classes process GET and POST parameter and call
* application classes to fulfill certain tasks.
*
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
* @author Max Friedrich <Max.Friedrich@mailbox.tu-dresden.de>
*
* $Id$
*
* Integration into control structure:
* - The GUI class is called by ilRepositoryGUI
* - GUI classes used by this class are ilPermissionGUI (provides the rbac
*   screens) and ilInfoScreenGUI (handles the info screen).
*
* @ilCtrl_isCalledBy ilObjReviewGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjReviewGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI, ilCommonActionDispatcherGUI, ilReviewOutputGUI, ilReviewInputGUI, assQuestionGUI, ilReviewerAllocFormGUI, ilGenerateQuestionTypesGUI
*
*/
class ilObjReviewGUI extends ilObjectPluginGUI {
        /**
        * Initialisation
        */
        protected function afterConstructor() {
                // anything needed after object has been constructed
                // - example: append my_id GET parameter to each request
                //   $ilCtrl->saveParameter($this, array("my_id"));
        }

        /**
        * Get type.
        */
        final function getType() {
                return "xrev";
        }

        /**
        * Handles all commmands of this class, centralizes permission checks
        *
        * @param string         $cmd            command to be performed by this class
        */
        function performCommand($cmd) {
            switch ($cmd) {
                case "editProperties":
                case "updateProperties":
                case "allocateReviewers":
                case "saveAllocateReviewers":
                case "convertQuestion":
                case "performConvertQuestion":
                case "saveConvertQuestion":
                case "addPhase":
                case "removePhase":
                case "generateQuestionPlugins":
                case "generateQuestionTypes":
                    $this->checkPermission("write");
                    $this->$cmd();
                    break;
                case "showContent":
                case "inputReview":
                case "showReviews":
                case "saveReview":
                    $this->checkPermission("read");
                    $this->$cmd();
                    break;
            }
        }

        /**
        * After object has been created -> jump to this command
        */
        function getAfterCreationCmd() {
                return "showContent";
        }

        /**
        * Get standard command
        */
        function getStandardCmd() {
                return "showContent";
        }

        /**
        * Set tabs
        */
        function setTabs() {
                global $ilTabs, $ilCtrl, $ilAccess;

                // tab for the "show content" command
                if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
                        $ilTabs->addTab("content", $this->txt("content"), $ilCtrl->getLinkTarget($this, "showContent"));
                }

                // standard info screen tab
                $this->addInfoTab();

                // tabs to edit properties and run the review cycle
                if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                        $ilTabs->addTab("properties", $this->txt("properties"), $ilCtrl->getLinkTarget($this, "editProperties"));
                        $ilTabs->addTab("allocation", $this->txt("reviewer_allocation"), $ilCtrl->getLinkTarget($this, "allocateReviewers"));
            $ilTabs->addTab("convert", $this->txt("convert_questions"), $ilCtrl->getLinkTarget($this, "convertQuestion"));
            $ilTabs->addTab("generate", $this->txt("generate_plugins"), $ilCtrl->getLinkTarget($this, "generateQuestionPlugins"));
                }

                // standard epermission tab
                $this->addPermissionTab();
        }

        function generateQuestionPlugins() {
            echo "plugins ";
            global $tpl, $ilTabs;

            $ilTabs->activateTab("generate");
            $this->initGenerateQuestionPluginsForm();
            $tpl->setContent($this->generate_form->getHTML());
        }
        
        function initGenerateQuestionPluginsForm() {
            echo "init ";
            global $ilCtrl;

            $this->generate_form = new ilGenerateQuestionTypesGUI( $this->object->getQuestionTypesWithNoReviewablePlugin() );
            $this->generate_form->setTitle($this->txt("generate_question_plugins"));
        }
        
        function generateQuestionTypes() {
            echo "types ";
            global $tpl, $ilTabs, $lng, $ilCtrl;
            
            $generator = ilReviewableQuestionPluginGenerator::get();

            foreach ( $_POST["question_type_name"] as $question_type ) {
                $generator->createPlugin( $question_type );
            }

            $ilTabs->activateTab("generate");
            $this->initGenerateQuestionPluginsForm();

            $ilCtrl->redirect($this, "generateQuestionPlugins");
            $tpl->setContent($this->generate_form->getHTML());
        }

        /**
        * Edit plugin object properties
        */
        function editProperties() {
                global $tpl, $ilTabs;

                $ilTabs->activateTab("properties");
                $this->initPropertiesForm();
                $this->getPropertiesValues();

                $tpl->setContent($this->form->getHTML());
        }

        /**
        * Input reviewer allocation
        */
        function allocateReviewers() {
                global $tpl, $ilTabs;

                $ilTabs->activateTab("allocation");
        $this->initReviewerAllocForm();
        //$this->alloc_form->setValuesByPost();
        $tpl->setContent($this->alloc_form->getHTML());
        }

        /**
        * Check and save reviewer allocation
        */
        function saveAllocateReviewers() {
                global $tpl, $ilTabs, $lng, $ilCtrl;

                $ilTabs->activateTab("allocation");
                $this->initReviewerAllocForm();
        /*
        foreach ($_POST as $key => $val) {
            echo $key . ": " . $val . "<br>";
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    echo $k . ": " . $v . "<br>";
                }
            }
        }
         */
                if ($this->alloc_form->checkInput()) {
                        $rows = array();
                        foreach ($this->alloc_form->getItems() as $item) {
                                if (method_exists($item, "getPostVars")) {
                    $row_postvars = $item->getPostVars();
                    $row_values = array();
                    foreach ($row_postvars as $row_postvar)
                        $row_values[$row_postvar] = $this->alloc_form->getInput($row_postvar);
                    $rows[] = array("q_id" => $item->getRowId(), "reviewers" => $row_values);
                }
                if ($item instanceof ilNumberInputGUI) {
                    $this->object->updateCyclePhase(explode("_", $item->getPostVar())[1], $this->alloc_form->getInput($item->getPostVar()));
                }
                        }
                        $this->object->allocateReviewers($rows);
                        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                        $ilCtrl->redirect($this, "allocateReviewers");
                }
                $this->alloc_form->setValuesByPost();
                $tpl->setContent($this->alloc_form->getHTML());
        }

    /*
     * Select old questions to make them reviewable
     */
    public function convertQuestion() {
        global $tpl, $ilTabs;

        $ilTabs->activateTab("convert");
        $convert_form = new ilConvertQuestionTableGUI($this, "performConvertQuestion", $this->object->loadNonReviewableQuestions());
        $tpl->setContent($convert_form->getHTML());
    }

    /*
     * Check and perform conversion of questions
     */
    public function performConvertQuestion() {
        global $tpl, $ilTabs, $ilCtrl;

        $ilTabs->activateTab("convert");
        $this->initMissingDataForm($_GET["q_id"]);
        $ilCtrl->setParameter($this, "q_id", $_GET["qid"]);
        $hv = new ilHiddenInputGUI("", "q_id");
        $hv->setValue($_GET["q_id"]);
        $this->missing_data_form->addItem($hv);
        $tpl->setContent($this->missing_data_form->getHTML());
    }

    /**
     * Init form to enter taxonomy and knowledge dimension
     */
    public function initMissingDataForm() {
        global $ilCtrl;

        $this->missing_data_form = new ilPropertyFormGUI();
        $this->missing_data_form->setTitle($this->txt("add_taxonomy"));


        $cog = new ilSelectInputGUI("", "taxonomy");
        $cog->setTitle($this->txt("taxonomy"));
        $cog->setValue(0);
        $cog->setOptions($this->object->getEnum("taxonomy"));
        $cog->setRequired(true);
        $this->missing_data_form->addItem($cog);

        $kno = new ilSelectInputGUI("", "knowledge_dimension");
        $kno->setTitle($this->txt("knowledge_dim"));
        $kno->setValue(0);
        $kno->setOptions($this->object->getEnum("knowledge dimension"));
        $kno->setRequired(true);
        $this->missing_data_form->addItem($kno);

        $loutc = new ilSelectInputGUI("", "learning_outcome");
        $loutc->setTitle($this->txt("learning_outcome"));
        $loutc->setValue(0);
        $loutc->setOptions($this->object->getEnum("taxonomy"));
        $loutc->setRequired(true);
        $this->missing_data_form->addItem($loutc);

        $cont = new ilSelectInputGUI("", "content");
        $cont->setTitle($this->txt("content"));
        $cont->setValue(0);
        $cont->setOptions($this->object->getEnum("taxonomy"));
        $cont->setRequired(true);
        $this->missing_data_form->addItem($cont);

        $topic = new ilSelectInputGUI("", "topic");
        $topic->setTitle($this->txt("topic"));
        $topic->setValue(0);
        $topic->setOptions($this->object->getEnum("taxonomy"));
        $topic->setRequired(true);
        $this->missing_data_form->addItem($topic);

        $subar = new ilSelectInputGUI("", "subject_area");
        $subar->setTitle($this->txt("subject_area"));
        $subar->setValue(0);
        $subar->setOptions($this->object->getEnum("taxonomy"));
        $subar->setRequired(true);
        $this->missing_data_form->addItem($subar);

        $ilCtrl->setParameter($this, "q_id", $_GET["qid"]);

        $this->missing_data_form->addCommandButton("saveConvertQuestion", $this->txt("save"));

        /* Evil hack as ILIAS won't pass this f***ing q_id */
        $this->missing_data_form->setFormAction($ilCtrl->getFormAction($this) . "&q_id=" . $_GET["q_id"]);
    }

    /**
     * Save adding missing taxonomy data and converting the question
     */
    public function saveConvertQuestion() {
        global $tpl, $ilTabs, $lng, $ilCtrl;

        $ilTabs->activateTab("convert");
        $this->initMissingDataForm();
        // if ($_GET["q_id"] == NULL) die;
        if ($this->missing_data_form->checkInput()
            && $this->missing_data_form->getInput("taxonomy") != 0
            && $this->missing_data_form->getInput("knowledge_dimension") != 0
        ) {
            $this->object->saveQuestionConversion($_GET["q_id"],
                $this->missing_data_form->getInput("taxonomy"),
                $this->missing_data_form->getInput("knowledge_dimension")
            );
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "convertQuestion");
        }
        ilUtil::sendFailure($lng->txt("form_input_not_valid"));

        $ilCtrl->setParameter($this, "q_id", $_GET["qid"]);
        $this->missing_data_form->setValuesByPost();
                $tpl->setContent($this->missing_data_form->getHTML());
        }

        /**
        * Init form for reviewer allocation
        */
        public function initReviewerAllocForm() {
                global $ilCtrl;

                $members = $this->object->loadMembers();
        $phases = $this->object->loadPhases();

        $this->alloc_form = new ilReviewerAllocFormGUI($members, $phases, $this);
        foreach ((array) $this->object->loadReviewerAllocation as $k => $v) {
            echo $k . ": " . $v . "<br>";
        }
        $this->alloc_form->setValuesByArray($this->object->loadReviewerAllocation());
    }

        /**
        * Init  form for editing plugin object properties
        */
        public function initPropertiesForm() {
                global $ilCtrl;

                $this->form = new ilPropertyFormGUI();

                // title
                $ti = new ilTextInputGUI($this->txt("title"), "title");
                $ti->setRequired(true);
                $this->form->addItem($ti);

                // description
                $ta = new ilTextAreaInputGUI($this->txt("description"), "desc");
                $this->form->addItem($ta);

                $this->form->addCommandButton("updateProperties", $this->txt("save"));

                $this->form->setTitle($this->txt("edit_properties"));
                $this->form->setFormAction($ilCtrl->getFormAction($this));
        }

        /**
        * Get values for edit properties form
        */
        function getPropertiesValues() {
                $values["title"] = $this->object->getTitle();
                $values["desc"] = $this->object->getDescription();
                $this->form->setValuesByArray($values);
        }

        /**
        * Update properties
        */
        public function updateProperties() {
                global $tpl, $lng, $ilCtrl;

                $this->initPropertiesForm();
                if ($this->form->checkInput()) {
                        $this->object->setTitle($this->form->getInput("title"));
                        $this->object->setDescription($this->form->getInput("desc"));
                        $this->object->update();
                        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                        $ilCtrl->redirect($this, "editProperties");
                }
                $this->form->setValuesByPost();
                $tpl->setContent($this->form->getHtml());
        }

        /**
        * Show plugin content (question and review table)
        */
        protected function showContent() {
                global $tpl, $ilTabs;

                $ilTabs->activateTab("content");

                $table_q = new ilQuestionTableGUI($this, "showContent", $this->object->loadQuestionsByUser());
                $table_r = new ilReviewTableGUI($this, "showContent", $this->object->loadReviewsByUser());
                $tpl->setContent($table_q->getHtml() . "<br><hr><br>" . $table_r->getHtml());
        }

        /**
        * Display review input form
        */
        public function inputReview() {
                global $tpl, $ilTabs, $ilCtrl, $lng;
                $ilTabs->activateTab("content");
                if (!ilObjReviewAccess::checkAccessToObject($_GET["r_id"], "", "inputReview", "review")) {
                        ilUtil::sendFailure($lng->txt("rep_robj_xrev_no_access"), true);
                        $ilCtrl->redirect($this, "showContent");
                }
                $ilCtrl->setParameter($this, "r_id", $_GET["r_id"]);
                $ilCtrl->setParameter($this, "q_id", $_GET["q_id"]);
                $input = new ilReviewInputGUI($this, "showContent", $this->object->loadReviewById($_GET["r_id"]),
                                                                                                $this->object->loadQuestionTaxonomyData($_GET["q_id"]),
                                                                                                $this->object->getEnum("taxonomy"),
                                                                                                $this->object->getEnum("knowledge dimension"),
                                                                                                $this->object->getEnum("expertise"),
                                                                                                $this->object->getEnum("rating"),
                                                                                                $this->object->getEnum("evaluation")
                                                 );
                $this->initQuestionOverview();
        $tpl->setContent($input->getHtml());
        }

        /*
        * Save review input
        */
        public function saveReview() {
                global $tpl, $ilTabs, $lng, $ilCtrl;
                $ilTabs->activateTab("content");
                if (!ilObjReviewAccess::checkAccessToObject($_GET["r_id"], "", "saveReview", "review")) {
                        ilUtil::sendFailure($lng->txt("rep_robj_xrev_no_access"), true);
                        $ilCtrl->redirect($this, "showContent");
                }
                $ilCtrl->setParameter($this, "r_id", $_GET["r_id"]);
                $ilCtrl->setParameter($this, "q_id", $_GET["q_id"]);
                $input = new ilReviewInputGUI($this, "showContent", $this->object->loadReviewById($_GET["r_id"]),
                                                                                                $this->object->loadQuestionTaxonomyData($_GET["q_id"]),
                                                                                                $this->object->getEnum("taxonomy"),
                                                                                                $this->object->getEnum("knowledge dimension"),
                                                                                                $this->object->getEnum("expertise"),
                                                                                                $this->object->getEnum("rating"),
                                                                                                $this->object->getEnum("evaluation")
                                                 );
                if ($input->checkInput()) {
                        $form_data = array();
                        $post_vars = array("dc", "dr", "de", "qc", "qr", "qe", "ac", "ar", "ae", "cog_r", "kno_r", "group_e", "comment", "exp");
                        foreach ($post_vars as $post_var)
                                $form_data[$post_var] = $input->getInput($post_var);
                        $this->object->storeReviewById($_GET["r_id"], $form_data);
                        $this->object->notifyAuthorAboutCompletion($_GET["r_id"]);
                        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                        $ilCtrl->redirect($this, "showContent");
                }
                else
                        $ilCtrl->setParameter($this, "r_id", $_GET["r_id"]);
                $input->setValuesByPost();
                $this->initQuestionOverview();
        $tpl->setContent($this->question_overview . $input->getHtml());
        }

        /**
        * Output reviews
        */
        public function showReviews() {
                global $tpl, $ilTabs, $ilCtrl, $lng;
                if (!ilObjReviewAccess::checkAccessToObject($_GET[substr($_GET["origin"], 0, 1)."_id"], "", "showReviews", $_GET["origin"])) {
                        ilUtil::sendFailure($lng->txt("rep_robj_xrev_no_access"), true);
                        $ilCtrl->redirect($this, "showContent");
                }
                $ilTabs->activateTab("content");
                $tbl = new ilReviewOutputGUI($this, "showReviews", $this->object->loadReviewsByQuestion($_GET["q_id"]),
                                            $this->object->loadQuestionTaxonomyData($_GET["q_id"]),
                                            $this->object->getEnum("taxonomy"),
                                            $this->object->getEnum("knowledge dimension"),
                                            $this->object->getEnum("expertise"),
                                            $this->object->getEnum("rating"),
                                            $this->object->getEnum("evaluation")

                                          );
        $this->initQuestionOverview();
                $tpl->setContent($this->question_overview . $tbl->getHtml());
        }

    /**
     * Create the question overview GUI
     */
    private function initQuestionOverview() {
        global $ilPluginAdmin;
        if (!isset($_GET["q_id"])) {
            $this->question_overview = "";
        } else {
            $q_gui = assQuestionGUI::_getQuestionGUI("", $_GET["q_id"]);
            $quest = new ilQuestionOverviewGUI($this, $q_gui->getSolutionOutput(0), $this->object->loadQuestionMetaData($_GET["q_id"]));
            $this->question_overview = $quest->getHTML();
        }
    }

    public function addPhase() {
        global $ilCtrl, $lng;

        $this->object->addPhaseToCycle();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "allocateReviewers");
    }

    public function removePhase() {
        global $ilCtrl, $lng;

        $this->object->removePhaseFromCycle();
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "allocateReviewers");
    }
}
?>
