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
<pre>{$pre}</pre>
</div>
{include file="footer.tpl"}