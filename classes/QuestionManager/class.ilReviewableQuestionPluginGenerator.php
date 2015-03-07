<?php
class ilReviewableQuestionPluginGenerator {
    
    private static $instance = null;
    
    private function _construct() {}
    private function _clone() {}
    
    public static function get() {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    private function getQuestionTypeId( $question_type ) {
        global $ilDB;
        
        $result = $ilDB->query('SELECT question_type_id FROM qpl_qst_type WHERE type_tag LIKE "ass'. $question_type .'"');
        if ( $result = $ilDB->fetchAssoc( $result ) ) {
            return $result['question_type'];
        } 
        return null;
    }
    
    private function getQuestionTypePath( $question_type ) {
        global $ilDB;
        
        $result = $ilDB->query('SELECT plugin FROM qpl_qst_type WHERE type_tag LIKE "ass'. $question_type .'"');
        if ( $result = $ilDB->fetchAssoc( $result ) ) {
            if ($result['plugin']) {
                return '.Modules/TestQuestionPool/Questions/ass'. $question_type . '/classes/';
            } else {
                return '.Modules/TestQuestionPool/classes/';
            }
        } 
        return null;
    }
    
    
    private function calculatePlaceholderValues( $question_type ) {
        $id = $this->getQuestionTypeId( $question_type_id );
        $path = $this->getQuestionTypePath( $question_type );
        if ( $id && $path ) {
            return array(
                '<id>'      => 'rev' . $id,
                '<minv>'    => '4.0.0',
                '<maxv>'    => '4.9.9',
                '<resp>'    => 'Max Friedrich, Richard Mörbitz',
                '<respm>'   => 'max.friedrich@tu-dresden.de, richard.moerbitz@tu-dresden.de',
                '<qtype>'   => $question_type,
                '<qpath>'   => $path
            );
        }
        return array();    
    }
    
    private function replaceTemplatePlaceholders( $template, $placeholder_values ) {
        foreach ($placeholder_values as $placeholder => $value) {
            $template = str_replace( $placeholder, $value, $template);
        }
        return $template;
    }
    
    private function createFileFromTemplate( $question_type, $template_path, $file_path ) {
        $template = file_get_contents( $template_path, "r");
        $placeholder_values = $this->calculatePlaceholderValues( $question_type );
        $template = $this->replaceTemplatePlaceholders( $template, $placeholder_values );
        file_put_contents( $file_path . $file_name, $template );
    }
    
    public function createPlugin( $question_type ) {
        $question_type = substr( $question_type, 3);
        $base_path = './Modules/TestQuestionPool/Questions/assReviewable'. $question_type .'/';
        mkdir( $base_path, true );
        // template_name, file_name, path
        $files = array(
            array( 'plugin-php',                            'plugin.php',                           '' ),
            array( 'class.assQuestionType.php',             'class.assReviewable'. $question_type .'php',               'classes/'              ),
            array( 'class.assQuestionTypeGUI.php',          'class.assReviewable'. $question_type .'GUI.php',           'classes/'              ),
            array( 'class.ilAssQuestionTypeFeedback.php',   'class.ilAssReviewable'. $question_type .'Feedback.php',    'classes/'              ),
            array( 'class.ilAssQuestionTypePlugin.php',     'class.ilAssReviewable'. $question_type .'Plugin.php',      'classes/'              ),
            array( 'ilias_en.lang',                         'ilias_en.lang',                                            'lang/'                 ),
            array( 'ilias_ger.lang',                        'ilias_ger.lang',                                           'lang/'                 ),
            array( 'dbupdate.php',                          'dbupdate.php',                                             'sql/'                  ),
            array( 'class.assQuestionTypeExport.php',       'class.assReviewable'. $question_type .'Export.php',        'classes/export/qti12/' ),
            array( 'class.assQuestionTypeImport.php',       'class.assReviewable'. $question_type .'Import.php',        'classes/import/qti12/' )
        ); 
        foreach( $files as $file ) {
            mkdir( $base_path . file[2], true );
            $template = 'templates/question_files/' . file[0];
            $file = $base_path . file[2] . file[1];
            $this->createFileFromTemplate( $question_type, $template, $file );
        }
    }
    
}
?>
