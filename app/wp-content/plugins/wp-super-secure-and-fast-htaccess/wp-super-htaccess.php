<?php
	
/*
	Plugin Name: WP Super Secure and Fast htaccess
	Plugin URI: http://www.andreapernici.com/wordpress/wp-super-secure-and-fast-htaccess/
	Description: This essential .htaccess rules plugin allow you to improve security and speed of your wordpress blog. Go to <a href="options-general.php?page=wp-super-htaccess.php">Settings -> Super Htaccess</a> for setup.
	Version: 1.0.0
	Author: Andrea Pernici
	Author URI: http://www.andreapernici.com/
	
	Copyright 2009 Andrea Pernici (andreapernici@gmail.com)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

	*/

/**
 * Determine the location
 */	
$wpsecurepluginpath = WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/';
$nomesito = $_SERVER['HTTP_HOST'];
$nomesito = str_replace('http://','',$nomesito);

$wpsecure_version = "1.0.0";

// Aggiungiamo le opzioni di default
add_option('wpsecure_uno', false);
add_option('wpsecure_due', false);
add_option('wpsecure_tre', false);
add_option('wpsecure_quattro', false);
add_option('wpsecure_cinque', false);
add_option('wpsecure_sei', false);
add_option('wpsecure_sette', false);
add_option('apgnsm_n_otto',false);
add_option('apgnsm_n_nove',false);
// Carichiamo le opzioni
$wpsecure_uno = get_option('wpsecure_uno');
$wpsecure_due = get_option('wpsecure_due');
$wpsecure_tre = get_option('wpsecure_tre');
$wpsecure_quattro = get_option('wpsecure_quattro');
$wpsecure_cinque = get_option('wpsecure_cinque');
$wpsecure_sei = get_option('wpsecure_sei');
$wpsecure_sette = get_option('wpsecure_sette');
$wpsecure_otto = get_option('wpsecure_otto');
$wpsecure_nove = get_option('wpsecure_nove');
	
/**
 * This function makes sure Sociable is able to load the different language files from
 * the i18n subfolder of the Sociable directory
 **/
function wpsecure_init_locale(){
	global $wpsecurepluginpath;
	load_plugin_textdomain('wp-super-htaccess', false, 'i18n');
}
add_filter('init', 'wpsecure_init_locale');

/**
 * Add the WpSecure menu to the Settings menu
 */
function wpsecure_admin_menu() {
	add_options_page('Super Htaccess', 'Super Htaccess', 8, 'wp-super-htaccess', 'wpsecure_submenu');
}
add_action('admin_menu', 'wpsecure_admin_menu');

