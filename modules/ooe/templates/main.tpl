{include file="header.tpl"}    

<div id="title">&raquo; Out of EvE</div>
{if ($users)}
<div id="pos">
<form action="{$index}" method="post">
  <input type="hidden" name="action" value="{$action}" />
  <select name="cid" onchange="submit()">
  {html_options options=$users selected=$sel_char}
  </select>
</form>
</div>
{/if}
<div id="menu">
<ul class="items">
	<li {if $action == 'accStatus'}id="selected"{/if}><a href="{$index}&action=accStatus">Account Status</a></li>
	<li {if $action == 'eveMails'}id="selected"{/if}><a href="{$index}&action=eveMails">Eve Mails</a></li>
	<li {if $action == 'eveAssets'}id="selected"{/if}><a href="{$index}&action=eveAssets">Eve Assets</a></li>
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}

{if $error}
	{include file="file:[ooe]accessMask.tpl"}
{else}
	{if $action != ''}
	{include file="file:[ooe]$action.tpl"}
	{else}
	{include file="file:[ooe]accessMask.tpl"}
	{/if}
{/if}
{include file="footer.tpl"}