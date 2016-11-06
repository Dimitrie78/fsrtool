function toggleOrders(user){
	$(document).ready(function(){
		$('tr.'+user).each(function(){
			if($(this).css('display')=='none'){
				$(this).css('display','');
			}else{
				$(this).css('display','none');
			}
		});	
	});
}

function checkOrders(user){
	$('input#check_'+user).attr('checked', true);
}

function clipboard(user){
	var request = 'dowork.php?module=eveorder&action=clipboard'
	var log = '';
    
    $('div#addFit textarea#fitt').val('');
    
	$.ajax({
		type:"POST",
		url:request,
		processData:true,
		dataType: "json",
		data: {user:user},
		success: function(data){
			for(var i=0;i<data.length;i++){ 
                log += data[i]+" \n";
            }
            console.log(log);
            $('div#addFit textarea#fitt').val(log);
            $('div#addFit').show();
            $('div#addFit').center();
            $('div#addFit textarea#fitt').select();
            
		}
	});
}

$(document).ready(function(){
	
	$('tr.merge_group').hover(
			function(){
				$('+ tr.merge', this).css('background-color','#800000');
				$(this).css('background-color','#800000');
			},
			function(){
				$('+ tr.merge', this).css('background-color','');
				$(this).css('background-color','');
			}
	).css('cursor','pointer');
	$('input#open_all').click(function(){
		if($('input#open_all:checked').length == 0){
			$('tr.merge').css('display','none');
		}else{
			$('tr.merge').css('display','');
		}
	});
	$('form#settings input:radio').change(function(){
		$('form#settings').submit();
	});
});

$.fn.center = function () {
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
    this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
    return this;
}