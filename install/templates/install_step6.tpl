{if !$stoppage}
	<p>Admin Char successfully created.</p>
	User: admin<br/>
	Pass: admin<br/>
	<br/>
	After registered your own char, login with admin and grand the 'Admin' role to you account.<br/>
	After that, login with you account and DELETE the ADMIN account.<br/><br/>
	<b><i>If you registered your own char, do not forget the admin account in the userManager to delete. It is for your own safety.<b/><i/>

    <p><a href="?step={$nextstep}">Next Step --&gt;</a></p>
{else}
	We got an Error: {$mError}
{/if}