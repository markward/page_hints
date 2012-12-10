M.local_page_hints = {};

M.local_page_hints.init = function (Y,printed,delays,triggers,follows){
    var noteDivs = []; // contains the DOM elements for each note
    var hideNote = []; // contains the animations for closing notes
    var showNote = []; //contains the animations for opening notes
    var shown = []; //needed to ensure items are only shown once
    
    openNote = function (noteID){
        if(shown[noteID] == 0){
            shown[noteID] = 1;
            noteDivs[noteID].setStyle('display', 'block');
            showNote[noteID].run();
            setTimeout(function() {
                noteDivs[noteID].addClass('visible');
            }, 475); //this makes IE8 fade in smoothly. 
            if(delays[noteID] > 0){
                setTimeout(function() {
                    closeNote(noteID);
                }, delays[noteID]);
            }
        }
    };
    
    closeNote = function (noteID){
        hideNote[noteID].run();
        setTimeout(function() {
            noteDivs[noteID].hide();
            noteDivs[noteID].removeClass('visible');
        }, 500);
        if(typeof follows == 'object'){
            if(typeof follows[noteID] == 'object'){
                Y.Array.each(follows[noteID], function (nextNote){
                    if(nextNote>0){
                        setTimeout(function() {
                            openNote(nextNote);
                        }, 500);
                    }
                });
            }
        }
    };
    
    Y.Array.each(printed, function (noteID) {
        shown[noteID] = 0;
        noteDivs[noteID] = Y.one('#loc_anno_'+noteID);
        
        hideNote[noteID] = new Y.Anim({
            node: noteDivs[noteID],
            to: { opacity: 0 },
            duration: 0.5
        });

        showNote[noteID] = new Y.Anim({
            node: noteDivs[noteID],
            to: { opacity: 1 },
            duration: 0.5
        });
        if(printed.indexOf(triggers[noteID]) == -1){
            openNote(noteID);
        }
        
    });  
    
    Y.Array.each(printed, function (noteID) {
        noteDivs[noteID].on('click', function() {
            closeNote(noteID);
        });
    });  
};
