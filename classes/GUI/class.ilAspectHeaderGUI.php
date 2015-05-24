<?php
/*
 * GUI showing multiple captions in a single line
 *
 * @var     array       $captions   captions (right part)
 */
class ilAspectHeaderGUI extends ilCustomInputGUI {
    private $captions;

    /*
     * Constructor
     */
    public function __construct($title, $captions) {
        global $tpl;

        parent::__construct();
        $this->captions = $captions;
        $this->setTitle($title);
        $tpl->addCss(
            'Customizing/global/plugins/Services/Repository/RepositoryObject/'
            . 'Review/templates/default/css/Review.css'
        );
        $path_to_il_tpl = ilPlugin::getPluginObject(
            IL_COMP_SERVICE,
            'Repository',
            'robj',
            'Review'
        )->getDirectory();
        $custom_tpl = new ilTemplate(
            "tpl.aspect_row.html",
            true,
            true,
            $path_to_il_tpl
        );
        foreach ($this->captions as $caption) {
            $label = new ilNonEditableValueGUI("");
            $label->setValue($caption);
            $label->insert($custom_tpl);
        }
        $this->setHTML($custom_tpl->get());
    }
}
?>
