{* Smarty *}
{************ userList.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}

<div id="title">&raquo; userManager</div>
{if $corps}
<div id="pos">
<form action="{$index}" method="post">
  <select name="corpID" onchange="submit()">
   {html_options options=$corps selected=$selectedCorp}
  </select>
  {if $action != ''}
  <input type="hidden" name="action" value="{$action}" />
  {/if}
</form>
</div>
{/if}
<div id="menu">
<ul class="items">
	{if $curUser->Manager}<li id="selected" class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
	<li {if $action == ''}id="selected"{/if}><a href="{$index}">userList</a></li>
	<li {if $action == 'roleList'}id="selected"{/if}><a href="{$index}&action=roleList">roleList</a></li>
	{if $curUser->Manager}<li {if $action == 'roleListAlts'}id="selected"{/if}><a href="{$index}&action=roleListAlts">roleListAlts</a></li>{/if}
	{if $curUser->Admin}<li {if $action == 'cron'}id="selected"{/if}><a href="{$index}&action=cron">cronSettings</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}

{if $action != ''}
{include file="file:[userManager]$action.tpl"}
{else}
{include file="file:[userManager]userList.tpl"}
{/if}

{include file="footer.tpl"}