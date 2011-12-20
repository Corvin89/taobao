/**
 * @package HeadSpace
 * @author John Godley
 * @copyright Copyright (C) John Godley
 **/

/*
============================================================================================================
This software is provided "as is" and any express or implied warranties, including, but not limited to, the
implied warranties of merchantibility and fitness for a particular purpose are disclaimed. In no event shall
the copyright owner or contributors be liable for any direct, indirect, incidental, special, exemplary, or
consequential damages (including, but not limited to, procurement of substitute goods or services; loss of
use, data, or profits; or business interruption) however caused and on any theory of liability, whether in
contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of
this software, even if advised of the possibility of such damage.

For full license details see license.txt
============================================================================================================ */

var HeadSpace;

(function($) {
  $(document).ready( function() {
    if (jQuery('#suggestions').length > 0)
      jQuery('#suggestions').headspace();
      
  	  $( 'a[href=#toggle]' ).click( function(){
		    $( this ).parent().parent().find( '.toggle' ).toggle();
      	return false;
		  });
  });

  HeadSpace = function( args ) {
    var opts = $.extend({
      ajaxurl: '',
      nonce:   ''
    }, args);
    
  	function save_sort( e, ui ) {
			if ( $( ui.element ).attr( 'id' ) != 'disabled-modules' ) {
        $.post( opts.ajaxurl, {
          action:   'hs_module_order',
          _ajax_nonce: opts.nonce,
          simple:   $( '#simple-modules' ).sortable( 'serialize' ),
          advanced: $( '#advanced-modules' ).sortable( 'serialize' )
         });
      }
		}

    function page_settings() {
      $('.settings ul li a').unbind( 'click' );
      
      $('.settings ul li a').click( function( event ) {
        var item = $( this ).parents( 'li' );
        
        // Loading icon
    		item.find( '.option' ).show ();
    		
    		// Ajax
    		item.load( $( this ).attr( 'href' ), function() {
  		    // Advanced toggle
    		  $( 'a[href=#toggle]' ).click( function(){
    		    item.find( '.toggle' ).toggle();
          	return false;
    		  });
    		  
  		    // Cancel button
  		    item.find( 'input[name=cancel]' ).click( function(){
        		$('#loading').show ();
        		
        		item.load( item.find( 'form' ).attr( 'action' ).replace( 'hs_settings_save', 'hs_settings_load' ), {
        		    action:      'hs_settings_load',
        		    module:      item.find( 'input[name=module] ').val(),
        		    _ajax_nonce: item.find( 'input[name=_ajax_nonce]' ).val()
        		  },
        		  function( data ) {
        		    $('#loading').hide();
        		    item.html( data );
        		  
        		    // Reset event handlers
        		    page_settings();
        		  });
        		
        		return false;
        	 });

          // Form ajax
        	item.find( 'form' ).ajaxForm ({
        	  beforeSubmit: function() {
        	    $('#loading').show ()
        	  },
        	  success: function( data ) {
        	    $('#loading').hide ();
        	    item.html( data );
        	    page_settings()
        	  }
        	});
    		} );
    		
    		return false;
    	});
    }
    
    function page_modules() {
  		$('#simple-modules').sortable( 'destroy' ).sortable( { connectWith: ['#advanced-modules', '#disabled-modules'], opacity: 0.7, update: save_sort } );
  		$('#advanced-modules').sortable( 'destroy' ).sortable( { connectWith: ['#simple-modules', '#disabled-modules'], opacity: 0.7, update: save_sort } );
  		$('#disabled-modules').sortable( 'destroy' ).sortable( { connectWith: ['#simple-modules', '#advanced-modules'], opacity: 0.7, update: save_sort } );

      // Help toggles
      $( 'a.help' ).unbind( 'click' ).click(function() {
        $( this ).parents( 'li:first' ).find( 'div.help' ).toggle();
        return false;
      });
      
      // Handler for module edit buttons
      $( 'a.edit' ).unbind( 'click' ).click(function() {
  		  var item = $( this ).parents( 'li:first' );
  		  var url  = $( this ).attr( 'href' );
  		  
  		  // Ajax load the details
  		  item.load( url, function() {
  		    // Cancel button
  		    item.find( 'input[name=cancel]' ).click( function() {
  		      item.load( url.replace( 'hs_module_edit', 'hs_module_load' ), function() {
    		      page_modules();
  		      } );
  		      
  		      return false;
  		    });

  		    // Hook the form into jQuery
        	$( this ).find( 'form' ).ajaxForm ( {
        	  success: function( data ) {
        	    $( item ).html( data );
        	    page_modules();
        	  }
        	});
  		  });
  		  
  			return false;
  		});
    }
    
    function site_modules_enable() {
      // Enable/disable button
      $( 'li :checkbox' ).unbind( 'click' ).click(function() {
        var checked = this;
        var item    = $( this ).parents( 'li:first' );
        
        item.find( '.load' ).show();
        
        $.post( opts.ajaxurl, {
            action:      'hs_site_onoff',
            onoff:       $( this ).attr( 'checked' ),
            file:        $( this ).val(),
            module:      $( this ).attr( 'name' ).replace( /\w*\[(\w*)\]/, '$1' ),
            _ajax_nonce: opts.nonce
          }, function() {
            item.find( '.load' ).hide();
            
            if ( $( checked ).attr( 'checked' ) )
              $( item ).removeClass( 'disabled' );
            else
              $( item ).addClass( 'disabled' );
        });
      });
    }
    
    function site_modules() {
      // Edit button
      $( 'li .edit' ).unbind( 'click' ).click(function() {
        var item = $( this ).parents( 'li:first' );
  		  var url  = $( this ).attr( 'href' );

        item.find( '.load' ).show();
        
        // Ajax load
        $( item ).load( $( this ).attr( 'href' ), {
            action:      'hs_site_edit',
            _ajax_nonce: opts.nonce
          }, function() {
            // Hide loader
            site_modules_enable();
            item.find( '.load' ).hide();
            
            // Cancel button
    		    item.find( 'input[name=cancel]' ).click( function() {
    		      item.load( url.replace( 'hs_site_edit', 'hs_site_load' ), {
    		          _ajax_nonce: opts.nonce
    		        },
    		        function() {
      		        site_modules();
    		        });

    		      return false;
    		    });
             
            // Form handler
          	$( item ).find( 'form' ).ajaxForm ( {
          	  beforeSubmit: function () {
                item.find( '.load' ).show();
          	  },
          	  success: function( data ) {
          	    item.html( data );
          	    site_modules();
          	  }
          	});
          });

        return false;
      });
      
      site_modules_enable();
      
      // Help
      $( 'a.help' ).unbind( 'click' ).click( function() {
        $( this ).parents( 'li:first' ).find( 'div.help' ).toggle();
        return false;
      });
    }
    
    var api = {
      page_settings:    page_settings,
      page_modules:     page_modules,
      site_modules:     site_modules
  	};

  	return api;
  }

  $.fn.AutoDescription = function(args) {
 	  args = args || {};

  	return this.each(function() {
  	  var opts = $.extend({
  	  }, args);
  	  
      $( this ).click(function() {
        var post = this.href.replace( /(.*?)post=(\d*)(.*)/, '$2' );
        
			  $.post( this.href, {
	          content: $( opts.content ).val ()
				  },
			    function( data ) {
			      if ( post > 0 )
			        $( opts.target + post ).val( data );
			      else
			        $( opts.target ).val( data );
			    });
				
			    return false;
			  });
  	});
  }
  
  $.fn.AutoTag = function(args) {
 	  args = args || {};

  	return this.each(function() {
  	  var opts = $.extend({
  	  }, args);
  	  
      $( this ).click(function() {
        var post_id = this.href.replace( /(.*?)id=(\d*)(.*)/, '$2' );
        
			  $.get( this.href, {},
			    function( data ) {
            $( 'input[name="edit\[' + post_id + '\]"]' ).val( data );
			    });
			  
		    return false;
		  });
  	});
  }
  
  $.fn.AutoTitle = function(args) {
 	  args = args || {};

  	return this.each(function() {
  	  var opts = $.extend({
  	  }, args);
  	  
      $( this ).click(function() {
        $( this ).parents( 'tr:first' ).find( '.text' ).val( $( $( this ).attr( 'href' ) ).text() );
		    return false;
		  });
  	});
  }
  
  $.fn.Counter = function(args){
 	  args = args || {};

  	return this.each(function() {
  	  var text = this;
  	  
      function charCount( item ) {
    		var charLength = 0 + $( item ).val().length;

    		counter.html( ( opts.limit - charLength ) + ' ' + opts.remaining );
    		
    		if ( $( text ).val().length >= opts.limit )
    			counter.css( 'color', 'red' );
    		else
    			counter.css( 'color', 'black' );
    	}

  	  var opts = $.extend({
        limit:      100,
        remaining: 'remaining'
      }, args);
      
      // Add status counter
      $( this ).after( '<br /><span class="counter">' + (opts.limit - $( this ).val().length ) + ' ' + opts.remaining + '</span>' );
      
      var counter = $( this ).nextAll( '.counter' );

      if ( opts.limit - $( this ).val().length < 0 )
        counter.css( 'color', 'red' );
      
      // Hook into key up/down handlers
      if ( opts.limit > 0 ) {
        $( this ).keydown( function( event ) {
          charCount( this )
        }).keyup( function( event ) {
          charCount( this )
        });
      }
	  });
	}
})(jQuery);



function add_plugin ()
{
  var text = '<li>';
  text += '<div class="option"><a href="#" onclick="return delete_plugin(this);"><img src="' + headspace_delete + '" alt="delete" width="16" height="16"/></a></div>';
  text +=  document.getElementById('headspace_plugin').options[document.getElementById('headspace_plugin').selectedIndex].innerHTML;
  text += '<input type=\'hidden\' name=\'headspace_plugins[]\' value=\'' + jQuery('#headspace_plugin').val() + '\'/></li>';
  
  jQuery('#headspace_plugins').append (text);
  return false;
}

function delete_plugin (item)
{
	jQuery(item.parentNode.parentNode).remove ();
	return false;
}

function copy_keywords(source)
{
  if (jQuery('#tax-input\\[post_tag\\]').length > 0)
    jQuery(source).val(jQuery('#tax-input\\[post_tag\\]').val ());
  else
    jQuery(source).val(jQuery('#tags-input').val ());
}
