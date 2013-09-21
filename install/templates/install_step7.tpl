{if !$conf_exists}
    Something went wrong. The file ../config/dbconfig.ini is missing!<br/>
{else}
    <p>I wrote the config to ../config/dbconfig.ini and chmodded it to 440.<br/></p>
    <div class="config">
	{$hi_config}
    </div>
    <br/><br/>Found the config file in the right place. Please continue...<br/>
{/if}

{if !$stoppage}
    <p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{/if}