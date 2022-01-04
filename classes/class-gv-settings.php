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
    // add_action( 'admin_init', array( $this, 'gv_process_legacy_data_import' ) );
    add_action( 'admin_init', array( $this, 'gv_upload_files' ) );
    add_action( 'admin_init', array( $this, 'gv_delete_files' ) );
    add_action( 'admin_init', array( $this, 'gv_import_businesses' ) );
    add_action( 'admin_init', array( $this, 'gv_import_vol_opps' ) );
    add_action( 'admin_init', array( $this, 'gv_link_bus_to_vol_opp' ) );
    add_action( 'admin_init', array( $this, 'gv_import_images' ) );
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
      </form> <!-- End Options Form -->

      <h2><?php _e( 'Import Legacy Data' ); ?></h2>
      <?php $uploaded_files = get_option( 'gv_import_uploaded_files', array() ); ?>
      <form method="post" enctype="multipart/form-data">
        <table class="form-table">
          <tr valign="top">
            <th scope="row">
              <label><?php _e( 'Businesses Data'); ?></label>
            </th>
            <td>
              <?php echo empty( $uploaded_files[ 'businesses' ] ) ? 'Empty' : $uploaded_files[ 'businesses' ]; ?> 
            </td>
            <td>
              <input type="file" name="file_import_0"/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row">
              <label><?php _e( 'Volunteer Opportunities Data'); ?></label>
            </th>
            <td>
              <?php echo empty( $uploaded_files[ 'volunteer_opportunities' ] ) ? 'Empty' : $uploaded_files[ 'volunteer_opportunities' ]; ?> 
            </td>
            <td>
              <input type="file" name="file_import_1"/>
            </td>
          </tr>
          <tr valign="top">
            <th scope="row">
              <label><?php _e( 'Images Archive'); ?></label>
            </th>
            <td>
              <?php echo empty( $uploaded_files[ 'images_archive' ] ) ? 'Empty' : $uploaded_files[ 'images_archive' ]; ?> 
            </td>
            <td>
              <input type="file" name="file_import_2"/>
            </td>
          </tr>
          <tr>
            <th></th>
            <td></td>
            <td>
              <input type="hidden" name="gv_action" value="upload_files" />
              <?php wp_nonce_field( 'gv_upload_files_nonce', 'gv_upload_files_nonce' ); ?>
              <?php submit_button( __( 'Upload Files' ) ); ?>
            </td>
          </tr>
        </table>
      </form>
      <form method="post">
        <input type="hidden" name="gv_action" value="delete_files" />
        <?php wp_nonce_field( 'gv_delete_files_nonce', 'gv_delete_files_nonce' ); ?>
        <?php submit_button( __( 'Delete Files' ), 'delete' ); ?>
      </form>
      <form method="post">
        <input type="hidden" name="gv_action" value="import_businesses" />
        <?php wp_nonce_field( 'gv_import_businesses_nonce', 'gv_import_businesses_nonce' ); ?>
        <?php submit_button( __( 'Import Businesses' ), 'secondary' ); ?>
      </form>
      <form method="post">
        <input type="hidden" name="gv_action" value="import_vol_opps" />
        <?php wp_nonce_field( 'gv_import_vol_opps_nonce', 'gv_import_vol_opps_nonce' ); ?>
        <?php submit_button( __( 'Import Vol Opps' ), 'secondary' ); ?>
      </form>
      <form method="post">
        <input type="hidden" name="gv_action" value="link_bus_to_vol_opp" />
        <?php wp_nonce_field( 'gv_link_bus_to_vol_opp_nonce', 'gv_link_bus_to_vol_opp_nonce' ); ?>
        <?php submit_button( __( 'Link Bus->Vol Opp' ), 'secondary' ); ?>
      </form>
      <form method="post">
        <input type="hidden" name="gv_action" value="import_images" />
        <?php wp_nonce_field( 'gv_import_images_nonce', 'gv_import_images_nonce' ); ?>
        <?php submit_button( __( 'Import Images' ), 'secondary' ); ?>
      </form>
    </div> <!-- .wrap -->
    <?php
  }

  // Check file extension
  private function has_file_extension( $filename, $extension ) {
    return $extension === substr( $filename, ( 0 - strlen( $extension ) ) );
  }

  // Check json file data structure
  private function is_businesses_json( $filename ) {
    $expected_business_keys = array(
      "address", "business_types", "description", "hours", "id", "latitude", "locations",
      "longitude", "name", "paired_volunteer_opportunities", "phone_numbers",
      "short_location", "slug", "url"
    );
    return $this->json_has_expected_keys( $filename, $expected_business_keys ); 
  }
  private function is_volunteer_opportunities_json( $filename ) {
    $expected_volunteer_opportunity_keys = array(
      "contact_info", "cost_label", "cost_suggestion", "description", "duration_notes",
      "durations", "facebook_url", "fees_notes", "id", "image", "image_id", "locations",
      "max_duration", "min_duration", "name", "organization_url", "other_ways_to_help",
      "paired_businesses", "short_location", "slug", "twitter_username", "types", "volunteer_url"
    );
    return $this->json_has_expected_keys( $filename, $expected_volunteer_opportunity_keys );
  }
  private function json_has_expected_keys( $filename, $keys ) {
    $records = $this->gv_import_json( $filename );
    if ( is_array( $records ) && empty( array_diff( $keys, array_keys( $records[0] ) ) ) ) {
      return true;
    }
    return false;
  }
  private function gv_import_json( $filename ) {
    return (array) json_decode( file_get_contents( $filename ), true );
  }


  // Return the path to GV uploads directory
  private function gv_upload_dir() {
    // Check for existence of the uploads directory and create if necessary
    $base_upload_dir = wp_upload_dir();
    // gv_debug( 'wp_upload_dir results:' );
    // gv_debug( $base_upload_dir );
    $gv_upload_dir = $base_upload_dir[ 'basedir' ] . '/gv_uploads';
    // gv_debug( 'gv_upload_dir: ' . $gv_upload_dir );
    if ( ! ( file_exists( $gv_upload_dir ) && is_dir( $gv_upload_dir ) ) ) {
      // gv_debug( 'GV uploads directory does not exist. Creating it.' );
      if ( ! mkdir( $gv_upload_dir, 0755 ) ) {
        gv_debug( 'Error creating upload directory:' . $gv_upload_dir );
        return '';
      }
      // gv_debug( 'Successfully created upload directory' );
    }
    return $gv_upload_dir;
  }

  // Get the full path of a file in the gv_uploads directory
  private function gv_upload_file_path( $filename ) {
    return sprintf( '%s/%s', $this->gv_upload_dir(), $filename );
  }
  
  // Process the uploaded files
  public function gv_upload_files() {
    // Return if not an upload_files action
    if ( ! array_key_exists( 'gv_action', $_POST ) || ( 'upload_files' !== $_POST[ 'gv_action' ] ) ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_upload_files_nonce' ], 'gv_upload_files_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Get the GV upload directory
    $gv_upload_dir = $this->gv_upload_dir();
    if ( empty( $gv_upload_dir ) ) {
      gv_debug( 'Problem creating the GV upload directory' );
      return;
    }
   
    // Iterate across the uploaded files and move to the uploads directory
    foreach ( $_FILES as $slot => $file ) {
      // gv_debug( 'Checking uploaded file slot ' . $slot );
      // Skip if this isn't an uploaded file
      if ( ! is_uploaded_file( $file[ 'tmp_name' ] ) ) continue;
      // gv_debug( 'Valid uploaded file: ' . $file[ 'tmp_name' ] );

      // Check that this is an expected file extension
      $valid_file_extensions = array( '.json', '.json.gz', '.tar', '.tar.gz' );
      $matching_file_extensions = array_filter( $valid_file_extensions, function ( $ext ) use ( $file ) {
        return $this->has_file_extension( $file[ 'name' ], $ext );
      } );
      if ( empty( $matching_file_extensions ) ) {
        // gv_debug( 'File does not have a valid file extension: ' . $file[ 'name' ] );
        continue;
      }
      // gv_debug( 'Valid file extension: ' . $file[ 'name' ] );

      // Check that this is an expected file
      $valid_file = '';
      // Assume this is an image archive if file extension is .tar or .tar.gz
      if ( $this->has_file_extension( $file[ 'name' ], '.tar' ) || $this->has_file_extension( $file[ 'name' ], '.tar.gz' ) ) {
        $valid_file = 'images_archive';
        // gv_debug( 'File is an image archive' );
      }
      // If this is a json file, check to see if it has one of the expected data structures
      if ( $this->has_file_extension( $file[ 'name' ], '.json' ) ) {
        if ( $this->is_businesses_json( $file[ 'tmp_name' ] ) ) {
          $valid_file = 'businesses';
        }
        if ( $this->is_volunteer_opportunities_json( $file[ 'tmp_name' ] ) ) {
          $valid_file = 'volunteer_opportunities';
        }
      }
      if ( empty( $valid_file ) ) {
        continue;
      }

      // Copy file to uploads directory
      if ( ! move_uploaded_file( $file[ 'tmp_name' ], $this->gv_upload_file_path( $file[ 'name' ] ) ) ) {
        gv_debug( 'Error copying file to uploads directory' );
        continue;
      }

      // Update the uploaded files option
      $uploaded_files = get_option( 'gv_import_uploaded_files', array() );
      if ( array_key_exists( $valid_file, $uploaded_files ) && ! empty( $uploaded_files[ $valid_file ] ) ) {
        // If this slot already had a file, delete it
        $this->gv_delete_single_file( $uploaded_files[ $valid_file ] );
      }
      $uploaded_files[ $valid_file ] = $file[ 'name' ];
      update_option( 'gv_import_uploaded_files', $uploaded_files );
    }
  }

  // Delete a single uploaded file
  private function gv_delete_single_file( $file ) {
    // Get the full path to the file
    $filename = $this->gv_upload_file_path( $file );
    // Delete the file if it exists
    if ( file_exists( $filename ) ) {
      if ( ! unlink( $filename ) ) {
        gv_debug( 'Problems unlinking file: ' . $filename );
      }
    }
  }

  // Delete the uploaded files
  public function gv_delete_files() {
    // Return if not a delete_files action
    if ( ! array_key_exists( 'gv_action', $_POST ) || ( 'delete_files' !== $_POST[ 'gv_action' ] ) ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_delete_files_nonce' ], 'gv_delete_files_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    // Delete the uploaded files found in options
    $uploaded_files = get_option( 'gv_import_uploaded_files', array() );
    foreach ( $uploaded_files as $file ) {
      if ( empty( $file ) ) continue;
      $this->gv_delete_single_file( $file );
    }

    // Clear out the stored filenames in options
    delete_option( 'gv_import_uploaded_files' );
}
  
  // Import the legacy business records from the uploaded json file
  public function gv_import_businesses() {
    // Return if not an delete_files action
    if ( ! array_key_exists( 'gv_action', $_POST ) || ( 'import_businesses' !== $_POST[ 'gv_action' ] ) ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_import_businesses_nonce' ], 'gv_import_businesses_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Check to see if a business json was uploaded
    $uploaded_files = get_option( 'gv_import_uploaded_files', array() );
    if ( empty( $uploaded_files[ 'businesses' ] ) ) {
      gv_debug( 'No businesses json for import businesses action' );
      return;
    }

    // Open the file
    $file_path = $this->gv_upload_file_path( $uploaded_files[ 'businesses' ] );
    gv_debug( 'Executing the import_businesses action on ' . $file_path );
    $records = $this->gv_import_json( $file_path );

    // Import the businesses records
    gv_debug( 'Found ' . count( $records ) . ' businesses to import' );
    $this->import_businesses( $records );
  }
  
  // Import the legacy volunteer opportunities records from the uploaded json file
  public function gv_import_vol_opps() {
    // Return if not an delete_files action
    if ( ! array_key_exists( 'gv_action', $_POST ) || ( 'import_vol_opps' !== $_POST[ 'gv_action' ] ) ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_import_vol_opps_nonce' ], 'gv_import_vol_opps_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    // Check to see if a volunteer opportunities json was uploaded
    $uploaded_files = get_option( 'gv_import_uploaded_files', array() );
    if ( empty( $uploaded_files[ 'volunteer_opportunities' ] ) ) {
      gv_debug( 'No volunteer opportunities json for import_vol_opps action' );
      return;
    }

    // Open the file
    $file_path = $this->gv_upload_file_path( $uploaded_files[ 'volunteer_opportunities' ] );
    gv_debug( 'Executing the import_vol_opps action on ' . $file_path );
    $records = $this->gv_import_json( $file_path );

    // Import the businesses records
    gv_debug( 'Found ' . count( $records ) . ' volunteer opportunities to import' );
    $this->import_volunteer_opportunities( $records );
  }
  
  // Link paired legacy businesses and volunteer opportunities
  public function gv_link_bus_to_vol_opp() {
    // Return if not an delete_files action
    if ( ! array_key_exists( 'gv_action', $_POST ) || ( 'link_bus_to_vol_opp' !== $_POST[ 'gv_action' ] ) ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_link_bus_to_vol_opp_nonce' ], 'gv_link_bus_to_vol_opp_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;
    
    // Check to see if businesses and volunteer opportunities json was uploaded
    $uploaded_files = get_option( 'gv_import_uploaded_files', array() );
    if ( empty( $uploaded_files[ 'businesses' ] ) && empty( $uploaded_files[ 'volunteer_opportunities' ] ) ) {
      // FIXME: Could technically try to link using only the data in already uploaded posts.
      gv_debug( 'No businesses or volunteer opportunities json for link_bus_to_vol_opp action' );
      return;
    }

    // Open the files
    $bus_file_path = $this->gv_upload_file_path( $uploaded_files[ 'businesses' ] );
    $vol_file_path = $this->gv_upload_file_path( $uploaded_files[ 'volunteer_opportunities' ] );
    gv_debug( sprintf( 'Executing the link_bus_to_vol_opp action on %s and %s', $bus_file_path, $vol_file_path ) );
    $bus_records = $this->gv_import_json( $bus_file_path );
    $vol_records = $this->gv_import_json( $vol_file_path );

    // Link the paired businesses to volunteer opportunities
    $paired_bus_records = array_filter( $bus_records, function ( $record ) {
      if ( count( $record[ 'paired_volunteer_opportunities' ] ) > 1 ) {
        gv_debug( sprintf( 'Business ID %s has %s paired volunteer_opportunities', $record[ 'id' ], count( $record[ 'paired_volunteer_opportunities' ] ) ) );
      }
      return ! empty( $record[ 'paired_volunteer_opportunities' ] );
    } );
    $paired_vol_records = array_filter( $vol_records, function ( $record ) {
      if ( count( $record[ 'paired_businesses' ] ) > 1 ) {
        gv_debug( sprintf( 'Vol Opp ID %s has %s paired businesses', $record[ 'id' ], count( $record[ 'paired_businesses' ] ) ) );
      }
      return ! empty( $record[ 'paired_businesses' ] );
    } );
    gv_debug( sprintf( 'Found %s businesses and %s volunteer opportunities that are paired', count( $paired_bus_records ), count( $paired_vol_records ) ) );

    // Pick one to serve as the master (businesses just because)
    foreach ( $paired_bus_records as $bus_record ) {
      // Get the legacy IDs of the paired business and volunteer opportunity
      $bus_legacy_id = $bus_record[ 'id' ];
      $vol_legacy_ids = array_map( function ( $vol ) { return $vol[ 'id' ]; }, $bus_record[ 'paired_volunteer_opportunities' ] );
      // gv_debug( sprintf( 'Business %s is paired with volunteer opportunity %s', $bus_legacy_id, implode( ' and ', $vol_legacy_ids ) ) );

      // Get the get the business pods object from the legacy ID
      $bus_pod = $this->gv_get_pod_from_legacy_id( $bus_legacy_id, 'business' );
      if ( FALSE === $bus_pod ) {
        // If the pod isn't found, continue
        gv_debug( sprintf( 'Business pod with legacy ID %s not found, continue' ), $bus_legacy_id );
        continue;
      }

      // Iterate across the paired volunteer opportunities...
      foreach ( $vol_legacy_ids as $vol_legacy_id ) {
        // Get the volunteer opportunity pods object from the legacy ID
        $vol_post_id = $this->gv_get_post_id_from_legacy_id( $vol_legacy_id, 'vol_opportunity' );
        if ( FALSE === $vol_post_id ) {
          gv_debug( sprintf( 'Volunteer opportunity with legacy ID %s not found, continue', $vol_legacy_id ) );
          continue;
        }
        $bus_pod->add_to( 'paired_vol_opps', $vol_post_id );
      }
    }
  }

  private function gv_get_post_id_from_legacy_id( $legacy_id, $post_type ) {
    $post_ids = get_posts( array(
      'post_type' => $post_type,
      'fields' => 'ids',
      'meta_key' => 'legacy_id',
      'meta_value' => $legacy_id,
    ) );
    // gv_debug( sprintf( 'Legacy ID %s matches %s %s post(s): %s', $legacy_id, count( $post_ids ), $post_type, implode( ', ', $post_ids ) ) );
    if ( empty( $post_ids ) ) {
      // If no matching posts were found, return FALSE
      gv_debug( sprintf( 'Legacy ID %s-%s does not match any posts', $post_type, $legacy_id ) );
      // Return FALSE to match the pods() strict call
      return FALSE;
    }
    // Else, return the first ID found
    // FIXME: Making the assumption that finding multiple matches shouldn't happen. Might need to check this in the future
    return $post_ids[ 0 ];
  }

  private function gv_get_pod_from_post_id( $post_id, $post_type ) {
    // Uses strict mode to return FALSE if the pod isn't found
    return pods( $post_type, $post_id, TRUE );
  }

  private function gv_get_pod_from_legacy_id( $legacy_id, $post_type ) {
      // FIXME: It should work to use pods()->find() to do this lookup in one step. It isn't working right now.
      $post_id = $this->gv_get_post_id_from_legacy_id( $legacy_id, $post_type );
      if ( FALSE === $post_id ) {
        // Return FALSE if the post wasn't found
        return FALSE;
      }
      // Else return the pods object
      return $this->gv_get_pod_from_post_id( $post_id, $post_type );
  }
  
  // Import the legacy images and link to the associated volunteer opportunities
  public function gv_import_images() {
    // Return if not an delete_files action
    if ( ! array_key_exists( 'gv_action', $_POST ) || ( 'import_images' !== $_POST[ 'gv_action' ] ) ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_import_images_nonce' ], 'gv_import_images_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Check to see if image archive and volunteer opportunities json was uploaded
    $uploaded_files = get_option( 'gv_import_uploaded_files', array() );
    if ( empty( $uploaded_files[ 'images_archive' ] ) && empty( $uploaded_files[ 'volunteer_opportunities' ] ) ) {
      // FIXME: Could technically try to link using only the data in already uploaded posts.
      gv_debug( 'No image archive or volunteer opportunities json for import_images action' );
      return;
    }

    // Open the volunteer opportunity json file
    $vol_file_path = $this->gv_upload_file_path( $uploaded_files[ 'volunteer_opportunities' ] );
    gv_debug( sprintf( 'Executing the import_images action on %s', $vol_file_path ) );
    $vol_records = $this->gv_import_json( $vol_file_path );

    // Handle the images archive
    // Detect if this is a .tar.gz or .tar archive
    $archive_filename = $uploaded_files[ 'images_archive' ];
    $gzip_filename = '';
    $tar_filename = '';
    if ( $this->has_file_extension( $archive_filename, '.tar.gz' ) ) {
      $gzip_filename = $archive_filename;
      $tar_filename = substr( $gzip_filename, 0, -3 );
    } elseif ( $this->has_file_extension( $archive_filename, '.tar' ) ) {
      $tar_filename = $archive_filename;
    }
    $untar_directory = implode( '_', explode( '.', $tar_filename ) );

    // If, for some reason, the images archive isn't at least a .tar file, return
    gv_debug( sprintf( 'archive: (%s), gzip: (%s), tar: (%s), untar_dir: (%s)', $archive_filename, $gzip_filename, $tar_filename, $untar_directory ) );
    if ( empty( $tar_filename ) ) return;

    // This is going to be a memory and time intensive process, temporarily increase both
    $memory_limit = ini_get( 'memory_limit' );
    // gv_debug( 'Memory limit is: ' . $memory_limit );
    ini_set( 'memory_limit', '1024MB' );


    // Unzip the archive, if necessary
    if ( $gzip_filename ) {
      // Check if the unzipped tar file already exits
      $tar_path = $this->gv_upload_file_path( $tar_filename );
      if ( file_exists( $tar_path ) ) {
        gv_debug( 'Tar images archive file already exists, unlinking' );
        unlink( $tar_path );
      }

      // Unzip the file
      $p = new \PharData( $this->gv_upload_file_path( $gzip_filename ) );
      $p->decompress();
    }

    // Check if the un-tar directory exists
    $untar_path = $this->gv_upload_file_path( $untar_directory );
    if ( file_exists( $untar_path ) && ! is_dir( $untar_path ) ) {
      unlink( $untar_path );
    }
    if ( ! file_exists( $untar_path ) ) {
      mkdir( $untar_path, 0755 );
    }

    // Un-tar the archive
    $p = new \PharData( $this->gv_upload_file_path( $tar_filename ) );
    $p->extractTo( $this->gv_upload_file_path( $untar_directory ), null, true );

    // Check the existence of the images directory in the archive
    // TODO: Could have it search for the directory, but that seem overkill for this application
    $images_dir = $untar_path . '/apps/grassrootsvolunteering/shared/uploads/image/image';
    if ( ! file_exists( $images_dir ) ) {
      gv_debug( sprintf( 'The archive images directory (%s) does not exist', $images_dir ) );
      return;
    }
    if ( ! is_dir( $images_dir ) ) {
      gv_debug( sprintf( 'The archive images directory (%s) is not a directory', $images_dir ) );
      return;
    }
    // gv_debug( 'The archive images directory exists' );

    // Iterate across the volunteer opportunities and upload/link the images
    foreach ( $vol_records as $record ) {
      // Get the attached image info
      // NOTE: At this time it doesn't seem necessary to pull out the image category info
      $img_legacy_id = $record[ 'image' ][ 'id' ];
      $img_name = $record[ 'image' ][ 'name' ];
      $img_file = $record[ 'image' ][ 'image' ];

      // Check for existence of the image in the archive
      $img_path = sprintf( '%s/%s/%s', $images_dir, $img_legacy_id, $img_file );
      if ( ! file_exists( $img_path ) ) {
        gv_debug( sprintf( 'Image file does not exist (%s)', $img_path ) );
        continue;
      }
      // Special case, there is one file that does not use a standard (.jpg/.jpeg) extension
      if ( ! preg_match( '/\.(jpg|jpeg|png)$/i', $img_path ) ) {
        $old_path = $img_path;
        $new_file = preg_replace( '/^(.*)\.(jpg|jpeg|png).+$/i', '${1}.${2}', $img_file );
        $new_path = sprintf( '%s/%s/%s', $images_dir, $img_legacy_id, $new_file );
        gv_debug( sprintf( 'Renaming unexpected image file extension: %s => %s', $old_path, $new_path ) );
        rename( $old_path, $new_path );
        $img_path = $new_path;
        $img_file = $new_file;
      }
      // gv_debug( sprintf( 'Image file exists (%s)', $img_path ) );

      // Check that the volunteer opportunity exists
      $vol_post_id = $this->gv_get_post_id_from_legacy_id( $record[ 'id' ], 'vol_opportunity' );
      if ( FALSE === $vol_post_id ) {
        gv_debug( sprintf( 'Volunteer opportunity with ID %s does not exist', $record[ 'id' ] ) );
        continue;
      }

      // $tmp_filetype = wp_check_filetype( $img_path );
      // if ( 'jpg' === $tmp_filetype[ 'ext' ] ) {
      //   gv_debug( 'wp_check_filetype:' );
      //   gv_debug( $tmp_filetype );
      // }
      // continue;

      // Upload the image file
      $upload = wp_upload_bits( $img_file, null, @file_get_contents( $img_path ) );
      if ( $upload[ 'error' ] ) {
        gv_debug( 'Error attempting to upload image file ' . $img_path );
        gv_debug( $upload[ 'error' ] );
        return;
        continue;
      }

      // Create the attachment and attach to the volunteer opportunity
      $filetype = wp_check_filetype( $img_file );
      $args = array(
        'post_mime_type' => $filetype[ 'type' ],
        'post_parent' => $vol_post_id,
        'post_title' => $img_name,
        'post_content' => '',
      );
      $attachment_id = wp_insert_attachment( $args, $upload[ 'file' ], $vol_post_id );
      update_post_meta( $attachment_id, '_wp_attachment_image_alt', $img_name );

      // Check that the attachment was successfully generated
      if ( ! is_wp_error( $attachment_id ) ) {
        // Generate the intermediate image sizes (this step can take a long time)
        // require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload[ 'file' ] );
        wp_update_attachment_metadata( $attachment_id, $attachment_data );

        // Attach the image to the volunteer opportunity
        set_post_thumbnail( $vol_post_id, $attachment_id );
      }

    }

    // Clean up after myself
    // Delete the unzipped/un-tarred archive
    // $this->gv_recursive_rmdir( $untar_path );
    // If the original image archive file was a .tar.gz, delete the tar file
    if ( ! empty( $gzip_filename ) ) {
      unlink( $tar_path );
    }
  }

  // Adapted from the PHP docs: https://www.php.net/manual/en/function.rmdir.php#98622
  private function gv_recursive_rmdir( $dir ) {
    if ( is_dir( $dir ) ) {
      $objs = scandir( $dir );
      foreach ( $objs as $obj ) {
        if ( $obj !== '.' && $obj !== '..' ) {
          $obj_path = sprintf( '%s/%s', $dir, $obj );
          if ( is_dir( $obj_path ) ) {
            $this->gv_recursive_rmdir( $obj_path );
          } else {
            unlink( $obj_path );
          }
        }
        reset( $objs );
        rmdir( $dir );
      }
    }
  }

  // TODO: Need to break this whole process into multiple steps.
  //         - Upload files, can be done in any order. Need to keep track of files so I don't have to upload multiple times.
  //         - Import businesses, requires businesses json to be uploaded
  //         - Import volunteer opportunities, requires volunteer json to be uploaded
  //         - Link businesses and volunteer opportunities, requires business and volunteer json to be uploaded
  //         - Import images, requires image archive (*.tar.gz) and volunteer json to be uploaded
  //           TODO: What do I do with images that aren't associated with a volunteer opportunity?
  // Process a legacy data import from json files
  // TODO: Change name of this function to gv_upload_files
  public function gv_process_legacy_data_import() {
    // Return if not an import_settings gv_action
    // TODO: Change gv_action name to upload_files
    if ( empty( $_POST[ 'gv_action' ] ) || 'import_legacy_data' !== $_POST[ 'gv_action' ] ) return;
    // Return if bad nonce
    if ( ! wp_verify_nonce( $_POST[ 'gv_import_nonce' ], 'gv_import_nonce' ) ) return;
    // Return if insufficient permissions
    if ( ! current_user_can( 'manage_options' ) ) return;

    // Get the uploaded file(s) info
    // TODO: No need to filter THEN iterate over the filtered files, do this in one loop
    $uploaded_files = array_filter(
      $_FILES,
      function ( $file_info ) {
        // Filter out empty file inputs
        if ( empty( $file_info[ 'name' ] ) || empty( $file_info[ 'tmp_name' ] ) ) return false;

        // Check that the uploaded file is json
        $exploded_filename = explode( '.', $file_info[ 'name' ] );
        $file_extension = array_pop( $exploded_filename );
        // If the file extension is gz, check the next extension
        if ( 'gz' === $file_extension ) {
          $file_extension = array_pop( $exploded_filename );
        }
        if ( 'json' === $file_extension ) {
          gv_debug( 'Expected json file extension' );
          return true;
        } elseif ( 'tar' === $file_extension ) {
          gv_debug( 'Expected tar file extension' );
          return true;
        }

        // Commenting out, going to just ignore unexpected file types
        // wp_die(__( 'Please upload .json, .tar, or .tar.gz file(s). Unexpected file type: ' . $file_info[ 'name' ] ) );
        // Filter out this file, return false
        gv_debug( 'Please upload .json, .tar, or .tar.gz file(s). Unexpected file type: ' . $file_info[ 'name' ] );
        return false;
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
      // If there isn't an uploads directory, create one
      $gv_upload_dir = GV_PLUGIN_PATH . 'uploads';
      if ( ! ( file_exists( $gv_upload_dir ) && is_dir( $gv_upload_dir ) ) ) {
        gv_debug( 'GV uploads directory does not exist. Creating it.' );
        if ( ! mkdir( $gv_upload_dir, 0755 ) ) {
          gv_debug( 'Error creating upload directory:' . $gv_upload_dir );
          continue;
        }
        gv_debug( 'Successfully created upload directory' );
      }

      // Move the file from the tmp area to the uploads directory
      $gv_upload_location = sprintf( '%s/%s', $gv_upload_dir, $file_info[ 'name' ] );
      if ( ! rename( $file_info[ 'tmp_name' ], $gv_upload_location ) ) {
        gv_debug( sprintf( 'Failed to copy tmp file (%s) to the upload directory (%s)', $file_info[ 'tmp_name' ], $gv_upload_location ) );
        continue;
      }
      // Update the permissions
      if ( ! chmod( $gv_upload_location, 0644 ) ) {
        gv_debug( sprintf( 'Failed to change permissions on uploaded file (%s)', $gv_upload_location ) );
        continue;
      }

      // Check if this is a zipped file and unzip it

      if ( '.json' !== substr( $file_info[ 'name' ], -5 ) ) {
        gv_debug( 'Not a json file, skipping for now' );
        continue;
      }
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
        // Modify the phone numbers array into something useful
        $mod_phone_nums = array();
        foreach ( $business[ 'phone_numbers' ] as $i => $n ) {
          $mod_phone_nums[ 'number_' . $i ] =  $n[ 'number' ];
          $mod_phone_nums[ 'description_' . $i ] = $n[ 'description' ];
        }
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
            // Use the filtered and modified phone number array from above
            // 'phone_numbers' => serialize( $business[ 'phone_numbers' ] ),
            'phone_numbers' => $mod_phone_nums,
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