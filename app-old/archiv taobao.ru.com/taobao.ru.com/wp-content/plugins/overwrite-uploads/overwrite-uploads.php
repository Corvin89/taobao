<?php
/*
Plugin Name: Overwrite Uploads
Description: Lets you choose whether or not Wordpress should overwrite files uploaded to the Media Library
Version: 1.0
Author: Ian Dunn
Author URI: http://iandunn.name
License: GPL2
*/

/*  
 * Copyright 2011 Ian Dunn (email : ian@iandunn.name)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if(basename($_SERVER['SCRIPT_FILENAME']) == basename(__FILE__))
	die("Access denied.");

define('OVUP_NAME', 'Overwrite Uploads');
define('OVUP_REQUIRED_PHP_VERSON', '5');

if( !class_exists('overwriteUploads') )
{
	/**
	 * A Wordpress plugin that allows the user to override files uploaded to the Media Library
	 * Requires PHP5+ because of various OOP features, pass by reference, etc
	 * Requires Wordpress 3.1+ because the issue in the link referenced in nonUniqueFilename()
	 *
	 * @package OverwriteUploads
	 * @author Ian Dunn <ian@iandunn.name>
	 * @todo
	 *		Once the necessary filter is added to core (http://core.trac.wordpress.org/ticket/16849): Remove custom filter, refactor environment check, and update required version
	 *		Add internationalization support
	 */
	class overwriteUploads
	{
		// Declare variables and constants
		protected $settings, $options, $updatedOptions, $environmentOK, $userUpdateCount, $userMessageCount;
		const REQUIRED_WP_VERSION	= '3.1';
		const PREFIX				= 'ovup_';
		const DEBUG_MODE			= false;
		
		/**
		 * Constructor
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function __construct()
		{
			// Initialize variables
			$defaultOptions						= array( 'updates' => array(), 'errors' => array() );
			$this->options						= array_merge( get_option( self::PREFIX . 'options', array() ), $defaultOptions );
			$this->updatedOptions				= false;
			$this->userMessageCount				= array( 'updates' => 0, 'errors' => 0 );
			$this->settings['overwriteUploads']	= get_option( self::PREFIX . 'overwrite-uploads' );
			$this->environmentOK				= $this->checkEnvironment();
			
			// Register action for error messages and updates
			add_action( 'admin_notices', array($this, 'printMessages') );
			
			// Register remaining actions and filters
			if($this->environmentOK)
			{
				add_action( 'admin_init', array($this, 'addSettings') );
				add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), array($this, 'addSettingsLink') );
				if($this->settings['overwriteUploads'])
					add_filter( 'wp_handle_upload_overrides', array($this, 'addUniqueFilenameCallback') );
			}
		}
		
		/**
		 * Checks whether the system requirements are met
		 * file.php is only loaded by WP when necessary, so we include it to make sure we can always check the flag inside it
		 * @author Ian Dunn <ian@iandunn.name>
		 * @return bool True if system requirements are met, false if not
		 */
		protected function checkEnvironment()
		{
			require_once(ABSPATH .'/wp-admin/includes/file.php');
			global $wp_version;
			$environmentOK = true;
			
			if( version_compare($wp_version, self::REQUIRED_WP_VERSION, "<") )
			{
				$this->enqueueMessage(OVUP_NAME . ' requires <strong>Wordpress '. self::REQUIRED_WP_VERSION .'</strong> or newer in order to work. Please upgrade if you would like to use this plugin.', 'error');
				$environmentOK = false;
			}
			
			if( !defined('OVUP_FILTER_ADDED') || OVUP_FILTER_ADDED !== true )
			{
				$this->enqueueMessage(OVUP_NAME . ' requires a new filter to be added to Wordpress. If this is a new installation or you recently upgraded Wordpress, please see the installation instructions in readme.txt for information on adding it.', 'error');
				$environmentOK = false;
			}
		
			return $environmentOK;
		}

		/**
		 * Adds our custom settings to the admin Settings pages
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function addSettings()
		{
			add_settings_section(self::PREFIX . 'media-settings', 'Overwrite Uploaded Files', array($this, 'settingsSectionCallback'), 'media');
			add_settings_field(self::PREFIX . 'overwrite-uploads', 'Overwrite uploaded files', array($this, 'settingsCallback'), 'media', self::PREFIX . 'media-settings');
			register_setting('media', self::PREFIX . 'overwrite-uploads');
		}
		
		/**
		 * Adds the section introduction text to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function settingsSectionCallback()
		{
			// Intentionally blank
		}
		
		/**
		 * Adds the input field to the Settings page
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function settingsCallback()
		{
			echo '<input id="'. self::PREFIX .'overwrite-uploads" name="'. self::PREFIX .'overwrite-uploads" type="checkbox" value="true" class="code" ' . checked('true', $this->settings['overwriteUploads'], false) . ' /> ';
			echo '<label for="'. self::PREFIX .'overwrite-uploads">If this is checked, files uploaded to the Media Library will overwrite any existing files with the same name.</label>';
		}
		
		/**
		 * Adds a 'Settings' link to the Plugins page
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param array $links The links currently mapped to the plugin
		 * @return array
		 */
		public function addSettingsLink($links)
		{
			array_unshift($links, '<a href="options-media.php">Settings</a>');
			return $links; 
		}
		
		/**
		 * Adds the callback necessary to avoid creating unique filenames
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param mixed $overrides The $overrides passed to wp_handle_upload. Either an array or boolean false if nothing was passed.
		 * @return array
		 */
		public function addUniqueFilenameCallback($overrides)
		{
			$overrides['test_form'] = false;
			$overrides['unique_filename_callback'] = array($this, 'nonUniqueFilename');
			
			return $overrides;
		}

		/**
		 * Returns the filename to be assigned by wp_handle_upload()
		 * This does the same thing that the comparable section of wp_unique_filename() does, except it doesn't postfix a number if the file already exists, which allows files to be overwritten.
		 * Requires WP 3.1 (see link below)
		 * @author Ian Dunn <ian@iandunn.name>
		 * @link http://core.trac.wordpress.org/ticket/14627 Before WP 3.1 there was a bug where $extension didn't get passed in
		 * @param string $directory The directory the file will be stored in
		 * @param string $name The name of the file (after being sanitized, etc)
		 * @param string $extension The file extension
		 * @return string The filename (without any postfixed numbers)
		 */
		public function nonUniqueFilename($directory, $name, $extension)
		{
			$filename = $name . strtolower($extension);
			$this->removeOldAttachments($filename);
			
			return $filename;
		}
		
		/**
		 * Removes the old attachment post and metadata so that there won't be multiple entries in the Media Library
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param string $filename
		 */
		function removeOldAttachments($filename)
		{
			$arguments = array(
				'numberposts'	=> -1,
				'meta_key'		=> '_wp_attached_file',
				'meta_value'	=> $filename,
				'post_type'		=> 'attachment'
			);
			$oldAttachments = get_posts($arguments);
			
			foreach($oldAttachments as $oa)
				if( !wp_delete_attachment($oa->ID, true) )
					$this->enqueueMessage(OVUP_NAME . ': Old attachment <strong>#'. $oa->ID .'</strong> deletion failed.', 'error', 'debug');
		}
		
		/**
		 * Displays updates and errors
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function printMessages()
		{
			foreach( array('updates', 'errors') as $type )
			{
				if( $this->options[$type] && ( self::DEBUG_MODE || $this->userMessageCount[$type] ) )
				{
					echo '<div id="message" class="'. ( $type == 'updates' ? 'updated' : 'error' ) .'">';
					foreach($this->options[$type] as $message)
						if( $message['mode'] == 'user' || self::DEBUG_MODE )
							echo '<p>'. $message['message'] .'</p>';
					echo '</div>';
					
					$this->options[$type] = array();
					$this->updatedOptions = true;
					$this->userMessageCount[$type] = 0;
				}
			}
		}
		
		/**
		 * Queues up a message to be displayed to the user
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param string $message The text to show the user
		 * @param string $type 'update' for a success or notification message, or 'error' for an error message
		 * @param string $mode 'user' if it's intended for the user, or 'debug' if it's intended for the developer
		 */
		protected function enqueueMessage($message, $type = 'update', $mode = 'user')
		{
			array_push($this->options[$type .'s'], array(
				'message' => $message,
				'type' => $type,
				'mode' => $mode
			) );
			
			if($mode == 'user')
				$this->userMessageCount[$type . 's']++;
			
			$this->updatedOptions = true;
		}
		
		/**
		 * Destructor
		 * Writes options to the database
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		public function __destruct()
		{
			if($this->updatedOptions)
				update_option(self::PREFIX . 'options', $this->options);
		}
	} // end overwriteUploads
}

/**
 * Prints an error that the required PHP version wasn't met.
 * This has to be defined outside the class because the class can't be called if the required PHP version isn't installed.
 * Writes options to the database
 * @author Ian Dunn <ian@iandunn.name>
 */
function ovup_phpOld()
{
	echo '<div id="message" class="error"><p>'. OVUP_NAME .' requires <strong>PHP '. OVUP_REQUIRED_PHP_VERSON .'</strong> in order to work. Please ask your web host about upgrading.</p></div>';
}

// Create an instance
if( is_admin() )
{
	if( version_compare(PHP_VERSION, OVUP_REQUIRED_PHP_VERSON, '>=') )
	{
		if(class_exists("overwriteUploads"))
			$ovup = new overwriteUploads();
	}
	else
		add_action('admin_notices', 'ovup_phpOld');
}

?>