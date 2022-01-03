<?php

/**
 * Plugin Name:       Grassroots Volunteering Plugin
 * Plugin URI:        http://grassrootsvolunteering.org/
 * Description:       Create, display, and manage Grassroots Volunteering business and volunteer opportunity databases.
 * Version:           0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Bob Grim
 * Author URI:        https://candolatitude.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gv-pods-plugin
 */

 namespace GVPlugin;

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Plugin wide defines
 */

define( 'GV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

function gv_debug( $msg ) {
  if ( is_array( $msg ) || is_object( $msg ) || is_bool( $msg ) ) {
    $msg = var_export( $msg, true );
  }
  error_log( 'gv-plugin: ' . $msg ); 
}

/**
 * Instantiate and run the plugin
 */

if ( ! class_exists( 'GV_Plugin' ) ) {
  require_once GV_PLUGIN_PATH . 'classes/class-gv-plugin.php';

  $gv = new GV_Plugin();
  register_activation_hook( __FILE__, array( $gv, 'gv_activation' ) );
  register_deactivation_hook( __FILE__, array( $gv, 'gv_deactivation' ) );
  $gv->run();
}