{* Smarty *}
{************ shopwerror.tpl *************************************}

{* ------------------------------- *}
{include file="header.tpl"}    
{* ------------------------------- *}
</div> {* end of div started in header.tpl *}
<h2>Critical error</h2>


{if $msg_error ne ""}
<div id="msg_error">{$msg_error}</div>
{/if}
<h3>Program execution aborded!</h3>

&nbsp;<br>&nbsp;
{* ------------------------------- *}
{include file="footer.tpl"}    
{* ------------------------------- *}
</body>
</html>