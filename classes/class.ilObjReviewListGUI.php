<?php

include_once "./Services/Repository/classes/class.ilObjectPluginListGUI.php";

/*
 * ListGUI implementation for Review object plugin.
 *
 * @author 		Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
 */
class ilObjReviewListGUI extends ilObjectPluginListGUI {

	/*
	 * Init type
	 */
	function initType() {
		$this->setType("xrev");
	}

	/*
	 * Get name of gui class handling the commands
	 *
	 * @return	string		$_			name of the GUI class
	 */
	function getGuiClass() {
		return "ilObjReviewGUI";
	}

	/*
	 * Get commands
	 *
	 * @return	array		$_			associative arrays of commands
	 */
	function initCommands() {
		return array(
			array(
				"permission" => "read",
				"cmd" => "showContent",
				"default" => true),
			array(
				"permission" => "write",
				"cmd" => "editProperties",
				"txt" => $this->txt("edit"),
				"default" => false),
		);
	}

	/*
	 * Get item properties
	 *
	 * @return	array		array of property arrays:
	 *						"alert" (boolean) => display as an alert property
	 *						"property" (string) => property name
	 *						"value" (string) => property value
	 */
	function getProperties() {
		global $lng, $ilUser;

		$props = array();
		$this->plugin->includeClass("class.ilObjReviewAccess.php");
		if (!ilObjReviewAccess::checkOnline($this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $this->txt("status"),
                "value" => $this->txt("offline")
            );
		}
		return $props;
	}
}
?>
