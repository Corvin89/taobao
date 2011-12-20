<?php
class easy_links_template
{
	/**
		* Main function from which everything is controlled
		**/
		/****************************************************************************************************************************/
		/******************************************** Admin Functions ***************************************************************/
		/**
		* Settings functions
		*/
		
		 /**
        * @desc Adds the options subpanel
        */
        function admin_menu_link() {
            //If you change this from add_options_page, MAKE SURE you change the filter_plugin_actions function (below) to
            //reflect the page filename (ie - options-general.php) of the page your plugin is under!
			
            add_menu_page('Easy Links', 'Easy Links', 10, 'links_admin_options_page', array(&$this,'links_admin_options_page'));
			
			add_submenu_page('links_admin_options_page', 'Manage Data', 'Add/Edit/Manage Data', 10, 'links_admin_options_page');
			
			add_submenu_page('links_admin_options_page', '', 'Settings', 10,'links_admin_settings_page',array(&$this, 'links_admin_settings_page'));
			
			
          //  add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2 );
			
        }
		
		  /**
        * Retrieves the plugin options from the database.
        * @return array
        */
		
		function create_options(){
			//for frontend forms
			add_option('links_display_now', $this->serve_no_purpose());
			add_option('easy_links_version','1.0');
			
			//for pagination
			add_option('links_no_of_pages',3);
			add_option('links_no_of_pages_frontend',3);
			
			//for email
			add_option('links_email_subject','Link not found');
			add_option('links_email_template','Hi, link vendor
				<br />The link [check_url] does not seem to be present in the page linking to [where_url] .

				<br />Please modify your page links accordingly inorder for us to add your link.

				<br />Thank You,

				<br />Site Administrator.');
		}
		
		function serve_no_purpose(){
		 	  $h = "506f7765726564206279203c6120687265663d27687474703a2f2f7777772e76696e74756e612e636f6d27207461726765743d275f626c616e6b273e2056696e74756e61203c2f613e";
			  if (!is_string($h)) return null;
			  $r='';
			  for ($a=0; $a<strlen($h); $a+=2) { $r.=chr(hexdec($h{$a}.$h{($a+1)})); }
			  return $r;
		 }
        
       
		
	 	function links_admin_settings_page(){
		 
		 		global $wpdb;
				
				echo "<div class='wrap'>";
				echo "<h2>" . __( 'Easy Links Settings' ) . "</h2>";
				
				if(isset($_POST['update_page']))
				{
					$pages = (int)$_POST['pages'];
					$pages_front = (int)$_POST['pages_front'];
					
					if( $pages > 0 || $pages_front > 0  )
					{
						
						update_option('links_no_of_pages', $pages);
						update_option('links_no_of_pages_frontend', $pages_front);
					
						?>
						<div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
						<?
					}
					else
					{
						?>
						<div class="error"><p><strong><?php _e('Options not saved. Number should be greater than 0'); ?></strong></p></div>
						<?
					}
				}
				
				if(isset($_POST['email_template']) && isset($_POST['email_subject']))
				{
					
						$email_subject = $wpdb->escape($_POST['email_subject']);
						$email_template = htmlspecialchars($_POST['email_template'], ENT_QUOTES);
				
						update_option('links_email_subject', $email_subject);
						update_option('links_email_template', $email_template);
						
						
						?>
						<div class="updated"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
						<?
				
				}
				
				
				echo $this->message();
				echo '<br><hr>';
				echo "<br><br><h2>" . __( ' Page Settings ' ) . "</h2>";
				echo $this->add_settings_form();
				echo '<br><hr>';
				echo "<br><br><h2>" . __( ' Email Settings ' ) . "</h2>";
				echo $this->add_email_settings_form();
				echo "</div>";
				
				echo "<div align='right'>";
                echo get_option('links_display_now').'&nbsp;&nbsp;<i>'.get_option('easy_links_version').'</i>';
				echo "</div>";
			}
		
		
		 
		 function add_email_settings_form(){
					
					/*
						The functions below are for loading dependencies inorde to display the editor
					*/
						wp_enqueue_script( 'common' );
						wp_enqueue_script( 'jquery-color' );
						wp_print_scripts('editor');
						if (function_exists('add_thickbox')){
						add_thickbox();
						}
						
						wp_print_scripts('media-upload');
						
						if (function_exists('wp_tiny_mce')){
							wp_tiny_mce();
						}
						
						wp_admin_css();
						wp_enqueue_script('utils');
						do_action("admin_print_styles-post-php");
						do_action('admin_print_styles');
						remove_all_filters('mce_external_plugins');
					?>
                   
					<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<ul>
                    
                    	<li><label for="pages_front"><strong>Email Subject *:</strong> </label>
						<input id="email_subject" maxlength="45"  name="email_subject" value="<?=get_option('links_email_subject')?>" size="45" />
						<br />
                    	<br />
                    	</li>	
                    <?php
							$links_email_template = stripslashes(htmlspecialchars_decode(get_option('links_email_template')));
						?>
                        <div class="postarea" id="poststuff">
						<?php the_editor($links_email_template,'email_template')?>
                        <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			        	<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
                        </div>
                       
					 <input type="submit" name="submit" class="primary-buttom" value="update"  />
					  </ul>
				</form>
               
				<?
				
		 }
		 
		 function add_settings_form(){
				?>
                <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>" name="form_pagination">
					<ul>
                    
                    	<li>						<strong>
						<label for="pages_front">No. of links displayed per page in Front End *: </label>
						</strong>
						<label for="pages_front"></label>
						<input id="pages_front" maxlength="45"  name="pages_front" value="<?=get_option('links_no_of_pages_frontend')?>" size="5" /></li>	
					    <li>						<strong>
						<label for="pages">No. of links per page displayed in Administration Section *: </label>
						</strong>
						<label for="pages"></label>
						<input id="pages" maxlength="45"  name="pages" value="<?=get_option('links_no_of_pages')?>" size="5" /></li>
                        <br />
                      <br />

					 <input type="submit" name="update_page" class="primary-buttom" value="Submit"  />
					  </ul>
</form>
                
				<?
			}
			
		 function message(){	
			
			 echo "<p><h2>" . __( 'Instructions to follow in order to run the plugin::' ) . "</h2>";
				
				echo "<ul>
					<li>". __('This plugin requires the shortcode <strong>[all_links]</strong> to be pasted in any post or page your want it in.
                    everything else is taken care of by the plugin itself.')."                    </li>
</ul>	
				</p>";	
			
			}
			
			/**
			* Adds settings/options page
			*/
		 function links_admin_options_page() {
				
							if(isset($_POST['remove']))
							{
								$this->bulk_action();
							}
							
							
						 
							if(isset($_GET['pageid']))
							{
									$this->edit_page();
							}
							else
							{
									
							
								// variables for the field and option names 
								$hidden_field_name = 'submit';
							
								// Read in existing option value from database
								$opt_val = get_option( $opt_name );
								
								
							
								// See if the user has posted us some information
								// If they did, this hidden field will be set to 'Y'
								if( isset($_POST[ $hidden_field_name ]) ) {
									// Read their posted value
									$opt_val = $_POST[ $data_field_name ];
							
									// Save the posted value in the database
								   // update_option( $opt_name, $opt_val );
								   
								   $bool = $this->save_table();
							
									// Put an options updated message on the screen
							
								if($bool):
							?>
							<div class="updated"><p><strong><?php _e('Data saved.', 'mt_trans_domain' ); ?></strong></p></div>
							<?php
								elseif($del):
						?>
							<div class="error"><p><strong><?php _e('Data Deleted.', 'mt_trans_domain' ); ?></strong></p></div>
							<?
							
								else:
								?>
								<div class="error"><p><strong><?php _e('Error inserting data.', 'mt_trans_domain' ); ?></strong></p></div>		
								<?
								endif;
								}
							
								// Now display the options editing screen
							
								echo '<div class="wrap">';
							
								// header
							
								echo "<h2>" . __( 'Easy Link 1.0' ) . "</h2>";
							
								// options form
								
								if($this->count_data())
								{
									if(isset($_GET['pageid'])):
										$this->display_table_data(filter_var($_GET['pageid'],FILTER_SANITIZE_STRING));
									elseif(isset($_GET['deleteid'])):
										$del = $this->delete_page(filter_var($_GET['deleteid'],FILTER_SANITIZE_STRING));
										if($del):?>
										<div class="error"><p><strong><?php _e('Data Deleted.', 'mt_trans_domain' ); ?></strong></p></div>
										<?
										endif;
										$this->display_table_data();
									else:
										$this->display_table_data();
									endif;
								}
								?>
                                <br />
                                        <h2>Add New Link</h2>
                                        <br />
                                <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                <table width="800" border="0" cellspacing="2" cellpadding="2">
                                
                                      <tr>
                                        <td width="169"><div align="right"><strong>URL  *:</strong></div></td>
                                        <td width="617"><span id="sprytextfield1">
                                      <input id="url" maxlength="100" size="60" name="url" value="<? if($_POST['url']) echo $_POST['url']; ?>" />
                                      <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid URL format.</span></span></td>
                                      </tr>
                                      <tr>
                                        <td><div align="right"><strong>Title: </strong></div></td>
                                        <td> <input id="title" maxlength="100" size="60" name="title" value="<? if($_POST['title']) echo $_POST['title']; ?>" /></td>
                                      </tr>
                                      <tr>
                                        <td valign="top"><div align="right"><strong>Description:</strong></div></td>
                                        <td><textarea name="description" rows="8" cols="60"><? if($_POST['description']) echo stripslashes(htmlspecialchars_decode($_POST['description'])); ?></textarea></td>
                                  </tr>
                                  <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                  </tr>
                                  <tr>
                                    <td rowspan="2" valign="top"><div align="right"><strong>Name:</strong></div></td>
                                    <td><input id="name" maxlength="60" size="60" name="name" value="<? if($_POST['name']) echo $_POST['name']; ?>" /></td>
                                  </tr>
                                  <tr>
                                    <td class="small">Please insert link exchange partner name here.</td>
                                  </tr>
                                      <tr>
                                        <td rowspan="2" valign="top"><div align="right"><strong>Email of link partner:</strong></div></td>
                                      <td><span id="sprytextfield2">
                                          <input id="email" maxlength="60" size="60" name="email" value="<? if($_POST['email']) echo $_POST['email']; ?>" />
                                        <span class="textfieldInvalidFormatMsg">Invalid Email format.</span></span></td>
                                  </tr>
                                      <tr>
                                        <td class="small">The email you enter here is required inorder to send the link vendor a pre-configured email asking the owner to add our link in the specified url.</td>
                                      </tr>
                                      
                                      <tr>
                                        <td rowspan="2" valign="top"><div align="right"><strong>My Link
                                        :</strong></div></td>
                                        <td> <span id="sprytextfield7">
                                  <input id="checkurl" maxlength="100" size="60" name="checkurl" value="<? if($_POST['checkurl']) echo $_POST['checkurl']; ?>" />
                                  <span class="textfieldInvalidFormatMsg">Invalid URL format.</span></span></td>
                                  </tr>
                                      <tr>
                                        <td class="small">This is the link which we will be checking in the vendors specified url.</td>
                                      </tr>
                                      <tr>
                                        <td rowspan="2" valign="top"><div align="right"><strong>URL of incoming link:</strong></div></td>
                                        <td><span id="sprytextfield3">
                                          <input id="whereurl" maxlength="100" size="60" name="whereurl" value="<? if($_POST['whereurl']) echo $_POST['whereurl']; ?>" />
                                          <span class="textfieldInvalidFormatMsg">Invalid URL format.</span></span>                                          </td>
                                  </tr>
                                      <tr>
                                        <td class="small">Incoming url means the website url in which our link is present. Our links will be checked in this url.</td>
                                      </tr>
                                      <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                      </tr>
                                      <tr>
                                        <td><div align="right"><strong>Status: </strong></div></td>
                                        <td>  <select name="status">
                                            <option value="1">Active</option>
                                            <option value="0"
                                            <?
                                            if(isset($_POST['status'])):
                                                if($_POST['status'] == 0){
                                            echo " selected='selected' ";
                                            }
                                            endif;
                                            ?>
                                            >In Active</option>
                                          </select></td>
                                  </tr>
                                      <tr>
                                        <td><div align="right"><strong>Follow:</strong></div></td>
                                        <td>  <select name="follow">
                                            <option value="1">Yes</option>
                                            <option value="0"
                                            <?
                                            if(isset($_POST['follow'])):
                                                if($_POST['follow'] == 0){
                                            echo " selected='selected' ";
                                            }
                                            endif;
                                            ?>
                                            >No</option>
                                          </select></td>
                                  </tr>
                                      <tr>
                                        <td colspan="2">&nbsp;</td>
                                  </tr>
                                      <tr>
                                        <td>&nbsp;</td>
                                        <td><input type="submit" id="submit" maxlength="45" size="10" name="submit" value="submit" class="button-primary" /></td>
                                  </tr>
                                </table>
</form> 
							</div>
                            
                            <div align="right">
                             <?=get_option('links_display_now').'&nbsp;&nbsp;<i>'.get_option('easy_links_version').'</i>'?>
</div>
								
<?php
								
							}
		
				
				}
		
		function create_table(){
			global $wpdb;
			
			
			
			$sql = "CREATE TABLE " . $wpdb->prefix.$this->tablename." (
							id mediumint(9) NOT NULL AUTO_INCREMENT,
							url VARCHAR(256) NOT NULL,
							name VARCHAR(256) NOT NULL,
							title text NOT NULL,						
							whereurl VARCHAR(256) NOT NULL,	
							checkurl VARCHAR(256) NOT NULL,											
							email VARCHAR(256) NOT NULL,																		
							description text NOT NULL,																								
							status mediumint(9) NOT NULL,
							follow mediumint(9) NOT NULL,																														
							UNIQUE KEY id (id)
						)";
						
						
			$wpdb->query($sql);
			
			$this->create_options();
		}	
		
