<div id="title">&raquo; POS</div>
{if ($corps)}
<div id="pos">
<form action="{$index}&action={$Status}" method="post">
  <select name="corpid" onchange="submit()">
  {html_options options=$corps selected=$sel_corp}
  </select>
</form>
</div>
{/if}
<div id="menu">
<ul class="items">
  {if $curUser->Admin || $curUser->Manager || $curUser->PosManager || $curUser->posalt}
	<li{if $action == "online"} id="selected"{/if}><a href="{$index}&action=online">online</a></li>
	<li{if $action == "offline"} id="selected"{/if}><a href="{$index}&action=offline">offline</a></li>
	{if $action == "editPos"}<li id="selected">POS Editor</li>{/if}
	<li{if $action == "phpBB"} id="selected"{/if}><a href="{$index}&action=phpBB">to phpBB</a></li>
    <li{if $action == "fuelBill"} id="selected"{/if}><a href="{$index}&action=fuelBill">Fuel Bill</a></li>
    <li {if $action == "globalTower"}id="selected"{/if}><a href="{$index}&action=globalTower">Global Tower</a></li>
  {else}
    <li id="selected">Global Tower</li>
  {/if}
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
  {if $curUser->Admin || $curUser->Manager || $curUser->PosManager || $curUser->posalt}
    {if $canEdit}<li class="right"{if $action == "settings"} id="selected"{/if}><a href="{$index}&action=settings">Options</a></li>{/if}
  {/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br/>