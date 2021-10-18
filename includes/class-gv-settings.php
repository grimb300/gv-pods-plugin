<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Settings {

  /* **********
   * Properties
   * **********/

  private $legacy_to_wp_ids = array(
    'business_location' => array(),
  );

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
    // gv_debug( sprintf( 'There are %d files uploaded', count( $uploaded_files ) ) );
    // gv_debug( $uploaded_files );

    // Check at least one file has been uploaded
    if ( empty( $uploaded_files ) ) {
      wp_die( __( 'Please upload at least one expected file to import' ) );
    }

    // Parse the uploaded json file(s)
    foreach( $uploaded_files as $file_info ) {
      $records = (array) json_decode( file_get_contents( $file_info[ 'tmp_name' ] ), true );
      if ( empty( $records ) ) {
        // gv_debug( sprintf( 'Uploaded an empty json file: %s', $file_info[ 'name' ] ) );
      } else {
        // gv_debug( sprintf( 'Uploaded %d records from %s', count( $records ), $file_info[ 'name' ] ) );

        // Expected business keys
        $expected_business_keys = array(
          "address",
          "business_types",
          "description",
          "hours",
          "id",
          "latitude",
          "locations",
          "longitude",
          "name",
          "paired_volunteer_opportunities",
          "phone_numbers",
          "short_location",
          "slug",
          "url"
        );

        // Check to see which json file this is
        // gv_debug( 'The first record in the array is:' );
        // gv_debug( $records[0] );
        if ( empty( array_diff( $expected_business_keys, array_keys( $records[0] ) ) ) ) {
          // gv_debug( 'This is an array of businesses' );
          $this->import_businesses( $records );
        } else {
          // gv_debug( 'Unknown array type' );
        }
      }
    }

    // Redirect back to the settings page
    wp_safe_redirect( admin_url( 'options-general.php?page=gv_settings' ) );
    exit;
  }

  private function import_businesses( $businesses = array() ) {
    // gv_debug( sprintf( 'Importing %d businesses', count( $businesses ) ) );

    foreach( $businesses as $business ) {
      // Convert legacy business types into an array of business_type IDs
      $business_type_ids = array_map( array( $this, 'convert_legacy_business_types' ), $business[ 'business_types' ] );
      // gv_debug( 'Converted business_type IDs' );
      // gv_debug( $business_type_ids );

      // Stringify the legacy_business_types field
      $stringified_business_types = array_map( function ( $type ) {
        return sprintf( '%s: %s', $type[ 'id' ], $type[ 'name' ] );
      }, $business[ 'business_types' ] );

      // Sort the locations based on ancestry_depth
      // Have to make a copy of the array since usort sorts in place
      $sorted_locations = $business[ 'locations' ];
      usort( $sorted_locations, function( $a, $b ) {
        $a_depth = $a[ 'ancestry_depth' ];
        $b_depth = $b[ 'ancestry_depth' ];
        if ( $a_depth === $b_depth ) return 0;
        return $a_depth < $b_depth ? -1 : 1;
      } );

      // Convert legacy locations into an array of business_location IDs
      $business_location_ids = array_map( array( $this, 'convert_legacy_business_locations' ), $sorted_locations );

      // Stringify the legacy_locations field
      $stringified_locations = array_map( function ( $location ) {
        return sprintf( '%s: %s', $location[ 'id' ], $location[ 'name' ] );
      }, $sorted_locations );

      // Stringify the legacy_paired_opportunities field
      $stringified_paired_opportunities = array_map( function ( $pair ) {
        return sprintf( '%s: %s', $pair[ 'id' ], $pair[ 'name' ] );
      }, $business[ 'paired_volunteer_opportunities' ] );

      $post_id = wp_insert_post( array(
        'post_content' => $business[ 'description' ][ 'full' ][ 'html' ],
        'post_title' => $business[ 'name' ],
        'post_status' => 'publish',
        'post_type' => 'business',
        'post_name' => $business[ 'slug' ],
        'meta_input' => array(
          'business_name' => $business[ 'name' ],
          // 'location' => serialize( array(
          //   'text' => "",
          //   'geo' => array(
          //     'lat' => $business[ 'latitude' ],
          //     'lng' => $business[ 'longitude' ],
          //   ),
          // ) ),
          'location' => array(
            'text' => "",
            'geo' => array(
              'lat' => $business[ 'latitude' ],
              'lng' => $business[ 'longitude' ],
            ),
          ),
          'short_location' => $business[ 'short_location' ],
          'address' => $business[ 'address' ],
          'description' => $business[ 'description' ][ 'full' ][ 'html' ],
          'hours' => $business[ 'hours' ][ 'html' ],
          'phone_numbers' => serialize( $business[ 'phone_numbers' ] ),
          'url' => $business[ 'url' ],
          'legacy_id' => $business[ 'id' ],
          'legacy_business_types' => implode( ', ', $stringified_business_types ),
          'legacy_locations' => implode( ' > ', $stringified_locations ),
          'legacy_paired_volunteer_opportunity_id' => implode( ', ', $stringified_paired_opportunities ),
          'legacy_slug' => $business[ 'slug' ],
        ),
        'tax_input' => array(
          'business_type' => $business_type_ids,
          'business_location' => $business_location_ids,
        ),
      ) );
    }
  }

  private function convert_legacy_business_types( $legacy_term ) {
    return $this->convert_legacy_terms( 'business_type', $legacy_term );
  }

  private function convert_legacy_business_locations( $legacy_term ) {
    return $this->convert_legacy_terms( 'business_location', $legacy_term );
  }

  private function convert_legacy_terms( $taxonomy, $legacy_term ) {
    $debug_msg = sprintf( '%s id %s ', $taxonomy, $legacy_term[ 'id' ] );

    // Check to see if this term has already been converted
    $term_id = $this->get_term_id_from_legacy_id( $taxonomy, $legacy_term[ 'id' ] );
    
    // If the term ID is greater than 0, return the converted ID
    if ( $term_id > 0 ) {
      $debug_msg .= sprintf( 'has already been created, new id %s', $term_id );
      if ( 'business_location' === $taxonomy ) {
        gv_debug( $debug_msg );
      }
      return $term_id;
    }
    
    // If the term ID is 0, create a new term
    if ( 0 === $term_id ) {
      $debug_msg .= 'will be created ';
      // If this is a hierarchial taxonomy, get the parent
      // NOTE: This assumes the parent has already been created due to the array of terms
      //       being fed to this mapping function are in parent->child order
      $parent_term_id = 0;
      if ( ! empty( $legacy_term[ 'ancestry' ] ) ) {
        $debug_msg .= sprintf( 'with ancestry %s ', $legacy_term[ 'ancestry' ] );
        $ancestry = explode( '/', $legacy_term[ 'ancestry' ] );
        //  gv_debug( 'Ancestry is: ' . $legacy_term[ 'ancestry' ] );
        //  gv_debug( $ancestry );
        $parent_term_id = $this->get_term_id_from_legacy_id( $taxonomy, end( $ancestry ) );
        $debug_msg .= sprintf( 'parent id is %s ', $parent_term_id );
      }
      
      $inserted_term = wp_insert_term(
        $legacy_term[ 'name' ],
        $taxonomy,
        array( 'parent' => $parent_term_id > 0 ? $parent_term_id : 0, )
      );
      if ( is_wp_error( $inserted_term ) ) {
        gv_debug( sprintf( 'While inserting %s "%s", error returned:', $taxonomy, $legacy_term[ 'name' ] ) );
        gv_debug( $inserted_term->get_error_messages() );
        return 0;
      }
      update_term_meta( $inserted_term[ 'term_id' ], 'legacy_id', $legacy_term[ 'id' ] );
      $debug_msg .= sprintf( 'newly created term id %s', $inserted_term[ 'term_id' ] );
      if ( 'business_location' === $taxonomy ) {
        gv_debug( $debug_msg );
      }
      $this->legacy_to_wp_ids[ $taxonomy ][ $legacy_term[ 'id' ] ] = $inserted_term[ 'term_id' ];
      return $inserted_term[ 'term_id' ];
    }
    
    // Else, something went wrong
    gv_debug( sprintf( 'Something went wrong "%s" matches multiple %ss', $legacy_term[ 'name' ], $taxonomy ) );
    return 0;
  }

  private function get_term_id_from_legacy_id( $taxonomy, $legacy_id ) {
    $found_term = 0;
    if ( array_key_exists( $legacy_id, $this->legacy_to_wp_ids[ $taxonomy ] ) ) {
      $found_term = $this->legacy_to_wp_ids[ $taxonomy ][ $legacy_id ];
    }

    // $found_term = get_terms( array(
    //   'taxonomy' => $taxonomy,
    //   'fields' => 'ids',
    //   'meta_key' => 'legacy_id',
    //   'meta_value' => $legacy_id,
    // ) );
    if ( 'business_location' === $taxonomy ) {
      gv_debug( sprintf( 'Searching for legacy id %s in %s', $legacy_id, $taxonomy ) );
      gv_debug( $found_term );
    }
    return $found_term;
    
    // if ( is_wp_error( $found_term ) ) {
    //   gv_debug( 'Error searching for legacy ID:' );
    //   gv_debug( $found_term->get_error_messages() );
    //   return -1;
    // }
    
    // gv_debug( sprintf( 'Found %s matching term(s):', count( $found_term ) ) );
    // gv_debug( $found_term );
    
    // If one term ID found, return that ID
    // if ( 1 === count( $found_term ) ) {
    //   return $found_term[ 0 ];
    // }
    
    // If no term IDs found, return 0
    // if ( 0 === count( $found_term ) ) {
    //   return 0;
    // }
    
    // If multiple term IDs found, something went wrong, return -1
    // gv_debug( sprintf( 'Something went wrong, legacy ID "%s" matches multiple %ss', $legacy_id, $taxonomy ) );
    // return -1;
  }
}