<?php
/*
Plugin Name: Neighborhood Demo Content
Description: Replicate the Neighborhood demo site in just a few clicks!
Author: Swift Ideas
Author URI: http://www.swiftideas.com
Version: 2.1
Text Domain: Neighborhood-importer
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
/**
 * WordPress Importer class for managing the import process of a WXR file
 *
 * @package WordPress
 * @subpackage Importer
 */

// Load Importer API
require_once ABSPATH . 'wp-admin/includes/import.php';

if ( ! class_exists( 'WP_Importer' ) ) {
	$class_Swift_Importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
	if ( file_exists( $class_Swift_Importer ) )
		require $class_Swift_Importer;


}

class Swift_Import extends WP_Importer {
	var $max_wxr_version = 1.2; // max. supported WXR version

	var $id; // WXR attachment ID

	// information to import from WXR file
	var $version;
	var $demofiles = array();

	function Swift_Import() { /* nothing */  }

	function initialize_data() {

		$plugin_path = dirname(__FILE__);

		// Demos
		$demofiles['id-0']           = 'demositefull';
		$demofiles['title-0']        = 'Full content';
		$demofiles['previewlink-0']  = 'http://neighborhood.swiftideas.net/';
		$demofiles['colors-0']       = $plugin_path .'/demofiles/demo0/demositefull-colors.json';
		$demofiles['themeoptions-0'] = $plugin_path .'/demofiles/demo0/demositefull-options.json';
		$demofiles['widgets-0']      = $plugin_path .'/demofiles/demo0/demositefull-widgets.json';

		$demofiles['id-1']           = 'demositealternative';
		$demofiles['title-1']        = 'Alternative content';
		$demofiles['previewlink-1']  = 'http://neighborhood.swiftideas.net/';
		$demofiles['colors-1']       = $plugin_path .'/demofiles/demo1/demositealternative-colors.json';
		$demofiles['themeoptions-1'] = $plugin_path .'/demofiles/demo1/demositealternative-options.json';
		$demofiles['widgets-1']      = $plugin_path .'/demofiles/demo1/demositealternative-widgets.json';

		return $demofiles;
	}

	/**
	 * Registered callback function for the WordPress Importer
	 *
	 * Manages the three separate stages of the WXR import process
	 */
	function dispatch() {

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		switch ( $step ) {
			case 0:
			    $this->header();
				$this->greet();
				$this->footer();
				break;

		}


	}

	/**
	 * Get Menu Item Id with special meta data from Custom links
	 *
	 */

	function get_menu_item_special_data_custom($item_name){

		  global $wpdb;

    	  $query = 'SELECT ID FROM '.$wpdb->posts.', '.$wpdb->term_relationships.', '.$wpdb->postmeta.' ';
     	  $query .= 'WHERE ID = object_id AND ID = post_id ';
          $query .= 'AND post_title = "'.$item_name.'" ';
          $query .= 'AND post_status = "publish" ';
          $query .= 'AND post_type = "nav_menu_item"';
          $query .= 'AND meta_key = "_menu_item_object" order by ID ASC';

		  return $wpdb->get_var( $query );

	}

	/**
	 * Get Menu Item Id with special meta data from Pages
	 *
	 */

	function get_menu_item_special_data_page($item_name){

		  global $wpdb;

		  if($item_name == 'Home'){

          		$query = 'SELECT ID FROM '.$wpdb->posts.', '.$wpdb->postmeta.' ';
     	 		$query .= 'WHERE ID = meta_value ';
          		$query .= 'AND post_title = "'.$item_name.'" ';
          		$query .= 'AND post_status = "publish" ';
          		$query .= 'AND post_type = "page" and menu_order = 1 ';
          		$query .= 'AND meta_key = "_menu_item_object_id" order by post_id asc';



		  }else{
    	  		$query = 'SELECT post_id FROM '.$wpdb->posts.', '.$wpdb->postmeta.' ';
     	 		$query .= 'WHERE ID = meta_value ';
          		$query .= 'AND post_title = "'.$item_name.'" ';
          		$query .= 'AND post_status = "publish" ';
          		$query .= 'AND post_type = "page"';
          		$query .= 'AND meta_key = "_menu_item_object_id" order by post_id asc';
          }

		  return $wpdb->get_var( $query );

	}

