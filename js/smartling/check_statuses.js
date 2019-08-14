var CheckStatuses = Class.create();

CheckStatuses.prototype = {
    
    initialize: function() {
        var nested = this;
        setTimeout(function(){
            nested.links = [];
            nested.pushLinks();
            nested.runRequest();
        },50);
    },

    pushLinks: function() {
        
        var downloadLinks = $$('a.download');
        for(i = 0; i < downloadLinks.length; i++) {
         
         link = downloadLinks[i].href.replace('/download/','/checkstatus/');
         var contentId = link.match(/content_id\/(\d+)\//i);
         
          var obj = new Object();
          obj.id = contentId[1];
          obj.link  = link;

         this.links.push(obj);
       }
       
       
    },
    
    runRequest: function() {

          for(i = 0; i < this.links.length; i++) {
              
              new Ajax.Request(this.links[i].link,
                {
                    method: 'POST',
                    parameters: {},
                    evalScripts: true,
                    loaderArea: false,
                    onSuccess: function(transport) {
                        try {
                            
                            if (transport.responseText.isJSON()) {
                                var response = transport.responseText.evalJSON();

                                if(response.error == 1) {
                                    this.logFail(response.message);
                                } else {
                                    this.setPercent(response.element_id, response.percent);
                                }
                                this.stopLoader(response.element_id);

                            } else {
                                this.logFail('Smartling check status: Received format is not JSON.');
                            }
                        } catch (e) {
                            this.logFail(e);
                        }
                    }.bind(this)
                }
              );
              
          }
      
    },
    
    logFail: function(message) {
        if (window.console) {
            window.console.log(message);
        }
    },
    
    stopLoader: function(element_id) {
        $('status_wraper_' + element_id).removeClassName('bar-loading');
    },
    
    setPercent: function(element_id, percent) {
        if(this.is_numeric(element_id) && this.is_numeric(percent)) {
            $('percent_' + element_id).setStyle({ display:  "block", width: percent + '%'});
            $('percent_content_' + element_id).update(percent + '%');
        }
    },
    
    is_numeric: function( mixed_var ) {
        return ( mixed_var == '' ) ? false : !isNaN( mixed_var );
    }
}

