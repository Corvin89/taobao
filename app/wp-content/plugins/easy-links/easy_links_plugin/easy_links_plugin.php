<?php
/*
Plugin Name:	Easy Link
Version:		1.1
Plugin URI: 	http://www.vintuna.com/easy-link
Description:	Easy links manager helps you manage your links and check its reciprocal through the admin backend.
Author:			Vintuna
Author URI:		http://www.vintuna.com
License: Copyright (c) 2010 Vintuna. All rights reserved.
*/   
   
/*  Copyright 2010 

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
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
* Guess the wp-content and plugin urls/paths
*/
// Pre-2.6 compatibility

if (!function_exists('add_action'))
{
	require_once("../../../wp-config.php");
}


if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
if ( ! defined( 'WP_PLUGIN_NAME' ) )
      define( 'WP_PLUGIN_NAME', 'easy_links_plugin' );


include(WP_PLUGIN_DIR.'/'.WP_PLUGIN_NAME.'/class/'.'class.pagination.php');
include(WP_PLUGIN_DIR.'/'.WP_PLUGIN_NAME.'/class/'.'class.paginationnonseo.php');
include(WP_PLUGIN_DIR.'/'.WP_PLUGIN_NAME.'/class/'.'class.easy_links_template.php');
include(WP_PLUGIN_DIR.'/'.WP_PLUGIN_NAME.'/class/'.'class.easy_links_reciprocal.php');


