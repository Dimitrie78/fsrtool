/**
 *
 * Timer Funktion
 *   
 * erwartete Parameter: 
 *   keine
 * optionaler Parameter "end":
 *   typ: Unix Timestamp (int/string)
 *	 gibt die verbleibende Zeit an, wird ein String angegeben, wird versucht diesen zu parsen
 *   ist kein end-Parameter angegeben wird versucht aus dem Inhalt des Elements einen UNIX-Timestamp zu lesen
 * optionaler Parameter "params":
 *   typ: Object
 *   wird "params" nicht angegeben, werden die Standardwerte der Funktion benutzt
 *   Konfigurationsparameter mit folgenden Einstellmöglichkeiten:
 *     prependMsg
 *       typ: String
 *       vor dem Zeitstring angehängter String
 *       Standard: ''
 *     appendMsg
 *       typ: String
 *       nach dem Zeitstring angehängter String
 *       Standard: ''
 *     finishMsg
 *       typ: String
 *       String, der angezeigt wird, wenn der Timer abgelaufen ist
 *       Standard: 'ready'
 *     showSeconds
 *       typ: bool
 *       Bool'scher Wert ob Sekunden angezeigt werden sollen
 *       Standard: false
 *     updateInterval
 *       typ: Integer
 *       Aktualisierungsintervall in Sekunden
 *       Standard: 10
 *     unitDay / unitHour / unitMinute / unitSecond
 *       typ: String
 *       Bezeichner für den Entsprechenden Zeitwert
 *       Standard: 'd' / 'h' / 'm' / 's'
 *     callback
 *       typ: Funktion
 *	     Ruft eine Callback-Funktion auf sobald der Timer abgelaufen ist.
 **/

(function($){

$.fn.extend({ 
	timer : function(options){
		
		this.init = function(autorun){
			if(!isNaN(settings.end)){
				$this.css('display','inline').data('timerRunning',true);
				if(typeof autorun!=='undefined' && autorun){
					this.tick();
				}
			}
			return this;
		};
		
		this.tick = function(){
			if($this.data('timerRunning')==false){
				return this;
			}
			var _this = this,
				time = Math.round(new Date().getTime()/1000),
				timer = settings.end-time,
				entity = {},
				output = '',
				mintime = (settings.showSeconds ? 0:60)+1;
			if(timer<=0){
				if($.isFunction(settings.callback))
					settings.callback.call(this);
				return $this.text(settings.finishMsg).data('timerRunning',false);
			}
			entity[settings.unitDay] = Math.floor(timer/60/60/24);
			entity[settings.unitHour] = Math.floor(timer/60/60)-entity[settings.unitDay]*24;
			entity[settings.unitMinute] = Math.floor(timer/60)-entity[settings.unitHour]*60-entity[settings.unitDay]*24*60;
			if(settings.showSeconds){
				entity[settings.unitSecond] = timer-entity[settings.unitMinute]*60-entity[settings.unitHour]*60*60-entity[settings.unitDay]*24*60*60;
			}
			for(var i in entity){
				if(entity[i]>0){
					output+=entity[i]+i+' ';
				}
			}
			
			if(timer>mintime){
				$this.text(settings.prependMsg+' '+output+' '+settings.appendMsg).each(function(){
					$this.data('timerRunning',setTimeout(function(){
						_this.tick();
					},settings.updateInterval*1000));
				});
			}else{
				$this.text(settings.prependMsg+' < 1'+(settings.showSeconds ? settings.unitSecond : settings.unitMinute)+' '+settings.appendMsg).each(function(){
					$this.data('timerRunning',setTimeout(function(){
						_this.tick();
					},1000));
				});
			}
			return this;
		};
		
		this.destroy = function(){
			clearTimeout($this.data('timerRunning'));
			return $this.data('timerRunning',false);
		};
		
		var $this = $(this), 
			settings = {
				end: Math.round((new Date().getTime()+($this.text()*1000))/1000),
				prependMsg: '',
				appendMsg: '',
				finishMsg: 'ready',
				showSeconds: false,
				updateInterval: 10,
				unitDay: 'd',
				unitHour: 'h',
				unitMinute: 'm',
				unitSecond: 's',
				callback: $.noop()
			};

		if(options=='destroy'){
			this.destroy();		
		}
		$.extend(settings,options);
		
		return this;
		
	}
});

})(jQuery);