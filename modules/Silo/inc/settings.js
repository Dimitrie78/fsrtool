$(document).ready(function(){ 
	$(":button").css({background:"green", border:"2px black solid", cursor:"pointer"});
	$(":button#delall").css({background:"red", color:"black", border:"2px black solid", cursor:"pointer"});
	$("#result").css({color:"red"});
	
	$("#update").click(function(){
		$("#result").html('Wait while loading Data...');
		$.post('dowork.php', {module:'Pos', action:'update'}, function(data) {
			$("#result").html(data);
		});
	});
	
	$("#price").click(function(){
		$("#result").html('Wait while loading Data...');
		$.post('dowork.php', {module:'Silo', action:'eve-central'}, function(data) {
			$("#result").html(data);
			location.reload();
		});
	});
	
	$(":button#delall").click(function(){
		//$("#result").html('Wait while deleting account and all Data...');
		var corpid = $('#corpid').val();
		$.msgBox({
			title: "Are You Sure",
			content: "Would you delete all Stuff?",
			type: "confirm",
			buttons: [{ value: "Yes" }, { value: "No" }],
			success: function (result) {
				if (result == "Yes") {
					$.post('dowork.php', {module:'Silo', action:'delall', corpid:corpid}, function(data) {
						$("#result").html(data);
					});
				}
			}
		});
		
	});
	
}); 