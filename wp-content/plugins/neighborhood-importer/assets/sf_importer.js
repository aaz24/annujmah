jQuery(document).ready(function(){

		//Close Black Overlay 
		jQuery('#sf_import_close').click(function(e){		
			jQuery('.sf-modal-notice , .sf-black-overlay').hide();
		});
		
		jQuery('.demoimp-button').click(function(e){
										
				demoid = jQuery(this).attr('data-demoid');
				var progressbar = jQuery('#progressbar')
				var p = 0, top_limit = 0, progress_increment = 0, files_count = 0;	
				var menus_once = true;				
				var last_process = false;
				var democontent = false;
			
			
			    //Neighborhood Full Demo
			    if ( demoid == 0 ){
                     progress_increment = 4;
                     top_limit = 96;	
                     files_count = 26;									  	  	 
                }
                                        	 
                //Neighborhood Alternative Demo
                if ( demoid == 1   ){
                   	 progress_increment = 10;
					 top_limit = 90;
					 files_count = 10;
				}
				
				
				//Check if it's to Import Demo Content
				if ( jQuery('#democontent'+demoid).is(':checked') ){
					    democontent = true;
					   	data_url_options = 'import';	
						jQuery('.sf-progress-bar-wrapper').show();
                        for( var i=1; i < files_count; i++ ){
                                
                             var str;
                             str = 'neighborhood-demo' + demoid + '-'+i+'.xml.gz';
                                 
                             jQuery.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    retryLimit : 3,
    				    tryCount : 0,
                                    data: {
                                        action: 'sf_import_content',
                                        xml: str,
                                        demo: demoid     
                                    }, 
                                    success: function(data, textStatus, XMLHttpRequest){
                                           // Uncomment to debug results                            
                                             console.log(XMLHttpRequest);
                                             
                                      	    if ( p + progress_increment < 100 )
										  	    p+= progress_increment;  	  
										  	    
										  	if ( p >= top_limit)
                                           		last_process = true;
                                      
                                        	jQuery('.progress-value').html((p) + '%');
                                        	progressbar.val(p);
                                        	
                                        	if ( last_process && menus_once) {
                                        		menus_once = false;
                                        	                                        	
                                            	jQuery.ajax({
                                                	type: 'POST',
                                                	url: ajaxurl,
                                                	data: {
                                                    	action: 'sf_import_content',
                                                    	xml: '',
                                                    	demo: demoid,
                                                    	menus: true    
                                                	},
                                                	success: function(data, textStatus, XMLHttpRequest){
	                                                    p= 100;
    	                                                jQuery('.progress-value').html((p) + '%');
        	                                            progressbar.val(p);
            	                                        jQuery('.progress-bar-message').html('<div class="alert alert-success"><strong>Demo Content Imported.</strong></div>');
                	                                    jQuery('#sf_import_close').show();
                    	                            },
                        	                        error: function(MLHttpRequest, textStatus, errorThrown){
                                                	
                            	                    }
                                	            });
                                    	    }
                                    	},
                                    	error: function(MLHttpRequest, textStatus, errorThrown){
                                    	
                                    	         this.tryCount++;
                                                 if (this.tryCount <= this.retryLimit) {
                                                    //try again
                                                    jQuery.ajax(this);
                                                    return;
                                                 }       
                                    	 	
                                    	}
                                	});
                            	}
                        
					}
				
				//Check if it's to Import Theme Options	
				if ( jQuery('#themeoption'+demoid).is(':checked') ){
					 
					 	data_url_options = 'import';
						 jQuery.ajax({
                                type: 'POST',
                                url: ajaxurl,
                                data: {
                                    action: 'sf_import_options',
                                    demo: demoid                   
                                    },
                                success: function(data, textStatus, XMLHttpRequest){
                                	console.log(XMLHttpRequest);
                               					 jQuery('#sf_import_close').before('<div class="alert alert-success"><strong>Theme Options Imported.</strong></div>');
                                   				 if ( !democontent) 
                                   				 	jQuery('#sf_import_close').show();
                                         },
                                error: function(MLHttpRequest, textStatus, errorThrown){
                                                	
                                       }
                        });
				}
				
				//Check if it's to Import Colors		
				if ( jQuery('#coloroption'+demoid).is(':checked') ){
					    data_url_options = 'import';
					    jQuery.ajax({
                               type: 'POST',
                               url: ajaxurl,
                               data: {
                                   action: 'sf_import_colors',
                                   demo: demoid
                                   },
                               success: function(data, textStatus, XMLHttpRequest){
                                              
                                                 jQuery('#sf_import_close').before('<div class="alert alert-success"><strong>Colors Imported.</strong></div>');
                                                 if ( !democontent) 
                                   				 	jQuery('#sf_import_close').show();
                               },
                               error: function(MLHttpRequest, textStatus, errorThrown){
                                                	
                                      }
                        });
				}
				
				//Check if it's to Import Widgets			
				if ( jQuery('#widgetsoption'+demoid).is(':checked') ){
					 data_url_options = 'import';
					 jQuery.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: {
                                action: 'sf_import_widgets',
                                demo: demoid
                                },
                            success: function(data, textStatus, XMLHttpRequest){
                           					  jQuery('#sf_import_close').before('<div class="alert alert-success"><strong>Widgets Imported.</strong></div>');
                               				  if ( !democontent) 
                                   				 	jQuery('#sf_import_close').show();
                                },
                            error: function(MLHttpRequest, textStatus, errorThrown){
                                                	
                                   }
                     });
				}
					
				//Check if any option was chosed 		
				if ( data_url_options == '' ){
					alert("Please check one of the options.");
				}
				else {
					
					jQuery('.sf-modal-notice , .sf-black-overlay').show();
					importElement = jQuery(this);
					jQuery('#sf_import_start').attr('data-url', importElement.attr('data-url')+data_url_options);
							
				}
						
					
		});
				
});
		