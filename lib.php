<?php
global $DB;
if($DB->record_exists('config_plugins', array('plugin'=>'local_page_hints'))){
    $skip = optional_param('annoskip', null, PARAM_TEXT);
    global $CFG, $PAGE, $USER;
    $hits = local_page_hints_hit();
    $html = html_writer::start_tag('div', array('id' => 'local_page_hints'));
    if($PAGE->pagetype == 'local_page_hint-new'){
		$html .= local_page_hints_print_demo();
    }
    if($hits){
		$hints = array();
	
        //loop 1 builds the note objects inc info on sequence
        foreach ($hits as $hit){
			if(!isset($hints[$hit->id])){
				$hints[$hit->id] = new stdclass();
			}
			$hints[$hit->id]->id = $hit->id;
			$hints[$hit->id]->content = $hit;
			$hints[$hit->id]->follows = $hit->sequence;
			$hints[$hit->id]->shown = 0;
			$hints[$hit->id]->delay = $hit->time * 1000;
			if(!isset($hints[$hit->id]->triggers)){
				$hints[$hit->id]->triggers = array();
			}
			
			if($hit->sequence > 0)
			{
				if(!isset($hints[$hit->sequence]))
				{
					$hints[$hit->sequence] = new stdclass();
					$hints[$hit->sequence]->triggers = array();
				}
				$hints[$hit->sequence]->triggers[] = $hit->id;
			}		
        }
		
        //loop 2 prints out the notes
        foreach ($hints as &$hint){
            if($skip !== $hint->id){
                $print = local_page_hints_print($hint->content,($hint->follows > 0));
                if($print){
                    $html .= $print.PHP_EOL;
                }
                else{
                    //if we arent printing this we need to take it out of the sequence
                    unset($hint);
                }
            }
        }
		unset($hint);
        
    }
    $html .= html_writer::end_tag('div');
	$CFG->additionalhtmltopofbody .= $html;
    if(isset($hints) && is_array($hints) && count($hints)){
		$hintarray = array(); //a true array that JS will be happy with.
		foreach($hints as $hint){
			$hintarray[] = $hint;
		}
	
		$PAGE->requires->yui_module('moodle-local_page_hints-pagehints', 'M.local.pagehints',
            array(array(
                'hints' => $hintarray,
            )));
    }
}

function local_page_hints_print_demo(){
	global $OUTPUT;
	$html = html_writer::start_tag('div', array('id' => 'loc_anno_demo', 'class'=>'page_hint', 'style'=>'width:200px;min-height:100px;'));
	
	$html .= html_writer::start_tag('div', array('class'=>'header'));
	$html .= html_writer::empty_tag('img', array('class'=>'icon', 'src'=>$OUTPUT->pix_url('icon', 'local_page_hints'), 'alt'=>get_string('pluginname', 'local_page_hints')));
	$html .= html_writer::tag('h3', '');
	$html .= html_writer::end_tag('div');
	
	$html .= html_writer::start_tag('div', array('class'=>'body'));
	$html .= html_writer::tag('p', get_string('defaultbody', 'local_page_hints'));
	$html .= html_writer::end_tag('div');

	$html .= html_writer::start_tag('div', array('class'=>'footer'));
	$html .= html_writer::tag('p', get_string('defaultfooter', 'local_page_hints'));
	$html .= html_writer::end_tag('div');
	
	$html .= html_writer::end_tag('div');
	return $html;
}

//This function checks to find any page_hints which should be displayed
function local_page_hints_hit(){
    global $PAGE, $DB, $USER;
    $output = '';	
    //start by building a few parts of the WHERE specific to what's on our page
    
    $sql = 'SELECT * from {local_page_hints_instances}'.PHP_EOL
          .'WHERE ';
	$attr = array();
    //page editor?
    if($PAGE->user_allowed_editing()){
        //editing now?
        if(!$PAGE->user_is_editing()){
            $sql .= 'editingonly = 0 AND'.PHP_EOL;
        }
    }
    else{
        $sql .= 'editoronly = 0 AND '.PHP_EOL;;
    }
    
    //language
    if(isloggedin()){
        $sql .= "(lang IS NULL OR lang LIKE ?) AND".PHP_EOL;
		$attr[] = $USER->lang;
    }
    
    //guest?
    if(!isloggedin() || isguestuser()){
        $sql .= 'forguests = 1 AND'.PHP_EOL;
    }
    
    //theme
    $sql .= "(theme IS NULL OR theme LIKE ?) AND".PHP_EOL;
	$attr[] = $PAGE->theme->name;

    $sql .= "? LIKE concat('%', pageid, '%') AND".PHP_EOL;
	$attr[] = $PAGE->bodyid;
		  
		  
    $sql .= "? LIKE concat('%', pageclass, '%') AND".PHP_EOL;
	$attr[] = $PAGE->bodyclasses;
	
    $sql .= 'enabled = 1';
    //debugging($sql);
    $hits = $DB->get_records_sql($sql, $attr);
    if(count($hits) > 0){
        //debugging("hit!");
        return $hits;
    }
    else{
        //debugging("no hit :(");
        return false;
    }
}

