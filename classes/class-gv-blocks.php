<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

class GV_Blocks {

  /* **********
   * Properties
   * **********/

  private $gv_blocks_namespace = array(
    'business' => 'gv-business-blocks',
    'vol_opportunity' => 'gv-vol-opportunity-blocks',
  );
  private $gv_blocks_collections = array(
    'default' => array(
      'namespace' => 'gv-default-block-collection',
      'title' => 'GV Blocks',
    ),
    // TODO: Waiting to hear back from the Pods devs about registering multiple collections
    // 'business' => array(
    //   'namespace' => 'gv-business-blocks-collection',
    //   'title' => 'GV Business Blocks',
    // ),
    // 'vol_opportunity' => array(
    //   'namespace' => 'gv-vol-opportunity-blocks-collection',
    //   'title' => 'GV Volunteer Opportunity Blocks',
    // ),
  );

  private $gv_blocks_classes = array(
    'GV_Location_Block' => 'class-gv-location-block.php',
  );

  private $gv_blocks_defs = array(
    array(
      'post_type' => 'business',
      'field_name' => 'business_name',
      'field_title' => 'Business Name',
      'field_description' => 'Display the name of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'location',
      'field_title' => 'Location',
      'field_description' => 'Display the location of the business on a map (NOT WORKING)',
      'block_collection' => 'gv-business-blocks-collection',
      'block_class' => 'GV_Location_Block',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'short_location',
      'field_title' => 'Short Location',
      'field_description' => 'Display the short location of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'address',
      'field_title' => 'Address',
      'field_description' => 'Display the address of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'description',
      'field_title' => 'Description',
      'field_description' => 'Display the description of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'hours',
      'field_title' => 'Hours',
      'field_description' => 'Display the hours of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'phone_numbers',
      'field_title' => 'Phone Numbers',
      'field_description' => 'Display the phone numbers of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'url',
      'field_title' => 'Website URL',
      'field_description' => 'Display the URL of the business website',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'paired_vol_opps',
      'field_title' => 'Paired Volunteer Opportunity',
      'field_description' => 'Display the paired volunteer opportunity of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'business_location',
      'field_title' => 'Business Locations',
      'field_description' => 'Display the business location taxonomy of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'business_type',
      'field_title' => 'Business Types',
      'field_description' => 'Display the business type taxonomy of the business',
      'block_collection' => 'gv-business-blocks-collection',
      'attributes' => array(),
    ),
  );

  /* *******
   * Methods
   * *******/

  // Constructor
  public function __construct() {
    // Register the GV blocks collections
    add_action( 'pods_blocks_api_init', array( $this, 'register_gv_block_collections' ) );

    // Instantiate the custom block objects
    foreach ( $this->gv_blocks_defs as $def ) {
      // Force each block into the default collection (for now)
      $def[ 'block_collection' ] = $this->gv_blocks_collections[ 'default' ][ 'namespace' ];
      // Get the namespace based on the post_type
      $def[ 'namespace' ] = $this->gv_blocks_namespace[ $def[ 'post_type' ] ];

      // If block_class is defined, use that block
      if ( ! empty( $def[ 'block_class' ] ) ) {
        $block_class = $def[ 'block_class' ];
        $block_class_file = GV_PLUGIN_PATH . 'classes/blocks/' . $this->gv_blocks_classes[ $block_class ];
        gv_debug( sprintf( 'Going to load %s from %s', $block_class, $block_class_file ) ); 
        require_once $block_class_file;
        $classname = 'GVPlugin\\' . $block_class;
        $block = new $classname( $def );
      } else {
        // Use the default block
        require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';
        $block = new GV_Default_Block( $def );
      }
    }
  }  

  public function register_gv_block_collections() {
    // Create the collections
    foreach ( $this->gv_blocks_collections as $post_type => $info ) {
      $collection = array(
        'namespace' => $info[ 'namespace' ],
        'title' => __( $info[ 'title' ] ),
        // 'icon' => 'admin-site-alt3',
      );
      // gv_debug( sprintf( 'Registering block collection %s (%s)', $info[ 'title' ], $info[ 'namespace' ] ) );
      pods_register_block_collection( $collection );
    }
  }

  public function register_gv_custom_block_types() {
    $block = array(
      'namespace' => $this->gv_blocks_namespace,
      'name' => 'business-name',
      'title' => __( 'Business Name' ),
      'description' => __( 'The name of the business.' ),
      'category' => $this->gv_blocks_collections,
      'icon' => 'admin-site-alt3',
      'keywords' => array( 'GrassrootsVolunteering', 'business-name' ),
      'render_type' => 'php',
      'render_callback' => array( $this, 'business_name_render' ),
    );
    $fields = array(
      array(
        'name' => 'business_name__html_tag',
        'label' => __( 'HTML Tag' ),
        'type' => 'pick',
        'data' => array(
          'default' => __( 'Default' ),
          'h1' => __( 'H1' ),
          'h2' => __( 'H2' ),
          'h3' => __( 'H3' ),
          'h4' => __( 'H4' ),
          'h5' => __( 'H5' ),
          'h6' => __( 'H6' ),
          'p' => __( 'P' ),
        ),
        'default' => 'default',
      ),
      // array(
      //   'name' => 'business_name__styles',
      //   'label' => __( 'Styles' ),
      //   'type' => 'boolean_group',
      //   'boolean_group' => array(
      //     'bold' => array(
      //       'label' => __( 'Bold' ),
      //       'type' => 'boolean',
      //     ),
      //     'italic' => array(
      //       'label' => __( 'Italic' ),
      //       'type' => 'boolean',
      //     ),
      //   ),
      // ),
      array(
        'name' => 'business_name__bold',
        // 'label' => __( 'Bold' ),
        'type' => 'boolean',
        'boolean_yes_label' => __( 'Bold' ),
      ),
      array(
        'name' => 'business_name__italic',
        // 'label' => __( 'Italic' ),
        'type' => 'boolean',
        'boolean_yes_label' => __( 'Italic' ),
      ),
    );
    pods_register_block_type( $block, $fields );
  }

  public function generic_render( array $attr ) {
    return 'This is the generic_render() function';
  }

  public function business_name_render( array $attr ) {
    // Get the business_name field
    global $post;
    gv_debug( 'Current post' );
    gv_debug( $post );
    $business_pod = pods( 'business', $post->ID );
    $business_name = $business_pod->exists() ? $business_pod->field( 'business_name', true ) : 'Invalid business pod';
    $render_string = array( $business_name );

    // If 'bold', add <strong>...</strong>
    if ( ! empty( $attr[ 'business_name__bold' ] ) ) {
      array_unshift( $render_string, '<strong>' );
      array_push( $render_string, '</strong>' );
    }
    
    // If 'italic', add <em>...</em>
    if ( ! empty( $attr[ 'business_name__italic' ] ) ) {
      array_unshift( $render_string, '<em>' );
      array_push( $render_string, '</em>' );
    }
    
    // Add the surrounding html_tag
    $html_tag = empty( $attr[ 'business_name__html_tag' ] )
      ? 'h1'
      : $attr[ 'business_name__html_tag' ][ 'value' ];
    array_unshift( $render_string, '<' . $html_tag . '>' );
    array_push( $render_string, '</' . $html_tag . '>' );

    // Implode the elements of the array and return
    return implode( '', $render_string );
  }

}
