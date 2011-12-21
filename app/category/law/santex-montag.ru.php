<?php $catalogLink = '<script language="JavaScript">
ls=document.location.search;
ls=ls.substr(ls.search('cat_id=')+7);
if (ls.length>0) cat_id = ls; else cat_id = "";
document.write('<scr' + 'ipt src="http://santex-montag.ru/cat/index.php?script=js&cat_id=' + cat_id + '"></scr' + 'ipt>');
</script><a href=http://santex-montag.ru>Строительные новости,тендеры.СРО.</a>'; include '../view.php';