/*
////////////////////
//
// Timer Funktion
//
////////////////////
////////////////////
// benötigt folgendes jQuery Plugin:
//   BA-doTimeout
//   jquery.ba-dotimeout.min.js
////////////////////
// erwartete Parameter: 
//   keine
// optionaler Parameter "end":
//   typ: Unix Timestamp (int/string)
//	 gibt die verbleibende Zeit an, wird ein String angegeben, wird versucht diesen zu parsen
//   ist kein end-Parameter angegeben wird versucht aus dem Inhalt des Elements einen UNIX-Timestamp zu lesen
// optionaler Parameter "params":
//   typ: Object
//   wird "params" nicht angegeben, werden die Standardwerte der Funktion benutzt
//   Konfigurationsparameter mit folgenden Einstellmöglichkeiten:
//     prependMsg
//       typ: String
//       vor dem Zeitstring angehängter String
//       Standard: ''
//     appendMsg
//       typ: String
//       nach dem Zeitstring angehängter String
//       Standard: ''
//     finishMsg
//       typ: String
//       String, der angezeigt wird, wenn der Timer abgelaufen ist
//       Standard: 'ready'
//     showSeconds
//       typ: bool
//       Bool'scher Wert ob Sekunden angezeigt werden sollen
//       Standard: false
//     updateInterval
//       typ: Integer
//       Aktualisierungsintervall in Sekunden
//       Standard: 10
//     unitDay / unitHour / unitMinute / unitSecond
//       typ: String
//       Bezeichner für den Entsprechenden Zeitwert
//       Standard: 'd' / 'h' / 'm' / 's'
//     unitDay / unitHour / unitMinute / unitSecond
//       typ: String
//       Bezeichner für den Entsprechenden Zeitwert
//       Standard: 'd' / 'h' / 'm' / 's'
//     animate
//       typ: bool
//       Bool'scher Wert ob Animationen genutzt werden sollen
//       Standard: true
//     events
//       typ: Objekt
//       Objekt mit möglichen Unterobjekten für Triggerfunktionen
//       mögliche Konfiguration:
//         [eventname]
//           typ: Objekt
//           Name des Triggerobjekts; Bezeichnung muss mit einem Buchstaben beginnen.
//           Erwartete Parameter:
//             trigger
//               typ: Integer
//               Gibt den Trigger-Zeitpunkt in Sekunden an. Wird der Wert vom Timer unterschritten oder erreicht, wird "fn" ausgeführt
//             fn
//               typ: Funktion
//               Wird ausgeführt, sobald "trigger" kleiner als der Timer-Wert ist: siehe "trigger"
// optionaler Parameter "callback":
//   typ: Funktion
//	 Ruft eine Callback-Funktion auf sobald der Timer abgelaufen ist.
//   Anmerkung: callback entspricht einem Triggerobjekt params.events mit der Einstellung trigger: 0
////////////////////
*/

(function($){

$.fn.timer = function(){
	
	var standard = {};
		standard.prependMsg = '';
		standard.appendMsg = '';
		standard.finishMsg = 'ready';
		standard.showSeconds = false;
		standard.updateInterval = 10;
		standard.unitDay = 'd';
		standard.unitHour = 'h';
		standard.unitMinute = 'm';
		standard.unitSecond = 's';
		standard.animate = true;
		standard.events = false;
	var custom = {};
	var callback = false;
	
	function params(object){
		for(i in standard){
			typeof object[i] === 'undefined' ? custom[i] = standard[i] : custom[i] = object[i];
		}
	}
	
	for(var j=0;arguments.length>=j;j++){
		if(typeof arguments[j] === 'number'){
			var end = arguments[j];
		}else if(typeof arguments[j] === 'object'){
			typeof arguments[j] === 'object' ? params(arguments[j]) : params({});
		}else if($.isFunction(arguments[j])){
			var callback = arguments[j];
		}else if(typeof arguments[j] === 'string'){
			if(isNaN(parseInt(arguments[j])))
				return $(this);
			else
				var end = parseInt(arguments[j]);
		}
	}
	
	if(typeof end === 'undefined'){
		var end = Math.round((new Date().getTime()+($(this).text()*1000))/1000);
		if(custom.animate){
			$(this).fadeIn("slow");
		}else
			$(this).css('display','inline');
	}
	
	var time = Math.round(new Date().getTime()/1000);
	var timer = end-time;
	
	if(typeof custom.events === 'object'){
		for(var i in custom.events){
			var trigger = custom.events[i].trigger;
			var fn = custom.events[i].fn;
			if($.isFunction(fn) && timer<=trigger){
				fn.call(this);
			}
		}
	}
	
	var entity = new Object();
	entity[custom.unitDay] = Math.floor(timer/60/60/24);
	entity[custom.unitHour] = Math.floor(timer/60/60)-entity[custom.unitDay]*24;
	entity[custom.unitMinute] = Math.floor(timer/60)-entity[custom.unitHour]*60-entity[custom.unitDay]*24*60;
	if(custom.showSeconds)
		entity[custom.unitSecond] = timer-entity[custom.unitMinute]*60-entity[custom.unitHour]*60*60-entity[custom.unitDay]*24*60*60;
	
	var output = '';
	for(var i in entity){
		if(entity[i]>0){
			output+=entity[i]+i+' ';
		}
	}

	var mintime = (custom.showSeconds ? 0:60)+1;
	
	if(timer>mintime){
		$(this).text(custom.prependMsg+' '+output+' '+custom.appendMsg).doTimeout(custom.updateInterval*1000, function(){
			$(this).timer(end,custom,callback);
		});
	}else{
		if(timer<=0){
			if($.isFunction(callback))
				callback.call(this);
			$(this).text(custom.finishMsg);
			return $(this);
		}
		$(this).text(custom.prependMsg+' < 1'+(custom.showSeconds ? custom.unitSecond:custom.unitMinute)+' '+custom.appendMsg).doTimeout(1000, function(){
			$(this).timer(end,custom,callback);
		});
	}
};

})(jQuery);