(function($){

$.fn.timerskill = function(){
	
	if(arguments.length == 0){
		var end = Math.round((new Date().getTime()+($(this).text()*1000))/1000);
	}else{
		var end = arguments[0];
	}
	var time = Math.round(new Date().getTime()/1000);
	var timer = end-time;
	
	var entity = new Object();
	entity['Days,'] = Math.floor(timer/60/60/24);
	entity['Hours,'] = Math.floor(timer/60/60)-entity['Days,']*24;
	entity['Minutes'] = Math.floor(timer/60)-entity['Hours,']*60-entity['Days,']*24*60;
	entity['Seconds'] = timer-entity['Minutes']*60-entity['Hours,']*60*60-entity['Days,']*24*60*60;
	
	var output = '';
	
	for(var i in entity){
		if(entity[i]>=0){
			output+=entity[i]+' '+i+' ';
			if(i=='Minutes'){
				output+=' and ';
			}
		}
	}
	if(timer>61){
		$(this).text(output).doTimeout(1000, function(){
			$(this).timerskill(end);
		});
	}else{
		$(this).text('expired!');
	}
	
};

})(jQuery);
