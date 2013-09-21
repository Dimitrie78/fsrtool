<div id="title">&raquo; eveOrder</div>
{if isset($corps)}
<div id="pos">
<form action="{$url_index}?module=eveorder&action=openOrders" method="post">
  <select name="corpid" onchange="submit()">
  {html_options options=$corps selected=$sel_corp}
  </select>
</form>
</div>
{else}
<div id="pos">
<form action="{$url_index}?module=eveorder&action={$action}" method="post">
  <select name="userID" onchange="submit()">
  {html_options options=$users selected=$sel_user}
  </select>
</form>
</div>
{/if}
<div id="menu">
<ul class="items">
	<li{if $action == 'main'} id="selected"{/if}><a href="{$index}&amp;action=main">{$language.order}</a></li>
    <li{if $action == 'Fittings'} id="selected"{/if}><a href="{$index}&amp;action=Fittings">Fittings</a></li>
	<li{if $action == 'myOrders'} id="selected"{/if}><a href="{$index}&amp;action=myOrders">{$language.my_orders}</a></li>
	<li{if $action == 'myFavorites'} id="selected"{/if}><a href="{$index}&amp;action=myFavorites">{$language.my_favorites}</a></li>
	{if $openOrders}
	<li{if $action == 'openOrders'} id="selected"{/if}><a href="{$index}&amp;action=openOrders">{$language.overview}</a></li>{/if}
	<li{if $action == 'stats'} id="selected"{/if}><a href="{$index}&amp;action=stats">{$language.statistics}</a></li>
	{if $curUser->CorpOrder}<li{if $action == 'shipRep'} id="selected"{/if}><a href="{$index}&amp;action=shipRep">Ship Manager</a></li>{/if}
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>
</div> {* end of div started in header.tpl *}
<br />