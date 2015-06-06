<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");

/*
 * Review repository object plugin
 *
 * @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
 * @version $Id$
 *
 */
class ilReviewPlugin extends ilRepositoryObjectPlugin {

	/*
	 * Get the plugin name
	 *
	 * @param		string		$_			name of the plugin
	 */
	function getPluginName() {
		return "Review";
	}
}
?>
