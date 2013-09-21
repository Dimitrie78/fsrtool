{if $error}
<p><b>You need a key with the red-backed masks</b></p>
{else}<p>Hello {$uName}, that is you current accessMask</p>{/if}

<table border="0" cellpadding="1" cellspacing="0" style="border: 2px solid #000">
	<tr bgcolor="#430" style="color:#FFF"><td colspan="3" align="center">Character</td></tr>
{foreach from=$list item=group}
	<tr bgcolor="#000000" style="color:#FFF"><td colspan="3">{$group.des}</td></tr>
	{foreach from=$group key=key item=type}
	{if $key !== 'des'}
	  {if $type.accessMask & $mask}
		{if $error && in_array($type.name, $error)}<tr bgcolor="#FF0000"><td><img src="icons/tick.png" width="12" height="12" /></td><td>{$type.name}</td><td>{$type.description}</td></tr>
		{else}<tr><td><img src="icons/tick.png" width="12" height="12" /></td><td>{$type.name}</td><td>{$type.description}</td></tr>{/if}
	  {else}
		{if $error && in_array($type.name, $error)}<tr bgcolor="#FF0000"><td><img src="icons/cross.png" width="12" height="12" /></td><td>{$type.name}</td><td>{$type.description}</td></tr>
		{else}<tr><td><img src="icons/cross.png" width="12" height="12" /></td><td>{$type.name}</td><td>{$type.description}</td></tr>{/if}
	  {/if}
	{/if}
	{/foreach}
{/foreach}
</table>