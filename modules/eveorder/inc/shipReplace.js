$(document).ready(function(){
	$('#search').autocomplete({
		minLength: 3,
		source: "dowork.php?module=eveorder&action=searchItem",
		select: function(event, ui) {
			$("#typeID").val(ui.item.id);
		}
	});
	
	$('#updateLoc').click(function(){
		$('#updateLoc').attr('disabled', 'disabled');
		$('#ajaxCont').show();
		$('#ajaxCont').text('wait while loading data...');
		$.post('dowork.php', {module:'eveorder', action:'updateLocation'}, function(data) {
			$('#ajaxCont').html(data);
			if (data == 'updated') {
				setTimeout("location.reload()", 2000);
			}
		});
	});
	
	$('#importFitt').click(function(){
		$('#importFitt').attr('disabled', 'disabled');
		$('#ajaxCont').show();
		$('#ajaxCont').text('wait while loading data...');
		$.post('dowork.php', {module:'eveorder', action:'importFitts'}, function(data) {
			setTimeout("location.reload()", 2000);
		});
	});
	
	$(".delete").click(function() {
		var id = $(this).attr("id").substr(4);
		
		$.post('dowork.php', {module:'eveorder', action:'delValue', id:id}, function(data) {
			if (data == 'deleted') {
				$("#tab_"+id).remove();
			}
		});
	});
	
	$("#upAssets").click(function() {
		$('#upAssets').attr('disabled', 'disabled');
		$('#ajaxCont').show();
		$('#ajaxCont').text('wait while loading data...');
		$.post('dowork.php', {module:'eveorder', action:'upAssets'}, function(data) {
			$('#ajaxCont').html(data);
			if (data == 'updated') {
				setTimeout("location.reload()", 2000);
			}
		});
	});
	/*
	$("table tbody td#zahl").append('<div class="inc button">+</div><div class="dec button">-</div>');
	
	$(".button").click(function() {
        var $button = $(this);
        var oldValue = $button.parent().find("input").val();
		var id = $button.parent().find("input").attr("id").substr(4);
		var cur = parseFloat($('#cur_'+id).text());
		// var buy = $('#buy_'+id).text();
    
        if ($button.text() == "+") {
    	  var newVal = parseFloat(oldValue) + 1;
		  if (isNaN(newVal)) newVal = 0;
		  $.ajax({
			type: "POST",
			url: "dowork.php",
			data: { module:'eveorder', action:'updateVal', id:id, newvalue:newVal},
			success: function(data){
				if (data) {
					$button.parent().find("input").val(newVal);
					var newValu = (newVal-cur);
					if (newValu >= 1 ) {
						$('#buy_'+id).text(newValu);
					} else {
						$('#buy_'+id).text('');
					}
				}
			}
		  });
    	} else {
    	  // Don't allow decrementing below zero
    	  if (oldValue >= 1) {
    	    var newVal = parseFloat(oldValue) - 1;
			$.ajax({
				type: "POST",
				url: "dowork.php",
				data: { module:'eveorder', action:'updateVal', id:id, newvalue:newVal},
				success: function(data){
					if (data) {
						$button.parent().find("input").val(newVal);
						var newValu = (newVal-cur);
						if (newValu >= 1 ) {
							$('#buy_'+id).text(newValu);
						} else {
							$('#buy_'+id).text('');
						}
					}
				}
			});
    	  }
    	}
    	$button.parent().find("input").val(newVal);
    });
	*/

});

function saveValue(id) {
	var newVal = parseFloat($('#min_'+id).val());
	var cur = parseFloat($('#cur_'+id).text());
	var order = parseFloat($('#order_'+id).text());
	if (isNaN(newVal)) newVal = 0;
	$.ajax({
		type: "POST",
		url: "dowork.php",
		data: { module:'eveorder', action:'updateVal', id:id, newvalue:newVal},
		success: function(data){
			if (data) {
				var newValu = (newVal-(cur+order));
				if (newValu >= 1 ) {
					$('#buy_'+id).text(newValu);
				} else {
					$('#buy_'+id).text('');
				}
			}
		}
	});
}

