<style type="text/css">
	#sortable { list-style-type: none; margin: 0; padding: 0; width: 100px; }
	#sortable li { margin: 1px; padding: 1px; border: 1px solid #000; text-align:left; }
</style>

<script type="text/javascript">
	$(document).ready(function(){
		$('ul#sortable li').hover(function(){
			$(this).css('background-color','#467c15');
		},
		function(){
			$(this).css('background-color','');
		});
		$("#sortable").sortable({
          update : function() {
            var serial = $('#sortable').sortable('serialize');
            alert(serial);
		  }
		});
		$("#sortable").disableSelection();
	});
</script>

<div id="targetSystems">
  
  <ul id="sortable">
	<li id="menu_1">Item 1<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li> 
	<li id="menu_2">Item 2<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li>
	<li id="menu_3">Item 3<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li>
	<li id="menu_4">Item 4<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li>
	<li id="menu_5">Item 5<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li>
	<li id="menu_6">Item 6<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li>
	<li id="menu_7">Item 7<span style="float:right"><a href="#"><img alt="delete" title="delete" src="icons/delete.png" /></a></span></li>
  </ul>
  
  
  <form action="{$index}" method="post">
    <input type="hidden" name="module" value="eveorder" />
    <input type="hidden" name="action" value="" />
	<input type="text" name="ort" />
	<input type="submit" value="add" />
  </form>

</div>