$(document).ready(function(){
	$("a.trigger").click(function(){
		$("tr.settings").toggle();
		$("tr.assign").toggle();
		$("div#assign").toggle();
		// if($(this).html() == 'Show Settings') { $(this).html('Hide Settings'); } else { $(this).html('Show Settings');}
	});
	$('img[title]').qtip({ style: { name: 'fsr', tip: true } });
	function init_timer(obj){
		$(obj).timer({
			prependMsg: 'Cycle in',
			finishMsg: 'loading new data...',
			events: {
				eventFiveMinutes: {
					trigger: 360,
					fn: function(){
						$(this).css({'font-weight':'bold','color':'#ffa500'});
					}
				},
				eventClean: {
					trigger: 0,
					fn: function(){
						$(this).css({'font-weight':'normal','color':'#fff'});
					}
				}
			}
		},function(){
			var moonID = $(obj).parents('table.silo').attr('id');
			var request = 'dowork.php?module=Silo&action=json'
			$.ajax({
				type:"POST",
				url:request,
				processData:true,
				dataType: "json",
				data: {moonID:moonID},
				success: function(json){
					//console.log(new Date().toGMTString(),json,moonID);
					if(json){
						for(var i in json.silos){
							$('tr#'+i)
								.find('div.pc').text(json.silos[i].endTime).end()
								.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
								.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
								.find('div.qty span').text(json.silos[i].quantity)
							;
						}
						$(obj).text(json.ttc);
						init_timer(obj);
					}
				}
			});
		});
	}
	
	$('span.timer').each(function(){
		init_timer($(this));
	});
});

function empty(moonID, emptyItemID) {
	var request = 'dowork.php?module=Silo&action=jsonempty'
	$.ajax({
		type:"POST",
		url:request,
		processData:true,
		dataType: "json",
		data: {moonID:moonID, emptyItemID:emptyItemID},
		success: function(json){
			//console.log(new Date().toGMTString(),json,moonID);
			if(json){
				for(var i in json.silos){
					$('tr#'+i)
						.find('div.pc').text(json.silos[i].endTime).end()
						.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
						.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
						.find('div.qty span').text(json.silos[i].quantity)
					;
					if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
				}
				if(json.alert == false) {
					$('table#'+moonID).removeClass("alert");
					$('table#'+moonID+' tr.alert').remove();
				}
			}
		}
	});
}

function online(moonID, itemID) {
	var request = 'dowork.php?module=Silo&action=jsononline'
	$.ajax({
		type:"POST",
		url:request,
		processData:true,
		dataType: "json",
		data: {moonID:moonID, itemID:itemID},
		success: function(json){
			//console.log(new Date().toGMTString(),json,moonID);
			if(json){
				for(var i in json.silos){
					$('tr#'+i)
						.find('div.pc').text(json.silos[i].endTime).end()
						.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
						.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
						.find('div.qty span').text(json.silos[i].quantity)
					;
					if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					if(json.silos[i].suspect == '0') $('tr#'+i).removeClass("suspect");
				}
				$('tr#'+itemID).find('a#online').remove();
				if(json.alert == false) {
					$('table#'+moonID).removeClass("alert");
					$('table#'+moonID+' tr.alert').remove();
				}
				if(json.suspect == false) {
					$('table#'+moonID).removeClass("suspect");
					$('table#'+moonID+' tr.suspect').remove();
				}
			}
		}
	});
}

function setfillempty(moonID, itemID, status) {
	var request = 'dowork.php?module=Silo&action=jsonsetsilo'
	if(status == 'fill') {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, IDtofill:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#'+itemID)
						.find('a#lorry img').attr('src', 'icons/lorry_left.png').end()
						.find('div.pcbar_pos').css('background-image', 'none').end()
						.find('div.pcbar_neg').css('background-image', 'url(icons/unfill.gif)').css('background-color', '#900');
					$('tr#'+itemID+' a#lorry img').qtip('api').updateContent(json.lang.fill_silo);
					$('tr#setti'+itemID)	
						.find('a#fillempty').attr('href', 'javascript:setfillempty('+moonID+','+itemID+',\'empty\')').end()
						.find('a#fillempty img').attr('src', 'icons/package_unfill.png');
					$('tr#setti'+itemID+' a#fillempty').qtip('api').updateContent(json.lang.silo_empties+'<hr>'+json.lang.click_to_convert);
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	} else {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, IDtoempty:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#'+itemID)
						.find('a#lorry img').attr('src', 'icons/lorry_right.png').end()
						.find('div.pcbar_pos').css('background-image', 'url(icons/fill.gif)').end()
						.find('div.pcbar_neg').css('background-image', 'none').css('background-color', '#777');
					$('tr#'+itemID+' a#lorry img').qtip('api').updateContent(json.lang.empty_silo);
					$('tr#setti'+itemID)
						.find('a#fillempty').attr('href', 'javascript:setfillempty('+moonID+','+itemID+',\'fill\')').end()
						.find('a#fillempty img').attr('src', 'icons/package_fill.png');
					$('tr#setti'+itemID+' a#fillempty').qtip('api').updateContent(json.lang.silo_filling_up+'<hr>'+json.lang.click_to_convert);
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	}
	//$('img[title]').qtip({ style: { name: 'fsr', tip: true } });
}

