<?php
/*
 * GUI, a row of checkboxes in the author - reviewer allocation matrix
 *
 * @var		array		$postvars		$_POST variables of each checkbox
 * @var     integer     $phase          id of the phase
 * @var		integer	    $author	        id of the author
 */
class ilAllocationRowGUI extends ilCustomInputGUI {
    private $checkboxes;
	private $postvars;
    private $phase;
	private $author;

	/*
	 * Constructor for a line in a table-like display of ilCheckboxInputGUIs
	 *
     * @param   integer     $phase          phase id
	 * @param	array		$author 		id of the author
	 * @param	array		$reviewers      reviewer id => allocated?
	 */
	public function __construct($phase, $author, $reviewers) {

		parent::__construct();

        $this->phase = $phase;
		$this->author = $author;
		$this->postvars = array();
		foreach ($reviewers as $reviewer => $allocated) {
            $this->postvars[$reviewer] = sprintf(
                "id_%s_%s_%s",
                $this->phase,
                $this->author,
                $reviewer
            );
			$chbox = new ilCheckboxInputGUI("", $postvar);
            $chbox->setChecked($allocated);
			if ($reviewer == $this->author) {
				$chbox->setDisabled(true);
            }
            $this->checkboxes[] = $chbox;
		}

		$this->setTitle(ilObject::_lookupTitle($author));

        $this->fillTemplate();
	}

    /*
     * fill the template with the given checkboxes
	 */
	private function fillTemplate() {
		global $tpl;
		$tpl->addCss('./Customizing/global/plugins/Services/Repository/RepositoryObject/Review/templates/default/css/Review.css');
		$path_to_il_tpl = ilPlugin::getPluginObject(IL_COMP_SERVICE, 'Repository', 'robj', 'Review')->getDirectory();
		$custom_tpl = new ilTemplate("tpl.matrix_row.html", true, true, $path_to_il_tpl);
        foreach ((array) $this->checkboxes as $chbox) {
			$chbox->insert($custom_tpl);
        }

		$this->setHTML($custom_tpl->get());
    }

    /*
	 * set the value of the checkboxes by a given array
	 *
	 * @param 	array		$a_values		associative array of ($postvar => $value)
	 */
	public function setValueByArray($a_value) {
        foreach ((array) $this->checkboxes as $chbox) {
            if (isset($a_value[$chbox->getPostVar()])) {
                $chbox->setChecked(true);
            }
        }
		$this->fillTemplate();
	}

    /*
     * get the number of selected checkboxes
     *
     * @return      integer         $ticks          number of selected boxes
     */
    public function getTickCount() {
        $ticks = 0;
        foreach ($this->checkboxes as $chbox) {
            if ($_POST[$chbox->getPostvar()]) {
                $ticks += 1;
            }
        }
        return $ticks;
    }

    /*
     * Get the phase number
     *
     * @return  string      $phase             phase number
     */
    public function getPhase() {
        return $this->phase;
    }

   /*
    * Get the author id
    *
    * @return  string      $author             author id
    */
    public function getAuthor() {
        return $this->author;
    }

   /*
    * Get the POST variables
    *
    * @return  string      $postvars           POST variables
    */
    public function getPostVars() {
        return $this->postvars;
    }
}
?>
