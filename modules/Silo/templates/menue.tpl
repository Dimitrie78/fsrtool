
<div id="title">&raquo; Silo Management</div>
{if ($corps)}
<div id="pos">
<form action="{$url_index}?module=Silo" method="post">
  <select name="corpid" onchange="submit()">
  {html_options options=$corps selected=$sel_corp}
  </select>
</form>
</div>
{/if}
<div id="menu">
<ul class="items">
	<li{if ($action == "Silos")} id="selected"{/if}><a href="{$index}&action=Silos">{$language.overview}</a></li>
	
	<li{if $MySelectetMenue != "Silos" and $MySelectetMenue} id="selected"{/if}>
	<form action="{$index}" method="get">
	<input type="hidden" name="module" value="Silo"/><input type="hidden" name="action" value="system"/>
	<select style="position:relative;top:-2px;border-width: 1px;font-size:0.8em;" name="id" onchange="submit()">
	<option value="">System ...</option>
{foreach from=$Menue item=systems key=region}
	<optgroup label="{$region}">
	{foreach from=$systems item=system}
		<option value="{$system.id}"{if $MySelectetMenue == $system.id} selected{/if}>{$system.Name}</option>
	{/foreach}
	</optgroup>
{/foreach}
	</select>
	</form>
	</li>
{if (count($manager) >= 1)}
	<li{if isset($MySelectetManager) && $MySelectetManager != ''} id="selected"{/if}>
	<form action="{$index}" method="get">
	<input type="hidden" name="module" value="Silo"/><input type="hidden" name="action" value="system"/>
	<select style="position:relative;top:-2px;border-width: 1px;font-size:0.8em;" name="manager" onchange="submit()">
	<option value="">Manager ...</option>
	{foreach from=$manager item=man}
		<option value="{$man}"{if $MySelectetManager == $man} selected{/if}>{$man}</option>
	{/foreach}
	</select>
	</form>
	</li>
{/if}	
	<li{if ($action == "calendar")} id="selected"{/if}><a href="{$index}&action=calendar">Calendar</a></li>
	<li{if ($action == "help")} id="selected"{/if}><a href="{$index}&action=help">Help</a></li>

	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
	<li class="right"{if ($action == "settings")} id="selected"{/if}><a href="{$index}&action=settings">Options</a></li>
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />
Assets picked: {$CacheTime} GMT
{if $ApiStatus}{include file="file:[Silo]ApiStatus.tpl"}{/if}