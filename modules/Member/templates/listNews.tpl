{* Smarty *}
{include file="header.tpl"}    

<div id="title">&raquo; Membertool</div>
<div id="pos">
<form action="{$url_index_module}" method="post" />
  <input type="hidden" name="module" value="Member" />
  <input type="hidden" name="action" value="showChar" />
  <input type="text" name="charID" id="charSearch" />
  <input type="submit" value="Search" />
</form>
</div>
{include file="file:[Member]bar.tpl"}
</div> {* end of div started in header.tpl *}

<script type="text/javascript" src="modules/Member/inc/snowflake.js"></script>

<p>Snowflake last updated: {$updateTime|date_format:"%d.%m.%Y - %H:%M:%S"}</p>

<table class="snow" cellpadding="3" cellspacing="0" style="width: 1000px">
 <thead> 
  <tr>
    <td style="width: 10%;text-align: center">Date</td>
	<td style="width: 90%;">News</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$news item=this}
{if $this.type == "1"}
  <tr bgcolor="#ffa9a9">
    <td style="text-align: center">{$this.dateTime|date_format:"%d/%m/%Y"}</td>
	<td style="text-align: left">{$this.name} has left the corp{getalts mainID=$this.charID}</td>
  </tr>
{/if}
{if $this.type == "2"}
  <tr bgcolor="#a0ffa0">
    <td style="text-align: center">{$this.dateTime|date_format:"%d/%m/%Y"}</td>
	<td style="text-align: left">{renderNameNew char=$this} has joined the corp.</td>
  </tr>
{/if}
{if $this.type == "3"}
  <tr bgcolor="#d0d0d0">
    <td style="text-align: center">{$this.dateTime|date_format:"%d/%m/%Y"}</td>
	<td style="text-align: left">{$this.name} has been autoflagged as inactive.</td>
  </tr>
{/if}
{if $this.type == "4"}
  <tr bgcolor="#ffa9a9">
    <td style="text-align: center">{$this.dateTime|date_format:"%d/%m/%Y"}</td>
	<td style="text-align: left">{$this.name}'s main has left the corp.</td>
  </tr>
{/if}
{if $this.type == "5"}
  <tr bgcolor="#a9a9ff">
    <td style="text-align: center">{$this.dateTime|date_format:"%d/%m/%Y"}</td>
	<td style="text-align: left">{$this.name} has been returned from inactivity.</td>
  </tr>
{/if}
{/foreach}
 </tbody>
</table>

<div id="isAltWin" style="display: none;"></div>
<div id="flagWin" style="display: none;"></div>

{literal}<script type="text/javascript">
$(document).ready(function(){
	$('#charSearch').autocomplete({
		minLength: 3,
		source: "dowork.php?module=Member&action=charSearch"
	});
});
</script>{/literal}

{include file="footer.tpl"}