function setinout(moonID, itemID, status) {
	var request = 'dowork.php?module=Silo&action=jsonsetsilo'
	if(status == 'input') {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, IDinput:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#setti'+itemID+' a#inout').qtip('api').updateContent(json.lang.silo100+'<hr>'+json.lang.click_to_convert);
					$('tr#setti'+itemID)	
						.find('a#inout').attr('href', 'javascript:setinout('+moonID+','+itemID+',\'output\')').end()
						.find('a#inout img').attr('src', 'icons/100.png');
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	} else {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, IDoutput:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#setti'+itemID+' a#inout').qtip('api').updateContent(json.lang.silo200+'<hr>'+json.lang.click_to_convert);
					$('tr#setti'+itemID)
						.find('a#inout').attr('href', 'javascript:setinout('+moonID+','+itemID+',\'input\')').end()
						.find('a#inout img').attr('src', 'icons/200.png');
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	}
}

function setfesimple(moonID, itemID, status) {
	var request = 'dowork.php?module=Silo&action=jsonsetsilo'
	if(status == 'fill') {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, IDfill:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#'+itemID+' a#lorry img').qtip('api').updateContent(json.lang.fill_silo);
					$('tr#'+itemID)
						.find('a#lorry img').attr('src', 'icons/lorry_left.png').end()
						.find('div.pcbar_pos').css('background-image', 'none').end()
						.find('div.pcbar_neg').css('background-image', 'url(icons/unfill.gif)').css('background-color', '#900');
					$('tr#setti'+itemID+' a#fesimple').qtip('api').updateContent(json.lang.silo_empties+'<hr>'+json.lang.click_to_convert);
					$('tr#setti'+itemID)	
						.find('a#fesimple').attr('href', 'javascript:setfesimple('+moonID+','+itemID+',\'empty\')').end()
						.find('a#fesimple img').attr('src', 'icons/package_unfill.png');
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	} else {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, IDempty:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#'+itemID+' a#lorry img').qtip('api').updateContent(json.lang.empty_silo);
					$('tr#'+itemID)
						.find('a#lorry img').attr('src', 'icons/lorry_right.png').end()
						.find('div.pcbar_pos').css('background-image', 'url(icons/fill.gif)').end()
						.find('div.pcbar_neg').css('background-image', 'none').css('background-color', '#777');
					$('tr#setti'+itemID+' a#fesimple').qtip('api').updateContent(json.lang.silo_filling_up+'<hr>'+json.lang.click_to_convert);
					$('tr#setti'+itemID)
						.find('a#fesimple').attr('href', 'javascript:setfesimple('+moonID+','+itemID+',\'fill\')').end()
						.find('a#fesimple img').attr('src', 'icons/package_fill.png');
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	}
}

function setstack(moonID, itemID, status) {
	var request = 'dowork.php?module=Silo&action=jsonsetsilo'
	//console.log(new Date().toGMTString(),moonID,status);
	if(status == 'set') {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, setstackID:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#setti'+itemID+' a#stack').qtip('api').updateContent(json.lang.desc_stack_off+'<hr>'+json.lang.click_to_convert);
					$('tr#setti'+itemID)	
						.find('a#stack').attr('href', 'javascript:setstack('+moonID+','+itemID+',\'unset\')').end()
						.find('a#stack img').attr('src', 'icons/arrow_join.png');
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	} else {
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {moonID:moonID, unsetstackID:itemID},
			success: function(json){
				//console.log(new Date().toGMTString(),json,moonID);
				if(json){
					for(var i in json.silos){
						$('tr#'+i)
							.find('div.pc').text(json.silos[i].endTime).end()
							.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
							.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
							.find('div.qty span').text(json.silos[i].quantity);
						if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
					}
					$('tr#setti'+itemID+' a#stack').qtip('api').updateContent(json.lang.desc_stack_on+'<hr>'+json.lang.click_to_convert);
					$('tr#setti'+itemID)
						.find('a#stack').attr('href', 'javascript:setstack('+moonID+','+itemID+',\'set\')').end()
						.find('a#stack img').attr('src', 'icons/arrow_join_red.png');
					if(json.alert == false) {
						$('table#'+moonID).removeClass("alert");
						$('table#'+moonID+' tr.alert').remove();
					}
				}
			}
		});
	}
}

function delSilo(moonID, itemID) {
	var request = 'dowork.php?module=Silo&action=jsonsetsilo'
	$.ajax({
		type:"POST",
		url:request,
		processData:true,
		dataType: "json",
		data: {moonID:moonID, delitemID:itemID},
		success: function(json){
			//console.log(new Date().toGMTString(),json,moonID);
			if(json){
				$('tr#'+itemID).remove();
				$('tr#setti'+itemID).remove();
				for(var i in json.silos){
					$('tr#'+i)
						.find('div.pc').text(json.silos[i].endTime).end()
						.find('div.pcbar_pos').css('width',(json.silos[i].pro)+'px').end()
						.find('div.pcbar_neg').css('width',(100-json.silos[i].pro)+'px').end()
						.find('div.qty span').text(json.silos[i].quantity);
					if(json.silos[i].alarm == '0') $('tr#'+i).removeClass("alert");
				}
				if(json.alert == false) {
					$('table#'+moonID).removeClass("alert");
					$('table#'+moonID+' tr.alert').remove();
				}
			}
		}
	});	
}