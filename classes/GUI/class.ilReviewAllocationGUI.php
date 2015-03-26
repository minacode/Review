<?php
/*
+-----------------------------------------------------------------------------+
| ILIAS open source                                                           |
+-----------------------------------------------------------------------------+
| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
|                                                                             |
| This program is free software; you can redistribute it and/or               |
| modify it under the terms of the GNU General Public License                 |
| as published by the Free Software Foundation; either version 2              |
| of the License, or (at your option) any later version.                      |
|                                                                             |
| This program is distributed in the hope that it will be useful,             |
| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
| GNU General Public License for more details.                                |
|                                                                             |
| You should have received a copy of the GNU General Public License           |
| along with this program; if not, write to the Free Software                 |
| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
+-----------------------------------------------------------------------------+
 */

/*
 * GUI to EIHTER _perform_ OR _configure_ the allocation of reviews
 * TODO make this clear, otherwise implementation cannot start
 *
 * @var     array       $reviewers      reviewer names and IDs
 *
 * @author Richard Mörbitz <Richard.Moerbitz@mailbox.tu-dresden.de>
 *
 * $Id$
 */
class ilReviewAllocationGUI extends ilPropertyFormGUI/ilCustomInputGUI {
    /* TODO
     * make clear if single lines or whole boxes shall be repeated dynamically
     * this determines the actual parent class
     */
    private $reviewers;

    /*
     * Constructor for a review allocation GUI (allocation checkbox matrix)
     *
     * @param       array       $reviewers      reviewer names and ids
     */
    public function __construct($reviewers) {
        parent::__construct;
        $this->reviewers = $reviewers;
        /* TODO set basic data and a caption with the reviewer names */
        /* TODO instanciate all the ilCheckMatrixRowGUIs */
    }
}
?>
