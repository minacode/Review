<?php
class ilReviewableQuestionPluginGenerator {
    
    private static $ilias_path = '/opt/lampp/htdocs/ilias/';
    
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
                return $this->ilias_path . 'Customizing/global/plugins/Modules/TestQuestionPool/Questions/ass'. $question_type .'/';
            } else {
                return $this->ilias_path . '/Modules/TestQuestionPool/';
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
        $plugin_path = self::$ilias_path . 'Customizing/global/plugins/Modules/TestQuestionPool/Questions/assReviewable'. $question_type .'/';
        $template_path = self::$ilias_path . 'Customizing/global/plugins/Services/Repository/RepositoryObject/Review/templates/question_files/';
        echo $plugin_path;
        mkdir( $plugin_path, true );
        // template_name, file_name, path
        $files = array(
            array( 
                'template_name' => 'plugin.php',
                'file_name'     => 'plugin.php',
                'path'          => ''
            ),
            array( 
                'template_name' => 'class.assQuestionType.php',
                'file_name'     => 'class.assReviewable'. $question_type .'php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'class.assQuestionTypeGUI.php',
                'file_name'     => 'class.assReviewable'. $question_type .'GUI.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'class.ilAssQuestionTypeFeedback.php',
                'file_name'     => 'class.ilAssReviewable'. $question_type .'Feedback.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'class.ilAssQuestionTypePlugin.php',
                'file_name'     => 'class.ilAssReviewable'. $question_type .'Plugin.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'ilias_en.lang',
                'file_name'     => 'ilias_en.lang',
                'path'          => 'lang/'
            ),
            array( 
                'template_name' => 'ilias_ger.lang',
                'file_name'     => 'ilias_ger.lang',
                'path'          => 'lang/'
            ),
            array( 
                'template_name' => 'dbupdate.php',
                'file_name'     => 'dbupdate.php',
                'path'          => 'sql/'
            ),
            array( 
                'template_name' => 'class.assQuestionTypeExport.php',
                'file_name'     => 'class.assReviewable'. $question_type .'Export.php',
                'path'          => 'classes/export/qti12/' 
            ),
            array( 
                'template_name' => 'class.assQuestionTypeImport.php',
                'file_name'     => 'class.assReviewable'. $question_type .'Import.php',
                'path'          => 'classes/import/qti12/' 
            )
        ); 
        foreach( $files as $file ) {
            echo $plugin_path . $file['path'];
            mkdir( $plugin_path . $file['path'], true );
            $template = $template_path . $file['template_name'];
            $file = $plugin_path . $file['path'] . $file['file_name'];
            $this->createFileFromTemplate( $question_type, $template, $file );
        }
    }
    
}
?>