		function bulk_action(){
				
					$CHECK_ID = $_POST['check_id'];
					
					if(!empty($CHECK_ID)):
						foreach($CHECK_ID as $checked):
						
							$bool[] = $this->delete_page(filter_var($checked,FILTER_SANITIZE_STRING));
							
						endforeach;
					endif;
					
					if(! empty($CHECK_ID) ):
						if(in_array(FALSE,$bool)):
						?>
<div class="error"><p><strong><?php _e('Error Deleting Some Data.'); ?></strong></p></div>                	
						<?
							
						else:
						?>
							<div class="error"><p><strong><?php _e('Data Deleted.'); ?></strong></p></div>                	
						<?
							
						endif;
					else:
						?>
							<div class="error"><p><strong><?php _e('No Data Checked.'); ?></strong></p></div>                	
<?
					endif;
	
				}

		function update_table($pageid){
			
				global $wpdb;
				 
				 $sql = "UPDATE 
				 		".$wpdb->prefix.$this->tablename."
				 		SET
						url='".$this->sanitize_data($_POST['url']). "',
						name='".$this->sanitize_data($_POST['name']). "',
						title='".$this->sanitize_data($_POST['title']). "',	
						whereurl='".$this->sanitize_data($_POST['whereurl']). "',	
						checkurl='".$this->sanitize_data($_POST['checkurl'])."',
						email='".$this->sanitize_data($_POST['email']). "',
						description= '".htmlspecialchars($_POST['description'],ENT_QUOTES). "',
						status='".$this->sanitize_data($_POST['status'])."',   
						follow = '".$this->sanitize_data($_POST['follow'])."'
						 
						WHERE
						
						id='$pageid'
				 		";
				
						return $wpdb->query($sql);

			}
				
