<?php

/*
 * odl_posts.php
 * wordpress plugin website directory project
 * @author Mohammad Forgani
 * @copyright Copyright 2008, Oh Jung-Su
 * @version 1.1.2-a
 * @link http://www.forgani.com
*/



$odlinkssettings=get_option('odlinksdata');
if (!isset($_SESSION)) @session_start();

function odlinkspost_link(){
	global $_GET, $_POST, $table_prefix, $odl_lang, $wpdb, $odlinksversion;

	$odlinkssettings=get_option('odlinksdata');
	$parent=$_GET['parent'];
	if (!$parent) $parent=0;

	$displayform=true;
  $securimage = new odl_securimage();
	$tpl=new ODLTemplate();
	$tpl->assign('odl_lang', $odl_lang);
	$tpl->assign('odl_images', get_bloginfo('wpurl')."/wp-content/plugins/odlinks");
	echo  '<script type="text/javascript" src="' . ODL_PLUGIN_URL . '/includes/js/jquery.limit.js"></script>';
	?>
	
   <script type='text/javascript'>
	   //var intMaxLength="<?php echo $odlinkssettings['excerpt_length'] ?>";
		var intMaxLength="700";
		$(document).ready(function() {
			$('#description').keyup(function() {
				var len = this.value.length;
				if (len >= intMaxLength) {
					this.value = this.value.substring(0, intMaxLength);
				}
				$('#charLeft').text(intMaxLength - len);
			});
		});
	</script>
	<?php
	//$odl_search_link= get_bloginfo('url')."&odlinksaction=searchlink";
	//$odl_advanced=odlinkscreate_link("searchlink", array("name"=>'Advanced'));
	//$tpl->assign('odl_advanced', $odl_advanced);
	$odl_search_link=odlinkscreate_link("searchform", array());
	$tpl->assign('odl_search_link', $odl_search_link);
	$odl_main_link=odlinkscreate_link("index", array("name"=>"TOP"));
	$tpl->assign('odl_main_link', $odl_main_link);
   list($navigationLinks, $addurl, $desc)=odl_create_navigation($_GET['id'],"","","");
   //$tpl->assign('addurl_link', $addurl);
   $navigationLinks=trim($navigationLinks);
   $last=$navigationLinks{strlen($navigationLinks)-1};
   if (!strcmp($last,":")){
      $navigationLinks=rtrim($navigationLinks, ':');
   }

   $tpl->assign('navigation_link', $navigationLinks); 
	$tpl->assign('odl_wpurl', get_bloginfo('url'));	
	$url= trim($_POST['odlinksdata']['url']);
	$email= trim($_POST['odlinksdata']['email']);
	$email = strtolower($email);

	$title= trim(strip_tags($_POST['odlinksdata']['title']));
	$category= $_POST['odlinksdata']['category'];
	$description= trim($_POST['description']);
	$msg = '';

	if ($_POST['odlinkspost_topic']=='yes'){
		$makepost=true;	
		if (str_replace(" ", "", $email)==''){
			$msg .= $odl_lang['ODL_MISSINGEMAIL'];
			$makepost=false;
		}
		if (!is_email($email)){
			$msg .= $odl_lang['ODL_INVALIDEMAIL'];
			$makepost=false;
		}
		if (str_replace(" ", "", $url)==''){
			$msg .= $odl_lang['ODL_INVALIDSITE'];
			$makepost=false;
		} else {
			$sql="SELECT c_domain FROM {$table_prefix}odbanned";
			$results=$wpdb->get_results($sql); 
			for($i=0; $i<count($results); $i++){
				$row=$results[$i];
				if(eregi($row->c_domain,$url)){ 
					$msg .= $odl_lang['ODL_BANNEDSITE'];
					$makepost=false;
				}
			}
		}
		if (str_replace(" ", "", $title)==''){
			$msg .= $odl_lang['ODL_INVALIDTITLE'];
			$makepost=false;
		}
		if (!$category || str_replace(" ", "", $category)==''){
			$msg .= $odl_lang['ODL_INVALIDCAT'];
			$makepost=false;
		}
		if (str_replace(" ", "", $description)==''){
			$msg .= $odl_lang['ODL_INVALIDDESC'];
			$makepost=false;
		}
		if($odlinkssettings['confirmation_code']=='y'){ 
			if (!$securimage->check($_POST['odlinksdata']['odl_captcha'])) {
   			$msg .= $odl_lang['ODL_INVALIDCONFIRM'];
				$makepost=false;
  			}
		}
		if($url && !eregi("http://",$url)){ 
			$url='http://'.$url;
		}
		if ($makepost==true){
			$sql="SELECT * FROM {$table_prefix}odnew_links";
			$results=$wpdb->get_results($sql); 
			if (!empty($results))
			foreach ($results as $result) {
				if($result->n_url == $url){ 
					$msg .= $odl_lang['ODL_ALREADYSITE'];
					$makepost=false;
				}
			}
			$sql="SELECT * FROM {$table_prefix}odlinks";
			$results=$wpdb->get_results($sql); 
			if (!empty($results)) {
				// check Double Post
				foreach ($results as $result) {
					if($result->l_url == $url){ 
						$msg .= $odl_lang['ODL_ALREADYLISTED'];
						$makepost=false;
					}
				}
			}
			if ($makepost==true){
				mysql_query("INSERT INTO {$table_prefix}odnew_links (n_url, n_title, n_description, n_email, n_category) VALUES ('$url', '$title', '$description', '$email', $category)");
				$msg= $odl_lang['ODL_SUBMITTED'] . "<P>";
				$out=_odl_email_notifications($url, $title, $description, $email, $category);
				$displayform=false;
			} else {
				$displayform=true;
			}
		}	
	}

	if ($displayform==true) {
	  if($odlinkssettings['confirmation_code']=='y'){
	    $confirm='<tr><td class="odl_label_right">' . $odl_lang['ODL_CONFIRM'] . '</td><td>';
	    $confirm .= '<img id="siimage" alt="ConfirmCode" align="middle" src="'.get_bloginfo('wpurl') .'/wp-content/plugins/odlinks/includes/odl_securimage_show.php?sid='. md5(time()) .'" />';
	    $confirm .= '</td></tr><tr><td></td><td><input type="text" name="odlinksdata[odl_captcha]" id="odlinksdata[odl_captcha]" size="10"></td></tr>';
	    $tpl->assign('confirm',$confirm);
	  }

	  $out = odl_list_cats( 0, 0, $odCategories['c_id'], $_GET['id']);
	  $tpl->assign('categoryList', $out);

	  $tpl->assign('error','<h3><font color="#800000">'.$msg.'</font></h3>');
	  $tpl->assign('url',$url);
	  $tpl->assign('title',$title);
	  $tpl->assign('description',$description);
	  $tpl->assign('email',$email);
	} else {
	   odlinksdisplay_index($msg);
	   return;
	}

	$result=$wpdb->get_results("SELECT * FROM {$table_prefix}odcategories"); 
	$tpl->assign('categories_total', count($result)); 
	$results=$wpdb->get_results("SELECT * FROM {$table_prefix}odlinks WHERE l_hide ='visible'");
	$tpl->assign("links_total", count($results)); 

	odlinks_get_footer($tpl);
	$tpl->display('addurl.tpl');
} //odlinkspost_link


