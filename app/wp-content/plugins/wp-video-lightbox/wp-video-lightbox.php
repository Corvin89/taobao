<?php
/*
Plugin Name: WP Video Lightbox
Version: v1.1
Plugin URI: http://tipsandtricks-hq.com
Author: Ruhul Amin
Author URI: http://www.tipsandtricks-hq.com/
Description: Simple video lightbox plugin to display videos in an overlay. It also supports images, flash, YouTube, iFrame.
*/
define('WP_LICENSE_MANAGER_VERSION', "1.1");
define('WP_VID_LIGHTBOX_FOLDER', dirname(plugin_basename(__FILE__)));
$wp_vid_lightbox_url = WP_PLUGIN_URL.'/'.WP_VID_LIGHTBOX_FOLDER;
if ($_SERVER["HTTPS"] == "on")
{
	$wp_vid_lightbox_url = str_replace("http","https",$wp_vid_lightbox_url);
}
define('WP_VID_LIGHTBOX_URL', $wp_vid_lightbox_url);

add_shortcode('video_lightbox_vimeo5', 'wp_vid_lightbox_vimeo5_handler');
add_shortcode('video_lightbox_youtube', 'wp_vid_lightbox_youtube_handler');

function wp_vid_lightbox_vimeo5_handler($atts) 
{
	extract(shortcode_atts(array(
		'video_id' => 'no video id specified',
		'width' => 'no width specified',	
		'height' => 'no height specified',
		'anchor' => 'no anchor specified',	
	), $atts));

	if (preg_match("/http/", $anchor)) // Use the image as the anchor
    {
    	$anchor_replacement = '<img src="'.$anchor.'" class="video_lightbox_anchor_image" alt="" />';
    }
    else
    {
    	$anchor_replacement = $anchor;
    }    
    $href_content = '#inline-'.$video_id.','.$width.','.$height;		
	
	$output = "";
	$output .= '<a rel="wp-video-lightbox" href="'.$href_content.'">'.$anchor_replacement.'</a>';
	$output .= '<div id="inline-'.$video_id.'" style="display: none;">';
	$output .= '<iframe src="http://player.vimeo.com/video/'.$video_id.'?title=0&amp;byline=0&amp;portrait=0" width="'.$width.'/embed/?moog_width='.$width.'" height="'.$height.'" frameborder="0"></iframe>';
	$output .= '</div>';
	
	return $output;
}

function wp_vid_lightbox_youtube_handler($atts)
{
	extract(shortcode_atts(array(
		'video_id' => 'no video id specified',
		'width' => 'no width specified',	
		'height' => 'no height specified',
		'anchor' => 'no anchor specified',	
	), $atts));

	if (preg_match("/http/", $anchor)) // Use the image as the anchor
    {
    	$anchor_replacement = '<img src="'.$anchor.'" class="video_lightbox_anchor_image" alt="" />';
    }
    else
    {
    	$anchor_replacement = $anchor;
    } 
    $href_content = 'http://www.youtube.com/watch?v='.$video_id.'?width='.$width.'&height='.$height;
	$output = '<a rel="wp-video-lightbox" href="'.$href_content.'">'.$anchor_replacement.'</a>';

	return $output;
}

function wp_vid_lightbox_head_content()
{	
	echo '<link type="text/css" rel="stylesheet" href="'.WP_VID_LIGHTBOX_URL.'/css/prettyPhoto.css" />';
}
function wp_vid_lightbox_init()
{
	if (!is_admin()) 
	{
		wp_enqueue_script('jquery');
	    wp_register_script('jquery.prettyphoto', WP_VID_LIGHTBOX_URL.'/js/jquery.prettyPhoto.js', array('jquery'), '2.5.6');
	    wp_enqueue_script('jquery.prettyphoto');	
	}
}

function wp_vid_footer_content()
{
	wp_vid_lightbox_style();
}

