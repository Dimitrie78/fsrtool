<div id="menu">
<ul class="items">
{if ($HighCommand || $Leader || $curUser->Manager)}
	<li{if $action == 'news'} id="selected"{/if}><a href="{$index}&amp;action=news"  >News</a></li>
	<li{if $action == 'member'} id="selected"{/if}><a href="{$index}&amp;action=member">Member list</a></li>
	<li{if $action == 'div'} id="selected"{/if}><a href="{$index}&amp;action=div"   >Division list</a></li>
	<li{if $action == 'flags'} id="selected"{/if}><a href="{$index}&amp;action=flags" >Flags</a></li>
	<li{if $action == 'stats'} id="selected"{/if}><a href="{$index}&amp;action=stats" >Statistics</a></li>
	<li{if $action == 'kill'} id="selected"{/if}><a href="{$index}&amp;action=kill"  >Kill Activity</a></li>
	<li{if $action == 'eval'} id="selected"{/if}><a href="{$index}&amp;action=eval"  >Evaluation</a></li>
	<li{if $action == 'carebears'} id="selected"{/if}><a href="{$index}&amp;action=carebears">Carebears</a></li>
{/if}
	{if $curUser->Manager}<li class="right"><a href="{$url_index}?module=userManager">userManager</a></li>{/if}
</ul>
</div>