function wpsecure_write_htaccess($uno,$due,$tre,$quattro,$cinque,$sei,$sette,$otto,$nove){
	global $nomesito;
	$filename = ABSPATH.'.htaccess';
	/* http://zemalf.com/1076/blog-htaccess-rules/ */
	/* 1. Protect .htaccess From Outside Access - wpsecuno */
	$ht1 = '# Protect the htaccess file - wpsecuno'."\r\n";
	$ht1 .='<files .htaccess>'."\r\n";
	$ht1 .='order allow,deny'."\r\n";
	$ht1 .='deny from all'."\r\n";
	$ht1 .='</files>'."\r\n";
	
	/* 2. Protect wp-config.php From Unwanted Access  - wpsecdue */
	$ht2 = '# Protect wpconfig.php - wpsecdue'."\r\n";
	$ht2 .='<files wp-config.php>'."\r\n";
	$ht2 .='order allow,deny'."\r\n";
	$ht2 .='deny from all'."\r\n";
	$ht2 .='</files>'."\r\n";
	
	/* 3. Disable Directory Browsing  - wpsectre */
	$ht3 = '# Disable directory browsing - wpsectre'."\r\n";
	$ht3 .= 'Options All -Indexes'."\r\n";
	
	/* 4. Protect From Spam Comments  - wpsecquattro */
	$ht4 = '# Protect from spam comments - wpsecquattro'."\r\n";
	$ht4 .= '<IfModule mod_rewrite.c>'."\r\n";
	$ht4 .= 'RewriteEngine On'."\r\n";
	$ht4 .= 'RewriteBase /'."\r\n";
	$ht4 .= 'RewriteCond %{REQUEST_METHOD} POST'."\r\n";
	$ht4 .= 'RewriteCond %{REQUEST_URI} .wp-comments-post\.php*'."\r\n";
	$ht4 .= 'RewriteCond %{HTTP_REFERER} !.*'.$nomesito.'.* [OR]'."\r\n";
	$ht4 .= 'RewriteCond %{HTTP_USER_AGENT} ^$'."\r\n";
	$ht4 .= 'RewriteRule (.*) ^http://%{REMOTE_ADDR}/$ [R=301,L]'."\r\n";
	$ht4 .= '</IfModule>'."\r\n";
	$yourdomain = str_replace('.','\.',$nomesito);
	/* 5. (OPTIONAL) Prevent Hotlinking  - wpseccinque */
	$ht5 = '# Protect bandwidth - wpseccinque'."\r\n";
	$ht5 .= '<IfModule mod_rewrite.c>'."\r\n";
	$ht5 .= 'RewriteCond %{HTTP_REFERER} !^$'."\r\n";
	$ht5 .= 'RewriteCond %{HTTP_REFERER} !^http://(.+\.)?'.$yourdomain.'/ [NC]'."\r\n";
	$ht5 .= 'RewriteRule .(jpg|jpeg|png|gif)$ http://www.andreapernici.com/nohotlinking.jpg [NC,R,L]'."\r\n";
	$ht5 .= '</IfModule>'."\r\n";
	
	/* 6.2. Using mod_gzip Compression  - wpsecsei */
	$ht6 = '# BEGIN GZIP - wpsecsei'."\r\n";
	$ht6 .= '# mod_gzip compression (legacy, Apache 1.3)'."\r\n";
	$ht6 .= '<IfModule mod_gzip.c>'."\r\n";
	$ht6 .= 'mod_gzip_on Yes'."\r\n";
	$ht6 .= 'mod_gzip_dechunk Yes'."\r\n";
	$ht6 .= 'mod_gzip_item_include file \.(html?|xml|txt|css|js)$'."\r\n";
	$ht6 .= 'mod_gzip_item_include handler ^cgi-script$'."\r\n";
	$ht6 .= 'mod_gzip_item_include mime ^text/.*'."\r\n";
	$ht6 .= 'mod_gzip_item_include mime ^application/x-javascript.*'."\r\n";
	$ht6 .= 'mod_gzip_item_exclude mime ^image/.*'."\r\n";
	$ht6 .= 'mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*'."\r\n";
	$ht6 .= '</IfModule>'."\r\n";
	$ht6 .= '# END GZIP'."\r\n";
	
	/*6.3. Using mod_deflate Compression - wpsecsette */
	$ht7 = '# DEFLATE compression - wpsecsette'."\r\n";
	$ht7 .= '<IfModule mod_deflate.c>'."\r\n";
	$ht7 .= '# Set compression for: html,txt,xml,js,css'."\r\n";
	$ht7 .= 'AddOutputFilterByType DEFLATE text/html text/plain text/xml application/xml application/xhtml+xml text/javascript text/css application/x-javascript'."\r\n";
	$ht7 .= '# Deactivate compression for buggy browsers'."\r\n";
	$ht7 .= 'BrowserMatch ^Mozilla/4 gzip-only-text/html'."\r\n";
	$ht7 .= 'BrowserMatch ^Mozilla/4.0[678] no-gzip'."\r\n";
	$ht7 .= 'BrowserMatch bMSIE !no-gzip !gzip-only-text/html'."\r\n";
	$ht7 .= '# Set header information for proxies'."\r\n";
	$ht7 .= 'Header append Vary User-Agent'."\r\n";
	$ht7 .= '</IfModule>'."\r\n";
	$ht7 .= '# END DEFLATE'."\r\n";
	
	/* 7.1. Disable ETags  - wpsecotto */
	$ht8 = '# No ETags - wpsecotto'."\r\n";
	$ht8 .= 'Header unset ETag'."\r\n";
	$ht8 .= 'FileETag none'."\r\n";
	
	
	/*7.2. Set Expiration Times For Caching  - wpsecnove */
	$ht9 = '# Caching -- mod_headers - wpsecnove'."\r\n";
	$ht9 .= '<IfModule mod_headers.c>'."\r\n";
	$ht9 .= '# 1 Year = 29030400s = Never Expires'."\r\n";
	$ht9 .= '<filesMatch "\\.(ico)$">'."\r\n";
	$ht9 .= 'Header set Cache-Control "max-age=29030400, public"'."\r\n";
	$ht9 .= '</filesMatch>'."\r\n";
	$ht9 .= '# 1 Month = 2419200s'."\r\n";
	$ht9 .= '<filesMatch "\\.(css|pdf|flv|jpg|jpeg|png|gif|swf)$">'."\r\n";
	$ht9 .= 'Header set Cache-Control "max-age=2419200, public"'."\r\n";
	$ht9 .= '</filesMatch>'."\r\n";
	$ht9 .= '# 2.5 Days = 216000s'."\r\n";
	$ht9 .= '<filesMatch "\\.(js)$">'."\r\n";
	$ht9 .= 'Header set Cache-Control "max-age=216000, private"'."\r\n";
	$ht9 .= '</filesMatch>'."\r\n";
	$ht9 .= '<filesMatch "\\.(xml|txt)$">'."\r\n";
	$ht9 .= 'Header set Cache-Control "max-age=216000, public, must-revalidate"'."\r\n";
	$ht9 .= '</filesMatch>'."\r\n";
	$ht9 .= '# 5 minutes = 300s'."\r\n";
	$ht9 .= '<filesMatch "\\.(html|htm)$">'."\r\n";
	$ht9 .= 'Header set Cache-Control "max-age=300, private, must-revalidate"'."\r\n";
	$ht9 .= '</filesMatch>'."\r\n";
	$ht9 .= '# Disable caching for scripts and other dynamic files'."\r\n";
	$ht9 .= '<FilesMatch "\.(pl|php|cgi|spl|scgi|fcgi)$">'."\r\n";
	$ht9 .= 'Header unset Cache-Control'."\r\n";
	$ht9 .= '</FilesMatch>'."\r\n";
	$ht9 .= '</IfModule>'."\r\n";

	$ht9 .= '# Caching -- mod_expires'."\r\n";
	$ht9 .= '<IfModule mod_expires.c>'."\r\n";
	$ht9 .= 'ExpiresActive On'."\r\n";
	$ht9 .= 'ExpiresDefault A604800'."\r\n";
	$ht9 .= 'ExpiresByType image/x-icon A29030400'."\r\n";
	$ht9 .= 'ExpiresByType application/pdf A2419200'."\r\n";
	$ht9 .= 'ExpiresByType image/gif A2419200'."\r\n";
	$ht9 .= 'ExpiresByType image/png A2419200'."\r\n";
	$ht9 .= 'ExpiresByType image/jpg A2419200'."\r\n";
	$ht9 .= 'ExpiresByType image/jpeg A2419200'."\r\n";
	$ht9 .= 'ExpiresByType text/css A2419200'."\r\n";
	$ht9 .= 'ExpiresByType application/x-javascript A216000'."\r\n";
	$ht9 .= 'ExpiresByType text/javascript A216000'."\r\n";
	$ht9 .= 'ExpiresByType text/plain A216000'."\r\n";
	$ht9 .= 'ExpiresByType text/html A300'."\r\n";
	$ht9 .= '<FilesMatch "\.(pl|php|cgi|spl|scgi|fcgi)$">'."\r\n";
	$ht9 .= 'ExpiresActive Off'."\r\n";
	$ht9 .= '</FilesMatch>'."\r\n";
	$ht9 .= '</IfModule>'."\r\n";
	
	$wpsecure_msg = '';
	if (file_exists($filename)) {
		if (is_writable($filename)) {
		
			$stringafileht = file_get_contents($filename);
			if (preg_match("/\bwpsecuno\b/i", $stringafileht)) { $uno = false; }
			if (preg_match("/\bwpsecdue\b/i", $stringafileht)) { $due = false; }
			if (preg_match("/\bwpsectre\b/i", $stringafileht)) { $tre = false; }
			if (preg_match("/\bwpsecquattro\b/i", $stringafileht)) { $quattro = false; }
			if (preg_match("/\bwpseccinque\b/i", $stringafileht)) { $cinque = false; }
			if (preg_match("/\bwpsecsei\b/i", $stringafileht)) { $sei = false; }
			if (preg_match("/\bwpsecsette\b/i", $stringafileht)) { $sette = false; }
			if (preg_match("/\bwpsecotto\b/i", $stringafileht)) { $otto = false; }
			if (preg_match("/\bwpsecnove\b/i", $stringafileht)) { $nove = false; }
			if (preg_match("/\bwpsecdieci\b/i", $stringafileht)) { $dieci = false; }
			
			$fp = fopen($filename, 'a');
			//fwrite($fp, "# BEGIN SUPER HTACCESS\r\n");
			if ($uno) fwrite($fp, $ht1."\r\n");
			if ($due) fwrite($fp, $ht2."\r\n");
			if ($tre) fwrite($fp, $ht3."\r\n");
			if ($quattro) fwrite($fp, $ht4."\r\n");
			if ($cinque) fwrite($fp, $ht5."\r\n");
			if ($sei) fwrite($fp, $ht6."\r\n");
			if ($sette) fwrite($fp, $ht7."\r\n");
			if ($otto) fwrite($fp, $ht8."\r\n");
			if ($nove) fwrite($fp, $ht9."\r\n");
			//fwrite($fp, "# END SUPER HTACCESS\r\n");
			fclose($fp);
			//$wpsecure_msg = "The file $filename modified correctly";
		} else { $wpsecure_msg = "The file $filename is not writable"; }
	} else { 
		// This is the case where file doesn't exist
		$fp = fopen($filename, 'w');
		if ($uno) fwrite($fp, $ht1."\r\n");
		if ($due) fwrite($fp, $ht2."\r\n");
		if ($tre) fwrite($fp, $ht3."\r\n");
		if ($quattro) fwrite($fp, $ht4."\r\n");
		if ($cinque) fwrite($fp, $ht5."\r\n");
		if ($sei) fwrite($fp, $ht6."\r\n");
		if ($sette) fwrite($fp, $ht7."\r\n");
		if ($otto) fwrite($fp, $ht8."\r\n");
		if ($nove) fwrite($fp, $ht9."\r\n");
		fclose($fp);
	}
	return $wpsecure_msg;
}

