<?php
/*
Plugin Name: Another Wordpress Seo Plugin
Plugin URI: http://www.gengtang.net/wordpress-plugin/another-wordpress-seo-plugin.html
Description:Auto add keywords and description in the head meta of home,single,page,category and tag. Help seo for head meta tag.
Author: gengtang
Version: 1.0.2
Author URI: http://www.gengtang.net

    Copyright (c) 2009 gengtang (http://www.gengtang.net)
    License (GPL) http://www.gnu.org/licenses/gpl.txt

*/
add_action('init', 'init_awsp');
function init_awsp(){
  load_plugin_textdomain('awsp',PLUGINDIR. '/' . dirname(plugin_basename(__FILE__)) . '/lang');
}
class gtOptions {

	function getOptions() {
		$options = get_option('gt_options');
		if (!is_array($options)) {
           $options['bname'] = false;

			$options['gt_keywords'] = false;
			$options['gt_keywords_content'] = '';
			 $options['gt_desription'] = false;
			$options['gt_description_content'] = '';
			update_option('gt_options', $options);
		}
		return $options;
	}

	function add() {
		if(isset($_POST['gt_save'])) {
			$options = gtOptions::getOptions();

			 if ($_POST['bname']) {
				$options['bname'] = (bool)true;
			} else {
				$options['bname'] = (bool)false;
			}
			
			 
			if ($_POST['gt_keywords']) {
				$options['gt_keywords'] = (bool)true;
			} else {
				$options['gt_keywords'] = (bool)false;
			}
			$options['gt_keywords_content'] = stripslashes($_POST['gt_keywords_content']);
        
			
		  if ($_POST['gt_description']) {
				$options['gt_description'] = (bool)true;
			} else {
				$options['gt_description'] = (bool)false;
			}
			$options['gt_description_content'] = stripslashes($_POST['gt_description_content']);

			 
			update_option('gt_options', $options);

		} else {
			gtOptions::getOptions();
		}

		add_options_page(__('AWSP-option','awsp'), __('AWSP-option','awsp'),5, basename(__FILE__), array('gtOptions', 'display'));
	}

	function display() {
		$options = gtOptions::getOptions();
?>

<form action="#" method="post" enctype="multipart/form-data" name="gt_form">
	
		<h3><?php _e('NOTE:','awsp');?></h3><p><?php _e('<strong>Home</strong>:<br/>1)If you set keywords and description in option2 and option3,the content will be displayed in Home head meta;<br/>2)If you leave they blank, the blogname and blogtagline(blogdescription) will be keywords and description instead;<br/><br/><strong>Single</strong>:<br/>1)In default, the tags for the single will be keywords, and some of single post content will be description;<br/>2)If add two custom fields for the single post, one key is keywords, another key is description, their content will be keywords and description in single head meta instead;<br/>3)If the post excerpt of the single is not blank, the content will be description in single head meta;<br/>4)If no tags for the single, the post title will be keywords instead;<br/><br/><strong>Page</strong>:<br/>1)If add two custom fields for the page, one key is keywords, another key is description, their content will be keywords and description in page head meta;<br/>2)otherwise, the page title and blog name will be keywords, and some of the page content will be description instead;<br/><br/><strong>Category</strong>:<br/>1)In default, the cat name and cat_description will be keywords and description in category head meta;<br/>2)If cat_description is blank, the cat name and blogtagline(blogdescription) will be description instead;<br/><br/><strong>Tag</strong>:<br/>1)In default, the tag name and tag_description will be keywords and description in tag head meta;<br/>2)If tag_description is blank, the tag name and blogtagline(blogdescription) will be description instead;','awsp'); ?></p>
	<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<strong><?php _e('option 1:','awsp');?></strong>
					</th>
					<td>
											
					<label>
	<input name="bname" type="checkbox" value="checkbox" <?php if($options['bname']) {echo "checked='checked'"; }?> />
		<?php  _e('Insert blog name into keywords in category head, tag head, page head and single head(if no tags for single)','awsp') ; ?>
		</label>
										
					</td>
				</tr>
			</tbody>
		</table>
						<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
					<strong><?php _e('option 2:','awsp');?></strong>
						<?php _e('Home_keywords','awsp'); ?>
						
						<small style="font-weight:normal;"><?php _e('(Separate keywords with comma)','awsp'); ?></small>
					</th>
					<td>
					
						
					<label>
							<input name="gt_keywords" type="checkbox" value="checkbox" <?php if($options['gt_keywords']) echo "checked='checked'"; ?> />
							 <?php  _e('Display keywords in Head meta tag','awsp') ; ?>
						</label>
						<br />
						<label>
							<textarea name="gt_keywords_content" cols="50" rows="10" style="width:98%;font-size:12px;" ><?php echo($options['gt_keywords_content']); ?></textarea>
						</label>
					
					</td>
				</tr>
			</tbody>
		</table>
<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
					<strong><?php _e('option 3:','awsp');?></strong>
						<?php _e('Home_description','awsp'); ?>
					</th>
					<td>
					
						<label>
							<input name="gt_description" type="checkbox" value="checkbox" <?php if($options['gt_description']) echo "checked='checked'"; ?> />
							 <?php  _e('Display description in Head meta tag','awsp') ; ?>
						</label>
						<br />
						<label>
							<textarea name="gt_description_content" cols="50" rows="10" style="width:98%;font-size:12px;"><?php echo($options['gt_description_content']); ?></textarea>
						</label>
					
					</td>
				</tr>
			</tbody>
		</table>
 
		<p class="submit">
			<input class="button-primary" type="submit" name="gt_save" value="<?php _e('Save Changes','awsp'); ?>" />
		</p>
	
</form>

 <?php
	

}}
add_action('admin_menu', array('gtOptions','add'));
?>
 <?php

