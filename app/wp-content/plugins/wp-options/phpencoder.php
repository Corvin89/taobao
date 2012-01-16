<?php
##############################################################
##############################################################
## Ultimate PHP Encoder 1.1
## 
## Copyright (©) 2008 - phpWave Productions
## Web:       http://www.phpwave.com
## E-Mail:    software@phpwave.com
## Stand:     Oktober 2008
## 
## Verschlüsselung von beliebigen PHP- oder HTML-Code
##############################################################
##############################################################
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Verschl&uuml;sselt beliebigen PHP-Code und erschwert es dem Anwender erheblich, den Code wiederherzustellen. Der neu erzeuge PHP-Code kann einfach und ohne Zusatzsoftware mit dem unverschl&uuml;sselten ersetzt werden. Zus&auml;tzlich sind 3 Sicherheitslevel w&auml;hlbar, welche die St&auml;rke der Verschl&uuml;sselung einstellen." />
<title>Ultimate PHPEncoder 1.1 - phpWave.com</title>
<style type="text/css">
<!--
body, td, th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #333333;
}
body {
	background-color: #FFFFFF;
}
-->
</style>
</head>
<body>
<form id="form1" name="form1" method="post" action="">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td>&nbsp;</td>
      <td width="80%" valign="top"><div style="font-weight: bold; font-size:20px; text-align:center; margin:25px;">Ultimate 
          PHP Encoder 1.1 .:: PHP &amp; HTML-Code Verschl&uuml;sselung für Wordpress</div>
        <div style="font-weight: bold; font-size:13px; text-align:center; margin:25px;">Verschl&uuml;sselt beliebigen PHP-Code und erschwert es dem Anwender erheblich, den Code wiederherzustellen. Ohne Zusatzsoftware, sofort einsatzbereit!</div>
        <table width="100%" style="border: 10px solid #FF6600;" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td colspan="2" style="padding:0px;"><div align="center" style="margin-bottom: 5px;"> <br />
                <div align="left" style="padding-left: 10px;"><strong>Originaler PHP/HTML-Code:</strong> F&uuml;gen Sie hier den zu verschl&uuml;sselnden PHP-Code (mit  &lt;?php und ?&gt;) oder HTML-Code ein.</div>
                <br />
                <textarea onfocus="this.select()" name="code" id="code" style="width: 98%; height: 200px; color: #666666;"><?php echo stripslashes($_POST['code']); ?></textarea>
                <br />
                <?php
				
				$resultcode ="";
				
				### PHP ENCODER
				
				if(isset($_POST['code']) && $_POST['code']!="")
				{
					$code = stripslashes(($_POST['code']));
					$code = encode($code);
					
					$cache = array();
					$cache2 = array();
					
					$result = preg_replace_callback("/(\\\\*\')/", "reescape", (ultimateencoder($code, true)));
					
					
					//Doppelte Verschlüsselung
					if($_POST['level']=="3")
					{
						$cache = array();
						$cache2 = array();
						
						$result = preg_replace_callback("/(\\\\*\')/", "reescape", (ultimateencoder($result, true)));
					}		

					echo '<br /><div align="left" style="padding-left: 10px;"><strong>Verschl&uuml;sselter PHP-Code:</strong> Dieser fertige PHP-Code kann 1:1 anstatt des originalen Codes verwendet werden.</div><br /'."<textarea onFocus=\"this.select()\" name=\"result\" style=\"width: 98%; height: 100px; color: #FF6600;\">".htmlentities($result, ENT_QUOTES, "utf-8")."</textarea>";
					
				}

				//Single-Quote Slashes reduzieren
				function reescape($saved)
				{
					return str_replace("\\\\\\\\", "\\\\", str_replace("\\\'", "\\'", ($saved[1])));
				}
			
				//Inhalt ersetzen
				function makekey($saved)
				{
					return str_replace($saved[1], newkey($saved[1]), $saved[0]);
				}
				
				
				//Codieren
				function arraysplit($saved)
				{
					return str_replace($saved[1], newkey($saved[1], true), $saved[0]);
				}
				
				//4-faches Escapes codieren
				function arrayescape($saved)
				{
					return str_replace($saved[1], newkey("8e3C7pe", true), $saved[0]);
				}
				
				//Neuen Key erstellen
				function newkey($saved, $strarr=false)
				{
					global $cache;
					global $cache2;
					
					if(!$strarr)
					{
						$num = 5;
						$alpha = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
					}
					else
					{
						$num = 3;
						$alpha = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
					}
					
					if($_POST['level']=="2" || $_POST['level']=="3")
						$num+=1;
					
					//Variablenname bereits vorhanden?
					if((!$strarr && !isset($cache[$saved])) || ($strarr && !isset($cache2[$saved])))
					{
						$alpha_nr = strlen($alpha);
						$key = "";
						
						 mt_srand((double)microtime()*1000000);
						
						for ($i = 0; $i < $num; $i++)
							$key .= $alpha[mt_rand(0,$alpha_nr - 1)];
						
						if(!$strarr)
							$cache[$saved] = $key;
						else
							$cache2[$saved] = $key;
					}
					
					if(!$strarr)
						return $cache[$saved];
					else
						return $cache2[$saved];
				}
				
				//Aufräumen
				function encode($code)
				{		
					//$code = stripslashes($code);
					
					//Kommentare entfernen
					$code = preg_replace("/\\/\*.*\*\\//s", "", $code); /**/
					$code = preg_replace("/\#.*/", "", $code); #
					$code = preg_replace("/\/\/.*/", "", $code); //
					
					//Whitespaces nach ; entfernen
					$code = preg_replace("/;\s*/", ";", $code);
				
					//Variablen
					$code = preg_replace_callback("/\\$([a-zA-Z0-9_]{1,})/", "makekey", $code);
					
					//Funktionen
					$code = preg_replace_callback("/function\s*([a-zA-Z0-9_]{1,})\s*\(/", "makekey", $code);
					
					//Zeilenumbrüche entfernen
					//$code = preg_replace("/[\n|\r]/", "", $code);
					
					return $code;
				}
				
				//Hauptfunktion
				function ultimateencoder($code, $addphptags=false)
				{
					global $cache2;
					
					######################
					$string = $code;
					
					if($_POST['level']=="2" || $_POST['level']=="3")
						$string = preg_replace_callback("/([a-zA-Z0-9_]{2})/", "arraysplit", $string);
					else
						$string = preg_replace_callback("/([a-zA-Z0-9_]{2,3})/", "arraysplit", $string);
				
					@asort($cache2);
					
					$buffer = "\$YPmBPG=array(";
					foreach($cache2 as $key=>$value)
						$buffer .=  "'".$key."'=>'".$value."',";
					$buffer .= ");";
					
					$return = 'eval("'.addcslashes($buffer,"$").' eval(str_replace(\$YPmBPG,array_keys(\$YPmBPG),\'?>'.addcslashes(addslashes($string), "\$").'<?php \'));");';

					
					if($addphptags)
						return "<?php ".$return." ?>";
					else
						return $return;
				}
				
				?>
                <br />
                <strong>Sicherheitslevel:</strong><br />
                <input type="radio" name="level" id="radio" value="1" <?php if(isset($_POST['level']) && $_POST['level']=="1") echo 'checked="checked"'; ?> />
                <label for="radio">Gering - Mittlere Sicherheit und wenig Code</label>
                <br />
                <input type="radio" name="level" id="radio2" value="2" <?php if(!isset($_POST['level']) || (isset($_POST['level']) && $_POST['level']=="2")) echo 'checked="checked"'; ?> />
                <label for="radio2">Standard - Mittlere Sicherheit und mehr Code</label>
                <br />
                <input type="radio" name="level" id="radio3" value="3" <?php if(isset($_POST['level']) && $_POST['level']=="3") echo 'checked="checked"'; ?> />
                <label for="radio3">Erweitert - Doppelte Verschlüsselung (Experimentell)</label>
                <br />
                <br />
                <input type="submit" name="submit" value="PHP-Code verschlüsseln!" style="font-weight:bold; width: 300px; height:30px;" />
              </div>
              <br /></td>
          </tr>
        </table>
        <br />
        <div align="center"><em>Copyright 2008 by phpWave Productions - <a href="http://www.phpwave.com" target="_blank">www.phpWave.com</a></em>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.phpwave.com/Freeware-Scripts/" target="_blank" title="Alle PHP-Scripts von phpWave Productions (wird in neuem Fenster ge&ouml;ffnet)..."><strong>Weitere Freeware-Scripts von phpWave.com &gt;&gt;</strong></a></div></td>
      <td><div align="center">
        &nbsp;</td>
    </tr>
  </table>
</form>
</body>
</html>
