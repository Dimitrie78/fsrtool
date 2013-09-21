<p><input name="search" value="" id="id_search" placeholder="Search User" /></p>
<div>
<table class="user" cellspacing="0" cellpadding="3">
  <thead>
    <tr><td class="head" colspan="{$roles.head|@count}"><span style="color:red;">Inactive Users - they contain no valid key</span></td></tr>
	<tr>
	{foreach from=$roles.head item=head}
	  <td>{$head}</td>
	{/foreach}
	</tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {foreach from=$roles.body item=body key=charID}
  {if $body._act == '0' or $body._act == ''}
  <tr {if $body._act == ''}bgcolor="red"{else}bgcolor="#181818"{/if}>
	<td>{counter}</td>
	{foreach from=$body item=name key=k}
	{if $k[0] == '_'}
	{elseif $k == 'uname'}
	<td>{$name}{if $body._act == ''}{$charID}{/if}</td> 
	<td>&nbsp;</td>
	{else}
	<td>{$name}</td>
    <td colspan="3">&nbsp;</td>
	
	{/if}
	{/foreach}
  </tr>
	{foreach from=$body._alts item=alts}
  <tr id="user_{$charID}_{$alts._charID}" class="{cycle values="up,down"}">
	<td>&nbsp;</td>
	<td>{$alts._mname}</td>
	{foreach from=$alts item=alt key=kk}
	{if $kk[0] == '_'}
	{elseif $kk == 'charName' or $kk == 'altCorp'}
	<td>{$alt}</td>
	{else}
	<td>{if $alt == '1'}<img id="{$kk}" class="altrole" src="icons/tick.png" />{else}<img id="{$kk}" class="altrole" src="icons/cross.png" />{/if}</td>
	{/if}
	{/foreach}
  </tr>
	{/foreach}
  {/if}
  {/foreach}
  
  </tbody>
</table>
</div>

<br />

<div>
<table class="user" cellspacing="0" cellpadding="3">
  <thead>
	<tr><td class="head" colspan="{$roles.head|@count}"><span style="color:green;">Active Users - they contain valid key</span></td></tr>
    <tr>
	{foreach from=$roles.head item=head}
	  <td>{$head}</td>
	{/foreach}
	</tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {foreach from=$roles.body item=body key=charID}
  {if $body._act == '1'}
  <tr bgcolor="#181818">
	<td>{counter}</td>
	{foreach from=$body item=name key=k}
	{if $k[0] == '_'}
	{elseif $k == 'uname'}
	<td>{$name}</td>
	<td>&nbsp;</td>
	{else}
	<td>{$name}</td>
    <td colspan="3">&nbsp;</td>
	
	{/if}
	{/foreach}
  </tr>
	{foreach from=$body._alts item=alts}
  <tr id="user_{$charID}_{$alts._charID}" class="{cycle values="up,down"}">
	<td>&nbsp;</td>
	<td>{$alts._mname}</td>
	{foreach from=$alts item=alt key=kk}
	{if $kk[0] == '_'}
	{elseif $kk == 'charName' or $kk == 'altCorp'}
	<td>{$alt}</td>
	{else}
	<td>{if $alt == '1'}<img id="{$kk}" class="altrole" src="icons/tick.png" />{else}<img id="{$kk}" class="altrole" src="icons/cross.png" />{/if}</td>
	{/if}
	{/foreach}
  </tr>
	{/foreach}
  {/if}
  {/foreach}
  
  </tbody>
</table>
</div>