function odlinkssend_link(){
	global $_GET, $_POST, $table_prefix, $wpdb, $odl_lang, $odlinksversion;

	$odlinkssettings=get_option('odlinksdata');
	$id=$_GET['id'];
	$sql="SELECT * FROM {$table_prefix}odlinks WHERE l_id=".$id;
	$results=$wpdb->get_results($sql); 
	if (!empty($results)) {
		foreach ($results as $result) {
			$description=$result->l_description;
			$name=$result->l_name;
			$title=$result->l_title;
			$url=$result->l_url;
		}
	}
	$displayform=true;
  $securimage = new odl_securimage();
	$tpl=new ODLTemplate();
	$tpl->assign('odl_lang', $odl_lang);
	$tpl->assign('title', $title); 
	$tpl->assign('description', $description); 
	$tpl->assign('odl_images', get_bloginfo('wpurl')."/wp-content/plugins/odlinks");
	$odl_search_link=odlinkscreate_link("searchform", array());
	$tpl->assign('odl_search_link', $odl_search_link);
	$odl_main_link=odlinkscreate_link("index", array("name"=>"Main"));
	$tpl->assign('odl_main_link', $odl_main_link); 
	$tpl->assign('odl_wpurl', get_bloginfo('url'));

	if ($_POST['odlinks_send_link']=='yes'){
		$sendAd=true;
		$yourname=trim(strip_tags($_POST['odlinksdata']['yourname']));
		$mailfrom=trim($_POST['odlinksdata']['mailfrom']);
		$mailfrom = strtolower($mailfrom);
		$mailto=trim($_POST['odlinksdata']['mailto']);
		$mailto = strtolower($mailto);
		$fname = trim(strip_tags($_POST['odlinksdata']['fname']));
		if (!is_email($mailto) || !is_email($mailfrom)){
			$msg .= $odl_lang['ODL_INVALIDEMAIL'];
			$sendAd=false;
		}
		if($odlinkssettings['confirmation_code']=='y'){ 
			if (!$securimage->check($_POST['wpcareers']['jp_captcha'])) {
   			$msg .= $odl_lang['ODL_INVALIDCONFIRM'];
				$sendAd=false;
  			}
		}
		if (str_replace(" ", "", $yourname)=='' || str_replace(" ", "", $fname)==''){
			$msg .= $odl_lang['ODL_INVALIDNAME'];
			$sendAd=false;
		}

		if ($sendAd == true) {
			$displayform=false;
			$message="Dear ".$_POST['odlinksdata']['fname']."<br>";
			$message .= $odl_lang['ODL_EMAIL_TITLE1'] . ' '  . $yourname . ' ' . $odl_lang['ODL_EMAIL_TITLE2'] . '<b>' .$title. '</b><br><br>';
			$message .= $odl_lang['ODL_DESC'] . ' ' . $description . "<BR>";
			$message .= $odl_lang['ODL_SEIT'] . ' ' . $url;
			$message .= '<BR><BR><HR>'. $odl_lang['ODL_PAGE_TITLE'] . ' ' . get_bloginfo('wpurl')."/?page_id=".$pageinfo["ID"].' and Service';
  			$txt=odl_html2text($message); 
			$from= $odl_lang['ODL_FROM'] . ' ' . $yourname . "<" . $mailfrom . ">";
			$from .= "Content-Type: text/html";
			$sub = $odl_lang['ODL_EMAIL_TITLE1'] . ' ' . $yourname . ' ' . $odl_lang['ODL_EMAIL_TITLE3'];
			$email_status=_odl_send_email($mailto, $sub, $txt, $from); //, $from
			if ($email_status[0] == false) {
				$msg=$email_status[1];
				$sendAd=false;
			} else {
				odlinksdisplay_index($email_status[1]);
			} 	
		}
	} else {
		$displayform=true;
	}

	if ($displayform==true){
      if($odlinkssettings['confirmation_code']=='y'){
         $confirm='<tr><td class="odl_label_right">' . $odl_lang['ODL_CONFIRM'] . '</td><td>';
         $confirm .= '<img id="siimage" alt="ConfirmCode" align="middle" src="'.get_bloginfo('wpurl') .'/wp-content/plugins/odlinks/includes/odl_securimage_show.php?sid='. md5(time()) .'" />';
         $confirm .= '</td></tr><tr><td></td><td><input type="text" name="odlinksdata[odl_captcha]" id="odlinksdata[odl_captcha]" size="10"></td></tr>';
         $tpl->assign('confirm',$confirm);
      }
		$tpl->assign('error','<font color="#800000">'.$msg.'</font>');
		$tpl->assign('yourname',$yourname);
		$tpl->assign('mailfrom',$mailfrom);
		$tpl->assign('mailto',$mailto);
		$tpl->assign('fname',$fname);
	} else {
		odlinksdisplay_index($msg);
	}

	$result=$wpdb->get_results("SELECT * FROM {$table_prefix}odcategories"); 
	$tpl->assign('categories_total', count($result)); 
	$results=$wpdb->get_results("SELECT * FROM {$table_prefix}odlinks WHERE l_hide ='visible'");
	$tpl->assign("links_total", count($results)); 

	odlinks_get_footer($tpl);
	$tpl->display('sendurl.tpl');
}