function local_page_hints_get_style($hit){
    if(!isset($hit->sizex) || !isset($hit->sizey) || !isset($hit->positionx) || !isset($hit->positiony)){
        return false;
    }
    else{
        return 'width:'.$hit->sizex.';min-height:'.$hit->sizey.';'.$hit->anchorx.':'.$hit->positionx.';'.$hit->anchory.':'.$hit->positiony;
    }
}

//how many times has this been seen before
//also, update the tracker
function local_page_hint_track($userid,$note,$optout = 0){
    if(isguestuser()){
        $timesseen = 0;
    }
    
    if(!is_numeric($note->id) || !is_numeric($userid)){
        $timesseen = false;
    }
    
    global $DB;
    $record = $DB->get_record('local_page_hints_tracker', array('noteid'=>$note->id, 'userid'=>$userid));
    if(!$record){
        $record = new stdclass;
        $record->noteid = $note->id;
        $record->userid = $userid;
        $record->hits = 0;
        $record->optout = $optout;
        if($note->onsessions){
            $record->lastsession = sesskey();
        }
        $DB->insert_record('local_page_hints_tracker', $record);
        $timesseen = 0;
    }
    else{
        if((!$note->onsessions || $record->lastsession != sesskey()) && $record->optout < 1){
            $record->hits++;
            //no need to update forever
            if($record->hits < $note->displayuntil + 5 && $record->optout < 1){
                $record->optout = $optout;
                $record->lastsession = sesskey();
                $DB->update_record('local_page_hints_tracker', $record);
            }
            $timesseen = $record->hits;
        }
        else{
            //already seen this session
            $timesseen = false;
        }
    }
       
    if($timesseen!== false && $timesseen >= $note->displayfrom && $timesseen < $note->displayuntil){
        return true;
    }
    else{
        return false;
    }
}

function local_page_hints_print($hit, $insequence = false){
    global $USER, $OUTPUT;
    if(local_page_hint_track($USER->id,$hit)){
        $skipurl = new moodle_url('', array('annoskip'=>$hit->id));
    
        if($insequence){
            $class = 'page_hint insequence';
        }
        else{
            $class = 'page_hint';
        }
		
        $style =  local_page_hints_get_style($hit);
		
		$html = html_writer::start_tag('div', array('id' => 'loc_anno_'.$hit->id, 'class'=>$class, 'style'=>$style));
		
		$html .= html_writer::start_tag('div', array('class'=>'header'));
		$html .= html_writer::empty_tag('img', array('class'=>'icon', 'src'=>$OUTPUT->pix_url('icon', 'local_page_hints'), 'alt'=>get_string('pluginname', 'local_page_hints')));
		$html .= html_writer::tag('h3', $hit->header);
		$html .= html_writer::end_tag('div');
		
		$html .= html_writer::start_tag('div', array('class'=>'body'));
		$html .= html_writer::tag('p', $hit->body);
		$html .= html_writer::end_tag('div');

		$html .= html_writer::start_tag('div', array('class'=>'footer'));
		$html .= html_writer::tag('p', $hit->footer);
		$html .= html_writer::end_tag('div');

		$html .= html_writer::start_tag('div', array('class'=>'nojs'));
		$html .= html_writer::link($skipurl, get_string('dismiss','local_page_hints'));
		$html .= html_writer::end_tag('div');
				
		$html .= html_writer::end_tag('div');
		
		return $html;
    }
    else{
        return false;
    }
}
