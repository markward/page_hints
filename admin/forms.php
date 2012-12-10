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
 * add new, edit and delete forms for page hints
 * @package   local_page_hints
 * @copyright 2012 Burton and South Derbyshire College (http://moodle.com)
 * @author    Mark Ward
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . '/formslib.php');

/**
 * This form display registration form to Moodle.org
 * TODO: this form had some none hidden inputs originally, it has been changed since this time
 *       delete this Moodle form for a renderer with a single button
 */
class page_hint_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $SITE;

        $hint = $this->_customdata;
        //separate units from positions and sizes
        $hint->posunits = preg_replace('/^[0-9\\.\\s]+/', '', $hint->positionx);
        $hint->positionx =(int)$hint->positionx;
        $hint->positiony =(int)$hint->positiony;
        $hint->sizeunits = preg_replace('/^[0-9\\.\\s]+/', '', $hint->sizex);
        $hint->sizex =(int)$hint->sizex;
        $hint->sizey =(int)$hint->sizey;
        //displayfrom and until are incremeneted by one to make it human readable
        $hint->displayfrom++;
        $hint->displayuntil++;
        //switch theme and lang from '' to 'none'
        if($hint->theme == '')$hint->theme='none';
        if($hint->lang == '')$hint->lang='none';
        
        $mform = & $this->_form;
        $mform->addElement('header', 'hint_topsection',
                get_string('addnew', 'local_page_hints'));
        $mform->addElement('static', 'comment', '',
                get_string('addnewdescription', 'local_page_hints'));
                
        //id and enabled
        $mform->addElement('hidden', 'id',
                get_string('id', 'local_page_hints'), '');
        $mform->setDefault('id', $hint->id);
        $mform->addElement('checkbox', 'enabled',
                get_string('enabled', 'local_page_hints'), '');
        $mform->setDefault('enabled', $hint->enabled);
        
        //positioning
        $mform->addElement('header', 'hint_positioning',
            get_string('position', 'local_page_hints'));
        
        $mform->addElement('text', 'positionx',
                get_string('positionx', 'local_page_hints'));        
        $mform->addRule('positionx', get_string('numeric', 'local_page_hints'),'numeric',null,'client');
        $mform->setDefault('positionx', $hint->positionx);
        $mform->addHelpButton('positionx', 'positionx', 'local_page_hints');
        $mform->addRule('positionx', get_string('required', 'local_page_hints'),'required',null,'client');
        
        $mform->addElement('text', 'positiony',
                get_string('positiony', 'local_page_hints'));        
        $mform->addRule('positiony', get_string('numeric', 'local_page_hints'),'numeric',null,'client');
        $mform->setDefault('positiony', $hint->positiony);
        $mform->addHelpButton('positiony', 'positiony', 'local_page_hints');
        $mform->addRule('positiony', get_string('required', 'local_page_hints'),'required',null,'client');
        
        $units = array('px' => 'px', 'em' => 'em', '%' => '%');
        $mform->addElement('select', 'posunits',
            get_string('posunits', 'local_page_hints'), $units);
        $mform->setDefault('posunits', $hint->posunits);
        $mform->addHelpButton('posunits', 'posunits', 'local_page_hints');
        $anchorsx = array('left' => 'left', 'right' => 'right');
        $mform->setAdvanced('posunits');
        
        $mform->addElement('select', 'anchorx',
                get_string('anchorx', 'local_page_hints'), $anchorsx);
        $mform->setDefault('anchorx', $hint->anchorx);
        $mform->addHelpButton('anchorx', 'anchorx', 'local_page_hints');
        $anchorsy = array('top' => 'top', 'bottom' => 'bottom');
        $mform->setAdvanced('anchorx');
        
        $mform->addElement('select', 'anchory',
                get_string('anchory', 'local_page_hints'), $anchorsy);
        $mform->setDefault('anchory', $hint->anchory);
        $mform->addHelpButton('anchory', 'anchory', 'local_page_hints');
        $mform->setAdvanced('anchory');
        
        //sizing      
        $mform->addElement('header', 'hint_sizing',
            get_string('size', 'local_page_hints'));
        
        $mform->addElement('text', 'sizex',
                get_string('sizex', 'local_page_hints'));        
        $mform->addRule('sizex', get_string('numeric', 'local_page_hints'),'numeric',null,'client');
        $mform->setDefault('sizex', $hint->sizex);
        $mform->addRule('sizex', get_string('required', 'local_page_hints'),'required',null,'client');
        
        $mform->addElement('text', 'sizey',
                get_string('sizey', 'local_page_hints'));        
        $mform->addRule('sizey', get_string('numeric', 'local_page_hints'),'numeric',null,'client');
        $mform->setDefault('sizey', $hint->sizey);   
        $mform->addRule('sizey', get_string('required', 'local_page_hints'),'required',null,'client');
        
        $mform->addElement('select', 'sizeunits',
                get_string('sizeunits', 'local_page_hints'), $units);
        $mform->setDefault('sizeunits', $hint->sizeunits);
        $mform->addHelpButton('sizeunits', 'sizeunits', 'local_page_hints');
        $mform->setAdvanced('sizeunits');
        
        //contents
        $mform->addElement('header', 'hint_contents',
            get_string('contents', 'local_page_hints'));
        
        $mform->addElement('text', 'header',
                get_string('header', 'local_page_hints'));        
        $mform->setDefault('header', $hint->header);
        
        $mform->addElement('textarea', 'body',
                get_string('body', 'local_page_hints'),'wrap="virtual" rows="5" cols="70"');        
        $mform->setDefault('body', $hint->body);
        $mform->addRule('body', get_string('required', 'local_page_hints'),'required',null,'client');
        
        $mform->addElement('text', 'footer',
                get_string('footer', 'local_page_hints'));        
        $mform->setDefault('footer', $hint->footer);
        
        //pagefilter
        $mform->addElement('header', 'hint_filters',
            get_string('pagefilter', 'local_page_hints'));
        
        $mform->addElement('text', 'pageid',
                get_string('pageid', 'local_page_hints'));        
        $mform->setDefault('pageid', $hint->pageid);
        $mform->addRule('pageid', get_string('required', 'local_page_hints'),'required',null,'client');
        $mform->addHelpButton('pageid', 'pageid', 'local_page_hints');
        
        $mform->addElement('text', 'pageclass',
                get_string('pageclass', 'local_page_hints'));        
        $mform->setDefault('pageclass', $hint->pageclass);
        $mform->addRule('pageclass', get_string('required', 'local_page_hints'),'required',null,'client');
        $mform->addHelpButton('pageclass', 'pageclass', 'local_page_hints');
        
        $mform->addElement('checkbox', 'onsessions',
                get_string('onsessions', 'local_page_hints'), '');
        $mform->setDefault('onsessions', $hint->onsessions);
        $mform->addHelpButton('onsessions', 'onsessions', 'local_page_hints');
        
        $themesflipped = array_keys(get_plugin_list("theme"));
        $themes = array_flip($themesflipped);
        foreach ($themes as $key=>$theme){
            $themes[$key] = $themesflipped[$theme];
        }
        $themes['none'] = 'none';
        $mform->addElement('select', 'theme',
                get_string('theme', 'local_page_hints'), $themes);
        $mform->setDefault('theme', $hint->theme);
        $mform->addHelpButton('theme', 'theme', 'local_page_hints');
        $mform->setAdvanced('theme');
        
        $mform->addElement('checkbox', 'forguests',
                get_string('forguests', 'local_page_hints'), '');
        $mform->setDefault('forguests', $hint->forguests);
        $mform->addHelpButton('forguests', 'forguests', 'local_page_hints');
        $mform->setAdvanced('forguests');
        
        $mform->addElement('checkbox', 'editoronly',
                get_string('editoronly', 'local_page_hints'), '');
        $mform->setDefault('editoronly', $hint->editoronly);
        $mform->addHelpButton('editoronly', 'editoronly', 'local_page_hints');
        $mform->setAdvanced('editoronly');
        
        $mform->addElement('checkbox', 'editingonly',
                get_string('editingonly', 'local_page_hints'), '');
        $mform->setDefault('editingonly', $hint->editingonly);
        $mform->addHelpButton('editingonly', 'editingonly', 'local_page_hints');
        $mform->setAdvanced('editingonly');
        
        $langsflipped = array_keys(get_string_manager()->get_list_of_translations());
        $langs = array_flip($langsflipped);
        foreach ($langs as $key=>$lang){
            $langs[$key] = $langsflipped[$lang];
        }
        $langs['none'] = 'none';
        $mform->addElement('select', 'lang',
                get_string('lang', 'local_page_hints'), $langs);
        $mform->setDefault('lang', $hint->lang);
        $mform->addHelpButton('lang', 'lang', 'local_page_hints');
        $mform->setAdvanced('lang');
        
        //timing
        $mform->addElement('header', 'hint_timing',
        get_string('timing', 'local_page_hints'));

        $mform->addElement('text', 'displayfrom',
            get_string('displayfrom', 'local_page_hints'));        
        $mform->addRule('displayfrom', get_string('numeric', 'local_page_hints'),'numeric',null,'client');
        $mform->setDefault('displayfrom', $hint->displayfrom);
        $mform->addHelpButton('displayfrom', 'displayfrom', 'local_page_hints');
        
        $mform->addElement('text', 'displayuntil',
            get_string('displayuntil', 'local_page_hints'));        
        $mform->addRule('displayuntil', get_string('numeric', 'local_page_hints'),'numeric',null,'client');
        $mform->setDefault('displayuntil', $hint->displayuntil);
        $mform->addHelpButton('displayuntil', 'displayuntil', 'local_page_hints');
       
       $mform->addElement('duration', 'time',
            get_string('time', 'local_page_hints'), array('optional' => true, 'defaultunit' => 1));        
        $mform->setDefault('time', $hint->time);
        $mform->addHelpButton('time', 'time', 'local_page_hints');
        $mform->setAdvanced('time');
        
        $hints = $DB->get_records_menu('local_page_hints_instances', null, null, ('id, body'));
        foreach($hints as $key => $hintinstance){
            $hints[$key] = substr($hintinstance,0,80);
        }
        $hints[0] = 'none';
        if(isset($hint->id)){
            unset($hints[$hint->id]);
        }
        $mform->addElement('select', 'sequence',
                get_string('sequence', 'local_page_hints'), $hints);
        $mform->setDefault('sequence', $hint->sequence);
        $mform->addHelpButton('sequence', 'sequence', 'local_page_hints');
        $mform->setAdvanced('sequence');
        
        $this->add_action_buttons(true);
    }
    
    /**
     * Validate fields
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
class page_hint_delete extends moodleform {

    public function definition() {
        global $CFG, $DB, $SITE;

        $hint = $this->_customdata;
        
        $mform = & $this->_form;
        $mform->addElement('header', 'hint_topsection',
                get_string('delete', 'local_page_hints'));
        $mform->addElement('static', 'comment', '',
                get_string('deletedescription', 'local_page_hints'));
           
        $mform->addElement('hidden', 'id',
                get_string('id', 'local_page_hints'), '');
        $mform->setDefault('id', $hint->id);
        $this->add_action_buttons(true, get_string('delete'));
    }
    
    /**
     * Validate fields
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}