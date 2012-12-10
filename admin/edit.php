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
 * On this page administrator can add or edit page hints
 * @package   local_page_hints
 * @copyright 2012 Burton and South Derbyshire College (http://moodle.com)
 * @author    Mark Ward
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('forms.php');

$hintid = optional_param('id', 0, PARAM_INT);

//page definition
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/local/page_hints/admin/edit.php');
$PAGE->set_pagetype('local_page_hint-new');
$PAGE->set_heading(get_string('pluginname', 'local_page_hints'));

if($hintid > 0){
    $dynamicheading = get_string('edit', 'local_page_hints');
    $hint = $DB->get_record('local_page_hints_instances', array('id'=>$hintid));
}
else{
    $dynamicheading = get_string('addnew', 'local_page_hints');
    $hint = new stdclass;
    $hint->id = 0;
    $hint->enabled = 1;
    $hint->positionx = '50%';
    $hint->positiony = '50%';
    $hint->anchorx = 'left';
    $hint->anchory = 'right';
    $hint->sizex = '190px';
    $hint->sizey = '150px';
    $hint->onsessions = 0;
    $hint->theme = '';
    $hint->pageid = 'page';
    $hint->pageclass = 'path';
    $hint->displayfrom = 0;
    $hint->displayuntil = 1;
    $hint->time = 0;
    $hint->sequence = 0;
    $hint->forguests = 0;
    $hint->editoronly = 0;
    $hint->editingonly = 0;
    $hint->lang = '';
    $hint->header = '';
    $hint->body = get_string('defaultbody', 'local_page_hints');
    $hint->footer = get_string('defaultfooter', 'local_page_hints');
}
$PAGE->set_title(get_string('pluginname', 'local_page_hints').' | '.$dynamicheading);

$newhintform = new page_hint_form(null, $hint);
if($newhintform->is_cancelled()){
    redirect($CFG->wwwroot.'/local/page_hints/admin/manage.php');
}
$fromform = $newhintform->get_data();
if (!empty($fromform) and confirm_sesskey()) {
    //clean the input up ready for the DB
    $fromform->positionx = $fromform->positionx.$fromform->posunits;
    $fromform->positiony = $fromform->positiony.$fromform->posunits;
    unset($fromform->posunits);
    $fromform->sizex = $fromform->sizex.$fromform->sizeunits;
    $fromform->sizey = $fromform->sizey.$fromform->sizeunits;
    unset($fromform->sizeunits);
    if($fromform->id == 0){
        unset($fromform->id);
    }
    if (!isset($fromform->onsessions)){
        $fromform->onsessions = 0;
    }
    if (!isset($fromform->forguests)){
        $fromform->forguests = 0;
    }
    if (!isset($fromform->editoronly)){
        $fromform->editoronly = 0;
    }
    if (!isset($fromform->editingonly)){
        $fromform->editingonly = 0;
    }
    if($fromform->theme == 'none'){
        $fromform->theme = null;
    }
    if($fromform->lang == 'none'){
        $fromform->lang = null;
    }
    $fromform->displayfrom--;
    $fromform->displayuntil--;
    
   
    if(isset($fromform->id)){
        $DB->update_record('local_page_hints_instances',$fromform);
    }
    else{
        $DB->insert_record('local_page_hints_instances',$fromform);
    }
    redirect($CFG->wwwroot.'/local/page_hints/admin/manage.php');
    
}
else{
    echo $OUTPUT->header();
    echo html_writer::tag('h2', $dynamicheading, array('class' => 'main'));
    $jsmodule = array(
        'name'     => 'local_page_hints_demo',
        'requires' => array('base','node', 'anim'),
        'fullpath' => '/local/page_hints/javascript/demo.js'
    );
    $PAGE->requires->js_init_call('M.local_page_hints_demo.init', array(), false, $jsmodule);
    $newhintform->display();
}
echo $OUTPUT->footer();