if (!class_exists('easy_links_plugin')) {

    class easy_links_plugin extends easy_links_template {
        //This is where the class variables go, don't forget to use @var to tell what they're for
        /**
        * @var string The options string name for this plugin
        */
        var $optionsName = 'links_plugin_options';
        var $limit = 2;
        /**
        * @var string $localizationDomain Domain used for localization
        */
        var $localizationDomain = "links_plugin";
        
        /**
        * @var string $pluginurl The path to this plugin
        */ 
        var $thispluginurl = '';
        /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $thispluginpath = '';
		
		  /**
        * @var string $pluginurlpath The path to this plugin
        */
        var $tablename = 'easy_links_management';
            
        /**
        * @var array $options Stores the options for this plugin
        */
        var $options = array();
        
		/*
		* variable for pagination
		*/
		var $objPaginate;
		var $objNonSeoPaginate;
		
		/*
		* object for reciprocal links
		*/
		var $objReciprocal;
		/*
		* variable for home links
		*/
		var $home_link = 'http://vujudesign.com';
		
        //Class Functions
        /**
        * PHP 4 Compatible Constructor
        */
        function links_plugin(){
		 
			$this->__construct();
			
		}
        
        /**
        * PHP 5 Constructor
        */        
        function __construct(){
            //"Constants" setup
            $this->thispluginurl = PLUGIN_URL . '/' . dirname(plugin_basename(__FILE__)).'/';
            $this->thispluginpath = PLUGIN_PATH . '/' . dirname(plugin_basename(__FILE__)).'/';
            
            //Initialize the options
            //This is REQUIRED to initialize the options when the plugin is loaded!
            
            //Actions        
            add_action("admin_menu", array(&$this,"admin_menu_link"));

            
            //Widget Registration Actions
            add_action('plugins_loaded', array(&$this,'register_widgets'));
			
			//Shortcode API use
			
			add_shortcode('all_links', array(&$this,'get_all_links'));
			add_shortcode('search_form', array(&$this,'get_search_result'));
            
			
            //add_action("wp_head", array(&$this,"_add_js"));
			add_action("admin_head", array(&$this,"_add_js"));
			add_action("admin_footer",array(&$this,"_add_js_admin_footer"));
			
			add_action("wp_head", array(&$this,"_add_pagination_header"));
			/*
            add_action('wp_print_scripts', array(&$this, 'add_js'));
            */
			
			
			
			
			//setup pagination
			$this->_set_no_of_pages();
			$this->objPaginate = new npagination();
			$this->objNonSeoPaginate = new npaginationnonseo();
			$this->objReciprocal = new easy_links_reciprocal();
			
			if(isset($_GET['checkid']) && isset($_GET['whereurl']) && isset($_GET['link']))
			{
									
									$wherelink = urldecode($_GET['whereurl']);
									
									$wherelink = filter_var($wherelink,FILTER_SANITIZE_URL);
									
									$link = urldecode($_GET['link']);
									
									$link = filter_var($link,FILTER_SANITIZE_URL);
									
									$this->check_url($wherelink,$link);
			}
			
			if(isset($_GET['email']) && isset($_GET['whereurl']) && isset($_GET['url']) && isset($_GET['name']))
			{
									$email = $_GET['email'];
									$email = filter_var($email,FILTER_VALIDATE_EMAIL);
									
									$wherelink = urldecode($_GET['whereurl']);
									$wherelink = filter_var($wherelink,FILTER_SANITIZE_URL);
									
									$url = urldecode($_GET['url']);
									$url = filter_var($url,FILTER_SANITIZE_URL);
									
									$name = filter_var($_GET['name'],FILTER_SANITIZE_STRING);
									
									$this->send_mail($email,$wherelink,$url,$name);
			}
			
        }
		
		
		
		/****************************************************************************************************************************/
		/******************************************** Front End Functions ***************************************************************/
		function save_frontend_data(){	
		
						global $wpdb;
						
						$bool[] = filter_var($_POST['url'], FILTER_VALIDATE_URL);
						
						if($_POST['email']!='')
						{
							$bool[] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
						}
						
					
						if($_POST['whereurl']!='')
						{
							$bool[] = filter_var($_POST['whereurl'], FILTER_VALIDATE_URL);
						}
						
						if($_POST['checkurl']!='')
						{
							$bool[] = filter_var($_POST['checkurl'], FILTER_VALIDATE_URL);
						}

						
						if($_POST['title'] == '')
						{
							$_POST['title'] = $_POST['url'];
						}
						
						if(!in_array(false,$bool))
						{
							$sql = "INSERT INTO  ".$wpdb->prefix.$this->tablename." (
											url,
											name,
											title ,
											whereurl ,
											checkurl,
											email ,
											description,
											status,
											follow
										)
										VALUES (
												'".$this->sanitize_data($_POST['url'])."',  
												'".$this->sanitize_data($_POST['name'])."',  
												'".$this->sanitize_data($_POST['title'])."',  
												'".$this->sanitize_data($_POST['whereurl'])."',
												'".$this->sanitize_data($_POST['checkurl'])."',  
												'".$this->sanitize_data($_POST['email'])."',  
												'".htmlspecialchars($_POST['description'], ENT_QUOTES )."',  
												'0', 
												'".$this->sanitize_data($_POST['follow'])."'
										);";
				
								return $wpdb->query($sql);
								
								
						  }
						  else
						  {	
							return false;
						  }
		}
		/*********************************************     Front End END            *******************************************************/
		/***************************************************************************************************************************/
		
		
		
		/****************************************************************************************************************************/
		/******************************************** Javascript    ***************************************************************/
		
		function _add_pagination_header(){
			
			?>
          	
            <script src="<?php bloginfo('wpurl') ?>/wp-content/plugins/<?=WP_PLUGIN_NAME?>/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="<?php bloginfo('wpurl') ?>/wp-content/plugins/<?=WP_PLUGIN_NAME?>/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
		
            <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
			<style type="text/css">
                .pagination { font-size: 80%; }
                .pagination a { text-decoration: none; border: solid 1px #AAE; color: #15B; }
                .pagination a, .pagination span { display: block; float: left; padding: 0.3em 0.5em; margin-right: 5px; margin-bottom: 5px; }
                .pagination .current { background: #26B; color: #fff; border: solid 1px #AAE; }
                .pagination .current.prev, .pagination .current.next{ color:#999; border-color:#999; background:#fff; }
            </style>
            <script> 
				
				 var i = 0;
				 
				var pagination_options = {
				  num_edge_entries: 10,
				  num_display_entries: 1000,
				  callback: pageselectCallback,
				  items_per_page:<?=get_option('links_no_of_pages_frontend')?>
				}
				
				function pageselectCallback(page_index, jq){
  				 
				  var items_per_page = pagination_options.items_per_page;
				  var offset = page_index * items_per_page;
				 
				  var new_content = $('#hiddenresult div.result').slice(offset, offset + items_per_page).clone();
				  
				  var data_length = $('#hiddenresult div.result').length;
				  
				  if(data_length  <= items_per_page )
				  {
				  	$('#Pagination').hide();
				  }
				  
				  $('#Searchresult').empty().append(new_content);
				  
				  return false;
				}
				function initPagination() {
				  var num_entries = $('#hiddenresult div.result').length;
				  // Create pagination element
				  $("#Pagination").pagination(num_entries, pagination_options);
				}
				$(document).ready(function(){      
				  initPagination();
				});
			</script>
			<script>/**
             * This jQuery plugin displays pagination links inside the selected elements.
             *
             * @author Gabriel Birke (birke *at* d-scribe *dot* de)
             * @version 1.2
             * @param {int} maxentries Number of entries to paginate
             * @param {Object} opts Several options (see README for documentation)
             * @return {Object} jQuery Object
             */
            jQuery.fn.pagination = function(maxentries, opts){
                opts = jQuery.extend({
                    items_per_page:10,
                    num_display_entries:10,
                    current_page:0,
                    num_edge_entries:0,
                    link_to:"#",
                    prev_text:"Prev",
                    next_text:"Next",
                    ellipse_text:"...",
                    prev_show_always:true,
                    next_show_always:true,
                    callback:function(){return false;}
                },opts||{});
                
                return this.each(function() {
                    /**
                     * Calculate the maximum number of pages
                     */
                    function numPages() {
                        return Math.ceil(maxentries/opts.items_per_page);
                    }
                    
                    /**
                     * Calculate start and end point of pagination links depending on 
                     * current_page and num_display_entries.
                     * @return {Array}
                     */
                    function getInterval()  {
                        var ne_half = Math.ceil(opts.num_display_entries/2);
                        var np = numPages();
                        var upper_limit = np-opts.num_display_entries;
                        var start = current_page>ne_half?Math.max(Math.min(current_page-ne_half, upper_limit), 0):0;
                        var end = current_page>ne_half?Math.min(current_page+ne_half, np):Math.min(opts.num_display_entries, np);
                        return [start,end];
                    }
                    
                    /**
                     * This is the event handling function for the pagination links. 
                     * @param {int} page_id The new page number
                     */
                    function pageSelected(page_id, evt){
                        current_page = page_id;
                        drawLinks();
                        var continuePropagation = opts.callback(page_id, panel);
                        if (!continuePropagation) {
                            if (evt.stopPropagation) {
                                evt.stopPropagation();
                            }
                            else {
                                evt.cancelBubble = true;
                            }
                        }
                        return continuePropagation;
                    }
                    
                    /**
                     * This function inserts the pagination links into the container element
                     */
                    function drawLinks() {
                        panel.empty();
                        var interval = getInterval();
                        var np = numPages();
                        // This helper function returns a handler function that calls pageSelected with the right page_id
                        var getClickHandler = function(page_id) {
                            return function(evt){ return pageSelected(page_id,evt); }
                        }
                        // Helper function for generating a single link (or a span tag if it's the current page)
                        var appendItem = function(page_id, appendopts){
                            page_id = page_id<0?0:(page_id<np?page_id:np-1); // Normalize page id to sane value
                            appendopts = jQuery.extend({text:page_id+1, classes:""}, appendopts||{});
                            if(page_id == current_page){
                                var lnk = jQuery("<span class='current'>"+(appendopts.text)+"</span>");
                            }
                            else
                            {
                                var lnk = jQuery("<a>"+(appendopts.text)+"</a>")
                                    .bind("click", getClickHandler(page_id))
                                    .attr('href', opts.link_to.replace(/__id__/,page_id));
                                    
                                    
                            }
                            if(appendopts.classes){lnk.addClass(appendopts.classes);}
                            panel.append(lnk);
                        }
                        // Generate "Previous"-Link
                        if(opts.prev_text && (current_page > 0 || opts.prev_show_always)){
                            appendItem(current_page-1,{text:opts.prev_text, classes:"prev"});
                        }
                        // Generate starting points
                        if (interval[0] > 0 && opts.num_edge_entries > 0)
                        {
                            var end = Math.min(opts.num_edge_entries, interval[0]);
                            for(var i=0; i<end; i++) {
                                appendItem(i);
                            }
                            if(opts.num_edge_entries < interval[0] && opts.ellipse_text)
                            {
                                jQuery("<span>"+opts.ellipse_text+"</span>").appendTo(panel);
                            }
                        }
                        // Generate interval links
                        for(var i=interval[0]; i<interval[1]; i++) {
                            appendItem(i);
                        }
                        // Generate ending points
                        if (interval[1] < np && opts.num_edge_entries > 0)
                        {
                            if(np-opts.num_edge_entries > interval[1]&& opts.ellipse_text)
                            {
                                jQuery("<span>"+opts.ellipse_text+"</span>").appendTo(panel);
                            }
                            var begin = Math.max(np-opts.num_edge_entries, interval[1]);
                            for(var i=begin; i<np; i++) {
                                appendItem(i);
                            }
                            
                        }
                        // Generate "Next"-Link
                        if(opts.next_text && (current_page < np-1 || opts.next_show_always)){
                            appendItem(current_page+1,{text:opts.next_text, classes:"next"});
                        }
                    }
                    
                    // Extract current_page from options
                    var current_page = opts.current_page;
                    // Create a sane value for maxentries and items_per_page
                    maxentries = (!maxentries || maxentries < 0)?1:maxentries;
                    opts.items_per_page = (!opts.items_per_page || opts.items_per_page < 0)?1:opts.items_per_page;
                    // Store DOM element for easy access from all inner functions
                    var panel = jQuery(this);
                    // Attach control functions to the DOM element 
                    this.selectPage = function(page_id){ pageSelected(page_id);}
                    this.prevPage = function(){ 
                        if (current_page > 0) {
                            pageSelected(current_page - 1);
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                    this.nextPage = function(){ 
                        if(current_page < numPages()-1) {
                            pageSelected(current_page+1);
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                    // When all initialisation is done, draw the links
                    drawLinks();
                    // call callback function
                    opts.callback(current_page, this);
                });
            }
             
             
            </script>
            
            <?
		}
		
	
		
		function _add_js(){
			//wp_enqueue_script('jquery');
			?>
            <script src="<?php bloginfo('wpurl') ?>/wp-content/plugins/<?=WP_PLUGIN_NAME?>/SpryAssets/SpryValidationTextField.js" type="text/javascript"></script>
<link href="<?php bloginfo('wpurl') ?>/wp-content/plugins/<?=WP_PLUGIN_NAME?>/SpryAssets/SpryValidationTextField.css" rel="stylesheet" type="text/css" />
            
<script type="text/javascript">
			var jvar = jQuery.noConflict();
			
			/* UTILITy Functions */
				function urldecode (str) {
   
    				return decodeURIComponent(str.replace(/\+/g, '%20'));
				}
				
				function urlencode(str) {
   

					str = (str+'').toString();
					
					return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
					
				}
			/* UTILITy Functions END */
				
				function toggle_all(bool){
					
					if(bool)
					{
						jvar("input[type=checkbox]").attr('checked',true); 
				
					}
					else
					{
						jvar("input[type=checkbox]").attr('checked',false); 
					}
					
				}
				
				function confirm_box(str){
							
						if(confirm(str)){
							return true;
						}
						else
						{
							return false;
						}
				}
				
				var id ='';
				
				

				function check_url(id,whereurl,url){
					
					var plugin_name = '<?=WP_PLUGIN_NAME?>';
					var plugin_file = '<?=WP_PLUGIN_NAME?>.php';
					var sendurl = "<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/"+plugin_file;
					var senddata = "link="+urlencode(url)+"&whereurl="+urlencode(whereurl)+"&checkid="+id;
					
					jvar('#check'+id).html("<img src='<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/images/ajax-loader.gif' />");
					
					var mero = jvar.ajax({
							type: "GET",
							url: sendurl,
							cache : true,
							async : true,
							success:function(data){
								
										if(data == 1)
										{
										
											jvar('#check'+id).html("<img src='<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/images/right.png' />").fadeIn('slow');
										}
										else
										{
											jvar('#check'+id).html("<img src='<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/images/wrong.png' />").fadeIn('slow');
										}
							},
							data: senddata,
							dataType : "text"
					});
					
					
					
				}
				
				function send_mail(email,whereurl,url,id,name){
			
					var plugin_name = '<?=WP_PLUGIN_NAME?>';
					var plugin_file = '<?=WP_PLUGIN_NAME?>.php';
					var sendurl = "<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/"+plugin_file;
					var senddata = "name="+name+"&email="+email+"&whereurl="+urlencode(whereurl)+"&url="+urlencode(url);
					
					jvar('#checkmail'+id).html("<img src='<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/images/ajax-loader.gif' />");
					
					var mero = jvar.ajax({
							type: "GET",
							url: sendurl,
							cache : true,
							async : true,
							success:function(data){
										
										if(data == 1)
										{
											jvar('#checkmail'+id).html("<img src='<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/images/right.png' />").fadeIn('slow');
										}
										else
										{
											jvar('#checkmail'+id).html("<img src='<?php bloginfo('wpurl') ?>/wp-content/plugins/"+plugin_name+"/images/wrong.png' />").fadeIn('slow');
										}
							},
							data: senddata,
							dataType : "text"
					});
					
				
					
				}
			</script>
            <?
			
		}
		
		function _add_js_admin_footer(){
		?>
        	<script type="text/javascript">
			<!--
			var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "url", {validateOn:["blur", "change"]});
			var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email", {validateOn:["blur", "change"], isRequired:false});
			var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "url", {validateOn:["blur", "change"], isRequired:false});
			var sprytextfield4 = new Spry.Widget.ValidationTextField("sprytextfield4", "url", {validateOn:["blur", "change"]});
			var sprytextfield5 = new Spry.Widget.ValidationTextField("sprytextfield5", "url", {validateOn:["blur", "change"], isRequired:false});
			var sprytextfield6 = new Spry.Widget.ValidationTextField("sprytextfield6", "email", {validateOn:["blur", "change"], isRequired:false});
			
			var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7", "url", {validateOn:["blur", "change"], isRequired:false});
			var sprytextfield8 = new Spry.Widget.ValidationTextField("sprytextfield8", "url", {validateOn:["blur", "change"], isRequired:false});
			
			
			//-->
             </script>
<?
		}
		
		/********************************************* Javascript END **** *******************************************************/
		/***************************************************************************************************************************/
		
		
		
		
		function _set_no_of_pages(){
			
			$this->limit = get_option('links_no_of_pages');
			return $this->limit;
		}
	
	
        /**
		* Drops and Creates a table
		* @returns nothing
		*/
		
		
		
        	
		function get_post_by_title(){ 
				global $wpdb;
				global $wp_query;
				
				if( isset( $_GET['detail_title'] ))
				{
					
					$detail_title = $_GET['detail_title'];
					$detail_title = esc_html($detail_title);
					$detail_title = sanitize_title($detail_title);
					$detail_title = str_replace('-',' ',$detail_title);
					
					$sql = "SELECT * FROM ".$wpdb->prefix.$this->tablename."
							WHERE name = '{$detail_title}'";
							
					$rows = $wpdb->get_results($sql);
					
						$results_row_html .=  "<div class=\"post\">";
						$results_row_html .=  "<p>";
						$results_row_html .=  "<div class=\"post-title\"><h2>"."<a href='".get_bloginfo('url')."/details/".sanitize_title( $rows[0]->name )."'>".$rows[0]->name."</a></h2></div><br>";
						$results_row_html .= "Address: <p>".htmlspecialchars_decode($rows[0]->address)."</p><br>";
  						$results_row_html .= "Email: ".$rows[0]->email."<br>";
						$results_row_html .= "URL: <a href='".$rows[0]->url."' target='_blank'>".$rows[0]->url."</a><br>";
						$results_row_html .= "Description: <p>".htmlspecialchars_decode($rows[0]->description)."</p><br>";
						$results_row_html .= "Map: <p>".htmlspecialchars_decode($rows[0]->map)."</p><br>";
						$results_row_html .= "</p><br>";
			
						$results_row_html .=  "</div>";
						
						echo $results_row_html;
				}
			}
			
		function detail_title(){
				global $wpdb;
				
				if(isset($_GET['detail_id']))
				{
					
					
					$detail_id = absint($_GET['detail_id']);
					$sql = "SELECT name FROM ".$wpdb->prefix.$this->tablename."
							WHERE id = {$detail_id}";
					$rows = $wpdb->get_results($sql);
			
					$str .= "<a href='#'>".$rows[0]->name."</a><br>";
			
			
			
					echo $str;
				}
			}
			
		function get_all_links($atts, $content=NULL, $code=""){
			
						global $wpdb;
						global $wp_query;
						
						
						
						if(isset($_GET['detail_title']))
						{
							$this->get_post_by_title();
						}
						else
						{
							
							$sql = "SELECT * FROM ".$wpdb->prefix.$this->tablename." WHERE status = 1";
							
							$listing_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix.$this->tablename.";"));
							
							
							$this->objPaginate->items($listing_count);
							$this->objPaginate->limit($this->limit);
							$this->objPaginate->target(get_bloginfo('url')."/all-links");
							$this->objPaginate->currentPage(get_bloginfo('url')."/all-links");
							
							$rows = $wpdb->get_results($sql);
							$str = "";
							/* All this for paginating with jquery */
							$str .= "<div id=\"Pagination\" class=\"pagination\"></div>";
							
						  	$str .= "<br style=\"clear:both;\" />";
						    $str .= "<div id=\"Searchresult\"></div> ";
							 
							$str .= "<div id=\"hiddenresult\" style=\"display:none;\">";
						 
							
						  
						 foreach($rows as $row):
							
									if($row->follow):
										$follow= 'follow';
									else:
										$follow = 'nofollow';
									endif;
										
									$str .= "<div class=\"result\"> ";
									

                                    $str .= "<p><a href='$row->url' title='$row->title' target='_blank' rel='$follow'><strong>$row->title</strong></a><br>";
								 	$str .= "$row->description</p>"; 
								 
                                    $str .= "<div style='border: #CCCCCC 1px dashed; width:100%;'></div>";                                    $str .= "</p>";
									
									$str .=" </div> ";
                            
						
							endforeach;
							
							$str .= "</div>";
							
							$str .='<script type="text/javascript">
							<!--
							var sprytextfield1 = new Spry.Widget.ValidationTextField("sprytextfield1", "url", {validateOn:["blur", "change"]});
							var sprytextfield2 = new Spry.Widget.ValidationTextField("sprytextfield2", "email", {validateOn:["blur", "change"], isRequired:false});
							var sprytextfield3 = new Spry.Widget.ValidationTextField("sprytextfield3", "url", {validateOn:["blur", "change"], isRequired:false});
							
							
							var sprytextfield7 = new Spry.Widget.ValidationTextField("sprytextfield7", "url", {validateOn:["blur", "change"], isRequired:false});
							
							
							
							//-->
                     </script>';
							/* All this for paginating with jquery */
							
							echo  $str;
							//$this->objPaginate->show();
						}
				}
				
		
		function check_url($wherelink = '',$link = ''){
			
			echo $this->objReciprocal->parse_page($wherelink,$link);
		}
		
		function send_mail($email,$wherelink,$url,$name){
		
			 $headers = 'Content-Type: text/html; charset='.get_settings('blog_charset').'\r\n';
			 $headers .= 'From: '.get_settings('blogname').' <'.get_settings('admin_email').'>' . "\r\n\\";
			 
			 $to = $email;
			 $subject = get_option('links_email_subject');
			 $message = stripslashes(htmlspecialchars_decode(get_option('links_email_template')));
			 
			 $search = array('[name]','[check_url]','[where_url]','[linkpartner_name]','[mysite_name]');
			 
			 $replace = array($name, $url, $wherelink,$name,get_settings('blogname'));
			 
			 $message = str_replace($search,$replace,$message);
			 
 			 echo wp_mail($to, $subject, $message, $headers, $attachments);
			 
		}
	
		/* Get Rows by id */
		function get_table_data_by_id($pageid){
			global $wpdb;
			
			$sql = "SELECT * FROM " . $wpdb->prefix.$this->tablename." 
					WHERE 
					id =$pageid
					";
						
			return $wpdb->get_results($sql);
		}
	
		
        
        /*
        * ============================
        * Plugin Widgets
        * ============================
        */                        
        function register_widgets() {
           /*create table*/
		   $this->create_table();
		   register_sidebar_widget("Easy Links",array(&$this,"widget_easy"));
		   /* Register the event to load init function when the plugin is loaded */
			add_action("plugins_loaded", "init");
			//register_sidebar_widget("Links Search",array(&$this,"links_search"));
	      
        } 
		
		function widget_easy($args){
			 extract ($args); // This is the argument passed by register_sidebar function 
			 
			/* Widget entire function goes here.  */
			
			echo $before_widget;
			
			echo $before_title." ".$widget_name. " ".$after_title;
			  
			  get_all_posts();
			
			echo $after_widget;
			
			/* EOF Widget entire function goes here.  */
		}
		
		function easy_plugin_deactivate(){
			//for frontend forms
			delete_option('links_display_front');
			delete_option('links_display_all');
			
			//for pagination
			delete_option('links_no_of_pages');
			delete_option('links_no_of_pages_frontend');
			
			//for email
			delete_option('links_email_subject');
			delete_option('links_email_template');
			
		}
        
		
  } //End Class
} //End if class exists statement

//instantiate the class
if (class_exists('easy_links_plugin')) {
    $links_plugin_var = new easy_links_plugin();
	//deactivate
	register_deactivation_hook( __FILE__, array(&$links_plugin_var,'easy_plugin_deactivate'));
}