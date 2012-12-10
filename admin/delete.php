<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * On this page administrator can delete page hints
 * @package   local_page_hint
 * @copyright 2012 Burton and South Derbyshire College (http://moodle.com)
 * @author    Mark Ward
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('forms.php');

//page definition
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/local/page_hints/admin/delete.php');
$PAGE->set_pagetype('local_page_hint-delete');
$PAGE->set_title(get_string('pluginname', 'local_page_hints').' | '.get_string('delete', 'local_page_hints'));
$PAGE->set_heading(get_string('pluginname', 'local_page_hints'));

$hintid = required_param('id', PARAM_INT);

if($hintid > 0){
    $hint = $DB->get_record('local_page_hints_instances', array('id'=>$hintid));
    $newhintform = new page_hint_delete(null, $hint);
    $fromform = $newhintform->get_data();
    if($newhintform->is_cancelled()){
        redirect($CFG->wwwroot.'/local/page_hints/admin/manage.php');
    }
    if (!empty($fromform) and confirm_sesskey()) {
        if($fromform->id > 0){
            $DB->delete_records('local_page_hints_instances',array('id'=>$fromform->id));
            redirect($CFG->wwwroot.'/local/page_hints/admin/manage.php');
        }
    }
    else{
        echo $OUTPUT->header();
        echo html_writer::tag('h2', get_string('addnew', 'local_page_hints'), array('class' => 'main'));
        $newhintform->display();
    }
}
echo $OUTPUT->footer();

