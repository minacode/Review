<?php
/*
 * Database abstraction class for access of ilDB
 *
 * @var     ilDB        $db         local reference to the ILIAS database
 */
class ilReviewDBMapper {
    private $db;

    /*
     * Constructor
     */
    public function __construct() {
        global $ilDB;

        $this->db = $ilDB;
    }
}
?>
