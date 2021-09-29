<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Settings {

  /* **********
   * Properties
   * **********/

  /* *******
   * Methods
   * *******/

  // Constructor
  public function __construct() {
    // gv_debug( 'GV_Settings constructor' );

    // Register settings
    add_action( 'admin_init', array( $this, 'gv_register_settings' ) );
    add_action( 'admin_menu', array( $this, 'gv_settings_menu' ) );

    // Import/Export settings
    // add_action( 'admin_init', array( $this, 'gv_process_settings_export' ) );
    // add_action( 'admin_init', array( $this, 'gv_process_settings_import' ) );
    add_action( 'admin_init', array( $this, 'gv_process_legacy_data_import' ) );
  }

  // Register the plugin settings
  public function gv_register_settings() {
    register_setting( 'gv_settings_group', 'gv_settings' );
  }

  // Register the settings page
  public function gv_settings_menu() {
    add_options_page(
      __( 'Grassroots Volunteering Settings' ),
      __( 'GV Settings' ),
      'manage_options',
      'gv_settings',
      array( $this, 'gv_settings_page' )
    );
  }

  // Render the settings page
  public function gv_settings_page() {
    $options = get_option( 'gv_settings' );
    ?>
    <div class="wrap">
      <h2><?php _e( 'Grassroots Volunteering Settings' ); ?></h2>
      <form action="options.php" method="post" class="options_form">
        <?php settings_fields( 'gv_settings_group' ); ?>
        <table class="form-table">
          <tr valign="top">
            <th scope="row">
              <label for="gv_settings[text]"><?php _e( 'GV Test Option'); ?></label>
            </th>
            <td>
              <input class="regular-text" type="text" id="gv_settings[text]" style="width:300px;"  name="gv_settings[label]" value="<?php if( isset( $options['label'] ) ) { echo esc_attr( $options['label'] ); } ?>">
  						<p class="description"><?php _e( 'Enter some text for the label here.' ); ?></p>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row">
						  <span><?php _e( 'Enable Feature' ); ?></span>
            </th>
            <td>
              <input class="checkbox" type="checkbox" id="gv_settings[enabled]" name="gv_settings[enabled]" value="1" <?php checked( 1, isset( $options['enabled'] ) ); ?>/>
						  <label for="gv_settings[enabled]"><?php _e( 'Enable some feature in this plugin?' ); ?></label>
					  </td>
          </tr>
        </table>
        <?php submit_button(); ?>
      </form>
      <div class="metabox-holder">
        <!-- <div class="postbox">
          <h3><span><?php //_e( 'Export Settings' ); ?></span></h3>
          <div class="inside">
            <p><?php //_e( 'Export the plugin settings for this site as a .json file. This allows you to easily import the configuration into another site.' ); ?></p>
            <form method="post">
						  <p><input type="hidden" name="gv_action" value="export_settings" /></p>
						  <p>
							  <?php //wp_nonce_field( 'gv_export_nonce', 'gv_export_nonce' ); ?>
							  <?php //submit_button( __( 'Export' ), 'secondary', 'submit', false ); ?>
						  </p>
            </form>
          </div>
        </div> -->
        <!-- <div class="postbox">
          <h3><span><?php //_e( 'Import Settings' ); ?></span></h3>
          <div class="inside">
            <p><?php //_e( 'Import the plugin settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.' ); ?></p>
            <form method="post" enctype="multipart/form-data">
              <p>
                <input type="file" name="import_file"/>
              </p>
              <p>
                <input type="hidden" name="gv_action" value="import_settings" />
                <?php //wp_nonce_field( 'gv_import_nonce', 'gv_import_nonce' ); ?>
                <?php //submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
              </p>
            </form>
          </div>
        </div> -->
        <div class="postbox">
          <h3><span><?php _e( 'Import Legacy Data' ); ?></span></h3>
          <div class="inside">
            <form method="post" enctype="multipart/form-data">
              <p>Import legacy businesses and/or volunteer opportunity json data</p>
              <p>
                <input type="file" name="file_import_0"/>
              </p>
              <p>
                <input type="file" name="file_import_1"/>
              </p>
              <p>
                <input type="hidden" name="gv_action" value="import_legacy_data" />
                <?php wp_nonce_field( 'gv_import_nonce', 'gv_import_nonce' ); ?>
                <?php submit_button( __( 'Import' ), 'secondary', 'submit', false ); ?>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php
  }

  // // Process a settings export that generates a .json file of the settings
  // public function gv_process_settings_export() {
  //   // Return if not an export_settings gv_action
  //   if ( empty( $_POST[ 'gv_action' ] ) || 'export_settings' !== $_POST[ 'gv_action' ] ) return;
  //   // Return if bad nonce
  //   if ( ! wp_verify_nonce( $_POST[ 'gv_export_nonce' ], 'gv_export_nonce' ) ) return;
  //   // Return if insufficient permissions
  //   if ( ! current_user_can( 'manage_options' ) ) return;

  //   // Get the settings
  //   $settings = get_option( 'gv_settings' );

  //   // This makes sure the export completes even if the user disconnects
  //   ignore_user_abort( true );

  //   // Response header
  //   nocache_headers();
  //   header( 'Content-Type: application/json; charset=utf-8' );
  //   header( 'Content-Disposition: attachment; filename=gv-settings-export-' . date( 'm-d-Y' ) . '.json' );
  //   header( 'Expires: 0' );

  //   // Return the file
  //   echo json_encode( $settings );
  //   exit;
  // }

  // // Process a settings import from a json file
  // public function gv_process_settings_import() {
  //   // Return if not an import_settings gv_action
  //   if ( empty( $_POST[ 'gv_action' ] ) || 'import_settings' !== $_POST[ 'gv_action' ] ) return;
  //   // Return if bad nonce
  //   if ( ! wp_verify_nonce( $_POST[ 'gv_import_nonce' ], 'gv_import_nonce' ) ) return;
  //   // Return if insufficient permissions
  //   if ( ! current_user_can( 'manage_options' ) ) return;

  //   // Check correct file extension
  //   $extension = end( explode( '.', $_FILES[ 'import_file' ][ 'name' ] ) );
  //   if ( 'json' !== $extension ) {
  //     wp_die( __( 'Please upload a valid .json file' ) );
  //   }

  //   // Check that a file was uploaded
  //   $import_file = $_FILES[ 'import_file' ][ 'tmp_name' ];
  //   if ( empty( $import_file ) ) {
  //     wp_die( __( 'Please upload a file to import' ) );
  //   }

  //   // Convert the json object in the uploaded file to an array and update the settings
  //   $settings = (array) json_decode( file_get_contents( $import_file ) );
  //   update_option( 'gv_settings', $settings );

  //   // Redirect back to the settings page
  //   wp_safe_redirect( admin_url( 'options-general.php?page=gv_settings' ) );
  //   exit;
  // }

  // Process a legacy data import from json files
  public function gv_process_legacy_data_import() {
    // Return if not an import_settings gv_action
    if ( empty( $_POST[ 'gv_action' ] ) || 'import_legacy_data' !== $_POST[ 'gv_action' ] ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_import_nonce' ], 'gv_import_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Get the uploaded file(s) info
    $uploaded_files = array_filter(
      $_FILES,
      function ( $file_info ) {
        // Filter out empty file inputs
        if ( empty( $file_info[ 'name' ] ) || empty( $file_info[ 'tmp_name' ] ) ) return false;

        // Check that the uploaded file is json
        $exploded_filename = explode( '.', $file_info[ 'name' ] );
        $file_extension = end( $exploded_filename );
        if ( 'json' !== $file_extension ) {
          wp_die(__( 'Please upload .json file(s). Unexpected file type: ' . $file_info[ 'name' ] ) );
        }

        // Passed all filter conditions, return true
        return true;
      }
    );
    gv_debug( sprintf( 'There are %d files uploaded', count( $uploaded_files ) ) );
    gv_debug( $uploaded_files );

    // Check at least one file has been uploaded
    if ( empty( $uploaded_files ) ) {
      wp_die( __( 'Please upload at least one expected file to import' ) );
    }

    // Parse the uploaded json file(s)
    foreach( $uploaded_files as $file_info ) {
      $records = (array) json_decode( file_get_contents( $file_info[ 'tmp_name' ] ) );
      if ( empty( $records ) ) {
        gv_debug( sprintf( 'Uploaded an empty json file: %s', $file_info[ 'name' ] ) );
      } else {
        gv_debug( sprintf( 'Uploaded %d records from %s', count( $records ), $file_info[ 'name' ] ) );
      }
    }

    // Redirect back to the settings page
    wp_safe_redirect( admin_url( 'options-general.php?page=gv_settings' ) );
    exit;
  }
}