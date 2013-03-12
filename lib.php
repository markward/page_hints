<?php
global $DB;
if($DB->record_exists('config_plugins', array('plugin'=>'local_page_hints'))){
    $skip = optional_param('annoskip', null, PARAM_TEXT);
    global $CFG, $PAGE, $USER;
    $hits = local_page_hints_hit($skip);
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
               $html .= local_page_hints_print($hint->content,($hint->follows > 0)).PHP_EOL;
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
                'hints' => $hints,
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

/**
 * Checks to find any page_hints which should be displayed and updates
 * the tracker table for this user.
 *
 * @return array    The hints which should be displayed on a page
 */
function local_page_hints_hit($optout = 0){
    global $PAGE, $DB, $USER;
    $output = '';	
    //*1st step is to find all relevant hints for this page using a complex SQL statement
    
    $sql = 'SELECT phi.*, pht.id as trackerid, pht.hits as trackerhits '.PHP_EOL
		  .'FROM {local_page_hints_instances} phi'.PHP_EOL
		  .'LEFT JOIN {local_page_hints_tracker} pht'.PHP_EOL
		  .'ON pht.noteid = phi.id'.PHP_EOL
		  .'AND pht.userid = ?'.PHP_EOL
          .'WHERE ';
	$attr = array($USER->id);
    //page editor?
    if($PAGE->user_allowed_editing()){
        //editing now?
        if(!$PAGE->user_is_editing()){
            $sql .= 'phi.editingonly = 0 AND'.PHP_EOL;
        }
    }
    else{
        $sql .= 'phi.editoronly = 0 AND '.PHP_EOL;;
    }
    
    //language
    if(isloggedin()){
        $sql .= "(phi.lang IS NULL OR phi.lang LIKE ?) AND".PHP_EOL;
		$attr[] = $USER->lang;
    }
    
    //guest?
    if(!isloggedin() || isguestuser()){
        $sql .= 'phi.forguests = 1 AND'.PHP_EOL;
    }
    
    //theme
    $sql .= "(phi.theme IS NULL OR phi.theme LIKE ?) AND".PHP_EOL;
	$attr[] = $PAGE->theme->name;

    $sql .= "? LIKE concat('%', phi.pageid, '%') AND".PHP_EOL;
	$attr[] = $PAGE->bodyid;
		  
		  
    $sql .= "? LIKE concat('%', phi.pageclass, '%') AND".PHP_EOL;
	$attr[] = $PAGE->bodyclasses;

    $sql .= "COALESCE(pht.lastsession,0) NOT LIKE ? AND".PHP_EOL
           .'phi.enabled = 1';
	$attr[] = sesskey();
	
    //debugging($sql);
    $hints = $DB->get_records_sql($sql, $attr);
	
    //*2nd step is to loop through these, update the tracker, and filter out notes which are out of display limits
	if(count($hints)>0){
		foreach($hints as $key => $hint){
			if($hint->trackerhits === null){
				//debugging('this hint hasnt been seen before');
				$record = new stdclass;
				$record->noteid = $hint->id;
				$record->userid = $USER->id;
				$record->hits = 1;
				$record->optout = false;
				if($optout == $hint->id){
					$record->optout = true;
				}
				$record->lastsession = 0;
				if($hint->onsessions){
					$record->lastsession = sesskey();
				}
				$DB->insert_record('local_page_hints_tracker', $record);
				$hint->trackerhits = 0;
			}
			else{
				$DB->set_field('local_page_hints_tracker', 'hits', ($hint->trackerhits + 1), array('id'=>$hint->trackerid));
			}
			//should we actually be displaying this? if not then remove the row.
			if($hint->trackerhits < $hint->displayfrom || $hint->trackerhits >= $hint->displayuntil){
				unset($hints[$key]);
			}
		}
		//one final loop needed to ensure the sequence is still valid
		foreach($hints as $key => $hint){
			if(!isset($hints[$hint->sequence])){
				$hints[$key]->sequence = 0;
			}
		}
	}
	
    if(count($hints) > 0){
        //debugging("hints!");
        return $hints;
    }
    else{
        //debugging("no hint :(");
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

function local_page_hints_print($hit, $insequence = false){
    global $USER, $OUTPUT;

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
