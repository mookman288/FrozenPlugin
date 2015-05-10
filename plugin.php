<?php
	/**
	 * Plugin Name: FrozenPlugin
	 * Plugin URI: https://github.com/mookman288/FrozenPlugin
	 * Description: Development Starter Plugin for WordPress - Change Me!
	 * Version: 0.0.0.1
	 * Requires at least: 
	 * Author: PxO Ink
	 * Author URI: http://www.pxoink.net/
	 * License: The MIT License (MIT)
	 */

	/**
	 * @name FrozenPlugin
	 * @author PxO Ink
	 * @see https://github.com/mookman288/FrozenPlugin
	 * @license The MIT License (MIT)
	 */
	class FrozenPlugin {
		/**
		 * Contains the activation level.
		 *
		 * @var boolean
		 */
		public	$activate;
		
		/**
		 * Contains WP editor settings.
		 *
		 * @var array
		 */
		private	$editor;
		
		/**
		 * Contains all of the notices. 
		 * 
		 * @var object
		 */
		private	$notices;
		
		/**
		 * Contains all of the plugin options and option structure. 
		 * 
		 * @var array
		 */
		private	$options;
		
		/**
		 * Contains the plugin page names.
		 * 
		 * @var array
		 */
		private	$pages;
		
		/**
		 * The class constructor which is fired upon instantiation. 
		 */
		public	function	__construct() {
			//Declare variables.
			$this -> editor		=	array(
					'textarea_name' => '',
					'textarea_rows'	=> 8,
					'media_buttons'	=> 'true',
					'tinymce'		=> array(
							'theme_advanced_buttons1'	=>	'bold,italic,underline,|,justifyleft,justifyfull,justifyright,|,bullist,numlist,|,outdent,indent,|,link,unlink,|,charmap,sub,sup,|,removeformat,cleanup',
							'theme_advanced_buttons3'	=>	'',
							'theme_advanced_buttons3'	=>	''
					)
			);
			$this -> namespace	=	'FrozenPlugin';
			$this -> notices	=	new noticeHandler();
			$this -> options	=	array(
					'required'		=> array(
							''							=>	''
					),
					'optional'		=> array(
							''							=>	''
					)
			);
			$this -> pages		=	array();
			
			//Declare requirements.
			require_once(ABSPATH . 'wp-includes/pluggable.php');
			
			//Run all actions, filters, and hooks necessary for this plugin on load.
			self::actionsFiltersHooks();
			
			/**
			 * Run initial logic.
			 */
			
			//For each required option.
			foreach($this -> options['required'] as $key => $value) {
				//Determine if the system is configured.
				if (!empty($value)) {
					//Set a warning.
					$this -> notices -> warning(__("Critical configuration settings are missing! This 
							plugin cannot be used until it is fully configured."));
					
					//Set a helpful message.
					$this -> notices -> message(__("If you're unsure how to configure this plugin, 
							consult the reference material or manual included under the help menu 
							in the upper-right-hand corner."));
					
					//Break.
					break;
				}
			}
			
			//If there has been a request to do so, set the options.
			if (isset($_POST['FrozenPluginOptions']))	self::setOptions();
			
			//Retrieve plugin options.
			self::retrieveOptions();
		}
		
		/**
		 * The class destructor that is fired when the script is finished. 
		 */
		public	function	__destruct() {
			/**
			 * Handle destruction here. 
			 */
		}
		
		/**
		 * Handle all actions, filters, and hooks here. 
		 */
		public	function	actionsFiltersHooks() {
			/**
			 * Handle actions here.
			 */
			
			//If this is not a network admin install.
			if (!is_multisite()) {
				//Add an action to implement the administrative menues.
				add_action('admin_menu', array($this, 'adminMenues'));
			} else {
				//Add an action to implement the network administrative menues.
				add_action('network_admin_menu', array($this, 'adminMenues'));
			}
			
			//If the user is not logged into the administration panel.
			if (!is_admin()) {
				//Add an action to load site related content.
				add_action('after_setup_theme', array($this, 'enqueueSite'), 18);
			}
			
			/**
			 * Handle filters here.
			 */
				
			//Add a filter to run meta content for the plugin.
			add_filter('plugin_row_meta', array($this, 'meta'), 10, 2);
				
			/**
			 * Handle hooks here.
			*/
				
			//Register an activation hook to install.
			register_activation_hook(__FILE__, array($this, 'install'));
				
			//Register an activation hook to uninstall.
			register_activation_hook(__FILE__, array($this, 'uninstall'));
		}
		
		/**
		 * Adds admin menues to WordPress for the plugin. 
		 */
		public	function	adminMenues() {
			/**
			 * Menu Pages.
			 */
			
			//Create a new menu page.
			$this -> pages[]	=	add_menu_page(
					'FrozenPlugin', //Page title. 
					'FrozenPlugin', //Menu title.
					'activate_plugins', //Capability.
					'FrozenPlugin-home', //Slug.
					array($this, 'panelHome'), //Function.  
					'dashicons-admin-generic', //Dashicon. 
					68 //Default position underneath plugins. 
			);
			
			/**
			 * Submenu Pages
			 */
			
			//Create a submenu page. 
			$this -> pages[]	=	add_submenu_page(
					'FrozenPlugin-home', //Parent slug.
					'FrozenPlugin', //Page title.
					'FP Home', //Menu title. 
					'activate_plugins', //Capability.
					'FrozenPlugin-home', //Slug - note this is identical to the parent slug. 
					array($this, 'panelHome') //Function.
					);
			
			//Create a submenu page.
			$this -> pages[]	=	add_submenu_page(
					'FrozenPlugin-home', //Parent slug.
					'FrozenPlugin', //Page title.
					'Settings', //Menu title.
					'activate_plugins', //Capability.
					'FrozenPlugin-settings', //Slug - note this is identical to the parent slug.
					array($this, 'panelSettings') //Function.
					);
			
			/**
			 * Actions, filters, and hooks that rely on the current page go here. 
			 */
			
			//For each page.
			foreach($this -> pages as $page) {
				//Add an action to load the help page.
				add_action("load-$page", array($this, 'helpMenu'));
				
				//Add an action to load admin area related content.
				add_action("admin_print_styles-$page", array($this, 'enqueueAdmin'));
			}
			
			
		}
		
		/**
		 * Handles the necessary queueing of stylesheets and scripts for the administrative 
		 * area. 
		 */
		public 	function	enqueueAdmin() {
			/**
			 * Register styles here.
			 */
			
			//Register the administrative CSS for FrozenPlugin.
			wp_register_style('FrozenPlugin-admin-css', plugins_url('css/admin.css', __FILE__));
			
			/**
			 * Register scripts here.
			 */
			
			//Register the administrative JS for FrozenPlugin.
			wp_register_script('FrozenPlugin-admin-js', plugins_url('js/admin.js', __FILE__), array('jquery'));
			
			/**
			 * Enqueue styles here.
			 */
			
			//Enqueue Farbtastic. 
			wp_enqueue_style('farbtastic');
			
			//Enqueue ThickBox. 
			wp_enqueue_style('thickbox');
			
			//Enqueue the administrative CSS. 
			wp_enqueue_style('FrozenPlugin-admin-css');
		
			/**
			 * Enqueue scripts here. 
			 */
			
			//Enqueue Farbtastic. 
			wp_enqueue_script('farbtastic');
			
			//Enqueue ThickBox. 
			wp_enqueue_script('thickbox');
			
			//Enqueue Media Uploads.
			wp_enqueue_media();
			//admin_enqueue_script('media-upload');
			
			//Enqueue the administrative JS. 
			wp_enqueue_script('FrozenPlugin-admin-js');
		}
		
		/**
		 * Handles the necessary queueing of stylesheets and scripts for the public-facing site. 
		 */
		public 	function	enqueueSite() {
			/**
			 * Register styles here.
			 */
			
			//Register the public site CSS for FrozenPlugin.
			wp_register_style('FrozenPlugin-site-css', plugins_url('css/site.css', __FILE__));
		
			/**
			 * Register scripts here.
			 */
			
			//Register the public site JS for FrozenPlugin.
			wp_register_script('FrozenPlugin-site-js', plugins_url('js/site.js', __FILE__), array('jquery'));
		
			/**
			 * Enqueue styles here.
			 */
			
			//Enqueue the public site CSS. 
			wp_enqueue_style('FrozenPlugin-site-css');
			
			/**
			 * Enqueue scripts here. 
			 */
			
			//Enqueue the public site JS. 
			wp_enqueue_script('FrozenPlugin-site-js');
		}
		
		/**
		 * Provides a contextual help menu in htm format.
		 */
		public 	function	helpMenu() { 
			//Declare variables.
			$entries			=	array();
			
			//Depending upon the type of directory reading function available. 
			if (!function_exists('glob') && !function_exists('scandir')) {
				//Open the directory.
				$handle			=	opendir(realpath(dirname(__FILE__) . '/doc'));
				
				//While there are entries to read.
				while (($entry = readdir($handle)) !== false) {
					//If the file is a PHP file.
					if (strpos($entry, '.php') !== false) {
						//Increment entries.
						$entries[]	=	$entry;
					}
				}
				
				//Close the directory.
				closedir($handle);
			} elseif (function_exists('glob')) {
				//Find all php files using glob.
				$files			=	glob(sprintf("%s/doc/*.php", dirname(__FILE__)));
				
				//If the files are an array.
				if (is_array($files)) {
					//For each file.
					foreach($files as $entry) {
						//Increment entries.
						$entries[]	=	$entry;
					}
				}
			} elseif (function_exists('scandir')) {
				//Get the files.
				$files		=	scandir(realpath(dirname(__FILE__) . '/doc'));
				
				//If the files are an array.
				if (is_array($files)) {
					//For each file.
					foreach($files as $entry) {
						//If the file is a PHP file.
						if (strpos($entry, '.php') !== false) {
							//Increment entries.
							$entries[]	=	$entry;
						}
					}
				}
			}
			
			//If there are entries.
			if (count($entries) > 0) {
				//For each of the menu tabs.
				foreach ($entries as $key => $entry) {
					//Get the basename.
					$entry		=	basename($entry);
					
					//Set the filename.
					$filename	=	sprintf("%sdoc/$entry", plugin_dir_path(__FILE__));
					
					//If there is an entry, and the file exists.
					if ($entry && file_exists($filename)) { 
						//Get the pieces of the filename. 
						$pieces			=	explode('.', $entry);
						
						//Start the output buffer.
						ob_start();
						
						//Include the PHP file for rendering.
						include_once($filename);
						
						//Add the help tab.
						get_current_screen() -> add_help_tab(array(
								'id' => "FrozenPlugin-help-$key",
								'title' => ucwords(__(str_replace('-', ' ', $pieces[0]))),
								'content' => ob_get_clean()
						));
					}
				}
			}
		}
		
		/**
		 * Initializes the plugin using a static method. 
		 * 
		 * @return FrozenPlugin
		 */
		static	public 	function	init() {
			/**
			 * Implement any processes that need to happen before the plugin is instantiated. 
			 */
			
			//Instantiate the plugin and return it. 
			return new self();
		}
		
		/**
		 * Activated upon installation. 
		 */
		public	function	install() {
			/**
			 * Handle plugin installation here.
			 */
		}
		
		/**
		 * Adds additional metadata to the plugin information screen. 
		 * 
		 * @param array $content
		 * @param string $file
		 * @return string
		 */
		public 	function	meta($content, $file) {
			//Ensure that this is loaded only for this plugin. 
			if ($file == plugin_basename(__FILE__)) {
				//Append HTML content to the metadata. 
				$content[]	=	sprintf('<a href="%s" target="_blank">%s</a>', 
						"options-general.php?page=FrozenPlugin-settings", 
						__('Plugin Settings'));
				$content[]	=	sprintf('<a href="%s" target="_blank">%s</a>', 
						"https://github.com/mookman288/FrozenPlugin", 
						__('Author'));
			}
		
			//Return content.
			return $content;
		}
		
		/**
		 * Gets the PHP panel corresponding to the slug. 
		 * 
		 * @param unknown $slug
		 */
		public	function	panel($slug) {
			//Declare variables.
			$panel	=	sprintf("%s/panels/%s.php", dirname(__FILE__), $slug);
			$uniqid	=	uniqid(true);
			
			//If the panel does not exist. 
			if (!file_exists($panel)) {
				//Return false.
				return false;
			} else {
				//Include the panel.
				include_once($panel);
			}
		}
		
		/**
		 * Loads the home panel. 
		 */
		public	function	panelHome() {
			//Load the panel matching the home slug.
			self::panel('FrozenPlugin-home');
		}
		
		/**
		 * Loads the settings panel. 
		 */
		public	function	panelSettings() {
			//Load the panel matching the settings slug.
			self::panel('FrozenPlugin-settings');
		}
		
		/**
		 * Retrieves the options based upon the option structure. 
		 */
		public 	function	retrieveOptions() {
			//For each option category.
			foreach($this -> options as $category => $k) {
				//For each option. 
				foreach ($this -> options[$category] as $key => $value) {
					//Set the option. 
					$this -> options[$category][$key]		=	get_option($key);
					
					//If the option is serialized.
					if (is_serialized($this -> options[$category][$key])) {
						//Unserialize the option.
						$this -> options[$category][$key]	=	 
						unserialize($this -> options[$category][$key]);
					}
				}
			}
		}
		
		/**
		 * Sets options based upon the option structure. 
		 * 
		 * @return boolean
		 */
		public 	function	setOptions() {
			//If the nonce does not verify.
			if (!wp_verify_nonce($_POST['nonce'], $_POST['uniqid'])) {
				//Set an error.
				$this -> notices -> error(__("This form has expired. Please try again."));
			} else {
				//Retrieve plugin options.
				self::retrieveOptions();
				
				//Update the options.
				foreach ($_POST['FrozenPluginOptions'] as $type => $optionArray) {
					//For each option array.
					foreach($optionArray as $optionName => $value) {
						//Get the option value.
						$optionValue		=	$this -> options[$type][$optionName];
						
						//If the value is not an array.
						if (is_array($value)) {
							//Serialize the option value. 
							$optionValue	=	serialize($optionValue);
							
							//Serialize the value.
							$value			=	serialize($value);
						}
						
						//If this is not a duplicate piece of data.
						if (((string) $value) != ((string) $optionValue)) {
							//If the option is not updated.
							if (!update_option($optionName, "$value")) {
								$this -> notices -> error(__(
										sprintf("%s could not be updated due to an error.",
												ucwords(str_replace('-', ' ', $optionName)))));
							}
						}
					}
				}
				
				//If there are no errors.
				if (count($this -> notices -> errors) < 1) {
					//Set a success message.
					$this -> notices -> success(__("Settings have been successfully updated."));
				}
			}
		}
		
		/**
		 * Activated upon deactivation.
		 */
		public	function	uninstall() {
			/**
			 * Handle plugin installation here. 
			 */
		}
	}
	
	//If the notice handler doesn't already exist.
	if (!class_exists('noticeHandler')) {
		/**
		 * @name noticeHandler
		 * @author PxO Ink
		 * @see https://github.com/mookman288/FrozenPlugin
		 * @license The MIT License (MIT)
		 */
		class	noticeHandler {
			/**
			 * Contains all of the plugin errors. 
			 * 
			 * @var array
			 */
			public $errors;
			
			/**
			 * Contains all of the plugin messages.
			 * 
			 * @var array
			 */
			public $messages;
			
			/**
			 * Contains all of the plugin successes.
			 *
			 * @var array
			 */
			public $successes;
			
			/**
			 * Contains all of the plugin warnings. 
			 * 
			 * @var array
			 */
			public $warnings;
			
			/**
			 * Class constructor.
			 */
			public function __construct() {
				//Declare variables.
				$this -> errors		=	array();
				$this -> messages	=	array();
				$this -> successes	=	array();
				$this -> warnings	=	array();
			}
			
			/**
			 * Assigns an error. 
			 * 
			 * @param string $err
			 */
			public function error($err) {
				//Increment errors.
				$this -> errors[]	=	$err;
			}
			
			/**
			 * Assigns a message. 
			 * 
			 * @param string $msg
			 */
			public function message($msg) {
				//Increment messages.
				$this -> messages[]	=	$msg;
			}
			
			/**
			 * Handles converting notices into HTML. 
			 * 
			 * @param boolean $output
			 */
			public function output($output = true) {
				//Start the output buffer.
				ob_start();
				
				//If there are errors.
				if (count($this -> errors) > 0) {
					?><div class="notice notice-error"><?php
						foreach($this -> errors as $error) {
							?><p><?php _e($error); ?></p><?php
						}
					?></div><?php
				}
				
				//If there are messages.
				if (count($this -> messages) > 0) {
					?><div class="notice notice-info"><?php
						foreach($this -> messages as $message) {
							?><p><?php _e($message); ?></p><?php
						}
					?></div><?php
				}
				
				//If there are successes.
				if (count($this -> successes) > 0) {
					?><div class="notice notice-success"><?php
						foreach($this -> successes as $success) {
							?><p><?php _e($success); ?></p><?php
						}
					?></div><?php
				}
					
				//If there are warnings.
				if (count($this -> warnings) > 0) {
					?><div class="notice notice-warning"><?php
						foreach($this -> warnings as $warning) {
							?><p><?php _e($warning); ?></p><?php
						}
					?></div><?php
				}
				
				//If the output should be returned.
				if (!$output) {
					//Return output. 
					return ob_get_clean();
				} else {
					//Print the output.
					print(ob_get_clean());
				}
			}
			
			/**
			 * Assigns a success message.
			 *
			 * @param string $msg
			 */
			public function success($msg) {
				//Increment successes.
				$this -> successes[]	=	$msg;
			}
			
			/**
			 * Assigns a warning. 
			 * 
			 * @param string $warn
			 */
			public function warning($warn) {
				//Increment warnings.
				$this -> warnings[]	=	$warn;
			}
		}
	}
	
	//Instantiate the object.
	$FrozenPlugin	=	FrozenPlugin::init();
?>