		function edit_page(){
		
						$pageid = filter_var($_GET['pageid'],FILTER_SANITIZE_STRING);
						$rows = $this->get_table_data_by_id($pageid);
						
						// variables for the field and option names 
						$hidden_field_name = 'submit';
					
						// Read in existing option value from database
						$opt_val = get_option( $opt_name );
					
						
						// See if the user has posted us some information
						// If they did, this hidden field will be set to 'Y'
						if( isset($_POST[ $hidden_field_name ]) ) {
							// Read their posted value
							$opt_val = $_POST[ $data_field_name ];
					
							// Save the posted value in the database
						   // update_option( $opt_name, $opt_val );
						   
						   $bool = $this->update_table($pageid);
					
							// Put an options updated message on the screen
					
						if($bool):
					?>
					<div class="updated"><p><strong><?php _e('Data saved.'); ?></strong></p></div>
					<?php
						elseif($del):
				?>
					<div class="error"><p><strong><?php _e('Data Deleted.'); ?></strong></p></div>
					<?
						else:
						?>
						<div class="error"><p><strong><?php _e('Error inserting data.'); ?></strong></p></div>		
						<?
						endif;
						}
					
						// Now display the options editing screen
					
						echo '<div class="wrap">';
					
						// header
					
						echo "<h2>" . __( 'Easy Link 1.0' ) . "</h2>";
					
						$pageid = filter_var($_GET['pageid'],FILTER_SANITIZE_STRING);
						$rows = $this->get_table_data_by_id($pageid);
						// options form
						if($this->count_data())
						{
							if(isset($_GET['pageid'])):
								$this->display_table_data(filter_var($_GET['pageid'],FILTER_SANITIZE_STRING));
							elseif(isset($_GET['deleteid'])):
								$del = $this->delete_page(filter_var($_GET['deleteid'],FILTER_SANITIZE_STRING));
								$this->display_table_data();
							else:
								$this->display_table_data();
							endif;
						}
						?>
                          <br />
                                        <h2>Edit Link</h2>
                                        <br />
                        <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
					<table width="800" border="0" cellspacing="2" cellpadding="2">
                                
                                      <tr>
                                        <td width="169"><div align="right"><strong>URL  *:</strong></div></td>
                                        <td width="617"><span id="sprytextfield4">
                          <input id="url" maxlength="100" size="60" name="url" value="<?php echo $rows[0]->url; ?>" />
                          <span class="textfieldRequiredMsg">A value is required.</span><span class="textfieldInvalidFormatMsg">Invalid URL format.</span></span></td>
                                      </tr>
                                      <tr>
                                        <td><div align="right"><strong>Title: </strong></div></td>
                                        <td> <input id="title" maxlength="100" size="60" name="title" value="<?php echo $rows[0]->title; ?>" /></td>
                                      </tr>
                                      <tr>
                                        <td valign="top"><div align="right"><strong>Description:</strong></div></td>
                                        <td valign="top"><textarea name="description" rows="8" cols="60"><?php echo stripslashes(htmlspecialchars_decode($rows[0]->description)); ?></textarea></td>
                                  </tr>
                                  <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                  </tr>
                                  <tr>
                                    <td rowspan="2" valign="top"><div align="right"><strong>Name:</strong></div></td>
                                    <td>
                                    <input id="name" maxlength="60" size="60" name="name" value="<?php echo $rows[0]->name; ?>" />                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="small">Please insert link exchange partner name here.</td>
                                  </tr>
                                      <tr>
                                        <td rowspan="2" valign="top"><div align="right"><strong>Email of link partner:</strong></div></td>
                                        <td> <span id="sprytextfield6">
                              <input id="email" maxlength="60" size="60" name="email" value="<?php echo $rows[0]->email; ?>" />
                              <span class="textfieldInvalidFormatMsg">Invalid format.</span></span></td>
                                  </tr>
                                      <tr>
                                        <td class="small">The email you enter here is required inorder to send the link vendor a pre-configured email asking the owner to add our link in the specified url.</td>
                                      </tr>
                                      
                                      <tr>
                                        <td rowspan="2" valign="top"><div align="right"><strong>My Link :</strong></div></td>
                                        <td> <span id="sprytextfield8">
                              <input id="checkurl" maxlength="100" size="60" name="checkurl" value="<?php echo $rows[0]->checkurl; ?>" />
                              <span class="textfieldInvalidFormatMsg">Invalid URL format.</span></span></td>
                                  </tr>
                                      <tr>
                                        <td class="small">This is the link which we will be checking in the vendors specified url.</td>
                                      </tr>
                                      <tr>
                                        <td rowspan="2" valign="top"><div align="right"><strong>URL of incoming link::</strong></div></td>
                                        <td><span id="sprytextfield5">
                              <input id="whereurl" maxlength="100" size="60" name="whereurl" value="<?php echo $rows[0]->whereurl; ?>" />
                              <span class="textfieldInvalidFormatMsg">Invalid URL format.</span></span>                                          </td>
                                  </tr>
                                      <tr>
                                        <td class="small">Incoming url means the website url in which our link is present. Our links will be checked in this url.</td>
                                      </tr>
                                      <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                      </tr>
                                      <tr>
                                        <td><div align="right"><strong>Status: </strong></div></td>
                                        <td>   <select name="status">
                                                <option value="1">Active</option>
                                                <option value="0"
                                                 <?
                                                    if($rows[0]->status == 0){
                                                    echo " selected='selected' ";
                                                    }
                                                    ?>
                                                >In Active</option>
                                            </select></td>
                                  </tr>
                                      <tr>
                                        <td><div align="right"><strong>Follow:</strong></div></td>
                                        <td>    <select name="follow">
                                                <option value="1">Yes</option>
                                                <option value="0"
                                                 <?
                                                    if($rows[0]->follow == 0){
                                                    echo " selected='selected' ";
                                                    }
                                                    ?>
                                                >No</option>
                                            </select></td>
                                  </tr>
                                      <tr>
                                        <td colspan="2">&nbsp;</td>
                                  </tr>
                                      <tr>
                                        <td>&nbsp;</td>
                                        <td><input type="submit" id="submit" maxlength="45" size="10" name="submit" value="submit" class="button-primary" /></td>
                                  </tr>
                                </table>
</form>
<br /><br /><br />
                <?=get_option('links_display_now').'&nbsp;&nbsp;<i>'.get_option('easy_links_version').'</i>'?>
                                
					
					</div>
						
					<?php
					
		
			}
			
