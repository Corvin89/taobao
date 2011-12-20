{include file='header.tpl'}
<P>
<div class="odl_container">
	<div class="main-content">
		<h3>{$odl_lang.ODL_SEARCHING} <font color=green>{$search_terms}</H3>
		{if $links}
			{$odl_lang.ODL_FOUND} <font color=green>{$results_num}</font> relevant matches, displaying <font color=green>{$results_limit}</font> {$odl_lang.ODL_PAGERESULT}</font>
			<hr>
         {foreach from=$links item=item key=key}
            <div class="viewlink">
              <table><tr><td valign="top" width="135">
              <a href="{$item.url}" target=_blank><img border=1 src="http://open.thumbshots.org/image.pxf?url={$item.url}"></a>&nbsp;</td>
              <td valign="top">
              <table><tr>
                <td><b><a href="{$item.url}" target=_blank>{$item.title}</a></b>&nbsp;<span class ="smallTxt">{$odl_lang.ODL_ADDED}{$item.date}</span><br/>{if $item.description <> ""}{$item.description}{/if}</td></tr>
                <tr>
                <td><br><img src="{$odl_images}/images/favourite.gif"><a href="javascript:addbookmark('{$item.url}','{$item.title}');"><font color="#840000">{$odl_lang.ODL_ADDFOVOURITE}</font></a>&nbsp;&nbsp;&nbsp;<img src="{$odl_images}/images/refer.gif">{$item.sendlink}&nbsp;&nbsp;&nbsp;{$item.rank_txt}&nbsp;&nbsp;&nbsp;<img src="{$odl_images}/images/{$item.rank_img}"></td></tr>
              </table>
          </td></tr></table>
          </div>
        {/foreach}
		{else}
			{if !$odl_search_error==""}{$odl_search_error}{/if}
			<p>&nbsp;</p>
		{/if}

{include file='footer.tpl'}
