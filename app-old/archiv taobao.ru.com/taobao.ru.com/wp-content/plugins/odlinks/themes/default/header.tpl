{*
 * $Revision: $
 * Description: Wordpress odlinks
 * Header_Templates
*}

<div style="border:1px dotted #cdcdcd; padding: 10px 2px 10px 2px; text-align:center;">

{*
{literal}
 change the value of "google_ad_client" to your correct adsense
   or remove it completely if you want,
{/literal}
*}

{literal}
<script type="text/javascript">
var getLocation = function(href) {
    var l = document.createElement("a");
    l.href = href;
    return l
}

function showIt(src) {
var holder = document.getElementById('imageshow');
var newpic= new Image(); 
var l = getLocation(src);
imgsrc = "http://open.thumbshots.org/image.pxf?url=" + l.hostname;
newpic.src= imgsrc;
holder.src=imgsrc;
holder.width = newpic.width;
holder.height=newpic.height;
}
</script>
{/literal}

</div>

<div class="odl_head">
	{$odl_lang.ODL_SUBMITTING_NOTE}<br>
	{$odl_lang.ODL_SUBMITTING_TEXT}<br>
</div>
{include file='searching.tpl'}
