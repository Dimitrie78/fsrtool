(function($){

$.fn.fwtimer = function(){

	if(arguments.length == 0){
		var timer = $(this).text();
		$(this).css('display','inline');
	}else{
		var timer = arguments[0];
	} 
	
	var entity = new Object();
	entity['d'] = Math.floor(timer/60/60/24);
	entity['h'] = Math.floor(timer/60/60)-entity['d']*24;
	entity['m'] = Math.floor(timer/60)-entity['h']*60-entity['d']*24*60;
	entity['s'] = timer-entity['m']*60-entity['h']*60*60-entity['d']*24*60*60;
	
	var output = '';
	
	for(var i in entity){
		if(entity[i]>0){
			output+=entity[i]+i+' ';
		}
	}
	$(this).text(output).doTimeout(15000, function(){
		$(this).fwtimer(parseInt(timer)+15);
	});
};

})(jQuery);
