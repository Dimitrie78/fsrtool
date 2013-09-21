<p><input name="search" value="" id="id_search" placeholder="Search User" /></p>

<div>
<table class="user" cellspacing="0" cellpadding="3">
  <thead>
    <tr><td class="head" colspan="{$users.head|@count}"><span style="color:red;">Inactive Users - they contain no valid key</span></td></tr>
	<tr>
	{foreach from=$users.head item=head}
	  <th>{$head}</th>
	{/foreach}
	</tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {foreach from=$users.body item=body}
  {if $body._act == '0'}
  <tr id="user_{$body._charID}" class="{cycle values="up,down"}">
	<td>{counter}</td>
	{foreach from=$body item=name key=k}
	{if $k[0] == '_'}
	{elseif $k == 'lastlogin' or $k == 'created'}
	<td>{if ($chosenLanguage == "DE")}{$name|date_format:"%d.%m.%Y %H:%M"}{else}{$name|date_format:"%m/%d/%Y %I:%M %p"}{/if}</td>
	{else}
	<td>{$name}</td>
	{/if}
	{/foreach}
    <td align="center"><img class="edit" src="icons/wrench.png" /></td>
	<td align="center"><img class="del" src="icons/delete.png" /></td>
  </tr>
	{foreach from=$body._alts item=alts}
  <tr id="user_{$body._charID}" bgcolor="#181818"> 
	<td>&nbsp;</td>
	{foreach from=$alts item=alt key=kk}
	{if $kk[0] == '_'}
	{elseif $kk == 'charName'}
	<td>{$alt}</td>
	<td>ALT</td>
	{else}
	<td colspan="7">{$alt}</td>
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
	<tr><td class="head" colspan="{$users.head|@count}"><span style="color:green;">Active Users - they contain valid key</span></td></tr>
    <tr>
	{foreach from=$users.head item=head}
	  <th>{$head}</th>
	{/foreach}
	</tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {foreach from=$users.body item=body}
  {if $body._act == '1'}
  <tr id="user_{$body._charID}" class="{cycle values="up,down"}">
	<td>{counter}</td>
	{foreach from=$body item=name key=k}
	{if $k[0] == '_'}
	{elseif $k == 'lastlogin' or $k == 'created'}
	<td>{if ($chosenLanguage == "DE")}{$name|date_format:"%d.%m.%Y %H:%M"}{else}{$name|date_format:"%m/%d/%Y %I:%M %p"}{/if}</td>
	{else}
	<td>{$name}</td>
	{/if}
	{/foreach}
    <td width="17" align="center"><img class="edit" src="icons/wrench.png" /></td>
	<td width="17" align="center"><img class="del" src="icons/delete.png" /></td>
  </tr>
	{foreach from=$body._alts item=alts}
  <tr id="user_{$body._charID}" bgcolor="#181818">
	<td>&nbsp;</td>
	{foreach from=$alts item=alt key=kk}
	{if $kk[0] == '_'}
	{elseif $kk == 'charName'}
	<td>{$alt}</td>
	<td>ALT</td>
	{else}
	<td colspan="7">{$alt}</td>
	{/if}
	{/foreach}
  </tr>
	{/foreach}
  {/if}
  {/foreach}
  
  </tbody>
</table>
</div>

<div id="edit" class="mail-popup"></div>

