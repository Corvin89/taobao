<?php
require_once('admin.php');
$title = __('Каталог - Проверка обратных ссылок');
$parent_file = 'link-exchange.php';
$today = current_time('mysql', 1);
require_once('admin-header.php');
// FUNCTIONS
function eblex_findhrefs($page)
{
    $using = false;
    $href = "";
    $hrefs[0] = "";
    $hrefcount = 0;

    for ($i = 0;$i < strlen($page)-1;$i++) {
        $a = $page[$i] . $page[$i + 1];
        if ($a == "<a") {
            $using = true;
        } 

        if ($page[$i] == ">") {
            $using = false;
            if ($href != "") {
                $hrefs[$hrefcount] = $href . ">";
                $hrefcount++;
            } 
            $href = "";
        } 

        if ($using == true) {
            $href .= $page[$i];
        } 
    } 

    return $hrefs;
} 

function eblex_parsehrefs($hrefs)
{
    $output[0] = "";
    for ($i = 0;$i < count($hrefs);$i++) {
        $work = str_replace("'", "\"", $hrefs[$i]);
        $position = strpos($work, "href=\"") + strlen("href=\"");

        $char = "";
        $url = "";
        for ($j = $position;$j < strlen($work);$j++) {
            $char = $work[$j];
            if ($char == "\"") {
                break;
            } 
            $url .= $char;
        } 

        $output[$i] = trim($url);
    } 
    return $output;
} 

$eblex_settings = $table_prefix . "eblex_settings";
$eblex_categories = $table_prefix . "eblex_categories";
$eblex_links = $table_prefix . "eblex_links";

$spoof = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='spoof'");
$reciprocalurl = $wpdb->get_var("SELECT `value` FROM `$eblex_settings` WHERE `option`='reciprocalurl'");

$l_activelinkcount = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`='1'");
$l_activelinkcountnref = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`='1' AND `nonreciprocal`!='1'");

