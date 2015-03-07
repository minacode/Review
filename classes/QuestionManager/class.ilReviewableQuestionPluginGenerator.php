<?php
class ilReviewableQuestionPluginGenerator() {
    
    private $instance = null;
    
    private function _construct() {}
    
    public static function get() {
        if ($this->instance == null) {
            $this->instance = new ilReviewableQuestionPluginGenerator();
        }
        return $this->instance;
    }
    
    private function get_question_type_id( $question_type ) {
        global ilDB;
        
        $result = ilDB->query('SELECT question_type_id FROM qpl_qst_type WHERE type_tag == "ass'. $question_type .'"');
        if ( $result = ilDB->fetchAssoc( $result ) ) {
            return $result['question_type'];
        } 
        return null;
    }
    
    private function get_question_type_path( $question_type ) {
        global ilDB;
        
        $result = ilDB->query('SELECT plugin FROM qpl_qst_type WHERE type_tag == "ass'. $question_type .'"');
        if ( $result = ilDB->fetchAssoc( $result ) ) {
            if ($result['plugin']) {
                return '.Modules/TestQuestionPool/Questions/ass'. $question_type . '/classes/';
            } else {
                return '.Modules/TestQuestionPool/classes/';
            }
        } 
        return null;
    }
    
    
    private function calculate_placeholder_values( $question_type ) {
        $id = this->get_question_type_id( $question_type_id );
        $path = this->get_question_type_path( $question_type );
        if ( $id && $path ) {
            return array(
                '<id>'      => 'rev' . $id,
                '<minv>'    => '4.0.0',
                '<maxv>'    => '4.9.9',
                '<resp>'    => 'Max Friedrich, Richard Mörbitz',
                '<respm>'   => 'max.friedrich@tu-dresden.de, richard.moerbitz@tu-dresden.de',
                '<qtype>'   => $question_type,
                '<qpath>'   => $path;
            );
        }
        return array();    
    }
    
    private function replace_template_placeholders( $template, $placeholder_values ) {
        foreach ($placeholder_values as $placeholder => $value) {
            $template = str_replace( $placeholder, $value, $template);
        }
        return $template;
    }
    
    private function create_file_from_template( $question_type, $template_path, $file_path ) {
        $template = file_get_contents( $template_path, "r");
        $placeholder_values = this->calculate_placeholder_values( $question_type );
        $template = this->replace_template_placeholders( $template, $placeholder_values );
        file_put_contents( $file_path . $file_name, $template );
    }
    
    public function create_plugin( $question_type, $original_path ) {
        $question_type = substr( $question_type, 2);
        $base_path = './Modules/TestquestionPool/Questions/assReviewable'. $question_type .'/';
        mkdir( $base_path, recursive = true );
        // template_name, file_name, path
        files = array(
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
            mkdir( $base_path . file[2], recursive = true );
            $template = 'templates/question_files/' . file[0];
            $file = $base_path . file[2] . file[1];
            this->create_file_from_template( $question_type, $template, $file );
        }
    }
    
}
?>
