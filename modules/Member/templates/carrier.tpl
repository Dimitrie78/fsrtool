  <tr>
    <td style="width:70%">Name</td>
	<td style="width:30%; text-align:center">Carrier</td>
  </tr>
 </thead>
 <tbody>
{foreach from=$list item=this}
{if (($eveTime-$this.joined) > 60*60*24*23) and (($eveTime-$this.joined) < 60*60*24*32)}
  <tr bgcolor="#52c8f2">
{elseif ($this.inactive == "1")}
  <tr bgcolor="#ffa9a9">
{elseif ($this.aID != "")}
  <tr bgcolor="#66ff99">
{elseif (($eveTime-$this.lastSeen) > 60*60*24*15)}
  <tr bgcolor="#fdff5e">
{elseif (($eveTime-$this.joined) < 60*60*24*30)}
  <tr bgcolor="#b9f0b9">
{else}
  <tr bgcolor="#ffffff">
{/if}
    <td>{if ($this.aID != "")}(alt){/if}
		{renderNameNew char=$this}</td>
	<td>{if     ($this.carrier == "1")}Archon
		{elseif ($this.carrier == "2")}Chimera
		{elseif ($this.carrier == "3")}Thanatos
		{elseif ($this.carrier == "4")}Nidhoggur
		{/if}</td>
  </tr>
{/foreach}