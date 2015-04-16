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


include_once('Services/Table/classes/class.ilTable2GUI.php');

/**
* Table GUI to generate reviewable question types
*
* @author Max Friedrich <Max.Friedrich@tu-dresden.de>
*
* $Id$
*/

class ilGenerateQuestionTypesGUI extends ilTable2GUI {

    /**
    * Constructor, configures GUI output
    *
    * @param    object  $a_parent_obj   GUI object that contains this object
    * @param    string  $a_parent_cmd   Command that causes construction of this object
    * @param        array       $question_types associative arrays of displayed data (column => value)
    */
    public function __construct($a_parent_obj, $a_parent_cmd, $question_types) {
        global $ilCtrl, $lng;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->setTitle($lng->txt('rep_robj_xrev_non_reviewable_question_types'));
        
        $this->addColumn($lng->txt('rep_robj_xrev_question_type_name'));
        $this->setEnableHeader(true);

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj, 'generateQuestionPlugins'));
        
        $this->setRowTemplate(
            'tpl.generate_question_types_table_row.html', 
            ilPlugin::getPluginObject(IL_COMP_SERVICE, 
                'Repository', 
                'robj', 
                'Review'
            )->getDirectory()
        );
        
        $this->setSelectAllCheckbox('question_type_name');
        $this->addCommandButton('generateQuestionTypes' , $this->lng->txt('generate'));
        
        $ilCtrl->saveParameterByClass('ilObjReviewGUI', array('question_type_name'));
        
        $data = array();
        
        foreach($question_types as $question_type)
            $data[] = array('question_type_name' => $question_type);
        
        $this->setLimit(100);
        
        $this->fillData($data);
    }

    private function fillData($question_types) {
        $this->setDefaultOrderField('question_type_name');
        $this->setDefaultOrderDirection('asc');
        
        $this->setData($question_types);
    }

    /*
    * Fill a single data row
    *
    * @param    array   $a_set  Data record, displayed as one table row (key => value)
    */
    protected function fillRow($a_set) {
        global $ilCtrl, $lng;
        
        $ilCtrl->saveParameterByClass('ilObjReviewGUI', array('question_type_name'));
        $ilCtrl->setParameterByClass('ilObjReviewGUI', 'name', $a_set['question_type_name']);
        $this->tpl->setVariable('CB_QUESTION_TYPE_NAME', $a_set['question_type_name']);
    }
}
?>
