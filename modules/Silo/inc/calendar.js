$(document).ready(function() {
	
	$('#calendar').fullCalendar({
		header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
		},
		editable: false,
		theme: true,
		eventSources: [

			// your event source
			{
				url: 'dowork.php',
				type: 'POST',
				data: {
					module: 'Silo',
					action: 'calendar'
				},
				error: function() {
					alert('there was an error while fetching events!');
				}
			}

			// any other sources...

		],
		
		eventDrop: function(event, delta) {
			alert(event.title + ' was moved ' + delta + ' days\n' +
				'(should probably update your database)');
		},
		
		loading: function(bool) {
			if (bool) $('#loading').show();
			else $('#loading').hide();
		}
		
	});
	
});