if ($_POST['counter'] != "") {
    $counter = $_POST['counter'];
    $deletedlinks = 0;
    if (is_numeric($counter)) {
        for ($i = 0; $i < $counter; $i++) {
            $linkid = $_POST['id' . $i];
            $details = $wpdb->get_var("SELECT `id` FROM `$eblex_links` WHERE `id`='$linkid' ORDER BY `zindex` DESC");

            if (isset($details)) {
                $wpdb->query("DELETE FROM `$eblex_links` WHERE `id`='$linkid'");
                $deletedlinks++;
            } 
        } 
        $noticemsg = "$deletedlinks ссылка удалена!";
    } 
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
<style type="text/css">
<!--
.catlink {
border:0px;
font-size:14px;
font-weight:bold;
color:#6666FF;
}

.catlink:hover {
color:#000066;
}

.catsublink {
border:0px;
font-size:12px;
color:#6666FF;
}

.catsublink:hover {
color:#000066;
}

.plink {
border:0px;
font-size:16px;
font-weight:bold;
color:#000000;
}

.plink:hover {
color:#0000CC;
}

.purl {
color:#CCCCCC;
font-size:11px;
}

.linkbox1
{
background-color:#FFFFFF;
border:1px #FFFFFF solid;
width:100%;
padding:3px;
}

.linkbox1:hover
{
background-color:#F9F9F9;
border:1px #CCCCCC solid;
}

.linkbox2
{
background-color:#FAFAFA;
border:1px #FFFFFF solid;
width:100%;
padding:3px;
}

.linkbox2:hover
{
background-color:#F9F9F9;
border:1px #CCCCCC solid;
}

.linkboxselected
{
background-color:#FFB7B7;
border:1px #FFFFFF solid;
width:100%;
padding:3px;
}

.linkboxselected:hover
{
background-color:#FF7777;
border:1px #CCCCCC solid;
}

.catbox {
width:95%;
padding:5px;
border:1px #FFFFFF solid;
}

.catbox:hover {
border:1px #EEEEEE solid;
}
-->
</style>
<script language="JavaScript" type="text/javascript">
function deletelink(linkname) {
	var answer = confirm("Удалить ссылку?")
	if (answer){
		return true;
	}
	else{
		return false;
	}
}
</script>
<div class="wrap">
<h2><?php _e('Проверка обратных ссылок'); ?></h2>
Количество ссылок для проверки: <strong><?=$l_activelinkcountnref;?></strong> из <strong><?=$l_activelinkcount;?></strong><br/>
<br/>
<form id="form1" name="form1" method="post" action="">
  <label>Считать обратную ссылку отсутсвующей, если сервер не ответил в течение <input name="timeout" type="text" class="code" id="timeout" value="10" size="5"/> секунд
  <br/>
  Этот процесс может занять длительное время, если количество ссылок в базе данных большое.
<p class="submit">
  <input type="submit" value="<?php _e('Начать проверку') ?>" name="Submit" />
</p>
</form>
<?php
if ($_POST['timeout'] != "")
{
?>
<h2><?php _e('Результаты проверки:'); ?></h2>
<form id="form2" name="form2" method="post" action="">
<?php
}
			
$randomuseragent[0] = "Firefox/1.0 (Windows; U; Win98; en-US; Localization; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
$randomuseragent[1] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.7) Gecko/20060909 Firefox/1.5.0.7";
$randomuseragent[2] = "Mozilla/5.0 (Windows; U; Windows NT 6.0; pt-BR; rv:1.8.0.7) Gecko/20060909 Firefox/1.5.0.7";
$randomuseragent[3] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.8.0.4) Gecko/20060508 Firefox/1.5.0.4";
$randomuseragent[4] = "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.0.4) Gecko/20060602 Firefox/1.5.0.4";
$randomuseragent[5] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.2) Gecko/20060308 Firefox/1.5.0.2";
$randomuseragent[6] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2; WOW64; .NET CLR 2.0.50727)";
$randomuseragent[7] = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2; Win64; x64; .NET CLR 2.0.50727)";
$randomuseragent[8] = "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 6.0)";
$randomuseragent[9] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; WOW64; SV1; .NET CLR 2.0.50727)";
$randomuseragent[10] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; Win64; x64; SV1; .NET CLR 2.0.50727)";
$randomuseragent[11] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) Netscape/8.0.4";
$randomuseragent[12] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows XP)";
$randomuseragent[13] = "Mozilla/4.0 (compatible; MSIE 6.0; WINDOWS; .NET CLR 1.1.4322)";
$randomuseragent[14] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; Maxthon; .NET CLR 1.1.4322)";
$randomuseragent[15] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.2; Win64; AMD64)";
$randomuseragent[16] = "Mozilla/5.0 (compatible; Konqueror/3.5; Linux; X11; i686; en_US) KHTML/3.5.3 (like Gecko)";
$randomuseragent[17] = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)";
$randomuseragent[18] = "Mozilla/5.0 (compatible; Konqueror/3.4; Linux 2.6.8; X11; i686; en_US) KHTML/3.4.0 (like Gecko)";
$randomuseragent[19] = "Mozilla/5.0 (compatible; Konqueror/3.4; Linux) KHTML/3.4.1 (like Gecko)";
$randomuseragent[20] = "Mozilla/5.0 (compatible; Konqueror/3.3; Linux) (KHTML, like Gecko)";
$randomuseragent[21] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/418.8 (KHTML, like Gecko) Safari/419.3";
$randomuseragent[22] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/417.9 (KHTML, like Gecko) Safari/417.8";
$randomuseragent[23] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; fr-fr) AppleWebKit/312.5.1 (KHTML, like Gecko) Safari/312.3.1";
$randomuseragent[24] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; es) AppleWebKit/85 (KHTML, like Gecko) Safari/85";
$randomuseragent[25] = "Mozilla/5.0 (Macintosh; U; PPC Mac OS X; en) AppleWebKit/51 (like Gecko) Safari/51";
$randomuseragent[26] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20050302 Firefox/0.9.6";
$randomuseragent[27] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20050302 Firefox/0.9.6";
$randomuseragent[28] = "Mozilla/5.0 (Windows; U; Windows NT 5.2 x64; en-US; rv:1.9a1) Gecko/20061007 Minefield/3.0a1";
$randomuseragent[29] = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-GB; rv:1.8.1) Gecko/20060918 Firefox/2.0";
$randomuseragent[30] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8) Gecko/20060319 Firefox/2.0a1";
$randomuseragent[31] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.8) Gecko/20051111 Firefox/1.5";
$randomuseragent[32] = "Mozilla/5.0 (X11; U; FreeBSD i386; en-US; rv:1.7.12) Gecko/20051105 Firefox/1.0.7";
$randomuseragent[33] = "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.7.10) Gecko/20050717 Firefox/1.0.6";
$randomuseragent[34] = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-GB; rv:1.7.8) Gecko/20050418 Firefox/1.0.4";
$randomuseragent[35] = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.6) Gecko/20050224 Firefox/1.0.2";

