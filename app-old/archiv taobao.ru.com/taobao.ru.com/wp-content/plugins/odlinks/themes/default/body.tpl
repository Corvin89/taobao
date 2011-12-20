{include file='header.tpl'}

<P>
<div class="odl_container">
  <div class="odl_editform">{if $error}<b>{$error}</b><hr>{/if}</div>
    <div class="main-content">
    {if ($cat_id != 0) && ($cat_desc <> "")}{$odl_lang.ODL_DESC}<br />{$cat_desc}{/if}
    {if $googletop}{$googleAd}{/if}
    {if $categories}
      <h3>{$odl_lang.ODL_CATS}</h3></td>
      <table border="0" width="100%" class="tbody" cellspacing="1" cellpadding="1">
      <tr><td width="100%">
        <table border="0" width="99%" cellspacing="3" cellpadding="3"><tr>
        {foreach from=$categories name=categories item=cat key=cats}
          <td valign="top" width="50%">
            <img src="{$odl_images}/images/folder.gif">&nbsp;<b>{$cat.cat_link}</b>&nbsp;<span class ="smallTxt">({$cat.c_links})</span><br>
            {if $subcategories}
              <table width="99%" border="0">
		{assign var=cnt value=0}
		{foreach from=$subcategories item=sub key=subs name=subcount}
		  {if $sub.c_parent==$cat.c_id}
		    {if $cnt is div by 2}</tr><tr>{/if}
		    <td width="50%">&nbsp;{$sub.c_path}<span class ="smallTxt">({$sub.c_links})</span></td>
		    {assign var=cnt value=$cnt+1}
		  {/if}
		{foreachelse}
		  <tr><td>{$odl_lang.ODL_NOTFOUND}</td></tr>
		{/foreach}
	      </table>
            {/if}
          </td>
          {if $smarty.foreach.categories.iteration is div by 2}
            </tr><tr>
          {elseif not $smarty.foreach.categories.last}
            <td>&nbsp;</td>
          {/if}
        {/foreach}
        </tr></table>
      </td></tr>
      </table>
    {/if}
      <BR />
      {if $links}
       <b>{$navigation_link}<div class="smallTxt">({$links|@count})</div></b>
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
      {/if}
      
{include file='footer.tpl'}