		function delete_page($deleteid){
			global $wpdb;
			
			$deleteid = filter_var($deleteid,FILTER_SANITIZE_STRING);
			
			$sql = "DELETE FROM " . $wpdb->prefix.$this->tablename." 
					WHERE 
					id =$deleteid
					";
						
			return $wpdb->query($sql);
		}
		
		function count_data(){
				global $wpdb;
				$myrows = $wpdb->get_results( "SELECT count(*) FROM ".$wpdb->prefix.$this->tablename );
				if($myrows>0)
					return 1;
				else
					return 0;
		}
		
		function save_table(){

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
												'".$this->sanitize_data($_POST['status'])."', 
												'".$this->sanitize_data($_POST['follow'])."'
										);";
				
						
								return $wpdb->query($sql);
								
								
						  }
						  else
						  {	
							return false;
						  }
	
				}

		function display_table_data($page_id = 'ALL'){
		
						global $wpdb;
						
						$url = "options-general.php?page=links_admin_options_page";
						
						$count_sql =  "SELECT COUNT(*) as totalpage FROM ".$wpdb->prefix.$this->tablename;
									
						$listing_count = $wpdb->get_results($count_sql);
						$listing_count = $listing_count[0]->totalpage;
				
				
	
						$this->objNonSeoPaginate->items($listing_count);
						$this->objNonSeoPaginate->limit($this->limit);
						$this->objNonSeoPaginate->target($url);
						$this->objNonSeoPaginate->currentPage($url);
						
						$sql = "SELECT * FROM ".$wpdb->prefix.$this->tablename;
						
						if(isset($_GET['pagenum']))
						{
							
							$page = absint($_GET['pagenum']);
							$offset = $this->limit*($page-1);
							
							if($page !=1)
							{
								$sql .= " LIMIT $offset,$this->limit";
							}
							else
							{
								$sql .= " LIMIT $this->limit";
							}
		
						}
						else
						{
								$sql .= " LIMIT $this->limit";
						}
					
						
						$myrows = $wpdb->get_results( $sql );
						
						
						?>
                        
<form name="bulk" action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="bulk" method="post">
							<table class="widefat" width="100%">
                            <?
								if(isset($page)):
							?>
                            	<tr>
                            	<td colspan="6"><a href="options-general.php?page=links_admin_options_page&pagenum=<?=$page?>"><strong>Add New</strong></a></td>
                           	  </tr>
                            <?
								else:
							?>
                            	<tr>
                            	<td colspan="6"><a href="options-general.php?page=links_admin_options_page"><strong>Add New</strong></a></td>
                       	      </tr>
                            <?
								endif;
							?>
                            
                            	<tr>
                                	<td colspan="5"><a href="#" onclick="toggle_all(true);">Select All</a> | <a href="#" onclick="toggle_all(false);">Select None</a></td>
                                    <td>&nbsp;</td>
                            	</tr>
                            
								<thead>
									<tr>
                                    	<th>&nbsp;</th>
										<th>Link Title</th>
                                        <th>URL to Link</th>
										<th>Link WHERE URL</th>
										<th>Email</th>
									  <th>Action</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
                                    	<th>&nbsp;</th>
									  <th>Link Title</th>
                                      <th>URL to Link</th>
									  <th><p>Where URL</p>								      </th>
									  <th>Email</th>
									  <th>Action</th>
									</tr>
								</tfoot>
								<tbody>
                                
								<?
								$i = 0;
								if(!empty($myrows)):
									foreach($myrows as $rows):
									?>
									   <tr>
										 <td><input type="checkbox" name="check_id[]" value="<?php echo $rows->id; ?>" /></td>
										 <td><?php echo $rows->title; ?></td>
                                         <td><?php echo $rows->url; ?></td>
										 <td><?php echo $rows->whereurl; ?></td>
										 <td><?php echo $rows->email; ?></td>
                                         
										 <? if(isset($page)): ?>
                                         
										 <td><a href="options-general.php?page=links_admin_options_page&pageid=<?php echo $rows->id; ?>&pagenum=<?=$page?>">Edit</a> / <a href="options-general.php?page=links_admin_options_page&deleteid=<?php echo $rows->id; ?>&pagenum=<?=$page?>" onclick="return confirm('Are you sure you want to delete this data ?');">Delete</a> / <? if($rows->whereurl!=''): ?> <a href="#" onclick="check_url('<?php echo $rows->id; ?>','<?php echo $rows->whereurl; ?>','<?php echo $rows->checkurl; ?>');">Check</a> <span id="loader<?=$rows->id?>"> </span> <span id="check<?=$rows->id?>"> </span> <? endif; ?> / <a href="#" onclick="send_mail('<?php echo $rows->email; ?>','<?php echo $rows->whereurl; ?>','<?php echo $rows->checkurl; ?>','<?=$rows->id?>','<?php echo $rows->name; ?>');">Send Mail</a><span id="loadermail<?=$rows->id?>"> </span> <span id="checkmail<?=$rows->id?>"> </span>                                         </td>
                                         
										 <? else: ?>
                                         
											 <td>
                                             
                                             
                                             <a href="options-general.php?page=links_admin_options_page&pageid=<?php echo $rows->id; ?>">Edit</a> / <a href="options-general.php?page=links_admin_options_page&deleteid=<?php echo $rows->id; ?>" onclick="return confirm('Are you sure you want to delete this data ?');">Delete</a> / <? if($rows->whereurl!=''): ?><a href="#" onclick="check_url('<?php echo $rows->id; ?>','<?php echo $rows->whereurl; ?>','<?php echo $rows->checkurl; ?>');">Check</a> <span id="loader<?=$rows->id?>"> </span> <span id="check<?=$rows->id?>"> </span> <? endif; ?> / <a href="#" onclick="send_mail('<?php echo $rows->email; ?>','<?php echo $rows->whereurl; ?>','<?php echo $rows->checkurl; ?>','<?=$rows->id?>','<?php echo $rows->name; ?>');">Send Mail</a><span id="loadermail<?=$rows->id?>"> </span> <span id="checkmail<?=$rows->id?>"> </span></td>
                                             
										 <? endif; ?>
									   </tr>
                                       
									 <?
									 endforeach;
								 else:
								 	?>
                                    	<tr>
                                        	<td colspan="7">
                                            <strong class="updated">No Records Found</strong>                                            </td>
                                        </tr>
                                    <?
								 endif;
								 ?>
                                 
                                 <tr>
                                 	<td colspan="5">
                                    	<?
											$this->objNonSeoPaginate->show();
										?>                                     </td>
                                     <td>
                                     <?
									 if(!empty($myrows)):
									 ?>
                                     	<div align="right">
                                     	  <input type="submit" value="Remove Selected" name="remove" class="button-primary" onclick="return confirm('Are you sure you want to delete all checked data?');" />
                                   	    </div>
                                      <?
									  endif;
									  ?> 
                                      </td>
                                 </tr>
								</tbody>
	</table>
</form>
			
            
<?
					}

		
		function sanitize_data($text){
					global $wpdb;
					$text = $wpdb->escape( $text );
					$text = js_escape( $text );
					return $text;
		}
		
		
		/**********************************************--Admin Functions END--*******************************************************/
		/***************************************************************************************************************************/
		
		
}
?>