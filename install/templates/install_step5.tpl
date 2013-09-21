
<p>Import additional data into Database.<br/><br/></p>

{if in_array('sov', $update)}
Update Sovereignty table DONE</a><br/>
{if in_array('conq', $update)}
Update ConquerableStationList table DONE<br/>
{if in_array('ref', $update)}
Update RefTypes table DONE<br/>
{if in_array('ally', $update)}
Update AllianceList table DONE<br/>
{if in_array('call', $update)}
Update CallList table DONE<br/>
{else}
Update CallList table <a href="?step=5&do=call">Start</a><br/>
{/if}
{else}
Update AllianceList table <a href="?step=5&do=ally">Start</a><br/>
{/if}
{else}
Update RefTypes table <a href="?step=5&do=ref">Start</a><br/>
{/if}
{else}
Update ConquerableStationList table <a href="?step=5&do=conq">Start</a><br/>
{/if}
{else}
Update Sovereignty table <a href="?step=5&do=sov">Start</a><br/>
{/if}






{$errors}<br/>
{$error}<br/>



{if !$stoppage}
<p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}