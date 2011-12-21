<?php
require_once('admin.php');
$title = __('Каталог - Статистика');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1);
require_once('admin-header.php');

$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

$l_categorycount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`=''");
$l_subcategorycount = $wpdb->get_var("SELECT count(*) FROM `$eblex_categories` WHERE `parent`!=''");
$l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links`");
$l_activelinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`='1'");
$l_inactivelinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`=0");
$l_approvedlinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `active`='1'");
$l_dissaprovedlinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `active`=0");
$l_reciprocallinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `reciprocalurl`!=''");
$l_linkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links`");


//----------------------------------- Search Engine -------------------------------------

$url=$_POST['url'];

function eblex_getsestats($url)
{
	$url=urlencode(str_replace("http://","",$url));
    
    //GOOGLE
    $urlgoogle="http://www.google.com/search?hl=en&q=link%3A$url";
    $file=fopen($urlgoogle, "r");
    if ($file!=NULL) {
        while (!feof($file)) {
            $data .= fread($file, 8192);
        }
        fclose($file);
        $pos=strpos($data,"</b> of about <b>")+17;
        $google="";
        $i=$pos;
        while (1) {
            if ($data[$i]=="<") {
                break;
            }
            $google.=$data[$i];
            $i++;
            if ($i>$pos+100) {
                break;
            }
        }
        if (strlen($google)>21) {
            $google="No backlinks found!";
        }
        if ($google!="No backlinks found!") {
            $one=str_replace(",","",$google);
            $one=str_replace(" ","",$one);
        } else {
            $one=0;
        }
    } else {
        $one=0;
        $google="Error!";
    }
    $data="";
    
    //MSN
    $urlmsn="http://search.msn.com/results.aspx?q=link%3A$url&FORM=QBHP";
    $file=@fopen($urlmsn, "r");
    if ($file!=NULL) {
        while (!feof($file)) {
            $data .= fread($file, 8192);
        }
        fclose($file);
        $pos=strpos($data,"Page 1 of ")+10;
        $msn="";
        $i=$pos;
        while (1) {
            if ($data[$i]==" ") {
                break;
            }
            $msn.=$data[$i];
            $i++;
            if ($i>$pos+100) {
                break;
            }
        }
        if ($msn=="html") {
            $msn="No backlinks found!";
        }
        
        if ($msn!="No backlinks found!") {
            $two=str_replace(",","",$msn);
            $two=str_replace(" ","",$two);
        } else {
            $two=0;
        }
    } else {
        $two=0;
        $msn="Error!";
    }
    $data="";
    //YAHOO!
    $urlyahoo="http://search.yahoo.com/search?p=linksite%3A$url&prssweb=Search&ei=UTF-8&fr=sfp&x=wrt";
    $file=@fopen($urlyahoo, "r");
    if ($file!=NULL) {
        while (!feof($file)) {
            $data .= fread($file, 8192);
        }
        fclose($file);
        $pos=strpos($data," of about ")+10;
        $yahoo="";
        $i=$pos;
        while (1) {
            if ($data[$i]==" ") {
                break;
            }
            $yahoo.=$data[$i];
            $i++;
            if ($i>$pos+100) {
                break;
            }
        }
        if ($yahoo=="html") {
            $yahoo="No backlinks found!";
        }
        if ($yahoo!="No backlinks found!") {
            $three=str_replace(",","",$yahoo);
            $three=str_replace(" ","",$three);
            $three=str_replace("<strong>","",$three);
            $three=str_replace("</strong>","",$three);
        } else {
            $three=0;
        }
    } else {
        $three=0;
        $yahoo="Error!";
    }
    
    $total=$one+$two+$three;
    $point=0;
    $ntotal="";
    $xtotal=$total."";
    for ($i=strlen($xtotal); $i>=0; $i--) {
        $ntotal=$xtotal[$i].$ntotal;
        if ($point==3 && $xtotal[$i-1]!="") {
            $ntotal=",".$ntotal;
            $point=0;
        }
        $point++;
    }
    $total=$ntotal;
	
	return array("google" => $google, "yahoo" => $yahoo, "msn" => $msn, "total" => $total);
}

if ($url != "")
{
	$stats = eblex_getsestats($url);
}
 
?>
<?php
if ($noticemsg!="")
{
?>
<div id="message1" class="updated fade"><p><?php _e($noticemsg); ?></p></div>
<?php 
}
?>
<div class="wrap">
<h2>Статистика</h2>
Ссылок в базе данных: <strong><?=$l_linkcount;?></strong><br/>
Одобренных: <strong><?=$l_approvedlinkcount;?></strong><br/>
Ждущих проверки: <strong><?=$l_dissaprovedlinkcount;?></strong><br/>
Активных: <strong><?=$l_activelinkcount;?></strong><br/>
Скрытых (неактивных): <strong><?=$l_inactivelinkcount;?></strong><br/>
Количество обратных ссылок: <strong><?=$l_reciprocallinkcount;?></strong><br/>
Категорий: <strong><?=$l_categorycount;?></strong><br/>
Подкатегорий: <strong><?=$l_subcategorycount;?></strong><br/>
<br/>
<h2>Статистика по поисковым системам</h2>
<?php
	if ($_POST['url'] != "")
	{
?>
Google: <strong><?=$stats["google"];?></strong><br/>
MSN: <strong><?=$stats["msn"];?></strong><br/>
Yahoo: <strong><?=$stats["yahoo"];?></strong><br/><br/>
Всего: <strong><?=$stats["total"];?></strong><br/><br/>
<?php
	}
?>
<form id="form1" name="form1" method="post" action="">
<label>URL для проверки:
<input name="url" type="text" id="title" value="<?=$_SERVER['HTTP_HOST'];?>" size="40" />
</label>
<br/>
<p class="submit">
<input type="submit" value="<?php _e('Проверить') ?>" name="Submit" />
</p>
</form>
<br/>
<br/>
</div>
<?php
require('./admin-footer.php');
?>