	/**
	 * Assign the menus to the locations
	 *
	 */

	function assign_menus_to_locations($demoid){

		$term = get_term_by('name', 'Main Menu', 'nav_menu');

		if($term == null || 0 == $term->term_id){
			$term = get_term_by('name', 'Main', 'nav_menu');
		}

		$menu_id =  $term->term_id;

	 	$new_theme_navs = get_theme_mod( 'nav_menu_locations' );
	 	$new_theme_locations = get_registered_nav_menus();

	 	foreach ($new_theme_locations as $location => $description ) {

				$new_theme_navs[$location] = $menu_id;

       	}

       	set_theme_mod( 'nav_menu_locations', $new_theme_navs );

	}

	function set_theme_options( $value = '' ) {

            $value['REDUX_last_saved'] = time();
            if( !empty($value) && isset($args) ) {
                $options = $value;
                if ( $args['database'] === 'transient' ) {
                    set_transient( 'sf_neighborhood_options-transient', $value, time() );
                } else if ( $args['database'] === 'theme_mods' ) {
                    set_theme_mod( $args['opt_name'] . '-mods', $value );
                } else if ( $args['database'] === 'theme_mods_expanded' ) {
                    foreach ( $value as $k=>$v ) {
                        set_theme_mod( $k, $v );
                    }
                } else {

					   update_option( 'sf_neighborhood_options', $value  );
                }

                $options = $value;

                /**
                 * action 'redux-saved-{opt_name}'
                 * @deprecated
                 * @param mixed $value set/saved option value
                 */

				 // To work for all the themes this must be replaced redux-saved-{opt_name} and assign a specific value for opt_name

                do_action( "redux-saved-sf_neighborhood_options", $value ); // REMOVE

                /**
                 * action 'redux/options/{opt_name}/saved'
                 * @param mixed $value set/saved option value
                 */

				 // To work for all the themes this must be replaced redux-saved-{opt_name} and assign a specific value for opt_name
                do_action( "redux/options/sf_neighborhood_options/saved", $value );

            }
        }

	// Display import page title
	function header() {
		echo '<div class="wrap">';
		screen_icon();
		//Welcome message
		echo '<h2 style="margin-left:20px;">' . __( 'Neighborhood Demo Content Importer', 'wordpress-importer' ) . '</h2>';

		$updates = get_plugin_updates();
		$basename = plugin_basename(__FILE__);
		if ( isset( $updates[$basename] ) ) {
			$update = $updates[$basename];
			echo '<div class="error"><p><strong>';
			printf( __( 'A new version of this importer is available. Please update to version %s to ensure compatibility with newer export files.', 'wordpress-importer' ), $update->update->new_version );
			echo '</strong></p></div>';
		}
	}

	// Close div.wrap
	function footer() {
		echo '</div>';
	}




