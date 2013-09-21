$(document).ready(function(){
  $('#fittings1 ul:not(.f_class)').css({display:'none'});
	$('#fittings1>ul').each(function(){
    $(this).find('a').click(function(){
      $(this).parent('li').children('ul').slideToggle();
      return false;
    });
  });
  
  $('.shipFitt').click(function(){		
		var fitID = $(this).attr('id');
		makeFitClick(fitID);
  });
  
  $('.saveButton').click(function() {
    // validate and process form here
	var fitArray = $('textarea#fitt').val();
	var user = $('div#fittop li.selected').attr('id');
	if(fitArray == ''){
	  $('div#addFit').hide();
	  return false;
	}
	var request = 'dowork.php?module=eveorder&action=ajaxfit&user='+user
	
	$.ajax({
		type:"POST",
		url:request,
		processData:true,
		dataType: "json",
		data: {fitArray:fitArray},
		success: function(json2){
			$('div#addFit').hide();
			makeFitting(json2);
		}
	});
  });
  
  $('div#fittop li#add').click(function() {
	$('div#addFit textarea#fitt').val('');
	$('div#addFit').show();
	$('div#addFit').center();
  });
  
  $('.closeButton').click(function() {
	$('div#addFit').hide();
  });
  
  $('div#fittop li:not(.right)').click(function() {
	var user = $(this).attr('id');
	var request = 'dowork.php?module=eveorder&action=ajaxfit&user='+user
	$.ajax({
		type:"POST",
		url:request,
		processData:true,
		dataType: "json",
		data: {user:user},
		success: function(json1){
			makeFitting(json1);
			$('div#fittop li').removeClass('selected');
			$('div#fittop li#'+user).addClass('selected');
			$('div#fittop li#del').remove();
			//$('div#fittop li#order').remove();
			$('div#addFit').hide();
		}
	});
  });
  

});

function fittIcon(icon) {
	var out = '<span class="item-icon" style="position:absolute; height:32px; width:32px; text-align:left;">'
			
			+ '<img style="position:absolute; z-index: 1; height:32px; width:32px;" src="icons/'+ icon.img +'" title="'+ icon.name +'" alt="'+ icon.name +'" />'
			+ '</span>'
	return out;
}

function fittIconAmmo(ammo) {
	var out = '<span class="item-icon" style="position:absolute; height:32px; width:32px; text-align:left;">'
			
			+ '<img style="position:absolute; z-index: 1; height:24px; width:24px;" src="icons/'+ ammo.img +'" title="'+ ammo.name +'" alt="'+ ammo.name +'" />'
			+ '</span>'
	return out;
}

function makeFitting(json1){
	$('div#fittings1').html('<ul class="f_class" style="text-align:left;">');
	for(var group in json1){
		var groupp = group.replace(/ /gi, '');
		$('div#fittings1 ul.f_class').append('<li id="'+groupp+'"><a href="#">'+group+'</a>');
		$('div#fittings1 li#'+groupp).append('<ul class="f_ship" style="">');
		for(var ship in json1[group]){
			var shipp = ship.replace(/ /gi, '');
			$('div#fittings1 li#'+groupp+' ul.f_ship').append('<li id="'+shipp+'"><a href="#">'+ship+'</a>');
			$('div#fittings1 li#'+shipp).append('<ul class="f_fit">');
			for(var i=0;i<json1[group][ship].length;i++){
				$('div#fittings1 li#'+shipp+' ul.f_fit').append('<li class="shipFitt" id="'+json1[group][ship][i].fittID+'">'+json1[group][ship][i].name+'</li>');
			}
			
		}
		
	}
	$('#fittings1 ul:not(.f_class)').css({display:'none'});
	$('#fittings1>ul').each(function(){
		$(this).find('a').click(function(){
			$(this).parent('li').children('ul').slideToggle();
			return false;
		});
	});
	$('.shipFitt').click(function(){		
		var fitID = $(this).attr('id');
		makeFitClick(fitID);
    });
}

