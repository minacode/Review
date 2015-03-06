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
    
    public create_file_from_template( $template_path, $file_path,  $file_name ) {
        $template = file_get_contents( $template_path, "r");
        $template = this->replace_template_placeholders( $template );
        file_put_contents( $file_path . $file_name, $template );
    }
    
    private replace_template_placeholders( $template ) {
        // calculate values for placeholders
        $placeholder_values = array(
            "key" => "value"
        );
        foreach ($placeholder_values as $placeholder => $value) {
            $template = str_replace( $placeholder, $value, $template);
        }
        return $template;
    }
}
?>