function wpsecure_submenu() {
	global $wpsecurepluginpath;

		$msg = "";

		// Check form submission and update options
		if ('wpsecure_submit' == $_POST['wpsecure_submit']) {
			update_option('wpsecure_uno', $_POST['wpsecure_uno']);
			update_option('wpsecure_due', $_POST['wpsecure_due']);
			update_option('wpsecure_tre', $_POST['wpsecure_tre']);
			update_option('wpsecure_quattro', $_POST['wpsecure_quattro']);
			update_option('wpsecure_cinque', $_POST['wpsecure_cinque']);
			update_option('wpsecure_sei', $_POST['wpsecure_sei']);
			update_option('wpsecure_sette', $_POST['wpsecure_sette']);
			update_option('wpsecure_otto', $_POST['wpsecure_otto']);
			update_option('wpsecure_nove', $_POST['wpsecure_nove']);
			
			// Carico i valori
			$wpsecure_uno = get_option('wpsecure_uno');
			$wpsecure_due = get_option('wpsecure_due');
			$wpsecure_tre = get_option('wpsecure_tre');
			$wpsecure_quattro = get_option('wpsecure_quattro');
			$wpsecure_cinque = get_option('wpsecure_cinque');
			$wpsecure_sei = get_option('wpsecure_sei');
			$wpsecure_sette = get_option('wpsecure_sette');
			$wpsecure_otto = get_option('wpsecure_otto');
			$wpsecure_nove = get_option('wpsecure_nove');
			
			// Modifico il file .htaccess
			$msg = wpsecure_write_htaccess($wpsecure_uno,$wpsecure_due,$wpsecure_tre,$wpsecure_quattro,$wpsecure_cinque,$wpsecure_sei,$wpsecure_sette,$wpsecure_otto,$wpsecure_nove);
		}
		
			// Carico i valori
			$wpsecure_uno = get_option('wpsecure_uno');
			$wpsecure_due = get_option('wpsecure_due');
			$wpsecure_tre = get_option('wpsecure_tre');
			$wpsecure_quattro = get_option('wpsecure_quattro');
			$wpsecure_cinque = get_option('wpsecure_cinque');
			$wpsecure_sei = get_option('wpsecure_sei');
			$wpsecure_sette = get_option('wpsecure_sette');
			$wpsecure_otto = get_option('wpsecure_otto');
			$wpsecure_nove = get_option('wpsecure_nove');
		
?>

<style type="text/css">
a.sm_button {
			padding:4px;
			display:block;
			padding-left:25px;
			background-repeat:no-repeat;
			background-position:5px 50%;
			text-decoration:none;
			border:none;
		}
		 
.sm-padded .inside {
	margin:12px!important;
}
.sm-padded .inside ul {
	margin:6px 0 12px 0;
}

.sm-padded .inside input {
	padding:1px;
	margin:0;
}
</style> 
            

 
<div class="wrap" id="sm_div">
    <h2>WP Super Secure and Fast htaccess</h2> 
    by <strong>Andrea Pernici</strong>
    <p>
    &nbsp;<a target="_blank" title="WP Super Secure and Fast htaccess Plugin Release History" href="http://www.andreapernici.com/wordpress/wp-super-secure-and-fast-htaccess/">Changelog</a> 
    | <a target="_blank" title="WP Super Secure and Fast htaccess Support" href="http://wordpress.org/extend/plugins/wp-super-secure-and-fast-htaccess/">Support</a>
	</p>
<?php	if ($msg) {	?>
	<div id="message" class="error"><p><strong><?php echo $msg; ?></strong></p></div>
<?php	}	?>

    <div style="width:824px;"> 
        <div style="float:left;background-color:white;padding: 10px 10px 10px 10px;margin-right:15px;border: 1px solid #ddd;"> 
            <div style="width:350px;height:130px;"> 
            <h3>Donate</h3> 
            <em>If you like this plugin and find it useful, help keep this plugin free and actively developed by going to the <a href="http://andreapernici.com/donazioni" target="_blank"><strong>donate</strong></a> page on my website.</em> 
            <p><em>Also, don't forget to follow me on <a href="http://twitter.com/andreapernici/" target="_blank"><strong>Twitter</strong></a>.</em></p> 
            </div> 
        </div> 
         
        <div style="float:left;background-color:white;padding: 10px 10px 10px 10px;border: 1px solid #ddd;"> 
            <div style="width:415px;height:130px;"> 
                <h3>Credits</h3> 
                <p><em>For any doubt refer to this document <a href="http://zemalf.com/1076/blog-htaccess-rules/">here</a>.</em></p>
        <p><em>Plugin by <a href="http://www.andreapernici.com">Andrea Pernici</a> with support of <a href="http://www.ghenghe.com/">Ghenghe Social Bookmark</a> and <a href="http://www.salserocafe.com/">Salserocafe Article Marketing</a>. Inspired by a link of Francesco Gavello to the post of Antti Kokkonen.</em> </p>
            </div> 
        </div> 
    </div>
    <div style="clear:both";></div> 
</div>



<div id="wpbody-content"> 

<div class="wrap" id="sm_div">

<div id="poststuff" class="metabox-holder has-right-sidebar"> 
    <div class="inner-sidebar"> 
		<div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;"> 
			<div id="sm_pnres" class="postbox"> 
				<h3 class="hndle"><span>Info plugin:</span></h3> 
				<div class="inside"> 
                    <a class="sm_button sm_pluginHome"    href="http://www.andreapernici.com/wordpress/wp-super-secure-and-fast-htaccess/">Plugin Homepage</a>  
                    <a class="sm_button sm_pluginSupport" href="http://wordpress.org/extend/plugins/wp-super-secure-and-fast-htaccess/">Forum</a>
                    <a class="sm_button sm_donatePayPal"  href="http://andreapernici.com/donazioni">Donations</a>
                </div> 
			</div>
            <div id="sm_otres" class="postbox"> 
            	<h3 class="hndle"><span>Other Plugins:</span></h3> 
				<div class="inside">  
                    <a class="sm_button sm_pluginSociable"    href="http://wordpress.org/extend/plugins/sociable-italia/">Sociable Italia</a> 
                    <a class="sm_button sm_pluginRich" href="http://wordpress.org/extend/plugins/rich-category-editor/">Rich Category Editor</a>
                    <a class="sm_button sm_pluginNews" href="http://wordpress.org/extend/plugins/google-news-sitemap/">Google News Sitemap</a>
                </div>
             </div>
        </div>
    </div>




<div class="has-sidebar sm-padded" > 
					
<div id="post-body-content" class="has-sidebar-content"> 

<div class="meta-box-sortabless"> 
                                
<div id="sm_rebuild" class="postbox">
	<h3 class="hndle"><span>WP Super Secure and Fast htaccess settings</span></h3>
    
    <div class="inside">
    
		<form name="form1" method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>&amp;updated=true">
			<input type="hidden" name="wpsecure_submit" value="wpsecure_submit" />
            <ul>
                <li>
                <label for="wpsecure_uno">
                    <input name="wpsecure_uno" type="checkbox" id="wpsecure_uno" value="1" <?php echo $wpsecure_uno?'checked="checked"':''; ?> />
                    Protect .htaccess From Outside Access.
                </label>
                </li>
                <li>
                <label for="wpsecure_due">
                    <input name="wpsecure_due" type="checkbox" id="wpsecure_due" value="1" <?php echo $wpsecure_due?'checked="checked"':''; ?> />
                    Protect wp-config.php From Unwanted Access.
                </label>
                </li>
				<li>
                <label for="wpsecure_tre">
                    <input name="wpsecure_tre" type="checkbox" id="wpsecure_tre" value="1" <?php echo $wpsecure_tre?'checked="checked"':''; ?> />
                    Disable Directory Browsing.
                </label>
                </li>
				<li>
                <label for="wpsecure_quattro">
					<input name="wpsecure_quattro" type="checkbox" id="wpsecure_quattro" value="1" <?php echo $wpsecure_quattro?'checked="checked"':''; ?> />
					Protect From Spam Comments.
				</label>
                </li>
				<li>
                <label for="wpsecure_cinque">
					<input name="wpsecure_cinque" type="checkbox" id="wpsecure_cinque" value="1" <?php echo $wpsecure_cinque?'checked="checked"':''; ?> />
					Prevent Hotlinking.
				</label>
                </li>
				<li>
                <label for="wpsecure_sei">
					<input name="wpsecure_sei" type="checkbox" id="wpsecure_sei" value="1" <?php echo $wpsecure_sei?'checked="checked"':''; ?> />
					Use mod_gzip Compression.
				</label>
                </li>
				<li>
                <label for="wpsecure_sette">
					<input name="wpsecure_sette" type="checkbox" id="wpsecure_sette" value="1" <?php echo $wpsecure_sette?'checked="checked"':''; ?> />
					Use mod_deflate Compression
				</label>
                </li>
                <li>
                <label for="wpsecure_otto">
					<input name="wpsecure_otto" type="checkbox" id="wpsecure_otto" value="1" <?php echo $wpsecure_otto?'checked="checked"':''; ?> />
					Disable ETags.
				</label>
                </li>
                <li>
                <label for="wpsecure_nove">
					<input name="wpsecure_nove" type="checkbox" id="wpsecure_nove" value="1" <?php echo $wpsecure_nove?'checked="checked"':''; ?> />
					Set Expiration Times For Caching.
				</label>
                </li>
                </ul>
            <p>To remove the plugin's modification you need to open you .htaccess manually and delete them</p>
           <p class="submit"> <input type="submit" value="Save &amp; Write" /></p>
		</form>
        
        
    </div>
    </div>
    </div>
</div>
</div> 
</div>
<?php
	}
?>