	/**
	 * Display introductory text and file upload form
	 */
	function greet() {

		global $plugin_path;

		$demofiles_data = $this->initialize_data();
		$plugin_path = dirname(__FILE__);
		$demourl = admin_url('admin.php?import=swiftdemo&amp;step=1');

		?>

		<div class="note-wrap clearfix">
			<h3>Please Read!</h3>
			<p>This demo content importer has been built to make the import process as easy for you as possible. We've done what we can to ensure as little difficulty as possible. We have also gone the extra mile to add in the extra things are sorted for you, such as setting the home page, menu, and widgets - things that aren't possible with the standard WordPress Importer!</p>
			<h4>Steps to take before using this plugin.</h4>
			<ol>
				<li>The import process will work best on a clean install. You can use a plugin such as WordPress Reset to clear your data for you.</li>
				<li>Ensure all plugins are installed beforehand, e.g. WooCommerce - any plugins that you add content to.</li>
				<li>Once you start the process, please leave it running and uninteruppted - the page will refresh and show you a completed message once the process is done.</li>
			</ol>
			<br/>
			<p style="font-weight: bold;">PLEASE NOTE: Due to the large amount of content on the main demo, we have provided a reduced version for you to import to ensure that you have no issues with it.</p>
		</div>

	<div>

	<?php
		$html_output = "";
		//Replace by the correct validation
		if (get_option('sf_neighborhood_options')) {
			$html_output = '<div><ul class="swift-demo">';

			for ( $i=0; $i<=0; $i++){

				$html_output .= '<li><a href="'.$demofiles_data["previewlink-$i"].'" target="_blank" class="product '.$demofiles_data["id-$i"].'"></a>';
				$html_output .= '<div class="item-wrap">';
				$html_output .= '<h3>'.$demofiles_data["title-$i"].'</h3>';
				$html_output .= '<div class="importoptions"><span>'.__("Select what you'd like to import:", 'swift-importer').'</span><div class="dinput">';
				$html_output .= '<input type="checkbox" name="democontent'.$i.'" id="democontent'.$i.'">Demo Content</input></div><div class="dinput">';
				$html_output .= '<input type="checkbox" name="widgetsoption'.$i.'" id="widgetsoption'.$i.'">Widgets</input></div><div class="dinput">';
				$html_output .= '<input type="checkbox" name="themeoption'.$i.'" id="themeoption'.$i.'">Theme Options</input></div><div class="dinput">';
				$html_output .= '<input type="checkbox" name="coloroption'.$i.'" id="coloroption'.$i.'">Color Options</input></div></div>';
				$html_output .= '<div data-demoid="'.$i.'" data-url="'.$demourl.'&amp;demoid='.$i.'" class="demoimp-button">Import</div>';
				$html_output .= '</div></li>';

			}
			$html_output .= '</ul></div>';
		}
		echo $html_output;
		?>

	</div>
	<div class="sf-modal-notice">
		<div class="spinnermessage">
			<h3>Importing Demo</h3>
			<p>Please be patient it could take a few minutes.</p>

			<div class="sf-progress-bar-wrapper html5-progress-bar">
			<p><span>Note</span>: If the progress indicator doesn't change for a couple minutes. Repeat the import process.</p>
            	<div class="progress-bar-wrapper">
                	<progress id="progressbar" value="0" max="100"></progress>
            	</div>
            	<div class="progress-value">0%</div>
            	<div class="progress-bar-message"></div>
            </div>
            <div id="sf_import_close">Close</div>
		</div>
	</div>
	<div  class="sf-black-overlay"></div>

	<?php	}

}

add_action( 'wp_ajax_sf_import_content', 'sf_import_content' );
add_action( 'wp_ajax_sf_import_colors', 'sf_import_colors' );
add_action( 'wp_ajax_sf_import_options', 'sf_import_options' );
add_action( 'wp_ajax_sf_import_widgets', 'sf_import_widgets' );





