<?php
class ilReviewableQuestionPluginGenerator() {
    
    private $instance = null;
    
    private _construct() {}
    
    public static get() {
        if ($this->instance == null) {
            $this->instance = new ilReviewableQuestionPluginGenerator();
        }
        return $this->instance;
    }
    
    private get_question_type_id( $question_type ) {
        global ilDB;
        
        $result = ilDB->query('SELECT question_type_id FROM qpl_qst_type WHERE type_tag == "'. $question_type .'"');
        if ($result) {
            $result = result->fetch(); // Entwurf..
            return result['question_type'];
        } else {
            return 0;
        }
    }
    
    private get_min_ilias_version() {
        return '4.0.0';
    }
    
    private get_max_ilias_version() {
        return '4.9.9';
    }
    
    private calculate_placeholder_values( $question_type ) {
        $placeholder_values = array(
            '<id>'      => 'rev' . this->get_question_type_id( $question_type ),
            '<minv>'    => this->get_min_ilias_version(),
            '<maxv>'    => this->get_max_ilias_version(),
            '<resp>'    => 'Richard Mörbitz, Max Friedrich, Julius Felchow',
            '<respm>'   => 'richard.moerbitz@tu-dresden.de, max.friedrich@tu-dresden.de, julius.felchow@mailbox.tu-dresden.de',
            '<qtype>'   => substr($question_type, 2) //without 'ass' at beginning
        );
        return $placeholder_values;
    }
    
    private replace_template_placeholders( $template, $placeholder_values ) {
        foreach ($placeholder_values as $placeholder => $value) {
            $template = str_replace( $placeholder, $value, $template);
        }
        return $template;
    }
    
    private create_file_from_template( $question_type ) {
        $template = file_get_contents( $template_path, "r");
        $placeholder_values = this->calculate_placeholder_values( $question_type );
        $template = this->replace_template_placeholders( $template, $placeholder_values );
        file_put_contents( $file_path . $file_name, $template );
    }
    
    public create_plugin( $question_type, $original_path ) {
        // create directories
        files = array(); // name, path
        foreach( $files as $file ) {
            this->create_file_from_template( $question_type );
        }
    }
    
}
?>