function makeFitClick(fitID) {
		var user = $('div#fittop li.selected').attr('id');
		var request = 'dowork.php?module=eveorder&action=ajaxfit&user='+user
		$.ajax({
			type:"POST",
			url:request,
			processData:true,
			dataType: "json",
			data: {fitID:fitID},
			success: function(json){
				if(json){
				
					if(json.imgShip){$('div#bigship').html('<img width="256" height="256" src="'+ json.imgShip +'" style="border:0;" alt="" />');}else{$('div#bigship').html('<img src="modules/eveorder/img/panel/wreck.png" alt="" />');}
					
					if(json.hiSlots){$('div#high0').html('<img src="modules/eveorder/img/panel/'+ json.hiSlots +'h_T3.gif" style="border:0;" alt="" />');}else{$('div#high0').html('<img src="modules/eveorder/img/panel/h_T3.gif" style="border:0;" alt="" />');}
					if(json.medSlots){$('div#mid0').html('<img src="modules/eveorder/img/panel/'+ json.medSlots +'m_T3.gif" style="border:0;" alt="" />');}else{$('div#med0').html('<img src="modules/eveorder/img/panel/m_T3.gif" style="border:0;" alt="" />');}
					if(json.lowSlots){$('div#low0').html('<img src="modules/eveorder/img/panel/'+ json.lowSlots +'l_T3.gif" style="border:0;" alt="" />');}else{$('div#low0').html('<img src="modules/eveorder/img/panel/l_T3.gif" style="border:0;" alt="" />');}
					if(json.rigSlots){$('div#rig0').html('<img src="modules/eveorder/img/panel/'+ json.rigSlots +'r_T3.gif" style="border:0;" alt="" />');}else{$('div#rig0').html('<img src="modules/eveorder/img/panel/r_T3.gif" style="border:0;" alt="" />');}
					if(json.subSlots){$('div#sub0').html('<img src="modules/eveorder/img/panel/'+ json.subSlots +'s_T3.gif" style="border:0;" alt="" />');}else{$('div#sub0').html('<img src="modules/eveorder/img/panel/s_T3.gif" style="border:0;" alt="" />');}
          
          			$('div.slots>div').html('');
           
					for(var rack in json.module.fitting){
						for(var i=0;i<json.module.fitting[rack].length;i++){ 
						  $('div#'+rack+(i+1)).html(fittIcon(json.module.fitting[rack][i].icon));
						  if(json.module.fitting[rack][i].ammo){
						  	$('div#'+rack+(i+1)+'l').html(fittIconAmmo(json.module.fitting[rack][i].ammo));
						  }
						}
					}
					
					$('div#fittop li#del').remove();
					//$('div#fittop li#order').remove();
					$('div#fittop ul').append('<li class="right" id="del">Delete</li>');//.append('<li class="right" id="order">Order</li>');
					$('div#fittop li#del').click(function() {
						
						$.post(request, {delFit:fitID}, function(){
							var g = $('div#fittings1 li#'+fitID).parent().parent().attr('id');
							
							$('div#fittings1 li#'+fitID).remove();
							if(!$('div#fittings1 li#'+g+' ul li').length > 0) {
								var gg = $('div#fittings1 li#'+g).parent().parent().attr('id');
								$('div#fittings1 li#'+g).remove();
							}
							if(!$('div#fittings1 li#'+gg+' ul li').length > 0) $('div#fittings1 li#'+gg).remove();
							$('div#fittop li#del').remove();
							//$('div#fittop li#order').remove();
							$('div.slots>div').html('');
							$('div#layout').html('');
						});
					});
					
					$('div#layout').html('<form id="orderForm" action="dowork.php" method="post">');
					$('form#orderForm').append('<input type="hidden" name="module" value="eveorder">');
					$('form#orderForm').append('<input type="hidden" name="action" value="saveOrderFromFitmenu">');
					$('form#orderForm').append('<table class="fittable" width="500" border="0" cellspacing="0">');
					$('table.fittable').append('<tr class="top"><td class="icon">'+ fittIcon(json.module.ship.icon) +'</td>'
						+ '<td><b>'+ json.module.ship.icon.name +' - ' + json.module.ship.stuf.name + '<b/><span style="float:right"><img width="16" height="16" src="icons/'+ json.module.ship.stuf.skillOK +'" alt="" border="0" /></span></td>'
						+ '<td align="center" width="30">1</td>'
						+ '<td align="center"><input type="checkbox" name="items['+ json.module.ship.stuf.ship +'][1]" checked/></td></tr>');
					for(var rack in json.module.order){
						if(rack=='high')$('table.fittable').append('<tr><td class="icon"><img width="32" height="32" src="icons/Icons/items/8_64_11.png" alt="Fitted - High slot" border="0" /></td><td colspan="2"><b>Fitted - High slot</b></td>	<td align="center"><b>Order?</b></td>  </tr>');
						if(rack=='mid')$('table.fittable').append('<tr><td class="icon"><img width="32" height="32" src="icons/Icons/items/8_64_10.png" alt="Fitted - Med slot" border="0" /></td><td colspan="2"><b>Fitted - Med slot</b></td>	<td align="center"><b>Order?</b></td>  </tr>');
						if(rack=='low')$('table.fittable').append('<tr><td class="icon"><img width="32" height="32" src="icons/Icons/items/8_64_9.png" alt="Fitted - Low slot" border="0" /></td><td colspan="2"><b>Fitted - Low slot</b></td>	<td align="center"><b>Order?</b></td>  </tr>');
						if(rack=='rig')$('table.fittable').append('<tr><td class="icon"><img width="32" height="32" src="icons/Icons/items/68_64_1.png" alt="Fitted - Rig slot" border="0" /></td><td colspan="2"><b>Fitted - Rig slot</b></td>	<td align="center"><b>Order?</b></td>  </tr>');
						if(rack=='sub')$('table.fittable').append('<tr><td class="icon"><img width="32" height="32" src="icons/Icons/items/76_64_4.png" alt="Fitted - Subsystems" border="0" /></td><td colspan="2"><b>Fitted - Subsystems</b></td>	<td align="center"><b>Order?</b></td>  </tr>');
						if(rack=='drone')$('table.fittable').append('<tr><td class="icon"><img width="32" height="32" src="icons/Icons/items/2_64_10.png" alt="Drone Bay" border="0" /></td><td colspan="2"><b>Drone Bay</b></td>	<td align="center"><b>Order?</b></td>  </tr>');
						for(var i in json.module.order[rack]){ 
						  $('table.fittable').append(tableadd(json.module.order[rack][i]));
						}
					}
					$('table.fittable').append('<tr><td colspan="4" align="center">'
						+ '<span style="float:left">For Corp?:<input type="checkbox" name="corp" /></span>'
						+ '<span style="float:none">Amount:<input type="text" name="amount" value="1" size="5"/></span>'
						+ '<span style="float:right"><input type="submit" value="Order Fit" id="orderFitting" /></span>'
						+ '</td></tr>');
				}
			}
		});
}

function tableadd(item) {
	var out = '<tr class="row"><td class="icon">'+ fittIcon(item.icon) +'</td>'
			+ '<td>'+ item.icon.name +'<span style="float:right"><img width="16" height="16" src="icons/'+ item.skillOK +'" alt="" border="0" /></span></td>'
			+ '<td align="center" width="30">'+ item.anzahl +'</td>'
			+ '<td align="center"><input type="checkbox" name="items['+ item.itemID +']['+ item.anzahl +']" checked/></td></tr>'
	return out;
}

$.fn.center = function () {
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
}
