<?php
/*
 * GUI showing multiple ilSelectInputGUIs in a single line
 *
 * @var     array		$si_guis        displayed instances of ilSelectInputGUI
 */
class ilAspectSelectInputGUI extends ilCustomInputGUI {
	private $si_guis;

	/*
	 * Constructor for table-like display of ilSelectInputGUIs
	 */
	public function __construct($title, $si_guis) {
		parent::__construct();
		$this->setTitle($title);
        foreach ($si_guis as $si_gui) {
            $new_gui = new ilSelectInputGUI("", $si_gui["postvar"]);
            $new_gui->setOptions($si_gui["options"]);
            $new_gui->setValue($si_gui["value"]);
            $new_gui->setDisabled($si_gui["disabled"]);
            $this->si_guis[] = $new_gui;
        }
	}

	/*
     * Generate and return the HTML representation
     *
     * @return  string      $_              html string
	 */
	public function getHTML() {
		global $tpl;

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
		foreach ($this->si_guis as $si_gui) {
			$si_gui->insert($custom_tpl);
		}
		return $custom_tpl->get();
	}

	/*
	 * Set the value of the select input GUIs by a given array
	 *
	 * @param 	array		$a_value        $postvar => $value
	 */
	public function setValueByArray($a_value) {
		foreach ((array) $this->select_inputs as $postvar => $values)
			$this->select_inputs[$postvar]["selected"] = $a_value[$postvar];
	}

    /*
     * Check the input of the select input GUIs regarding default values
     *
     * @return  boolean     $valid          true, if value is not default
     */
    public function checkInput() {
        $valid = true;
        foreach ($this->si_guis as $si_gui) {
            $valid &&= !$si_gui->getDisabled()
                || $si_gui->checkInput() && $_POST[$si_gui->getPostVar() != 0;
        }
        return true;
    }
}
