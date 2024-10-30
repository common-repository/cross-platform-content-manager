<?php
/**
 * Plugin Name: Cross Platform Content Manager
 * Description: A fast and reliable solution for synchronizing all types of posts and their taxonomies across remote sites.
 * Version: 1.0.0
 * Author: Ione Digital
 * Author URI: https://ione.digital
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: cross-platform-content-manager
 * 
 * Main plugin file for Cross Platform Content Manager.
 * This file represents the bootstrap of the plugin, defining constants,
 * including dependencies, and setting up the core plugin classes.
 *
 * @package Cross Platform Content Manager
 */

// Prevent direct access to the script.
defined( 'ABSPATH' ) or die( 'Access denied!' );

// Define the current version of the plugin.
define( 'icross_version', '1.0.0' );

// Define the directory path of the plugin.
define( 'icross_plugin_dir', plugin_dir_path( __FILE__ ) );

// Define the URL path for the assets directory.
define( 'icross_assets_url', plugin_dir_url( __FILE__ ) . 'assets' );

// Include the Sync Manager class responsible for orchestrating the sync processes.
include_once icross_plugin_dir . 'includes/sync/class-sync-manager.php';

// Include the Settings class for managing plugin settings.
include_once icross_plugin_dir . 'includes/settings/class-settings.php';

// Include additional classes for media, taxonomies, metadata, and post synchronization.
include_once icross_plugin_dir . 'includes/api/class-media-sync.php';
include_once icross_plugin_dir . 'includes/api/class-taxonomies-sync.php';
include_once icross_plugin_dir . 'includes/api/class-post-sync.php';

// Instantiate core plugin classes.
$sync_manager = new iCross_SyncManager();
// $api_handler = new iCross_APIHandler();
$settings_page = new iCross_SettingsPage();

// Instantiate synchronization classes for different content types.
$media_sync = new iCross_Media_Sync();
$taxonomies_sync = new iCross_Taxonomy_Sync();
$post_sync = new iCross_Post_Sync($media_sync, $taxonomies_sync);

// Add a settings link to the plugin action links.
function icross_add_settings_link( $links ) {
  $settings_link = '<a href="admin.php?page=icross-settings">' . esc_html__( 'Settings' ) . '</a>';
  array_unshift( $links, $settings_link );
  return $links;
}
  
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'icross_add_settings_link' );
  
// Load the plugin's text domain for localization.
function icross_load_textdomain() {
  load_plugin_textdomain('cross-platform-content-manager', false, basename(dirname(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'icross_load_textdomain');