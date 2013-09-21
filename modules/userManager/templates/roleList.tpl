<p><input name="search" value="" id="id_search" placeholder="Search User" /></p>
<div>
<table class="user" cellspacing="0" cellpadding="3">
  <thead>
    <tr><td class="head" colspan="{$roles.head|@count}"><span style="color:red;">Inactive Users - they contain no valid key</span></td></tr>
	<tr>
	{foreach from=$roles.head item=head}
	  {if $head == 'Name' || $head == 'Corp'}
	  <td>{$head}</td>
	  {else}
	  <td align="center">{$head}</td>
	  {/if}
	{/foreach}
	</tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {foreach from=$roles.body item=body key=charID}
  {if $body._act == '0'}
  <tr id="user_{$charID}" class="{cycle values="up,down"}">
	<td>{counter}</td>
	{foreach from=$body item=name key=k}
	{if $k[0] == '_'}
	{elseif $k == 'uname' or $k == 'corp'}
    <td>{$name}</td>
	{else}
	<td align="center">{if $name == '1'}<img id="r_{$k}" class="role" src="icons/tick.png" />{else}<img id="r_{$k}" class="role" src="icons/cross.png" />{/if}</td>
	{/if}
	{/foreach}
  </tr>
	
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
	  {if $head == 'Name' || $head == 'Corp'}
	  <td>{$head}</td>
	  {else}
	  <td align="center">{$head}</td>
	  {/if}
	{/foreach}
	</tr>
  </thead>
  <tbody>
  {counter start=0 print=false}
  {foreach from=$roles.body item=body key=charID}
  {if $body._act == '1'}
  <tr id="user_{$charID}" class="{cycle values="up,down"}">
	<td>{counter}</td>
	{foreach from=$body item=name key=k}
	{if $k[0] == '_'}
	{elseif $k == 'uname' or $k == 'corp'}
    <td>{$name}</td>
	{else}
	<td align="center">{if $name == '1'}<img id="r_{$k}" class="role" src="icons/tick.png" />{else}<img id="r_{$k}" class="role" src="icons/cross.png" />{/if}</td>
	{/if}
	{/foreach}
  </tr>
	
  {/if}
  {/foreach}
  
  </tbody>
</table>
</div>