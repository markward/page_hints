YUI.add('moodle-local_page_hints-pagehints',
    function(Y) {
        var CSS;

        // The CSS selectors we use
        CSS = {
        };

        function PAGEHINTS(config) {
            PAGEHINTS.superclass.constructor.apply(this, [config]);
        }

        PAGEHINTS.NAME = 'local_page_hints-pagehints';

        Y.extend(PAGEHINTS, Y.Base, {
            // Contains the hint objects from PHP
            hints: {},	

            /**
              * Initialize the module
              */
            initializer : function() {
                this.hints = this.get('hints');
				var id;
				for (id in this.hints) {
					if (!this.hints.hasOwnProperty(id)) {
						continue;
					}
					if (this.hints[id].follows == 0) {
						this.hints[id].shown = 0;

						if(this.hints[id].follows == 0){
							this.open_note(id);
						}
					}
				}
            },
            open_note : function(id) {
                if(this.hints[id].shown === 0){
                    this.hints[id].shown = 1;
					
                    this.hints[id].div = Y.one('#loc_anno_'+id);
					
                    this.hints[id].anim = new Y.Anim({
                        node: this.hints[id].div,
                        to: { opacity: 1 },
                        from: { opacity: 0 },
                        duration: 0.5
                    });
					
                    this.hints[id].div.on('click', function() {
                        this.close_note(id);
                    }, this);

                    this.hints[id].div.setStyle('display', 'block');
                    this.hints[id].anim.run();
                    Y.later(475, this, function() {
                        // This makes IE8 fade in smoothly.
                        this.hints[id].div.addClass('visible');
                    });
                    if(this.hints[id].delay > 0){
                        Y.later(this.hints[id].delay, this, function() {
                            this.close_note(id);
                        });
                    }
                }
            },
            close_note : function(id) {
                // Reverse the animation
                this.hints[id].anim.set('reverse', true);
                this.hints[id].anim.run();

                // Destroy the animation when it finishes running
                this.hints[id].anim.on('end', function(){
                    this.destroy();
                });

                Y.later(500, this, function() {
                    this.hints[id].div.hide();
                    this.hints[id].div.removeClass('visible');
                });

                Y.Array.each(this.hints[id].triggers, function (tid) {
                    if(typeof this.hints[tid] === 'object'){
						Y.later(500, this, function() {
							this.open_note(tid);
						});
                    }
				}, this);
            }
        },
        {
            NAME : PAGEHINTS,
            ATTRS : {
                hints : {
                    'value' : '',
                }
            }
        });

        M.local = M.local || {};
        M.local.pagehints = M.local.pagehints || function(config) {
            return new PAGEHINTS(config);
        };
    },
    '@VERSION@', {
        requires : ['base', 'event-key', 'anim']
    }
);