$jcounter = 0;
if ($_POST['timeout']!="")
{
	$timeout = $_POST['timeout'];
	if (is_numeric($timeout))
	{
		$numberoflinks = $wpdb->get_var("SELECT count(*) FROM `$eblex_links` WHERE `status`='1' ORDER BY `zindex` DESC");
		
		if ($numberoflinks != "0")
		{
			$lnk = $wpdb->get_results("SELECT * FROM `$eblex_links` WHERE `status`='1' ORDER BY `zindex` DESC");
   			$checkcount = 0;
			foreach ($lnk as $row)
			{
			$ok = 0;
			$checkcount++;
			$contents = "";
						
			if ($row -> nonreciprocal != "1")
			{
			
				$url =  @parse_url($row->reciprocalurl);
			
				if ($url[query] != "")
				{
					$host = $url[host]."?".$url[query];
				}
				else
				{
					$host = $url[host];
				}
			
				if ($spoof == 1)
				{
					$random = rand(0,35);
					$useragent = $randomuseragent[$random];
				}
				else
				{
					$useragent = "LinkExchange checker (script)";
				}
				
				if ($url[path] == "")
				{
					$geturl = "/";
				}
				else
				{
					$geturl = $url[path];
				}
				
				$out = "GET ".$geturl." HTTP/1.0\r\n";
				$out .= "Host: $host\r\n";
				$out .= "User-Agent: $useragent\r\n";
   				$out .= "Connection: Close\r\n\r\n";
				
				if ($row->reciprocalurl != '' && $row->reciprocalurl != 'http://')
				{
					$sock = @fsockopen($url[host],80,$error,$errorstring,$timeout);
				}
				else
				{
					$sock = false;
				}
						
				if ($sock)
				{
				fwrite($sock,$out);
				$contents = "";
				
				$time = time();
				
				do
				{
				    $part = fread($sock, 128);
					$contents .= $part;
					$ntime = time();
				}
					while (($part != "") && ($ntime - $time < $timeout));
				}
				
				@fclose($sock);
				
				//----------------------- CHECKING -----------------------------
				if ($contents != "Невозможно открыть страницу.")
				{	
						$foundlinks = eblex_findhrefs($contents);
						$found = eblex_parsehrefs($foundlinks);
						
						foreach ($found as $href)
						{
						//echo("<!-- ".rtrim(ltrim(strtolower($href)))." -->");
							if (strpos(rtrim(ltrim(strtolower($href))),$reciprocalurl) > -1)
							{
								$ok = 1;
							}
						}	
				}
				
				
if ($ok != 1)
{
if ($class == "linkbox1") {$class = "linkbox2";} else {$class = "linkbox1";}
?>											
<div class="<?php echo($class); ?>" id="del<?=$jcounter;?>">
<input name="id<?=$jcounter;?>" id="id<?=$jcounter;?>" type="hidden" value="" />
<a href="<?php echo($row->url); ?>" class="plink" target="_blank"><?php echo($row->title); if ($row->status == "0") {echo("(inactive)");} ?></a><br/><a href="#" id="hdel<?=$jcounter;?>" onclick="obj = document.getElementById('del<?=$jcounter;?>'); hobj = document.getElementById('hdel<?=$jcounter;?>'); cobj = document.getElementById('deletecounter'); cobjk = parseInt(document.getElementById('deletecounter').value); sobj = document.getElementById('id<?=$jcounter;?>'); if (obj.className != 'linkboxselected') {obj.className = 'linkboxselected'; hobj.innerHTML = '[Deselect]'; cz = cobjk+1; cobj.value = cz; sobj.value = '<?php echo($row->id); ?>'} else {obj.className = '<?=$class;?>'; hobj.innerHTML = '[Select]'; cz = cobjk-1; cobj.value = cz; sobj.value = ''} return false;">[Отметить]</a> <a href="link-exchange-browse.php<?php echo("?id=".$row->id."&action=details"); ?>" target="_blank">[Детали]</a> <a href="link-exchange-browse.php<?php echo("?id=".$row->id."&action=edit"); ?>" target="_blank">[Редактировать]</a> <a href="link-exchange-browse.php<?php echo("?id=".$row->id."&action=delete"); ?>" onclick="return deletelink()" target="_blank">[Delete]</a><br/>
	<span class="purl"><?php echo(str_replace("/","",str_replace("http://","",$row->url))); ?></span><br/>
	<?php
	$jcounter++; //increment the counter for checkboxing
	
	if ($contents == "")
	{
		echo("Невозможно открыть страницу");
	}
	else
	{
		$contents = explode("\n",$contents);
		$responce = $contents[0];
		$responce = rtrim(ltrim(str_replace("HTTP/1.1","",str_replace("HTTP/1.0","",$responce))));
		echo("<strong>Обратная ссылка, URL:</strong> <a href=\"".$row->reciprocalurl."\">".$row->reciprocalurl."</a><br/>");
		if ($responce == "404 Not Found") {echo("<strong>Статус:</strong> 404 Страница не найдена<br/>");}
		if ($responce == "400 Bad Request") {echo("<strong>Статус:</strong> 400 Сервер не может обработать перенаправление<br/>");}
		if ($responce == "200 OK") {echo("<strong>Статус:</strong> Страница существует, но обратная ссылка на ней отсутсвует<br/>");}
		
		$viewresponce = "";
		
		for ($m=0;$m<10;$m++)
		{
			$viewresponce .= $contents[$m];
		}
		$viewresponce = str_replace("\"","&quot;",$viewresponce);
		$viewresponce = str_replace("'","",$viewresponce);
		$viewresponce = str_replace("\n","",$viewresponce);
		$viewresponce = str_replace("\r","\\n",$viewresponce);
		
		echo("<strong>Ответ:</strong> <a href=\"#\" onclick=\"alert('$viewresponce'); return false;\">Просмотр 10 первых строчек ответа сервера</a><br/>");
	}
	?>
  </div>
	<br/>
				<?php
				}
				
				
				//--------------------------------------------------------------
				
			}
		  }
		}
		else
		{
			$noticemsg = "Нет ссылок для проверки!";
		}
	}
	else
	{
		$noticemsg = "Неправильные ответ!";
	}
?>
<script language="JavaScript" type="text/javascript">
function checkdelete()
{
	count = document.getElementById('deletecounter').value;
  if (count == "0")
  {
  	alert("Нечего удалять!");
	return false;
  }
  else
  {
	if (confirm("Вы уверены, что хотите удалить "+count+" ссылок?"))
	{
		return true;
	}
	else
	{
		return false;
	}
   }
}
</script>
 <br/><strong><?=$checkcount;?> Ссылки были проверены.</strong><br/>
 <input name="counter" type="hidden" id="counter" value="<?php echo($jcounter++); ?>" />
 <input name="deletecounter" type="hidden" id="deletecounter" value="<?php echo("0"); ?>" />
 <p class="submit">
  <input type="submit" value="<?php _e('Удалить отмеченные ссылки') ?>" name="Submit" onclick="return checkdelete()"/>
</p>
</form>
<?php
}
?>
</div>
<?php
require('./admin-footer.php');
?>