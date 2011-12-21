{include file='header.tpl'}
<P>


<div class="odl_container">
	<h3>{$odl_lang.ODL_SENDTOF}</h3>
	<div class="editform">
		{if $error}<font color="red"><b>{$error}</b></font><hr>{/if}
		{if $googletop}{$googleAd}{/if}
		{$odl_lang.ODL_REQUIRED_FIELDS}
		<HR>
		<BR>
		<p><b>Title: </b>{$title}<BR><b>{$odl_lang.ODL_DESC} </b>{$description}</p>
		<form method="post" id="odl_sendlink" name="odl_sendlink" onsubmit="this.sub.disabled=true;this.sub.value='Sending Link...';" action="{$odl_send_link}">
		<input type="hidden" name="odlinks_send_link" value="yes" />
		<table border=0 cellpadding=5 cellspacing=5 width="90%">
			<tr bgcolor="#F4F4F4">
			<td class="odl_label_right">{$odl_lang.ODL_YOURNAME} </td>
			<td><input type="text" name="odlinksdata[yourname]" value="{$yourname}" size="35"></td>
			</tr>
			<tr>
			<td class="odl_label_right">{$odl_lang.ODL_YOUREMAIL} </td>
			<td><input type="text" name="odlinksdata[mailfrom]" value="{$mailfrom}" size="35"></td>
			</tr>
			<tr bgcolor="#F4F4F4">
			<td class="odl_label_right">{$odl_lang.ODL_FRIENDNAME} </td>
			<td><input type="text" name="odlinksdata[fname]" value="{$fname}" size="35"></td>
			</tr>
			<tr>
			<td class="odl_label_right">{$odl_lang.ODL_FRIENDMAIL} </td>
			<td><input type="text" name="odlinksdata[mailto]" value="{$mailto}" size="35"></td>
			</tr>
			{$confirm}
			<tr bgcolor="#F4F4F4"><td></td><td><input type=submit value="{$odl_lang.ODL_SEND}" name=odlinksdata[send]></p></td></tr>
		</table>
		
		{php}do_action('odlinkspost_topic_above_submit');{/php}
		<p>&nbsp;</p>
		</form>
	</div>
	<hr>
	<div class="main-content">

{include file='footer.tpl'}