function gt_keyw_descr() {
    $options = get_option('gt_options');
    $zhpost_desc_length  = 4; 
     $enpost_desc_length  = 60;
    $custom_desc_key= 'description';
	$custom_keyw_key= 'keywords'; 

    global $cat, $cache_categories, $wp_query, $key,$boname;
	$boname=get_option('blogname');
	foreach((get_the_category()) as $category) { 
    $key = $category->cat_name;}
	
    if(is_single() || is_page()) {
        $post = $wp_query->post;
        $post_custom = get_post_custom($post->ID);
        $custom_desc_value = $post_custom["$custom_desc_key"][0];
        $custom_keyw_value = $post_custom["$custom_keyw_key"][0];
        if($custom_desc_value) {
            $descr = trim(strip_tags($custom_desc_value));
        } elseif(!empty($post->post_excerpt)) {
            $descr = $post->post_excerpt;
        } else {
            $descr = $post->post_content;
        }
		$descr = str_replace(array("\r","\n"), " ", $descr);
        $descr = trim(strip_tags($descr));
         
		$descr= explode(' ', $descr);
		$count=count($descr);
		
        if($count > $enpost_desc_length) {
            $l = $enpost_desc_length;
            $ellipsis = 'бнбн';
        } elseif ($count > $zhpost_desc_length){
		 $l = $zhpost_desc_length;
		 $ellipsis = 'бнбн';
           } else {
            $l = $count;
            $ellipsis = '';
        }
        $description = '';
        for ($i=0; $i<$l; $i++)
            $description .= $descr[$i] . ' ';
           $description .=$ellipsis;
		
		if(is_single()){
		$keywords='';
		$tags = wp_get_post_tags($post->ID);
	    foreach ($tags as $tag ) {
		$keywor[]=$tag->name;}if ($keywor==''){$keywords=$post->post_title;}else{
		$keywords=implode(",",$keywor);}
        } 
		elseif($custom_keyw_value) {
            $keywords = trim(strip_tags($custom_keyw_value));
        }
		else {
		if ($options['bname']){
			$keywords=$post->post_title.','.strip_tags($boname);}else{
			$keywords=$post->post_title;
        }}
    } elseif(is_category()) {
	    if ($options['bname']){
		$keywords= $key.','.strip_tags($boname);}else{$keywords= $key;}
        $category = $wp_query->get_queried_object();
        $description = trim(strip_tags($category->category_description));if($description==""){$description=$key .",". trim(strip_tags(get_option('blogdescription')));}
    } elseif(is_tag()) {
	    $tag= $wp_query->get_queried_object();
		if ($options['bname']){
		$keywords= $tag->name.','.strip_tags($boname);}else{$keywords= $tag->name;}
        $tag = $wp_query->get_queried_object();
        $description = trim(strip_tags($tag->tag_description));if($description==""){$description=$tag->name .",". trim(strip_tags(get_option('blogdescription')));}
    } 
	else {
		if ($options['gt_keywords'] && $options['gt_keywords_content']){
			$keywords=($options['gt_keywords_content']);}else{$keywords=get_option('blogname');}
		if ($options['gt_description'] && $options['gt_description_content']){
			$description=($options['gt_description_content']);}else{$description=trim(strip_tags(get_option('blogdescription')));}
       
    }
   if($keywords){echo"<meta name=\"keywords\" content=\"$keywords\" />\n";}
    if($description) {
        echo "<meta name=\"description\" content=\"$description\" />\n";
    }
}



add_action('wp_head', 'gt_keyw_descr');

?>