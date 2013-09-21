{if $MySelectetMenue != "Silos"}<div id="assign" style="display:none;"><a href="{$index}&action=system&id={$MySelectetMenue}&allreactors=1">auto linking all Silos in this System</a></div>{/if}
<div>
{if $Towers|@count == 2}
<table cellpadding="3" cellspacing="0" style="width:666px;">
 <tr>
  <td align="center" valign="top"><div>{$Towers[0]}</div></td>
  <td align="center" valign="top"><div>{$Towers[1]}</div></td>
 </tr>
</table>

{else}

<table cellpadding="3" cellspacing="0" style="width:1000px;">
<tr>
{foreach from=$Towers item=tower name=pos}
{assign var=x value=$smarty.foreach.pos.total}
{math equation="a % b" a=$smarty.foreach.pos.iteration b=3 assign=mod}
<td valign="top" align="center"><div>
{$tower}
</div><br /></td>
{if $mod == 0 && $smarty.foreach.pos.iteration != x}
</tr><tr>
{/if}
{/foreach}
</tr>
</table>

{/if}
</div>

<div style="width:1000px;text-align:right"><a href="#" class="trigger">Show/Hide Settings</a></div>