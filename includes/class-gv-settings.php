<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Settings {

  /* **********
   * Properties
   * **********/

  // Arrays indexed by the data structure and legacy ID, final value is WP ID
  private $legacy_id_cache = array();

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

        // Expected volunteer opportunity keys
        $expected_volunteer_opportunity_keys = array(
          "contact_info",
          "cost_label",
          "cost_suggestion",
          "description",
          "duration_notes",
          "durations",
          "facebook_url",
          "fees_notes",
          "id",
          "image",
          "image_id",
          "locations",
          "max_duration",
          "min_duration",
          "name",
          "organization_url",
          "other_ways_to_help",
          "paired_businesses",
          "short_location",
          "slug",
          "twitter_username",
          "types",
          "volunteer_url"
        );

        // Check to see which json file this is
        // gv_debug( 'The first record in the array is:' );
        // gv_debug( $records[0] );
        if ( empty( array_diff( $expected_business_keys, array_keys( $records[0] ) ) ) ) {
          // gv_debug( 'This is an array of businesses' );
          $this->import_businesses( $records );
        } elseif ( empty( array_diff( $expected_volunteer_opportunity_keys, array_keys( $records[0] ) ) ) ) {
          // gv_debug( 'This is an array of volunteer opportunities' );
          $this->import_volunteer_opportunities( $records );
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

      // Check to see if this legacy ID has already been converted
      $post_id = $this->get_post_id_from_legacy_id( 'business', $business[ 'id' ] );
      if ( 0 === $post_id ) {
        // Business doesn't exist, insert
        $post_id = wp_insert_post( array(
          'post_content' => $business[ 'description' ][ 'full' ][ 'html' ],
          'post_title' => $business[ 'name' ],
          'post_status' => 'publish',
          'post_type' => 'business',
          'post_name' => $business[ 'slug' ],
          'meta_input' => array(
            'business_name' => $business[ 'name' ],
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

      // Update the legacy ID cache
      $this->add_id_to_legacy_id_cache( 'business', $business[ 'id' ], $post_id );
    }
    // gv_debug( 'End of businesses, legacy ID cache:' );
    // gv_debug( $this->legacy_id_cache );
  }

  private function import_volunteer_opportunities( $volunteer_opportunities = array() ) {
    // gv_debug( sprintf( 'Importing %d volunteer opportunity', count( $volunteer_opportunities ) ) );

    foreach( $volunteer_opportunities as $vol_opp ) {
      // Convert legacy business types into an array of business_type IDs
      $volunteer_type_ids = array_map( array( $this, 'convert_legacy_volunteer_types' ), $vol_opp[ 'types' ] );
      // gv_debug( 'Converted volunteer_type IDs' );
      // gv_debug( $volunteer_type_ids );

      // Stringify the legacy_volunteer_types field
      $stringified_volunteer_types = array_map( function ( $type ) {
        return sprintf( '%s: %s', $type[ 'id' ], $type[ 'name' ] );
      }, $vol_opp[ 'types' ] );

      // Sort the locations based on ancestry_depth
      // Have to make a copy of the array since usort sorts in place
      $sorted_locations = $vol_opp[ 'locations' ];
      usort( $sorted_locations, function( $a, $b ) {
        $a_depth = $a[ 'ancestry_depth' ];
        $b_depth = $b[ 'ancestry_depth' ];
        if ( $a_depth === $b_depth ) return 0;
        return $a_depth < $b_depth ? -1 : 1;
      } );

      // Convert legacy locations into an array of volunteer_location IDs
      $volunteer_location_ids = array_map( array( $this, 'convert_legacy_volunteer_locations' ), $sorted_locations );

      // Stringify the legacy_locations field
      $stringified_locations = array_map( function ( $location ) {
        return sprintf( '%s: %s', $location[ 'id' ], $location[ 'name' ] );
      }, $sorted_locations );

      // Convert legacy durations into an array of volunteer_duration IDs
      $volunteer_duration_ids = array_map( array( $this, 'convert_legacy_volunteer_durations' ), $vol_opp[ 'durations' ] );

      // Stringify the legacy_durations field
      $stringified_durations = array_map( function( $duration ) {
        return sprintf( '%s: %s', $duration[ 'id' ], $duration[ 'name' ] );
      }, $vol_opp[ 'durations' ] );

      // Convert legacy cost labels into a volunteer_cost_label ID
      $volunteer_cost_label_ids = $this->convert_legacy_volunteer_cost_labels( $vol_opp[ 'cost_label' ] );

      // Stringify the legacy_cost_label field
      $cost_suggestion = $vol_opp[ 'cost_label' ][ 'cost_suggestion' ];
      $label = 0 === $cost_suggestion ? "Free" : $vol_opp[ 'cost_label' ][ 'label' ];
      $stringified_cost_label = sprintf( '%s: %s', $cost_suggestion, $label );

      // Stringify the legacy_paired_businesses field
      $stringified_paired_businesses = array_map( function ( $pair ) {
        return sprintf( '%s: %s', $pair[ 'id' ], $pair[ 'name' ] );
      }, $vol_opp[ 'paired_businesses' ] );

      // Check to see if this legacy ID has already been converted
      $post_id = $this->get_post_id_from_legacy_id( 'vol_opportunity', $vol_opp[ 'id' ] );
      // gv_debug( sprintf( 'Legacy vol opp ID %s returned post ID %s', $vol_opp[ 'id' ], $post_id ) );
      if ( 0 === $post_id ) {
        // Business doesn't exist, insert
        $post_id = wp_insert_post( array(
          'post_content' => $vol_opp[ 'description' ][ 'full' ][ 'html' ],
          'post_title' => $vol_opp[ 'name' ],
          'post_status' => 'publish',
          'post_type' => 'vol_opportunity',
          'post_name' => $vol_opp[ 'slug' ],
          'meta_input' => array(
            'volunteer_name' => $vol_opp[ 'name' ],
            'short_location' => $vol_opp[ 'short_location' ],
            'organization_url' => $vol_opp[ 'organization_url' ],
            'volunteer_url' => $vol_opp[ 'volunteer_url' ],
            'facebook_url' => $vol_opp[ 'facebook_url' ],
            'twitter_username' => $vol_opp[ 'twitter_username' ],
            'min_duration' => $vol_opp[ 'min_duration' ],
            'max_duration' => $vol_opp[ 'max_duration' ],
            'duration_notes' => $vol_opp[ 'duration_notes' ][ 'html' ],
            'description' => $vol_opp[ 'description' ][ 'full' ][ 'html' ],
            'cost_suggestion' => $vol_opp[ 'cost_suggestion' ],
            'fees_notes' => $vol_opp[ 'fees_notes' ][ 'html' ],
            'other_ways_to_help' => $vol_opp[ 'other_ways_to_help' ][ 'html' ],
            'contact_info' => $vol_opp[ 'contact_info' ],
            'legacy_id' => $vol_opp[ 'id' ],
            'legacy_volunteer_types' => implode( ', ', $stringified_volunteer_types ),
            'legacy_locations' => implode( ' > ', $stringified_locations ),
            'legacy_durations' => implode( ', ', $stringified_durations ),
            'legacy_cost_label' => $stringified_cost_label,
            'legacy_paired_business_id' => implode( ', ', $stringified_paired_businesses ),
            'legacy_image' => $vol_opp[ 'image' ][ 'image' ],
            'legacy_image_id' => $vol_opp[ 'image_id' ],
            'legacy_slug' => $vol_opp[ 'slug' ],
          ),
          'tax_input' => array(
            'volunteer_type' => $volunteer_type_ids,
            'volunteer_location' => $volunteer_location_ids,
            'volunteer_cost_label' => $volunteer_cost_label_ids,
            'volunteer_duration' => $volunteer_duration_ids,
          ),
        ), true );
      }

      // Update the legacy ID cache
      $this->add_id_to_legacy_id_cache( 'vol_opportunity', $vol_opp[ 'id' ], $post_id );
    }
    // gv_debug( 'End of volunteer opportunities, legacy ID cache:' );
    // gv_debug( $this->legacy_id_cache );
  }

  private function convert_legacy_business_types( $legacy_term ) {
    // Pull the term meta values out of legacy_term
    $term_meta = array( 'legacy_id' => $legacy_term[ 'id' ] );
    return $this->convert_legacy_terms( 'business_type', $legacy_term, $term_meta );
  }

  private function convert_legacy_business_locations( $legacy_term ) {
    // Pull the term meta values out of legacy_term
    $term_meta = array( 'legacy_id' => $legacy_term[ 'id' ] );
    return $this->convert_legacy_terms( 'business_location', $legacy_term, $term_meta );
  }

  private function convert_legacy_volunteer_types( $legacy_term ) {
    // Pull the term meta values out of legacy_term
    $term_meta = array( 'legacy_id' => $legacy_term[ 'id' ] );
    return $this->convert_legacy_terms( 'volunteer_type', $legacy_term, $term_meta );
  }

  private function convert_legacy_volunteer_locations( $legacy_term ) {
    // Pull the term meta values out of legacy_term
    $term_meta = array( 'legacy_id' => $legacy_term[ 'id' ] );
    return $this->convert_legacy_terms( 'volunteer_location', $legacy_term, $term_meta );
  }

  private function convert_legacy_volunteer_cost_labels( $legacy_term ) {
    // convert_legacy_terms() expects the term name to be in $legacy_term[ 'name' ]
    $legacy_term[ 'name' ] = $legacy_term[ 'label' ];
    // If cost_suggestion is 0, the label is blank. Special case this to "Free"
    if ( 0 === $legacy_term[ 'cost_suggestion' ] ) {
      $legacy_term[ 'name' ] = "Free";
    }
    // Pull the term meta values out of legacy_term
    $term_meta = array(
      'cost_suggestion' => $legacy_term[ 'cost_suggestion' ],
      'legacy_id' => $legacy_term[ 'id' ]
    );
    return $this->convert_legacy_terms( 'volunteer_cost_label', $legacy_term, $term_meta );
  }

  private function convert_legacy_volunteer_durations( $legacy_term ) {
    // The 'name'field in the JSON has some annoying dashes between each word, strip those out
    $name = implode( ' ', explode( '-', $legacy_term[ 'name' ] ) );
    $legacy_term[ 'name' ] = $name;
    // Pull the term meta values out of legacy_term
    $term_meta = array(
      'min' => $legacy_term[ 'min' ],
      'min_units' => $legacy_term[ 'unit' ],
      'max' => $legacy_term[ 'max' ],
      'max_units' => $legacy_term[ 'unit' ],
      'min_in_days' => $legacy_term[ 'min_in_days' ],
      'max_in_days' => $legacy_term[ 'max_in_days' ],
      'legacy_id' => $legacy_term[ 'id' ]
    );
    return $this->convert_legacy_terms( 'volunteer_duration', $legacy_term, $term_meta );
  }

  private function convert_legacy_terms( $taxonomy, $legacy_term, $term_meta = array() ) {
    // Get the interesting data out of the legacy_term array
    $legacy_id = $legacy_term[ 'id' ];
    $name = $legacy_term[ 'name' ];
    $ancestry = empty( $legacy_term[ 'ancestry' ] ) ? '' : $legacy_term[ 'ancestry' ];
    $parent_id = 0;
    if ( ! empty( $legacy_term[ 'ancestry' ] ) ) {
      $ancestry = explode( '/', $legacy_term[ 'ancestry' ] );
      $parent_id = $this->get_term_id_from_legacy_id( $taxonomy, end( $ancestry ) );
      // FIXME: Making a possibly wrong assumption that the parent has already been converted
      if ( $parent_id <= 0 ) {
        // gv_debug( sprintf( 'Expected %s term %s to have a valid parent', $taxonomy, $name ) );
      }
    }

    // Check to see if this term has already been converted
    $term_id = $this->get_term_id_from_legacy_id( $taxonomy, $legacy_id );
    
    // If the returned term ID is 0, add the missing term to the database
    if ( 0 === $term_id  ) {
      // The args array contains the parent ID, if it exists
      $args = $parent_id > 0 ? array( 'parent' => $parent_id ) : array();
      $term_id = $this->add_legacy_term_to_database( $name, $taxonomy, $args, $term_meta );
    }
    
    // Update the legacy ID cache
    $this->add_id_to_legacy_id_cache( $taxonomy, $legacy_id, $term_id );

    // Return the term ID
    return $term_id;
  }

  private function get_id_from_legacy_id_cache( $data_structure, $legacy_id ) {
    // Check this data structure is in the cache
    if ( array_key_exists( $data_structure, $this->legacy_id_cache ) ) {
      // Check this legacy ID is in the cache
      if ( array_key_exists( $legacy_id, $this->legacy_id_cache[ $data_structure ] ) ) {
        // Return the WP ID
        return $this->legacy_id_cache[ $data_structure ][ $legacy_id ];
      }
    }

    // The cache doesn't have the WP ID, return 0
    return 0;
  }

  private function add_id_to_legacy_id_cache( $data_structure, $legacy_id, $wp_id ) {
    $this->legacy_id_cache[ $data_structure ][ $legacy_id ] = $wp_id;
  }

  private function get_id_from_term_database( $taxonomy, $legacy_id ) {
    // gv_debug( sprintf( 'Searching for a %s with legacy ID %s', $taxonomy, $legacy_id ) );
    // Search the database for this legacy ID
    $found_term = get_terms( array(
      'hide_empty' => false,
      'taxonomy' => $taxonomy,
      'fields' => 'ids',
      'meta_key' => 'legacy_id',
      'meta_value' => $legacy_id,
    ) );
    // gv_debug( sprintf( 'Found %s matching terms', count( $found_term ) ) );

    // Log error and return -1
    if ( is_wp_error( $found_term ) ) {
      gv_debug( 'Error searching for legacy ID:' );
      gv_debug( $found_term->get_error_messages() );
      return -1;
    }

    // If one term ID found, return that ID
    if ( 1 === count( $found_term ) ) {
      return $found_term[ 0 ];
    }
    
    // If no term IDs found, return 0
    if ( 0 === count( $found_term ) ) {
      return 0;
    }
    
    // If multiple term IDs found, something went wrong, return -1
    gv_debug( sprintf( 'Something went wrong, legacy ID "%s" matches multiple %ss', $legacy_id, $taxonomy ) );
    return -1;
  }

  private function get_id_from_post_database( $post_type, $legacy_id ) {
    // Search the database for this legacy ID
    $found_post = get_posts( array(
      'post_type' => $post_type,
      'fields' => 'ids',
      'meta_key' => 'legacy_id',
      'meta_value' => $legacy_id,
    ) );

    // Log error and return -1
    if ( is_wp_error( $found_post ) ) {
      gv_debug( 'Error searching for legacy ID:' );
      gv_debug( $found_post->get_error_messages() );
      return -1;
    }

    // If one post ID found, return that ID
    if ( 1 === count( $found_post ) ) {
      return $found_post[ 0 ];
    }
    
    // If no post IDs found, return 0
    if ( 0 === count( $found_post ) ) {
      return 0;
    }
    
    // If multiple post IDs found, something went wrong, return -1
    gv_debug( sprintf( 'Something went wrong, legacy ID "%s" matches multiple %ss', $legacy_id, $taxonomy ) );
    return -1;
  }

  private function add_legacy_term_to_database( $name, $taxonomy, $args = array(), $meta = array() ) {
    $inserted_term = wp_insert_term( $name, $taxonomy, $args );
    if ( is_wp_error( $inserted_term ) ) {
      gv_debug( sprintf( 'While inserting %s "%s", error returned:', $taxonomy, $name ) );
      gv_debug( $inserted_term->get_error_messages() );
      return 0;
    }
    foreach( $meta as $meta_key => $meta_value ) {
      update_term_meta( $inserted_term[ 'term_id' ], $meta_key, $meta_value );
    }
    return $inserted_term[ 'term_id' ];
  }

  private function get_term_id_from_legacy_id( $taxonomy, $legacy_id ) {
    // Check the cache, if returned ID > 0, return that ID
    $cached_term_id = $this->get_id_from_legacy_id_cache( $taxonomy, $legacy_id );
    if ( $cached_term_id > 0 ) {
      return $cached_term_id;
    }

    // Check the database, return the result
    $db_term_id = $this->get_id_from_term_database( $taxonomy, $legacy_id );
    return $db_term_id;
  }

  private function get_post_id_from_legacy_id( $post_type, $legacy_id ) {
    // Check the cache, if returned ID > 0, return that ID
    $cached_term_id = $this->get_id_from_legacy_id_cache( $post_type, $legacy_id );
    if ( $cached_term_id > 0 ) {
      return $cached_term_id;
    }

    // Check the database, return the result
    $db_term_id = $this->get_id_from_post_database( $post_type, $legacy_id );
    return $db_term_id;
  }
}