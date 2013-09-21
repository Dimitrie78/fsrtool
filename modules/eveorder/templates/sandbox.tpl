{include file="header.tpl"}    
{include file="file:[eveorder]menu.tpl"}

{$cacheTime}
<h3>Ship Replacement On Hand-Shopping List Calculator</h3>

<table class="shipReplace" cellpadding="3" cellspacing="0">
  <thead>
  
    <tr class="headcol">
      <td colspan="6">
	    <ul class="item">
		  <li{if !$state} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=0">Ships</a></li>
		  <li{if $state == "1"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=1">high slots</a></li>
		  <li{if $state == "2"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=2">med slots</a></li>
		  <li{if $state == "3"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=3">low slots</a></li>
		  <li{if $state == "4"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=4">rig slots</a></li>
		  <li{if $state == "5"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=5">sub slots</a></li>
		  <li{if $state == "6"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=6">ammo</a></li>
		  <li{if $state == "7"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=7">drone</a></li>
		  <li{if $state == "8"} id="selected"{/if}><a href="{$index}&amp;action=shipRep&amp;state=8">other</a></li>
		  <li{if $state == "9"} id="selected"{/if} class="right"><a href="{$index}&amp;action=shipRep&amp;state=9">Settings</a></li>
	    </ul>
	  </td>
    </tr>
 {if $state != 9} 
	<tr>
	  <td width="8%">On Hand</td>
	  <td width="10%">Minimum Level</td>
	  <td width="8%">Shopping List</td>
	  <td width="8%">On Order</td>
	  <td width="64%">Item</td>
	  <td>&nbsp;</td>
	</tr>
  </thead>
  <tbody>
	{foreach from=$list item=this}
	<tr id="tab_{$this.typeID}">
	  <td align="right" id="cur_{$this.typeID}">{$this.curlvl|number_format:0:",":"."}</td>
	  <td align="right" id="zahl"><input style="float:none; text-align:right;width:90px" type="text" name="minlvl[]" id="min_{$this.typeID}" value="{$this.minlvl}" size="4" onchange="saveValue('{$this.typeID}')" /></td>
	  <td align="right" id="buy_{$this.typeID}">{if $this.buy > 0}{$this.buy|number_format:0:",":"."}{/if}</td>
	  <td align="right" id="order_{$this.typeID}">{if $this.inorder > 0}{$this.inorder}{/if}</td>
	  <td align="left"><a href="{$index}&action=searchResult&searchIDs={$this.typeID}"><img src="icons/Types/{$this.typeID}_32.png" width="16" height="16" title="{$this.typeName}" alt="{$this.typeName}" /></a>&nbsp;{$this.typeName}</td>
	  <td align="right"><img src="modules/eveorder/img/folder_delete.png" id="del_{$this.typeID}" class="delete" title="delete" /></td>
	</tr>
	{/foreach}
  </tbody>
  <tfoot>
    <tr>
	  <td colspan="6">
	  <span style="float:left;"><input type="button" id="importFitt" value="import Fittings" /></span>
	  <span style="float:right;"><input type="button" id="upAssets" value="update Assets" /></span>
	  <form method="post" action="dowork.php">
	    <input type="hidden" name="module" value="eveorder"/>
	    <input type="hidden" name="action" value="addReplacement"/>
	    <input type="hidden" name="state" value="{$state}"/>
		Search: <input type="text" name="item" id="search" placeholder="Search item" />
		<input type="hidden" name="typeID" id="typeID" />
		<input type="submit" value="add" />
	  </form>
	  <div align="center" id="ajaxCont" style="display:none;font-weight:bold;color:red;"></div>
	  </td>
	</tr>
  </tfoot>
{else}
  </thead>
  <tbody>
    <tr>
	  <td colspan="6">
		<div align="center">Select location(s)</div>
		<div align="center"><input type="button" id="updateLoc" value="update Locations" /></div>
		<div align="center" id="ajaxCont" style="display:none;font-weight:bold;color:red;"></div>
		<div>
		  <form method="post" action="dowork.php">
			<input type="hidden" name="module" value="eveorder"/>
			<input type="hidden" name="action" value="addLocation"/>
			<input type="hidden" name="state" value="{$state}"/>
		  {foreach from=$loc item=thisLoc}
			<input type="checkbox" name="loc[]" value="{$thisLoc.locationID}" {if $thisLoc.sel}checked="checked"{/if} />{$thisLoc.locationName}<br />
		  {/foreach}
			<p align="center"><input type="submit" value="save" /></p>
		  </form>
		</div>
	  </td>
    </tr>
  </tbody>
{/if}
</table>
{include file="footer.tpl"}