function sf_import_widgets(){

			// include Widget data class
			if (!class_exists( 'Swift_Widget_Data' )) {
				require dirname( __FILE__ ) . '/class-widget-data.php';
			}

			$demoid =  $_POST['demo'] ;
			$swift_import = new Swift_Import();
			$widget_data = new Swift_Widget_Data();
			$demofiles_data = $swift_import->initialize_data();
			$widget_file = $demofiles_data['widgets-'.$demoid];
			$widget_data->ajax_import_widget_data($widget_file);

			wp_die();
	}

	function sf_import_options(){
		echo "testing";
			$demoid =  $_POST['demo'] ;
			$swift_import = new Swift_Import();
			$demofiles_data = $swift_import->initialize_data();
			$file = $demofiles_data['themeoptions-'.$demoid];
			$import = file_get_contents( $file );
			$imported_options = array();

			if ( !empty( $import ) ) {
                //$imported_options = json_decode( htmlspecialchars_decode( $import ), true );
                $imported_options = unserialize(trim($import,'###'));
         	}

			$plugin_options['REDUX_imported'] = 1;
        	foreach($imported_options as $key => $value) {
					$plugin_options[$key] = $value;
        	}

			if(!empty($imported_options) && is_array($imported_options) && isset($imported_options['redux-backup']) && $imported_options['redux-backup'] == '1' ) {
            	 $plugin_options['REDUX_imported'] = 1;
             	foreach($imported_options as $key => $value) {
						  $plugin_options[$key] = $value;
             	}

             	/**
               	* action 'redux/options/{opt_name}/import'
               	* @param  &array [&$plugin_options, redux_options]
               	*/

                do_action_ref_array( "redux/options/sf_neighborhood_options/import", array(&$plugin_options, $imported_options));

                $plugin_options['REDUX_COMPILER'] = time();
                unset( $plugin_options['defaults'], $plugin_options['compiler'], $plugin_options['import'], $plugin_options['import_code'] );
			    $swift_import->set_theme_options( $plugin_options );
        	}

			update_option( 'sf_neighborhood_options', $plugin_options );
		echo $file;
			wp_die();
}


function sf_import_options_2(){

			$demoid =  $_POST['demo'] ;
			$swift_import = new Swift_Import();
			$demofiles_data = $swift_import->initialize_data();
			$file = $demofiles_data['themeoptions-'.$demoid];
			echo $file;
			$import = file_get_contents( $file );
			$imported_options = array();

			if ( !empty( $import ) ) {
                $imported_options = json_decode( htmlspecialchars_decode( $import ), true );
         	}

			$plugin_options['REDUX_imported'] = 1;
        	foreach($imported_options as $key => $value) {
					$plugin_options[$key] = $value;
        	}

			if(!empty($imported_options) && is_array($imported_options) && isset($imported_options['redux-backup']) && $imported_options['redux-backup'] == '1' ) {
            	 $plugin_options['REDUX_imported'] = 1;
             	foreach($imported_options as $key => $value) {
						  $plugin_options[$key] = $value;
             	}

             	/**
               	* action 'redux/options/{opt_name}/import'
               	* @param  &array [&$plugin_options, redux_options]
               	*/

                do_action_ref_array( "redux/options/sf_neighborhood_options/import", array(&$plugin_options, $imported_options));

                $plugin_options['REDUX_COMPILER'] = time();
                unset( $plugin_options['defaults'], $plugin_options['compiler'], $plugin_options['import'], $plugin_options['import_code'] );
			    $swift_import->set_theme_options( $plugin_options );
        	}

			update_option( 'sf_neighborhood_options', $plugin_options );

			wp_die();
}

function sf_import_colors() {

			global $plugin_path;
			$demoid =  $_POST['demo'] ;
			$plugin_path = dirname(__FILE__);
			$swift_import = new Swift_Import();
			$demofiles_data = $swift_import->initialize_data();
			$file = $demofiles_data['colors-'.$demoid];
			$import = file_get_contents( $file );

			if ( !empty( $import ) ) {
	        	$imported_options = json_decode( htmlspecialchars_decode( $import ), true );
        	}
			$sf_customizer_options = array();
			if( !empty( $imported_options ) && is_array( $imported_options ) )  {

            	    foreach($imported_options as $key => $value) {

						update_option($key, $value);

                	}
			}

			wp_die();
}

