{* Smarty *}
{************ menu.tpl *************************************}
<div id="title">&raquo; MiningMax - {$title}</div>
<div id="menu">
<ul class="items">
	<li{if $action==""} id="selected"{/if}><a href="{$index}">Aktuelle Mining Operationen</a></li>
	<li{if $action=="highscore"} id="selected"{/if}><a href="{$index}&action=highscore">Highscore</a></li>
	{if $MySelf->canCreateRun() == "1"}<li{if $action=="newrun"} id="selected"{/if}><a href="{$index}&action=newrun">Starte neuen OP</a></li>{/if}
	<li{if $action=="MinsPreise"} id="selected"{/if}><a href="{$index}&action=MinsPreise">Mineralienpreise</a></li>
	<li{if $action=="Skills"} id="selected"{/if}><a href="{$index}&action=Skills">Mining Div Skills</a></li>
	<li{if $action=="project"} id="selected"{/if}><a href="{$index}&action=project">Projekt</a>
	{if $curUser->Manager}<li class="right"><a href="{$url_index_module}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />
{* ------------------------------- *}
{include file="../modules/MiningMax/templates/ticker.tpl"}
{* ------------------------------- *}