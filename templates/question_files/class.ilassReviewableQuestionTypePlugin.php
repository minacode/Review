<?php

/*
 * auto-generated plugin class file for question plugins
 * placeholder used:
 * <qtype>  type of the non-reviewable question
 */

require_once("./Modules/TestQuestionPool/classes/class.ilQuestionsPlugin.php");

/*
 * Reviewable<qtype> Plugin
 *
 * @version $Id$
 */

class ilassReviewable<qtype>Plugin extends ilQuestionsPlugin {

    final function getPluginName() {
        return "assReviewable<qtype>";
    }

    final function getQuestionType() {
        return "assReviewable<qtype>";
    }

    final function getQuestionTypeTranslation() {
        return $this->txt($this->getQuestionType());
    }
}
?>
