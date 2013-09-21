(function($){

$.fn.dg = function(){
	
	if(arguments[0] < 1){
		var decPl = 0;
		var decPt = '';
	}else{
		var decPl = arguments[0] || 2;				// Dezimalstellen, Standard 2
		var decPt = ',';							// Ganzzahl-Dezimal-Trennzeichen
	}
	var thSep = '.';								// Tausender-Trennzeichen

	var decPart = 0;
	var intPart = 0;
	
	$(this).bind({
		
		clean: function(){
			var regExp = new RegExp('[^0-9'+decPt+']','g');					// Alle nichtnumerischen Zeichen  
			var cleaned = $(this).attr('value').replace(regExp,'');			// bis auf G/Z-Trennzeichen löschen
			decPart = cleaned.replace(/(\d*,)|.*/,'').replace(/[,]/g,'');	// Nachkomma G/Z-Trennzeichen entfernen
			
			intPart = parseInt(cleaned.replace(/,.*/,''),10)*1;				// führende Nullen im Ganzzahlteil entfernen, umwandlung in INT
			if(intPart+'' == 'NaN')
				intPart = 0; 
				
			decPart = Math.round(Number('1.'+decPart)*Math.pow(10,decPl)).toString();	// Nachkomma auf decPl-Stellen runden
			if(decPart.substring(0,1)>1){												// Rundungs-fix
				intPart += decPart.substring(0,1)-1;
			}
			decPart = decPart.substring(1);
		},
		
		change: function(){
			$(this).trigger('clean');
 			var arr = intPart.toString().split('').reverse();
			var res = new Array();
			for (var i = 0;i<arr.length;i++){											// Tausender-Trennzeichen einfügen
				res.unshift(arr[i]);
				if((i+1) % 3 == 0 && i+1<arr.length && arr[i+1] != thSep){
					res.unshift(thSep);
				}
			}
			$(this).attr('value',res.join('')+decPt+decPart);
		},
		
		focusin: function(){
			$(this).trigger('clean').select();
		},
		
		focusout: function(){
			$(this).trigger('change');
		}
	
	});
	
	$(this).trigger('change');
	return $(this);
	
};

})(jQuery);