function wp_vid_lightbox_style() 
{
    $vid_lightbox_rel             = "wp-video-lightbox";
    $vid_lightbox_speed           = 'normal';
    $vid_lightbox_padding         = '20';
    $vid_lightbox_opacity         = '0.50';
    $vid_lightbox_title           = 'true';
    $vid_lightbox_resize          = 'true';
	$default_width		  		  = '640';
	$default_height		  		  = '480';    
    $vid_lightbox_counterlabel    = '/';
    $vid_lightbox_theme           = 'dark_rounded';
    $vid_lightbox_callback        = 'function(){}';
    $vid_lightbox_hideflash       = 'false';
    $vid_lightbox_modal           = 'false';
    $vid_lightbox_picturecallback = 'function(){}';
    $vid_lightbox_wmode		  = 'opaque';
    $vid_lightbox_autoplay	  = 'false';
    $vid_lightbox_markup	  = '<div class="pp_pic_holder"> \
						<div class="pp_top"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
						<div class="pp_content_container"> \
							<div class="pp_left"> \
							<div class="pp_right"> \
								<div class="pp_content"> \
									<div class="pp_fade"> \
										<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
										<div class="pp_loaderIcon"></div> \
										<div class="pp_hoverContainer"> \
											<a class="pp_next" href="#">next</a> \
											<a class="pp_previous" href="#">previous</a> \
										</div> \
										<div id="pp_full_res"></div> \
										<div class="pp_details clearfix"> \
											<a class="pp_close" href="#">Close</a> \
											<p class="pp_description"></p> \
											<div class="pp_nav"> \
												<a href="#" class="pp_arrow_previous">Previous</a> \
												<p class="currentTextHolder">0/0</p> \
												<a href="#" class="pp_arrow_next">Next</a> \
											</div> \
										</div> \
									</div> \
								</div> \
							</div> \
							</div> \
						</div> \
						<div class="pp_bottom"> \
							<div class="pp_left"></div> \
							<div class="pp_middle"></div> \
							<div class="pp_right"></div> \
						</div> \
					</div> \
					<div class="pp_overlay"></div> \
					<div class="ppt"></div>';
    $vid_lightbox_imarkup	  = '<img id="fullResImage" src="" />';
    $vid_lightbox_fmarkup	  = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{width}" height="{height}"><param name="wmode" value="{wmode}" /><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="{path}" /><embed src="{path}" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="{width}" height="{height}" wmode="{wmode}"></embed></object>';
    $vid_lightbox_qmarkup	  = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" height="{height}" width="{width}"><param name="src" value="{path}"><param name="autoplay" value="{autoplay}"><param name="type" value="video/quicktime"><embed src="{path}" height="{height}" width="{width}" autoplay="{autoplay}" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/"></embed></object>';
    $vid_lightbox_frmmarkup	  = '<iframe src ="{path}" width="{width}" height="{height}" frameborder="no"></iframe>';
    $vid_lightbox_inmarkup	  = '<div class="pp_inline clearfix">{content}</div>';
    $output = <<<EOHTML
      <script type="text/javascript" charset="utf-8">
        /* <![CDATA[ */
        jQuery(document).ready(function($) {
          $("a[rel^='{$vid_lightbox_rel}']").prettyPhoto({
            animationSpeed: '{$vid_lightbox_speed}',
            padding: {$vid_lightbox_padding},
            opacity: {$vid_lightbox_opacity},
            showTitle: {$vid_lightbox_title},
            allowresize: {$vid_lightbox_resize},
            counter_separator_label: '{$vid_lightbox_counterlabel}',
            theme: '{$vid_lightbox_theme}',
            wmode: '{$vid_lightbox_wmode}',
            autoplay: {$vid_lightbox_autoplay},
            hideflash: {$vid_lightbox_hideflash},
            modal: {$vid_lightbox_modal},
            changepicturecallback: {$vid_lightbox_picturecallback},
            callback: {$vid_lightbox_callback},
            markup: '{$vid_lightbox_markup}',
            image_markup: '{$vid_lightbox_imarkup}',
            flash_markup: '{$vid_lightbox_fmarkup}',
            quicktime_markup: '{$vid_lightbox_qmarkup}',
            iframe_markup: '{$vid_lightbox_frmmarkup}',
            inline_markup: '{$vid_lightbox_inmarkup}'
          });
        });
				/* ]]> */
      </script>
EOHTML;
    echo $output;
}
  
add_action('init', 'wp_vid_lightbox_init');
add_action('wp_head', 'wp_vid_lightbox_head_content');
add_action('wp_footer', 'wp_vid_footer_content');
?>