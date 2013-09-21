<div id="title">&raquo; JB</div>
<div id="pos">... under Construction ...</div>
<div id="menu">
<ul class="items">
	<li {if $action == "main"}id="selected"{/if}><a href="{$index}&action=main">Main</a></li>
	<li {if $action == "options"}id="selected"{/if}><a href="{$index}&action=options">Options</a></li>
	<li {if $action == "api"}id="selected"{/if}><a href="{$index}&action=api">Fetch API</a></li>
	
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>