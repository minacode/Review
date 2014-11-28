<?php

include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
 
/**
* Review repository object plugin
*
* @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
* @version $Id$
*
*/
class ilReviewPlugin extends ilRepositoryObjectPlugin {
	
	function getPluginName() {
		return "Review";
	}
}
?>