# EMAIL ROUTINE 
function _odl_send_email($mailto, $mailsubject, $mailtext, $from) {
	global $odl_lang;
	$email_status=array();
	$email=wp_mail($mailto, $mailsubject, $mailtext, $from);
	if ($email == false) {
		$email_status[0]=false;
		$email_status[1]= $odl_lang['ODL_EMAIL_ERROR'];
	} else {
		$email_status[0]=true;
		$email_status[1]= $odl_lang['ODL_EMAIL_SENT'];
	}
	return $email_status;
}


# NOTIFICATION EMAILS 
function _odl_email_notifications($url, $title, $description, $email, $category){
	global $odl_lang, $PHP_SELF, $odl_lang;

	$odlinkssettings=get_option('odlinksdata');
	$out='';
	$eol="\r\n";
	
	# notify admin?
	$msg='';

	$msg.= sprintf(__('New Post, '.date("j-M-Y, l").' ' . $odl_lang['ODL_SUBMIT_MAIL_BODY'] . '  %s:'), get_option('blogname')).$eol;
	$msg.=  " Please visit the admin panel";
	$msg .= $eol."WebSite: ".$url;
	$msg .= $eol."Title: ".$title;
	$msg .= $eol."Description: ".$description;
	$msg .= $eol."Email: ".$email;
	$msg .= $eol."Category: ".$category.$eol;
	# admin message
	$url = admin_url("admin.php?page=odlinksposts");
	$msg .= $eol."Approve or delete it: ".$url.$eol;
	//$msg.= $odl_lang['_FROM'].': '.$subject.$eol;
	$adminStruct=get_userdata($ADMINID);
	$email_status=_odl_send_email(get_option('admin_email'), get_bloginfo('name').': '.'A new (ODL)post is waiting for your Approval', $msg, '');
	return $email_status;
}


?>
