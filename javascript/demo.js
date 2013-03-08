// Add ECMA262-5 Array methods if not supported natively
//
if (!('indexOf' in Array.prototype)) {
    Array.prototype.indexOf= function(find, i /*opt*/) {
        if (i===undefined) i= 0;
        if (i<0) i+= this.length;
        if (i<0) i= 0;
        for (var n= this.length; i<n; i++)
            if (i in this && this[i]===find)
                return i;
        return -1;
    };
}


M.local_page_hints_demo = {};

M.local_page_hints_demo.init = function (Y){
    noteDiv = Y.one('#loc_anno_demo');
    noteDiv.setStyle('display', 'block');
    noteDiv.setStyle('opacity', '1');
    noteDiv.setStyle('pointer-events', 'none');

    //set up animations
    fadeOut = new Y.Anim({
        node: noteDiv,
        to: { opacity: 0 },
        duration: 0.1
    });
    
    fadeIn = new Y.Anim({
        node: noteDiv,
        to: { opacity: 1 },
        duration: 0.2
    });
    
    //get the DOM objects for our inputs
    positionx = Y.one('#id_positionx');
    positiony = Y.one('#id_positiony');
    posunits = Y.one('#id_posunits');
    anchorx = Y.one('#id_anchorx');
    anchory = Y.one('#id_anchory');
    sizex = Y.one('#id_sizex');
    sizey = Y.one('#id_sizey');
    sizeunits = Y.one('#id_sizeunits');
    header = Y.one('#id_header');
    body = Y.one('#id_body');
    footer = Y.one('#id_footer');
    hintTitle = Y.one('#loc_anno_demo > .header > h3');
    hintBody = Y.one('#loc_anno_demo > .body > p');
    hintFooter = Y.one('#loc_anno_demo > .footer > p');
    
    //get the values of the annotation.
    positionxValue = positionx.get('value');
    positionyValue = positiony.get('value');
    posunitsValue = posunits.get('value');
    anchorxValue = anchorx.get('value');
    anchoryValue = anchory.get('value');
    sizexValue = sizex.get('value');
    sizeyValue = sizey.get('value');
    sizeunitsValue = sizeunits.get('value');
    headerValue = header.get('value');
    bodyValue = body.get('value');
    footerValue = footer.get('value');

    //initial setup
    noteDiv.setStyle(anchorxValue, positionxValue+posunitsValue);
    noteDiv.setStyle(anchoryValue, positionyValue+posunitsValue);
    noteDiv.setStyle('width', sizexValue+sizeunitsValue);
    noteDiv.setStyle('min-height', sizeyValue+sizeunitsValue);
    hintTitle.set('innerHTML', headerValue);
    hintBody.set('innerHTML', bodyValue);
    hintFooter.set('innerHTML', footerValue);
    
    //set the events on which to modify stuff
    positionx.on('blur', function() {
        if (positionxValue != positionx.get('value')){
            positionxValue = positionx.get('value');
            fadeOut.run();
            fadeOut.on('end', function() {
                noteDiv.setStyle(anchorxValue, positionxValue+posunitsValue);
                fadeIn.run();
            });
        }
    });
    positiony.on('blur', function() {
        if (positionyValue != positiony.get('value')){
            positionyValue = positiony.get('value');
            fadeOut.run();
            fadeOut.on('end', function() {
                noteDiv.setStyle(anchoryValue, positionyValue+posunitsValue);
                fadeIn.run();
            });
        }
    });
    posunits.on('blur', function() {
        if (posunitsValue != posunits.get('value')){
            posunitsValue = posunits.get('value');
            fadeOut.run();
            fadeOut.on('end', function() {
                noteDiv.setStyle(anchorxValue, positionxValue+posunitsValue);
                noteDiv.setStyle(anchoryValue, positionyValue+posunitsValue);
                fadeIn.run();
            });
        }
    });
    anchorx.on('blur', function() {
        if (anchorxValue != anchorx.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                noteDiv.setStyle(anchorxValue, 'auto');
                anchorxValue = anchorx.get('value');
                noteDiv.setStyle(anchorxValue, positionxValue+posunitsValue);
                fadeIn.run();
            });
        }
    });
    anchory.on('blur', function() {
        if (anchoryValue != anchory.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                noteDiv.setStyle(anchoryValue, 'auto');
                anchoryValue = anchory.get('value');
                noteDiv.setStyle(anchoryValue, positionyValue+posunitsValue);
                fadeIn.run();
            });
        }
    });
    sizex.on('blur', function() {
        if (sizexValue != sizex.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                sizexValue = sizex.get('value');
                noteDiv.setStyle('width', sizexValue+sizeunitsValue);
                fadeIn.run();
            });
        }
    });
    sizey.on('blur', function() {
        if (sizeyValue != sizey.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                sizeyValue = sizey.get('value');
                noteDiv.setStyle('min-height', sizeyValue+sizeunitsValue);
                fadeIn.run();
            });
        }
    });
    sizeunits.on('blur', function() {
        if (sizeunitsValue != sizeunits.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                sizeunitsValue = sizeunits.get('value');
                noteDiv.setStyle('width', sizexValue+sizeunitsValue);
                noteDiv.setStyle('min-height', sizeyValue+sizeunitsValue);
                fadeIn.run();
            });
        }
    });
    header.on('blur', function() {
        if (headerValue != header.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                headerValue = header.get('value');
                hintTitle.set('innerHTML',headerValue);
                fadeIn.run();
            });
        }
    });
    body.on('blur', function() {
        if (bodyValue != body.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                bodyValue = body.get('value');
                hintBody.set('innerHTML',bodyValue);
                fadeIn.run();
            });
        }
    });
    footer.on('blur', function() {
        if (footerValue != footer.get('value')){
            fadeOut.run();
            fadeOut.on('end', function() {
                footerValue = footer.get('value');
                hintFooter.set('innerHTML',footerValue);
                fadeIn.run();
            });
        }
    });
};
