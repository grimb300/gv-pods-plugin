<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Plugin {

  /* **********
   * Properties
   * **********/

  public $gv_settings;
  public $gv_edit_screen;
  public $gv_blocks;

  /* *******
   * Methods
   * *******/

  // Constructor
  public function __construct() {
    // gv_debug( 'GV_Plugin constructor' );

    // Make sure that jQuery is enqueued in the head
    // This is necessary because the pods maps plugin is adding jQuery code in the body of the post
    // Really, this is just a workaround and should be fixed in the git repo
    add_action( 'wp_enqueue_scripts', array( $this, 'gv_jquery_in_head' ), 1 );

    // Instantiate the settings class
    require_once GV_PLUGIN_PATH . 'classes/class-gv-settings.php';
    $this->gv_settings = new GV_Settings();
    
    // Instantiate the edit screen customization class
    // TODO: Should this only be instantiated on admin screens?
    require_once GV_PLUGIN_PATH . 'classes/class-gv-edit-screen.php';
    $this->gv_edit_screen = new GV_Edit_Screen();

    // // Example from the Pods docs
    // require_once GV_PLUGIN_PATH . 'examples/pods-custom-blocks.php';
    
    // Instantiate the custom GV blocks
    require_once GV_PLUGIN_PATH . 'classes/class-gv-blocks.php';
    // require_once GV_PLUGIN_PATH . 'classes/class-gv-blocks-new.php';
    $this->gv_blocks = new GV_Blocks();

    // Example from the Pods docs (testing ordering)
    // require_once GV_PLUGIN_PATH . 'examples/pods-custom-blocks.php';

    // Register the GV Phone Numbers field type
    add_action( 'plugins_loaded', array( $this, 'gv_phone_numbers_field_init'), 20 );

  }

  public function gv_phone_numbers_field_init() {
    // Return if Pods not active
    // TODO: This check should be done earlier, I think
    if ( ! function_exists( 'pods' ) || ! function_exists( 'pods_register_field_type' ) | ! defined( 'PODS_DIR' ) ) {
      return;
    }

    add_filter( 'pods_api_field_types', array( $this, 'gv_phone_numbers_field_add_field_type' ) );

    pods_register_field_type( 'gv_phone_numbers', GV_PLUGIN_PATH . 'classes/fields/gv-phone-numbers.php' );
  }

  public function gv_phone_numbers_field_add_field_type( $types ) {
    $types[] = 'gv_phone_numbers';
    return $types;
  }

  public function gv_jquery_in_head() {
    wp_enqueue_script( 'jquery', false, array(), false, false );
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
      // gv_debug( 'Found the following pods:' );
      // gv_debug( array_map( function( $pod ) {
      //   return $pod[ 'name' ];
      // }, $all_pods ) );
      gv_debug( 'Pods details:' );
      foreach( $all_pods as $pod ) {
        if ( 'business' === $pod[ 'name' ] ) {
          gv_debug( 'Business CPT' );
          gv_debug( $pod );
        }
        if ( 'table_business' === $pod[ 'name' ] ) {
          gv_debug( 'Business Custom Table' );
          gv_debug( $pod );
        }
      }
    }
  }

  // Plugin deactivation
  public function gv_deactivation() {
    gv_debug( 'GV_Plugin deactivate!' );
  }
}