function sf_import_content() {

			global $wpdb; // this is how you get access to the database
    		$sf_import = new Swift_Import();

    		if ( !empty($_POST['xml']) )
				$file =  $_POST['xml'] ;

			if ( isset($_POST['demo']) )
				$demoid =  $_POST['demo'] ;

		    if ( !class_exists('WP_Importer') ) {

            	$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            	require_once($class_wp_importer);
    		}
    		ob_start();


            require_once(dirname(__FILE__) . '/includes/class.wordpress-importer.php');
            $swift_import = new WP_Import();
            set_time_limit(0);
            $swift_import->fetch_attachments = true;

            if ( isset($_POST['menus']) && $_POST['menus'] ){
             	$path = dirname(__FILE__) . '/demofiles/demo' .$demoid  . '/demo-' . $demoid . '-pagesmenus.xml.gz';
            }
            else{
				$path = dirname(__FILE__) . '/demofiles/demo' .$demoid  . '/' . $file;
			}


         	$returned_value = $swift_import->import($path);


            if ( is_wp_error($returned_value) ){
                echo "An Error Occurred During Import";
            }
            else {
            	ob_get_clean();
                echo  "Content imported successfully - " . $path;

                if ( isset($_POST['menus']) && $_POST['menus'] ){

					$sf_import->assign_menus_to_locations($demoid);
                	$static_frontpage = get_page_by_title( 'Home' );
					update_option( 'page_on_front', $static_frontpage->ID );
					update_option( 'show_on_front', 'page' );

		 		}
     		}



			wp_die();
}

// Updater
require_once('wp-updates-plugin.php');
new WPUpdatesPluginUpdater_1008( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));

//Message displayed when Neighborhood theme is not active
function admin_notice_message(){
		echo '<div class="updated"><p>Neighborhood theme must be instaled to use this plugin. The import functionalities are disabled.</p></div>';
}

function swift_importer_menu_page(){
    add_menu_page( 'Neighborhood Demo Content', 'Neighborhood Demos', 'manage_options', 'admin.php?import=swiftdemo', '', plugin_dir_url(__FILE__).'/assets/images/logo.png');
}

add_action( 'admin_menu', 'swift_importer_menu_page' );

if ( ! defined( 'WP_LOAD_IMPORTERS' ) )
	return;

// include Widget data class
if (!class_exists( 'Swift_Widget_Data' )) {
	require dirname( __FILE__ ) . '/class-widget-data.php';
}

function swift_importer_init() {

	if ( !function_exists('sf_is_neighborhood') ) {
		//add_action('admin_notices', 'admin_notice_message');
	}

	load_plugin_textdomain( 'wordpress-importer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * WordPress Importer object for registering the import callback
	 * @global Swift_Import $Swift_Import
	 */
	$GLOBALS['Swift_Import'] = new Swift_Import();

	register_importer( 'swiftdemo', 'Neighborhood Demo Content', __("Import demo content for Neighborhood by Swift Ideas. 1 click and you're ready to go!  <strong>By Swift Ideas</strong>", 'wordpress-importer'), array( $GLOBALS['Swift_Import'], 'dispatch' ) );

define( "WIDGET_DATA_MIN_PHP_VER", '5.3.0' );

register_activation_hook( __FILE__, 'swift_widget_data_activation' );

function swift_widget_data_activation() {
	if ( version_compare( phpversion(), WIDGET_DATA_MIN_PHP_VER, '<' ) ) {
		die( sprintf( "The minimum PHP version required for this plugin is %s", WIDGET_DATA_MIN_PHP_VER ) );
	}
}

}

add_action( 'admin_init', 'swift_importer_init' );
add_action( 'admin_enqueue_scripts', 'swift_importer_scripts');

if (!function_exists('swift_importer_scripts')){
	function swift_importer_scripts(){
			wp_enqueue_style( 'widget_data', plugins_url( '/assets/sf_importer.css', __FILE__ ) );
			wp_enqueue_script( 'widget_data', plugins_url( '/assets/sf_importer.js', __FILE__ ), array( 'jquery' ) );
		}
}
