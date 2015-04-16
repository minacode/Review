<?php
class ilReviewableQuestionPluginGenerator {
    
    private static $ilias_path = ILIAS_ABSOLUTE_PATH . '/';
    
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
        $data = $ilDB->fetchAssoc( $result );
        return $data['question_type_id'];
    }
    
    private function getQuestionTypePath( $question_type ) {
        global $ilDB;
        
        $result = $ilDB->query('SELECT plugin FROM qpl_qst_type WHERE type_tag LIKE "ass'. $question_type .'"');
        if ( $result = $ilDB->fetchAssoc( $result ) ) {
            if ($result['plugin']) {
                return $this->ilias_path . 'Customizing/global/plugins/Modules/TestQuestionPool/Questions/ass'. $question_type .'/';
            } else {
                return $this->ilias_path . 'Modules/TestQuestionPool/';
            }
        } 
        return null;
    }
    
    
    private function calculatePlaceholderValues( $question_type ) {
        $id = $this->getQuestionTypeId( $question_type );
        $path = $this->getQuestionTypePath( $question_type );
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
    
    private function replaceTemplatePlaceholders( $template, $placeholder_values ) {
        foreach ($placeholder_values as $placeholder => $value) {
            $template = str_replace( $placeholder, $value, $template );
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
        if ( !file_exists( $plugin_path ) ) {
            mkdir( $plugin_path, 0777, true );
            chmod( $plugin_path, 0777 );
        }
        // template_name, file_name, path
        $files = array(
            array( 
                'template_name' => 'plugin.php',
                'file_name'     => 'plugin.php',
                'path'          => ''
            ),
            array( 
                'template_name' => 'class.assReviewableQuestionType.php',
                'file_name'     => 'class.assReviewable'. $question_type .'.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'class.assReviewableQuestionTypeGUI.php',
                'file_name'     => 'class.assReviewable'. $question_type .'GUI.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'class.ilAssReviewableQuestionTypeFeedback.php',
                'file_name'     => 'class.ilAssReviewable'. $question_type .'Feedback.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'class.ilassReviewableQuestionTypePlugin.php',
                'file_name'     => 'class.ilassReviewable'. $question_type .'Plugin.php',
                'path'          => 'classes/'
            ),
            array( 
                'template_name' => 'ilias_en.lang',
                'file_name'     => 'ilias_en.lang',
                'path'          => 'lang/'
            ),
            array( 
                'template_name' => 'ilias_de.lang',
                'file_name'     => 'ilias_de.lang',
                'path'          => 'lang/'
            ),
            array( 
                'template_name' => 'dbupdate.php',
                'file_name'     => 'dbupdate.php',
                'path'          => 'sql/'
            ),
            array( 
                'template_name' => 'class.assReviewableQuestionTypeExport.php',
                'file_name'     => 'class.assReviewable'. $question_type .'Export.php',
                'path'          => 'classes/export/qti12/' 
            ),
            array( 
                'template_name' => 'class.assReviewableQuestionTypeImport.php',
                'file_name'     => 'class.assReviewable'. $question_type .'Import.php',
                'path'          => 'classes/import/qti12/' 
            ),
            array(
                'template_name' => 'tpl.il_as_qpl_<PluginId>_output.html',
                'file_name'     => 'tpl.il_as_qpl_rev'. $this->getQuestionTypeId( $$question_type ) .'_output.html',
                'path'          => ''
            ),
            array(
                'template_name' => 'tpl.il_as_qpl_<PluginId>_output_solution.html',
                'file_name'     => 'tpl.il_as_qpl_rev'. $this->getQuestionTypeId( $$question_type ) .'_output_solution.html',
                'path'          => ''
            )
        ); 
        foreach( $files as $file ) {
            if ( !file_exists( $plugin_path . $file['path'] ) ) {
                mkdir( $plugin_path . $file['path'], 0777, true );
                chmod( $plugin_path . $file['path'], 0777 );
            }
            $template = $template_path . $file['template_name'];
            $file_path = $plugin_path . $file['path'] . $file['file_name'];
            if ( !file_exists( $file_path ) ) {
                $this->createFileFromTemplate( $question_type, $template, $file_path );
            }
        }   
        // scan files..     
        // $plugin = ilPlugin::getPluginObject(IL_COMP_MODULE, 'TestQuestionPool', 'qst', 'assReviewable' . $question_type);
        // $plugin->update();
        // $plugin->activate();
    }
}
?>
