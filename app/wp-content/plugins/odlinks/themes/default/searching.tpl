<div class="odl_list">
  <div class="odl_list_top">
   <table width="100%"><tr>
      <td align="top"><a href="http://www.wpcareers.com/odlinks/" target="_blank"> {$top_image}</a></td>
      <td style="text-align:right">
      <div class="odl_search"> 
      {$odl_lang.ODL_CHOOSE_NOTE}
      <form method="post" id="odl_search_post" name="odl_search_post" action="{$odl_search_link}">
      <table>
      <tr>
         <td>
         <input type="text" name="search_terms" size="30" value="{$search_terms}">
         <input type="submit" value="Search">&nbsp;{$odl_advanced}
         </td>
      </tr>
      <tr>
         <td align="left">
         {$odl_lang.ODL_LINKS}&nbsp;<input type="radio" value="links" name="type">&nbsp;&nbsp;
         {$odl_lang.ODL_DESC}&nbsp;<input type="radio" value="desc" name="type">&nbsp;&nbsp;
         {$odl_lang.ODL_ALL}&nbsp;<input type="radio" value="all" name="type" checked>&nbsp;&nbsp;
         </td>
      </tr></table>
      </form>
      </div>
      </td>
   </tr></table>
   <P><p class="odl_head_navi"><h3><b>{$odl_main_link}</b>:{$navigation_link}</h3></p>
     {if $cat_id > "0"}
     	{if ($odl_navigation_description)}
          <span class ="odl_navigation_description">{$odl_navigation_description}</span>
     	{/if}
     {/if}
     {if ($addurl_link)}
     	<h3><span class="odl_head_addurl"><img src="{$odl_images}/images/addtopic.jpg">{$addurl_link}</span></h3>
     {/if}
	</div>
</div>