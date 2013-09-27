$(document).ready(function() {
	
	var calendar = $('#calendar').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		theme: true,
		selectable: true,
		selectHelper: true,
		select: function(start, end, allDay) {
			$.msgBox({ type: "prompt",
				title: "New Event",
				inputs: [
				{ header: "Title:", type: "textarea", rows:10, cols:45, name: "title" }],
				buttons: [
				{ value: "OK" }, {value:"Cancel"}],
				success: function (result, values) {
					if(result == "OK") {
						if(allDay) var Day = 1; else var Day = 0;
						$.post('dowork.php', {
									module:'Silo', 
									action:'addEvent',
									data:{title: values[0].value,start: start,end: end,allDay:Day}
								}, function(data) {
							calendar.fullCalendar('refetchEvents');
						});
						
					}
					calendar.fullCalendar('unselect');
				}
				
			});
			
			
		},
		editable: true,
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
		eventClick: function(calEvent, jsEvent, view) {
			if (calEvent.url) {
				window.open(calEvent.url);
				return false;
			}
			$.msgBox({
				title: "Are You Sure",
				content: "Delete event?",
				type: "confirm",
				
				buttons: [{ value: "Yes" }, { value: "No" }],
				success: function (result) {
					if (result == "Yes") {
						$.post('dowork.php', {
									module:'Silo', 
									action:'delEvent',
									id: calEvent._id
								}, function(data) {
							calendar.fullCalendar('refetchEvents');
						});
						//calendar.fullCalendar('removeEvents', calEvent._id);
						//console.log(calEvent._id);
					}
				}
			});
		},
		eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
			if(allDay) var Day = 1; else var Day = 0;
			$.post('dowork.php', {
						module:'Silo', 
						action:'dropEvent',
						data: {id:event.id,day:dayDelta,min:minuteDelta,allDay:Day}
					}, function(data) {
				calendar.fullCalendar('refetchEvents');
			});
		},
		eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
			$.post('dowork.php', {
						module:'Silo', 
						action:'resizeEvent',
						data: {id:event.id,day:dayDelta,min:minuteDelta}
					}, function(data) {
				calendar.fullCalendar('refetchEvents');
			});
		},
		loading: function(bool) {
			if (bool) $('#loading').show();
			else $('#loading').hide();
		}
	});
	
});