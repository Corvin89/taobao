<?php
function RBTloadCSS($v918017053_18) {return '
<link href="'.$v918017053_18.'rico21/css/demo.css" type="text/css" rel="stylesheet" />
';} function RBTloadRico($v918017053_18) {return '
<script src="'.$v918017053_18.'rico21/src/prototype.js" type="text/javascript"></script>
<script src="'.$v918017053_18.'rico21/src/rico.js" type="text/javascript"></script>
';} function RBTloadModule($v1044502671_46) {return '
Rico.loadModule(\'Tree\');var tree'.$v1044502671_46.';';} function bookmarkPage($v737192983_15,$v1057455077_27) {if($v737192983_15){$v1285592845_25="<p><strong>$v737192983_15</strong></p>";} foreach($v1057455077_27 as $v1044502671_46) {$v1285592845_25.="<div id='tree$v1044502671_46' class='ricoTree'></div>\n";} return $v1285592845_25;} function bookmarkbooklet($v918017053_18) {require_once("popupjs.php");return '*Drag this <a href="'.popupjs($v918017053_18).'">Bookmarklet</a> to Bookmarks Toolbar of the browser.'/*</div>'*/;} function makeBookmarkTree($v1057455077_27, $v828318234_3, $v91571839_1) {$v1285592845_25="<script type='text/javascript'>";if(!$v91571839_1) {$v1285592845_25.="
Rico.loadModule('Tree');";} foreach($v1057455077_27 as $v2106384614_2) {$v1285592845_25.="
var tree".$v2106384614_2.";\n";} $v1285592845_25.='Rico.onLoad( function() {var options={showCheckBox : false,
showLines    : false,
showPlusMinus: false,
showFolders  : true };';$c=0;foreach($v1057455077_27 as $v1044502671_46) {$v1285592845_25.='tree'.$v1044502671_46.'=new Rico.TreeControl("tree'.$v1044502671_46.'",null,options);tree'.$v1044502671_46.'.setTreeDiv(\'tree'.$v1044502671_46.'\');';foreach($v828318234_3[$v1044502671_46] as $v2106384614_2) {$v1285592845_25.='tree'.$v1044502671_46.'.addNode('.($v2106384614_2['parent']!=null?'\''.$v2106384614_2['parent'].'\'':'null').',\''
.$v2106384614_2['node'].'\',\''.$v2106384614_2['desc'].'\','.($v2106384614_2['iscontainer']==true?'true':'false').','
.($v2106384614_2['selectable']==false?'false':'\''.$v2106384614_2['selectable'].'\'').');'."\n";} $v1285592845_25.='tree'.$v1044502671_46.'.open();';} $v1285592845_25.='});';foreach($v1057455077_27 as $v1044502671_46) {$v1285592845_25.='
function TreeClick'.$v1044502671_46.'(e) {var items=tree'.$v1044502671_46.'.getCheckedItems();var msg=items.length==0 ? \'No items are checked\' : items.join(\'\n\');alert(msg);}';} $v1285592845_25.='</script>';$v1285592845_25.='
<style type="text/css">
div.ricoTree {border:none;} .ricoTreeLevel0 {font-weight: bold;font-size: larger;} .ricoTreeBranch {margin-left: 10px;} </style>';return $v1285592845_25;} ?>