<?php
/*
 * Encapsulated data of a phase in the review cycle
 *
 * @var     integer         $phase_nr           phase number
 * @var     integer         $num_reviewers      reviewer count in the phase
 * @var     integer         $review_obj         id of the calling review object
 * @var     ilDB            $db                 reference to the ILIAS database
 * @var     ilReviewDBMapper    $mapper         reference to the mapper
 */
class ilCyclePhase {
    private $phase_nr;
    private $num_reviewers;
    private $review_obj;
    private $db;
    private $mapper;

    /*
     * Constructor
     */
    public function __construct(
        $db,
        $mapper,
        $phase_nr = "",
        $num_reviewers = "",
        $review_obj = ""
    ) {
        $this->db = $db;
        $this->mapper = $mapper;
        $this->phase_nr = $phase_nr;
        $this->num_reviewers = $num_reviewers;
        $this->review_obj = $review_obj;
    }

    /*
     * Load the data of a phase object from the database
     *
     * @param   integer     $phase_nr           phase number
     * @param   integer     $review_obj         review obj
     *
     * @return  boolean     $success            true: operation performed
     */
    public function loadFromDB($phase_nr, $review_obj) {
        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_alloc "
            . "WHERE phase = %s AND review_obj = %s",
            array("integer", "integer"),
            array($phase_nr, $review_obj)
        );
        $num_reviewers = reset(
            $this->mapper->getCyclePhases(array($phase_nr => $phase_nr))
        );
        if ($result->numRows() >= $num_reviewers) {
            while ($record = $this->db->fetchObject($result)) {
                $this->phase_nr = $record->phase;
                $this->author = $record->author;
                $this->review_obj = $record->review_obj;
                $this->reviewers[] = $record->reviewer;
            }
            return true;
        }
        return false;
    }

    /*
     * Store the data of a phase object to the database
     *
     * @return  boolean     $success            true: operation performed
     */
    public function storeToDB() {
        if ($this->phase_nr == "" || $this->review_obj == "") {
            return false;
        }

        $result = $this->db->queryF(
            "SELECT * FROM rep_robj_xrev_phases "
            . "WHERE phase = %s AND review_obj = %s",
            array("integer", "integer"),
            array($this->phase_nr, $this->review_obj)
        );
        if ($result->numRows() == 0) {
            $this->db->insert(
                "rep_robj_xrev_phases",
                array(
                    "phase" => array("integer", $this->phase_nr),
                    "nr_reviewers" => array("integer", $this->num_reviewers),
                    "review_obj" => array("integer", $this->review_obj)
                )
            );
        } else {
            $this->db->update(
                "rep_robj_xrev_phases",
                array(
                    "nr_reviewers" => array("integer", $this->num_reviewers)
                ),
                array(
                    "phase" => array("integer", $this->phase_nr),
                    "review_obj" => array("integer", $this->review_obj)
                )
            );
        }
        $this->mapper->notifyAboutChanges("cycle_phases");
        return true;
    }

    /*
     * Delete a cycle phase object from the database
     *
     * @return  boolean     $success            true: operation performed
     */
    public function deleteFromDB() {
        if ($this->phase_nr == "" || $this->review_obj == "") {
            return false;
        }
        $this->db->manipulateF(
            "DELETE FROM rep_robj_xrev_phases "
            . "WHERE phase = %s AND review_obj = %s",
            array("integer", "integer"),
            array($this->phase_nr, $this->review_obj)
        );
        $this->mapper->notifyAboutChanges("cycle_phases");
        return true;
    }

    /*
     * Set the number of reviewers in the phase
     *
     * @param   integer     $num_reviewers      number of reviewers in the
     * phase
     */
    public function setNumReviewers($num_reviewers) {
        $this->num_reviewers = $num_reviewers;
    }

    /*
     * Get the phase number
     *
     * @return  integer     $phase_nr           phase number
     */
    public function getPhaseNr() {
        return $this->phase_nr;
    }

    /*
     * Get the number of reviewers in the phase
     *
     * @return  integer     $num_reviewers      number of reviewers in the
     * phase
     */
    public function getNumReviewers() {
        return $this->num_reviewers;
    }

    /*
     * Get the review object
     *
     * @return  integer     $review_obj         review object
     */
    public function getReviewObj() {
        return $this->review_obj;
    }
}
?>
