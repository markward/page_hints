<?php
global $DB;
if($DB->record_exists('config_plugins', array('plugin'=>'local_page_hints'))){
    $skip = optional_param('annoskip', null, PARAM_TEXT);
    global $CFG, $PAGE, $USER, $OUTPUT;
    $hits = local_page_hints_hit();
    $printed = array();
    $triggers = array();
    $follows = array();
    $CFG->additionalhtmltopofbody .= '<div style="" id="local_page_hints">'.PHP_EOL;
    if($PAGE->pagetype == 'local_page_hint-new'){
        $CFG->additionalhtmltopofbody .= '<div style="width:200px;min-height:100px;" class="page_hint" id="loc_anno_demo">'.PHP_EOL
        .'<div class="header"><img class="icon" src="'.$OUTPUT->pix_url('icon', 'local_page_hints').'" alt="'.get_string('pluginname', 'local_page_hints').'"/><h3></h3></div>'.PHP_EOL
        .'<div class="body"><p>'.get_string('defaultbody', 'local_page_hints').'</p></div>'.PHP_EOL
        .'<div class="footer"><p>'.get_string('defaultfooter', 'local_page_hints').'</p></div>'.PHP_EOL
        .'</div>'.PHP_EOL;
    }
    if($hits){
        //loop one establishes any notes which need to be displayed in sequence
        foreach ($hits as $hit){
            $triggers[$hit->id]=$hit->sequence;
        }
        //loop two prints out the notes
        foreach ($hits as $hit){
            if($skip !== $hit->id){
                if(array_key_exists($hit->id, $triggers)){
                    $insequence = true;
                }
                else{
                    $insequence = false;
                }
                $print = local_page_hints_print($hit,$insequence);
                if($print){
                    $CFG->additionalhtmltopofbody .= $print.PHP_EOL;
                    $printed[] = $hit->id;
                    $delays[$hit->id] = $hit->time * 1000;
                }
                else{
                    //if we arent printing this we need to take it out of the sequence
                    unset($triggers[$hit->id]);
                }
            }
        }
        $follows = array();
        foreach($triggers as $child=> $parent){
            $follows[$parent][] = $child;
            //debugging('Parent '.$parent.' spawns '.$child);
        }
        
    }
    $CFG->additionalhtmltopofbody .= '</div>'.PHP_EOL;

    if(count($printed) > 0){
        $jsmodule = array(
            'name'     => 'local_page_hints_lib',
            'requires' => array('base','node', 'anim'),
            'fullpath' => '/local/page_hints/javascript/lib.js'
        );
        $PAGE->requires->js_init_call('M.local_page_hints.init', array($printed, $delays, $triggers, $follows), false, $jsmodule);
    }
}

//This function checks to find any page_hints which should be displayed
function local_page_hints_hit(){
    global $PAGE, $DB, $USER;
    $output = '';	
    //start by building a few parts of the WHERE specific to what's on our page
    
    //page editor?
    if($PAGE->user_allowed_editing()){
        $editoronly = '';
        //editing now?
        if($PAGE->user_is_editing()){
            $editingonly = '';
        }
        else{
            $editingonly = 'editingonly = 0 AND'.PHP_EOL;
        }
    }
    else{
        $editoronly = 'editoronly = 0 AND'.PHP_EOL;
        $editingonly = '';
    }
    
    //language
    if(isloggedin()){
        $lang = '(lang IS NULL OR lang LIKE "%'.$USER->lang .'%") AND'.PHP_EOL;
    }
    else{
        $lang = '';
    }
    
    //guest?
    if(!isloggedin() || isguestuser()){
        $guest = 'forguests = 1 AND'.PHP_EOL;
    }
    else{
        $guest = '';
    }
    
    //theme
    $theme = '(theme IS NULL OR theme LIKE "'.$PAGE->theme->name.'") AND'.PHP_EOL;

    $sql = 'SELECT * from {local_page_hints_instances}'.PHP_EOL
          .'WHERE '.$editoronly
          .$editingonly
          .$lang
          .$guest
          .$theme
          .'"'.$PAGE->bodyid.'" LIKE concat("%", pageid, "%") AND'.PHP_EOL
          .'"'.$PAGE->bodyclasses.'" LIKE concat("%", pageclass, "%") AND'.PHP_EOL
          .'enabled = 1';
    //debugging($sql);
    $hits = $DB->get_records_sql($sql);
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
    global $PAGE, $USER, $OUTPUT;
    if(local_page_hint_track($USER->id,$hit)){
        if(strstr($PAGE->url,'?')){
            $skipurl = $PAGE->url.'&annoskip='.$hit->id;
        }
        else{
            $skipurl = $PAGE->url.'?annoskip='.$hit->id;
        }
    
        if($insequence){
            $class = 'page_hint insequence';
        }
        else{
            $class = 'page_hint';
        }
        $style =  local_page_hints_get_style($hit);
        return '<div style="'.$style.'" class="'.$class.'" id="loc_anno_'.$hit->id.'">'.PHP_EOL
              .'<div class="header"><img class="icon" src="'.$OUTPUT->pix_url('icon', 'local_page_hints').'" alt="'.get_string('pluginname', 'local_page_hints').'"/><h3>'.$hit->header.'</h3></div>'.PHP_EOL
              .'<div class="body"><p>'.$hit->body.'</p></div>'.PHP_EOL
              .'<div class="footer"><p>'.$hit->footer.'</p></div>'.PHP_EOL
              .'<div class="nojs"><a href="'.$skipurl.'">'.get_string('dismiss','local_page_hints').'</a></div>'.PHP_EOL
              .'</div>';
    }
    else{
        return false;
    }
}
