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
 * manage site-wide instances of page hints
 * @package   local_page_hints
 * @copyright 2012 Burton and South Derbyshire College (http://moodle.com)
 * @author    Mark Ward
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('forms.php');

//page definition
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
$PAGE->set_url('/local/page_hints/admin/manage.php');
$PAGE->set_pagetype('local_page_hint-manage');
$PAGE->set_title(get_string('pluginname', 'local_page_hints').' | '.get_string('manage', 'local_page_hints'));
$PAGE->set_heading(get_string('pluginname', 'local_page_hints'));

$hintid = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 0, PARAM_INT);
if(is_numeric($action) && $hintid > 0){
    switch ($action){
        case 0:
            //toggle enabled
            $hint = $DB->get_record('local_page_hints_instances', array('id'=>$hintid));
            $hint->enabled = 1 - $hint->enabled;
            $DB->update_record('local_page_hints_instances', $hint);
            break;
        case 1:
            //clone
            $hint = $DB->get_record('local_page_hints_instances', array('id'=>$hintid));
            unset($hint->id);
            $hint->enabled = 0;
            $hint->body .= ' *'.get_string('cloned', 'local_page_hints').'* ';
            $DB->insert_record('local_page_hints_instances', $hint);
            break;
    }
}

echo $OUTPUT->header();

$instances = $DB->get_records('local_page_hints_instances');
echo html_writer::tag('h2', get_string('manage', 'local_page_hints'), array('class' => 'main'));
echo html_writer::start_tag('div', array('class' => 'box generalbox'));
echo html_writer::start_tag('table', array('class' => 'generaltable', 'id'=>'hintinstances','style' => 'width:100%;'));
echo html_writer::start_tag('thead');
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('id', 'local_page_hints'));
echo html_writer::tag('th', get_string('body', 'local_page_hints'));
echo html_writer::tag('th', get_string('pageid', 'local_page_hints'));
echo html_writer::tag('th', get_string('pageclass', 'local_page_hints'));
echo html_writer::tag('th', get_string('fromto', 'local_page_hints'));
echo html_writer::tag('th', get_string('options', 'local_page_hints'));
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thead');
echo html_writer::start_tag('tbody');
$parity = 0;
foreach ($instances as $key => $instance){
    $instance->displayfrom++;
    $instance->displayuntil++;
    echo html_writer::start_tag('tr', array('class' => 'r'.$parity));
    echo html_writer::tag('td', $instance->id);
    echo html_writer::tag('td', substr($instance->body,0,80).'...');
    echo html_writer::tag('td', $instance->pageid);
    echo html_writer::tag('td', $instance->pageclass);
    echo html_writer::tag('td', $instance->displayfrom.' / '.$instance->displayuntil);
    
    $commands = html_writer::link(new moodle_url('edit.php', array('id'=>$instance->id)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit', 'core'), 'title'=>get_string('edit', 'core'),'alt'=>get_string('edit', 'core'))));
    $commands .= html_writer::link(new moodle_url('delete.php', array('id'=>$instance->id)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete', 'core'), 'title'=>get_string('delete', 'core'),'alt'=>get_string('delete', 'core'))));
    if($instance->enabled){
        $commands .= html_writer::link(new moodle_url('manage.php', array('id'=>$instance->id, 'action' => 0)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide', 'core'), 'title'=>get_string('hide', 'core'),'alt'=>get_string('hide', 'core'))));
    }
    else{
        $commands .= html_writer::link(new moodle_url('manage.php', array('id'=>$instance->id, 'action' => 0)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show', 'core'), 'title'=>get_string('show', 'core'),'alt'=>get_string('show', 'core'))));
    }
    $commands .= html_writer::link(new moodle_url('manage.php', array('id'=>$instance->id, 'action' => 1)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/copy', 'core'), 'title'=>get_string('copy', 'core'),'alt'=>get_string('copy', 'core'))));
    
    echo html_writer::tag('td', $commands);
    echo html_writer::end_tag('tr');
    $parity = 1 - $parity;
}
echo html_writer::end_tag('table');
echo html_writer::link(new moodle_url('edit.php'), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/add', 'core'), 'title'=>get_string('edit', 'core'),'alt'=>get_string('edit', 'core'))).get_string('addnew', 'local_page_hints'));
echo html_writer::end_tag('div');
//display footer
echo $OUTPUT->footer();