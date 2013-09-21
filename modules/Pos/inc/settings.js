$(document).ready(function(){ 
	$(":button").css({background:"green", border:"2px black solid", cursor:"pointer"});
	$("#result").css({color:"red"});
	$("#update").click(function(){
		$("#result").html('Wait while loading Data...');
		$.post('dowork.php', {module:'Pos', action:'update'}, function(data) {
			$("#result").html(data);
		});
	});
	
}); 