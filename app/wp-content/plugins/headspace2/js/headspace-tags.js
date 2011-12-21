(function($) {
	$.headspace = function(input, options) {
		var thing;

    // Attach HeadSpace tags to WP area (different in 2.8 than <2.8)
    if ($('#tagsdiv').length == 0)
      $(input).appendTo('#tagsdiv-post_tag .inside').show ();
    else
      $(input).appendTo('#tagsdiv .inside').show ();

    if ($('.headspace-tags').length == 1)
      $('.headspace-tags').keyup (function() { highlight_tags () });

    refresh ();
    highlight_tags ();

    function refresh () {
      // Toggle single tag
      jQuery('#suggested_tags a').click(function() {
         var element  = get_tag_element();
         var existing = $(element).val ().toLowerCase ().split(',');
         var word     = $(this).text ().toLowerCase ();

         if ($.inArray (word, existing) === -1) {
           // Add it to the list
           $(element).val (($(element).val() ? ($(element).val()+ ',') : '') + word);
         }
         else {
           // Remove it
           existing.splice ($.inArray (word, existing), 1);
           $(element).val (existing.join(','));
         }
         
         highlight_tags ();
         return false;
      });
      
      // Add all suggestions
      $('.headspace-add-all').click(function() {
        var element = $(get_tag_element ())
        var words   = element.val().toLowerCase().split (',');
        
        // Add all HeadSpace keywords to WordPress list
        $('#suggested_tags a').each(function () {
          if ($.inArray (this.text.toLowerCase(), words) === -1)
            element.val (element.val () + ',' + this.text);
        });

        highlight_tags ();
        return false;
      });
      
      // Suggest link
      $('.headspace-suggest').click(function() { 
        $('#tag_loading').show ();
        $('#suggestions').load (this.href, { content: $('#content').val () + ' ' + $('#title').val () },
          function() {
            $('#tag_loading').hide ();
            refresh ();
            highlight_tags ();
          });
          
        return false;
      });
    }
    
    function get_tag_element () {
      if ($('#tax-input-post_tag').length == 1)
        return '#tax-input-post_tag';
      else if ($('#tags-input').length == 1)
        return '#tags-input';
      else if ($('#tax-input\[post_tag\]').length == 1)
        return '#tax-input\[post_tag\]';
    }

    // Highlights headspace tags using the WordPress tag field as source
    function highlight_tags () {
      var words;
      var wordArray = $(get_tag_element());
      (wordArray.length) ? words = $(get_tag_element()).val().toLowerCase().split(',') : words = []
      
      // Now go through the suggested words and highlight or dehighlight them
      $('#suggested_tags a').each (function () {
        $(this).removeClass ('enabled').removeClass ('disabled');

        if ($.inArray ($(this).html().toLowerCase (), words) !== -1)
          $(this).addClass ('enabled');
        else
          $(this).addClass ('disabled');
      });
      
      // Ensure WordPress field is in sync
      if (typeof tag_update_quickclicks == 'function')
        tag_update_quickclicks ();
    }
	}

	$.fn.headspace = function(options) {
		options = options || {};

		this.each(function() {
			new $.headspace(this, options);
		});

		return this;
	};
})(jQuery);
