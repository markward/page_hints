YUI.add('moodle-local_page_hints-pagehints',
    function(Y) {
        var CSS,
            PAGEHINTSNAME = 'local_page_hints-pagehints',
            PAGEHINTS,
            INITIALIZED = false;

        // The CSS selectors we use
        CSS = {
        };

        PAGEHINTS = function() {
            PAGEHINTS.superclass.constructor.apply(this, arguments);
        };

        Y.extend(PAGEHINTS, Y.Base, {

            /**
              * Initialize the module
              */
            initializer : function() {
				var printed = this.get('printed');
				var delays = this.get('delays');
				var triggers = this.get('triggers');
				var follows =  this.get('follows');
				
				var note_divs = []; // contains the DOM elements for each note
				var show_note = []; //contains the animations for opening and closing notes
				var shown = []; //needed to ensure items are only shown once

				Y.Array.each(printed, function (note_id) {
					shown[note_id] = 0;
					note_divs[note_id] = Y.one('#loc_anno_'+note_id);
					
					if(printed.indexOf(triggers[note_id]) == -1){
						this.open_note(note_id);
					}
				});  

				Y.Array.each(printed, function (note_id) {
					note_divs[note_id].on('click', function() {
						this.close_note(note_id);
					});
				});  
            },
			open_note : function(note_id) {
				if(shown[note_id] == 0){
					shown[note_id] = 1;

					show_note[note_id] = new Y.Anim({
						node: note_divs[note_id],
						to: { opacity: 1 },
						from: { opacity: 0 },
						duration: 0.5
					});

					if(printed.indexOf(triggers[note_id]) == -1){
						openNote(note_id);
					}
					
					note_divs[note_id].setStyle('display', 'block');
					show_note[note_id].run();
					setTimeout(function() {
						note_divs[note_id].addClass('visible');
					}, 475); //this makes IE8 fade in smoothly. 
					if(delays[note_id] > 0){
						setTimeout(function() {
							closeNote(note_id);
						}, delays[note_id]);
					}
				}
			},
			close_note : function(note_id) {
				show_note[note_id].reverse();
				show_note[note_id].on('end', function(){destroy();});
				setTimeout(function() {
					note_divs[note_id].hide();
					note_divs[note_id].removeClass('visible');
				}, 500);
				if(typeof follows == 'object'){
					if(typeof follows[note_id] == 'object'){
						Y.Array.each(follows[note_id], function (nextNote){
							if(nextNote>0){
								setTimeout(function() {
									openNote(nextNote);
								}, 500);
							}
						});
					}
				}
			}
        },
        {
            NAME : PAGEHINTS,
            ATTRS : { 
                printed : {
                    'value' : ''
                },
                delays : {
                    'value' : ''
                },
                triggers : {
                    'value' : ''
                },
                follows : {
                    'value' : ''
                }
            }
        });

        M.core = M.core || {};
        M.core.init_pagehints = M.core.init_pagehints || function(config) {
            return new PAGEHINTS(config);
        };
    },
    '@VERSION@', {
        requires : ['base', 'event-key']
    }
);
