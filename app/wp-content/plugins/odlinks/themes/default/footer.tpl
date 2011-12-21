		<BR />
      <BR />
      {if $new_links}
         <h3>{$odl_lang.ODL_LAST} {$linksNum} {$odl_lang.ODL_POSTED}</h3>
         <table><tr><td width=550>
         <table>
         {foreach from=$new_links item=item key=key}
            <tr><td width="20"> 
            <img border=0 src="{$odl_images}/images/camera.gif" onmouseover="showIt('{$item.url}')">
            </td><td><a href="{$item.url}" target=_blank>{$item.title}</a>&nbsp;
            <span class="smallTxt">({$item.category}&nbsp;[{$item.date}])</span><br /></td></tr>
         {/foreach}
         </table>
         </td><td valign="top" align="center" width="135"><img src="{$odl_images}/images/default.jpg" id="imageshow"></td></tr></table>
      {/if}
      <BR />
      {if $googlebtn}<div class="odl_googleAd">{$odl_googleAd}</div>{/if}
      <h3>{$odl_lang.ODL_LEGEND}</h3>
      <span class ="smallTxt">{$odl_lang.ODL_CATEGORIES} {$categories_total}, {$odl_lang.ODL_LINKS} {$links_total}</span>
      {$odlFbLike}
		{$rssLink}
   </div><!--main-content-->
</div><!--odl_container-->
