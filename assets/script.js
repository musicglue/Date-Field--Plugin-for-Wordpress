// @TODO - use real dates. Can I get this with javascript??
// @TODO - order an array and find the one furthest in the future.


// remap jQuery to $
(function($){

	$( document ).ready( function() {
	
		var dfCalendar	= $( 'ul.df_calendar' );
	
		dfCalendar.each( function(){
		
			var dfEvents = {};
		
			//Find all list elements - one for each event.
			$(this).find( 'li' ).each( function(){
				
				//Build an array of all the Events from the data added to the list elements.
				dfEvents[ $(this).attr('data-date') ] =  $(this).find( 'a' ).attr('href');
			
			});
		
			$(this).wrap('<div id="' + $(this).attr('id') + '_calendar" class="widget_calendar"/>');
			$(this).hide();

			$(this).closest('.widget_calendar').datepicker({
			    //minDate: new Date( 2011, 10-1, 13),
			   	//maxDate: new Date( 2012, 02-1, 13),
			    dateFormat: 'yy-mm-dd',
			    beforeShowDay: function(date) {
			        var date_str = $.datepicker.formatDate('yy-mm-dd', date );
			        
			    	if ( dfEvents[date_str] ) {
			    	    return [true, 'date_event', 'This date is selectable'];
			    	} else {
			    	    return [false, 'date_diabled', date_str ];
			    	}
			    },
			    onSelect: function( dateUrl ) {
			    	document.location = dfEvents[dateUrl];
			    }
			});

		});
		
	});

})(this.jQuery);