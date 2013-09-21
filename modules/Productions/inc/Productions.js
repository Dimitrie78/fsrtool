rv = {

	getDependencies : function(){
		var root = 'modules/Productions/inc/js/', load = 0;
		$.ajaxSetup({async: false});
		for(var i=0; i < arguments.length;i++){
			$.getScript(root+arguments[i],function(){
				load++;
			}); 
		}
		$.ajaxSetup({async: true});
		if(load != arguments.length)
			return false;
		return true;
	},

	topAnchor : function(){
		var anchor = $('#topanchor');
		anchor.hide().click(function(){
			$(window).scrollTop(0);
			return false;
		});
		$(window).bind('scroll load', function show_top(){
			$(window).scrollTop() > 50 ? anchor.fadeIn(): anchor.fadeOut();
		});
	},

	flagToHangar : function (flag){
		var hangar = '';
		switch(parseInt(flag)){
			case 4: hangar = '1.'; break;
			case 116: hangar = '2.'; break;
			case 117: hangar = '3.'; break; 
			case 118: hangar = '4.'; break; 
			case 119: hangar = '5.'; break; 
			case 120: hangar = '6.'; break;
			case 121: hangar = '7.'; break;  
		}
		return hangar;
	},
  
	activityIDToActivity : function (activityID){
		var activity = '';
		switch (parseInt(activityID)){
			case 1: activity = 'M'; break;
			case 3: activity = 'PE'; break;
			case 4: activity = 'ME'; break;
			case 5: activity = 'C'; break;
			case 7: activity = 'RE'; break;
			case 8: activity = 'I'; break;         
		}
		return activity;
	}, 
  
	templateJobs : function (row,data){
		var time = new Date;
		row.find('.job_type').text(rv.activityIDToActivity(data.activityID));
		row.find('.job_product').addClass(data.outputCategoryName).text(data.outputTypeName);
		if(data.activityID == 3 || data.activityID == 4)
			data.runs = '+'+data.runs;            
		row.find('.job_qty').text(data.runs);
		row.find('.job_sys').text(data.installedInSolarSystemName);
		row.find('.job_location').text(data.containerName.replace(data.installedInSolarSystemName+" ",""));
		var output_in = rv.flagToHangar(data.outputFlag); 
		if(!output_in)
			output_in = rv.flagToHangar(data.installedItemFlag);
		row.find('.job_output_in').text(output_in);
		row.find('.job_installer').text(data.installer);
		row.find('.job_ml').text(data.installedItemMaterialLevel);
		row.find('.job_pl').text(data.installedItemProductivityLevel);
		var ttc = data.endProductionTimeUnix-parseInt(time.getTime()/1000)-time.getTimezoneOffset()*60;
		if(ttc <= 0) ttc = 0;
		row.find('.job_ttc').attr('title',ttc).text(""+ttc);
		row.find('.job_endtime').attr('title',data.endProductionTimeUnix).text(data.endProductionTime);
		row.find('.job_installtime').attr('title',data.installTimeUnix).text(data.installTime);
		return row;
	}, 
  
	templateChars : function (row,data){
		row.find('.char_name').text(data['name']);
		var char_m = row.find('.char_m');
		char_m.text(""+ (data['jobs'][1] ? data['jobs'][1] : 0));
		var r = 0;
		for(i in data['jobs']){
			if(i!=1)
				r += data['jobs'][i];
		}
		row.find('.char_r').text(""+r);
		return row;
	}, 
  
	templateActivity : function (row,activityID,data){
		row.find('.act_type').text(rv.activityIDToActivity(i));
		row.find('.act_qty').text(""+data['qty']);
		row.find('.act_stn').text(""+ (typeof data['stn'] == 'undefined' ? 0 : data['stn']));
		row.find('.act_pos').text(""+ (typeof data['pos'] == 'undefined' ? 0 : data['pos']));
		return row;
	},
  
	templateManufacturing : function (row,data){
		var time = new Date;
		row.find('.man_product').text(data.outputTypeName);
		var size = 0;
		var hasJobs = (typeof data.jobs != 'undefined');
		if(hasJobs){
			row.find('.man_qty').text(""+data.jobs[1].all);
			for(key in data.jobs[1]){
				if(!isNaN(parseInt(key)))
					size++;
			}
			var nxtRdy = false;
				key = 0;
			for(i in data.jobs[1]){
				if((data.jobs[1][i].endProductionTimeUnix < nxtRdy || !nxtRdy) && i!='all'){
					nxtRdy = data.jobs[1][i].endProductionTimeUnix;
					key = i;
				}
			}
			var ttc = nxtRdy-parseInt(time.getTime()/1000)-time.getTimezoneOffset()*60;
			if(ttc <= 0) ttc = 0; 
			row.find('.man_nxt_rdy').attr('title',ttc).text(""+ttc);
			row.find('.man_nxt_batch').text(""+data.jobs[1][key].runs);
		}else{
			row.find('.man_qty').text("0");
			row.find('.man_nxt_rdy').attr('title',0).text("---");
			row.find('.man_nxt_batch').text("---");
			row.find('span.timer').removeClass('timer');
		}
		row.find('.man_jobs').text(""+size);
		row.find('.man_stock').text(""+typeof data.stockQuantity == 'undefined' ? 0 : data.stockQuantity);
		row.find('.man_stock_stn').text('wip');
		row.find('.man_stock_pos').text('wip');
		return row;
	},
  
	getTables : function(){
		var request = 'dowork.php';
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {module:'Productions', action:'get'},
			success: function(json){
				if(json['error']) {
					$('#error').text(json['error']);
					return false;
				}
				// CacheInfo
				var time = new Date;
				$('.cachedUntil').unbind('click').removeAttr('href').text(json['info']['cachedUntil']-parseInt(time.getTime()/1000)-time.getTimezoneOffset()*60+"")
				.timer({ 
					prependMsg : 'Neue Datenabfrage in',
					appendMsg : 'm\u00f6glich.',
					finishMsg : 'Daten eventuell veraltet! Neue Datenabfrage per API m\u00f6glich.',
					unitMinute : ' Minuten',
					callback: function(){
						$(this).unbind('click').click(function(){
							rv.getTables();
						}).attr('href','#');
					}
				}).init(true);
	
				// clean up duty ;)
				$('table tbody tr:not(.template,.foot)').remove();
	
				// Job-Table
				var jobs = $('#jobs'),
					jobsBody = jobs.find('tbody'),
					jobsTemplate = jobsBody.find('.template'),
					jobsParent = jobs.parent();
				jobs.detach();
				for(i in json['jobs']){                   
					var newRow = jobsTemplate.clone().removeClass('template');
					rv.templateJobs(newRow,json['jobs'][i]).appendTo(jobsBody).find('span.timer')
					.timer({
						finishMsg : 'fertig',
						callback: function(){
							$(this).parents('tr').addClass('ready');
						}
					}).init(true);
				}
				jobsParent.append(jobs);
	
				// manufacturing-Table
				var manufacturingTemplate = $('#manufacturing .template');
				for(i in json['info']['manjobs']){
					var newRow = manufacturingTemplate.clone().removeClass('template');
					rv.templateManufacturing(newRow,json['info']['manjobs'][i]).appendTo('#manufacturing tbody').find('span.timer')
					.timer({
						finishMsg : 'fertig',
						callback: function(){
							$(this).parents('tr').addClass('ready');
						}
					}).init(true);
				}
	
				// Char-Table
				var charsTemplate = $('#chars .template');
				for(i in json['info']['chars']){
					var newRow = charsTemplate.clone().removeClass('template');
					rv.templateChars(newRow,json['info']['chars'][i]).appendTo('#chars tbody');
				}
	
				// Activity-Table
				var activityTemplate = $('#activity .template');
				var act_all = 0,act_all_stn = 0,act_all_pos = 0,j=0;
				for(i in json['info']['activity']){
					var newRow = activityTemplate.clone().removeClass('template');
					rv.templateActivity(newRow,i,json['info']['activity'][i]).appendTo('#activity tbody');
					act_all += json['info']['activity'][i]['qty'] ? json['info']['activity'][i]['qty'] : 0 ; 
					act_all_stn += json['info']['activity'][i]['stn'] ? json['info']['activity'][i]['stn'] : 0 ;
					act_all_pos += json['info']['activity'][i]['pos'] ? json['info']['activity'][i]['pos'] : 0 ;  
					j++;
				}
				$('#activity tr.foot').appendTo($('#activity tbody'))
					.find('span.act_all').text(""+act_all).end()
					.find('span.act_all_stn').text(""+act_all_stn).end()
					.find('span.act_all_pos').text(""+act_all_pos).end(); 
	
				$('td').parent('tr').click(function(){ 
					$(this).hasClass('selected') ? $(this).removeClass('selected') : $(this).addClass('selected');
				}); 
				
				rv.sortTable('#chars',0,true);
				rv.sortTable('#activity',0,true);
				rv.sortTable('#manufacturing',0,true);
				rv.sortTable('#jobs',9,true);
				
				// Add Zebra
				rv.zebra();
				
				// Add additional headers
				rv.repeatHeader($('#jobs'),15,2);
				
				$('#jobs>thead>tr:not(.desc)>th').each(function(i){
					if($(this).find('span').length)
						$(this).find('span').contents().unwrap();
					$(this).wrapInner('<span>').unbind('click').click(function(){
						rv.sortTable('#jobs',i);
						rv.zebra();
						rv.repeatHeader($('#jobs'),15,2);
						return false;
					});
				});
				$('#manufacturing>thead>tr:not(.desc)>th').each(function(i){
					if($(this).find('span').length)
						$(this).find('span').contents().unwrap();
					$(this).wrapInner('<span>').unbind('click').click(function(){
						rv.sortTable('#manufacturing',i);
						rv.zebra();
						return false;
					});
				});
			}
		});
		return true;
	},
	
	zebra : function(){
		$('table>tbody>tr').removeClass('odd even');
		$('table>tbody').each(function(){
			$(this).find('tr:visible').each(function(i){
				var bg = (i%2) ? 'odd' : 'even'; 
				$(this).addClass(bg);
			});
		});
	},
	
	sortTable : function (tbl,nr) {
		$tbl = $(tbl);
		if(typeof aAsc === 'undefined')
			aAsc = [];
 		if(typeof aAsc[tbl] === 'undefined')
			aAsc[tbl] = [];
		var setting = {};
		if($tbl.find('td:eq('+nr+')>span[title]').length)
			setting.attr = 'title';
		aAsc[tbl][nr] = arguments[2] ? "asc" : aAsc[tbl][nr]=="asc"?"desc":"asc";
		setting.order = aAsc[tbl][nr];
		$tbl.find('tbody>tr:not(.template)').tsort("td:eq("+nr+")>span",setting)
			.each(function(){
				$(this)
					.find('td.selected').removeClass('selected').end()
					.find('td:eq('+nr+')').addClass('selected');
			});
		$tbl.find('tr:not(.desc)>th').parent('tr')
			.each(function(){
				$(this)
					.find('th').removeClass('selected asc desc').end()
					.find('th:eq('+nr+')').addClass('selected '+setting.order);
			});
	},
	
	repeatHeader : function(table, every, head) {
		$(table).each(function() {
			$(this).find('tbody>tr>th').parent('tr').remove();
			var rowsLen = $(this).find('tbody>tr').length;
			$(this).find('thead>tr:eq('+(head-1)+')')
				.clone()
				.unwrap()
				.insertAfter($(this).find('tbody>tr:nth-child(' + every + 'n)'));
			if ((rowsLen) % every === 0) {
				$(this).find('tbody>tr:last').remove();
			}
		});
	},
	
	searchTable : function(table, val){
		$(table).each(function(){
			var _this = $(this);
			_this
				.find('tr.even,tr.odd')
				.find('span.job_product:not(:containsNoCase('+val+'))')
				.parent()
				.parent()
				.toggle();
		});
	}
};

$(document).ready(function(){

	$('#noJS').hide();
	var ajaxInProgress = $('#ajaxInProgress');
	//ajaxInProgress.addClass('progress');

	$.expr[":"].containsNoCase = function(el, i, m) {
		var search = m[3];
		if (!search) return false;
		return eval("/" + search + "/i").test($(el).text());
	};
	
	rv.getDependencies('cookie.min.js','timer2.js','md5.min.js','tinysort.js');

	ajaxInProgress
		.ajaxStart(function() {
			$(this).addClass('progress');
		})
		.ajaxSuccess(function() {
			$('div.col,div#cacheInfo').show();	
			//$('div#passwd').hide();
			$(this).removeClass('progress');
			rv.zebra();
		})
		.ajaxError(function() {
			
			$(this).removeClass('progress'); 
		});

	rv.getTables();
	rv.topAnchor();
 
});