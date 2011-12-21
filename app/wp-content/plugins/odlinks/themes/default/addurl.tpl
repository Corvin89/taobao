{include file='header.tpl'}
<P>
<div class="odl_container">
	<h3>{$odl_lang.ODL_SUBMITSITE}</h3>
	<div class="editform">
      {if $error}<font color="red"><b>{$error}</b></font><hr>{/if}
      {if $googletop}{$googleAd}{/if}

      {$odl_lang.ODL_REQUIRED_FIELDS}
      {if $title}<br>{$odl_lang.ODL_CATEGORIES} {$title}{/if}
      {if $description}<br>{$description}{/if}
      <form method="post" id="odl_addlink_post" name="odl_addlink_post" onsubmit="this.sub.disabled=true;this.sub.value='{$odl_lang.ODL_POSTED}';" action="{$odl_post_link}">
      <input type="hidden" name="odlinkspost_topic" value="yes" />
      <table border=0 cellpadding=5 cellspacing=5 width="90%">
         <tr bgcolor="#F4F4F4">
         <td class="odl_label_right">{$odl_lang.ODL_URL} </td>
         <td><input type="text" name="odlinksdata[url]" value="{$url}" size="50"></td>
         </tr>
         <tr>
         <td class="odl_label_right">{$odl_lang.ODL_TITLE} </td>
         <td><input type="text" name="odlinksdata[title]" value="{$title}" size="50"><br />
         <span class ="smallTxt">Maximum 50 characters</span></td>
         </tr>
         <tr bgcolor="#F4F4F4">
         <td class="odl_label_right">{$odl_lang.ODL_DESC} </td>
         <td><textarea rows="6" name="description" id="description" cols="55">{$description}</textarea><br />
         <span class ="smallTxt"><span id="charLeft"> </span>&nbsp;chars left. Maximum 700 characters</span></td>
         </tr>
         <tr>
         <td class="odl_label_right">{$odl_lang.ODL_CATEGORIY} </td>
         <td>

         <select name="odlinksdata[category]">
            {$categoryList}
         </select><br />

         <span class ="smallTxt">{$odl_lang.ODL_CATNOTE}</span></td>
         </tr>
         <tr bgcolor="#F4F4F4">
         <td class="odl_label_right">{$odl_lang.ODL_EMAIL} </td>
         <td><input type="text" name="odlinksdata[email]" value="{$email}" size="40">
         <br /><span class ="smallTxt">{$odl_lang.ODL_EMAILNOTE}</span></td>
         </tr>
         {$confirm}
         <tr bgcolor="#F4F4F4"><td></td><td bgcolor="#F4F4F4"><p>{$odl_lang.ODL_PAGENOTE}<BR>
      <input type=submit value="{$odl_lang.ODL_ADD}" name=odlinksdata[add]></p></td></tr>
      </table>
      {php}do_action('odlinkspost_topic_above_submit');{/php}
      <p>&nbsp;</p>
      </form>
      {if $googlebtn}{$googleAd}{/if}
	</div>
   <HR />

   <div class="main-content">

{include file='footer.tpl'}
