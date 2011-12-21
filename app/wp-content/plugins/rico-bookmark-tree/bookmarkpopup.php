<?php
require_once(dirname(__FILE__).'/config.php');require_once('functions.php');if(isset($_GET['popup'])) {$v1016259232_3='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta content="text/html;charset=UTF-8" http-equiv="content-type">
<title>Bookmark for "%%TITLE%%"</title>
<title></title>
<style rel="stylesheet" type="text/css" media="screen" />@import url(\'popup.css\')</style>
</head>
<body>
<div>%%TITLE%%</div>
Paste the below code onto the bookmark tree.<br>
Please add items for [PARENTCAT] and [SUBCAT] if necessary.<br>
<br>
<b>[LINK]<br>
[PARENTCAT]%%PARENT%%[/PARENTCAT]<br>
[SUBCAT]%%CAT%%[/SUBCAT]<br>
[NAME]%%TITLE%%[/NAME]<br>
[URL]%%URL%%[/URL]<br>
[/LINK]</b>
</body>';$v1694876817_1=RBTsanitize($_GET['title']);$v1109112748_14=RBTsanitize($_GET['url']);$v486087318_9=RBTsanitize($_GET['desc']);$v1016259232_3=preg_replace("/%%PARENT%%/","",$v1016259232_3);$v1016259232_3=preg_replace("/%%CAT%%/","",$v1016259232_3);$v1016259232_3=preg_replace("/%%TITLE%%/",$v1694876817_1,$v1016259232_3);$v1016259232_3=preg_replace("/%%URL%%/",$v1109112748_14,$v1016259232_3);echo $v1016259232_3;} ?>