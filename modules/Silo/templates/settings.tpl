{include file="header.tpl"}
{include file="file:[Silo]menue.tpl"}

<p>
<input type="button" value="Fetch API" name="update" id="update" />
<input type="button" value="eve-central" name="price" id="price" />
<input type="button" value="Delete all" name="delall" id="delall" />
<input id="corpid" name="corpid" type="hidden" value="{$sel_corp}" />
<div id="result"></div>
</p>
<div style="text-align:left;width:1000px">
<table>
  <tr>
	<th> - </th>
	<th>type</th>
	<th>value</th>
  </tr>
{foreach $price as $k => $v}
{if $k == 'in'}{foreach $v as $key => $val}
  <tr>
	<td>Input</td>
	<td>{$val.typeName}</td>
	<td align="right">{$val.quantity}</td>
  </tr>
{/foreach}
{elseif $k == 'out'}{foreach $v as $key => $val}
  <tr>
	<td>Output</td>
	<td>{$val.typeName}</td>
	<td align="right">{$val.quantity}</td>
  </tr>
{/foreach}
{else}
  <tr>
	<td>{$k}</td>
	<td colspan="2" align="right">{$v}</td>
  </tr>
{/if}
{/foreach}
</table>
<pre>{*$pre*}</pre>
</div>
{include file="footer.tpl"}