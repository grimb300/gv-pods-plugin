<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Plugin {

  /* **********
   * Properties
   * **********/

  /* *******
   * Methods
   * *******/

  // Constructor
  public function __construct() {
    // gv_debug( 'GV_Plugin constructor' );
  }

  // Run -- probably unnecessary
  public function run() {
    // gv_debug( 'GV_Plugin run' );
  }

  // Plugin activation
  public function gv_activation() {
    gv_debug( 'GV_Plugin activate!' );
    // Check if the Pods plugin is active
    if ( ! is_plugin_active( 'pods/init.php' ) ) {
      gv_debug( 'Pods plugin is NOT active' );
      die( 'Grassroots Volunteering Plugin requires the Pods plugin to be activated' );
    } else {
      gv_debug( 'Pods plugin is active' );
      // Get all available pods
      // Using this Pods documentation:
      //   https://pods.io/docs/code/
      //   https://github.com/pods-framework/pods
      $pods_api = pods_api();
      $all_pods = $pods_api->load_pods();
      gv_debug( 'Found the following pods:' );
      gv_debug( array_map( function( $pod ) {
        return $pod[ 'name' ];
      }, $all_pods ) );
    }
  }

  // Plugin deactivation
  public function gv_deactivation() {
    gv_debug( 'GV_Plugin deactivate!' );
  }
}