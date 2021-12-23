<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

class GV_Blocks {

  /* **********
   * Properties
   * **********/

  public $gv_blocks_namespace = array(
    'business' => 'gv-business-blocks',
    'vol_opportunity' => 'gv-vol-opportunity-blocks',
  );
  public $gv_blocks_collection_namespace = array(
    'business' => array(
      'namespace' => 'gv-business-blocks-collection',
      'title' => 'GV Business Blocks',
    ),
    'vol_opportunity' => array(
      'namespace' => 'gv-vol-opportunity-blocks-collection',
      'title' => 'GV Volunteer Opportunity Blocks',
    ),
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
    // Register the GV blocks collection
    add_action( 'pods_blocks_api_init', array( $this, 'register_gv_custom_blocks' ) );
    // Register the GV blocks within that collection
    // add_action( 'pods_blocks_api_init', array( $this, 'register_gv_custom_block_types' ) );

    // Instantiate the custom block objects
    foreach ( $this->gv_blocks_defs as $def ) {
      $block = new GV_Generic_Block( $def );
    }

    // Instantiate the custom block objects
    // $business_name_block = new GV_Generic_Block( array(
    //   'field_name' => 'business_name',
    //   'post_type' => 'business',
    // ) );
    // Location returns an array, needs more work/customization
    // $location_block = new GV_Generic_Block( array(
    //   'field_name' => 'location',
    //   'post_type' => 'business',
    // ) );
    // $short_location_block = new GV_Generic_Block( array(
    //   'field_name' => 'short_location',
    //   'post_type' => 'business',
    // ) );

  }  

  public function register_gv_custom_blocks() {

    // Create the generic collection for testing purposes
    $collection = array(
      'namespace' => 'gv-generic-block-collection',
      'title' => __( 'GV Generic Blocks' ),
      'icon' => 'admin-site-alt3',
    );  
    pods_register_block_collection( $collection );

    // Create the collections
    foreach ( $this->gv_blocks_collection_namespace as $post_type => $info ) {
      $collection = array(
        'namespace' => $info[ 'namespace' ],
        'title' => __( $info[ 'title' ] ),
        'icon' => 'admin-site-alt3',
      );
      pods_register_block_collection( $collection );
    }

    // Create the blocks
    // foreach ( $this->gv_blocks_defs as $block_def ) {
    //   $field_name = $block_def[ 'field_name' ];
    //   $post_type = $block_def[ 'post_type' ];
    //   $block = array(
    //     'namespace' => $this->gv_blocks_namespace[ $post_type ],
    //     'name' => $field_name,
    //     'title' => $field_name,
    //     'description' => __( 'This is a ' . $field_name ),
    //     'category' => $this->gv_blocks_collection_namespace[ $post_type ],
    //     'icon' => 'admin-site-alt3',
    //     'keywords' => array( 'Grassroots Volunteering', $field_name ),
    //     'render_type' => 'php',
    //     'render_callback' => array( $this, 'generic_render' ),
    //   );
    //   pods_register_block_type( $block, $block_def[ 'attributes' ] );
    // }
  }

  public function register_gv_custom_block_types() {
    $block = array(
      'namespace' => $this->gv_blocks_namespace,
      'name' => 'business-name',
      'title' => __( 'Business Name' ),
      'description' => __( 'The name of the business.' ),
      'category' => $this->gv_blocks_collection_namespace,
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

// Generic custom block
class GV_Generic_Block {

  /* **********
   * Properties
   * **********/

  protected $field_name = 'generic_field';
  protected $post_type = 'generic_post_type';
  protected $field_title = 'GV Generic Block';
  protected $field_description = 'Generic Grassroots Volunteering custom block';
  protected $block_collection = 'gv-generic-block-collection';
  protected $attributes = array();

   /* *******
   * Methods
   * *******/

  // Constructor
  public function __construct( $params = array() ) {
    // Update any properties passed through the constructor
    foreach ( $params as $param => $val ) {
      if ( 'field_name' === $param ) $this->field_name = $val;
      if ( 'post_type' == $param ) $this->post_type = $val;
      if ( 'field_title' == $param ) $this->field_title = $val;
      if ( 'field_description' == $param ) $this->field_description = $val;
      if ( 'block_collection' == $param ) $this->block_collection = $val;
      if ( 'attributes' == $param ) $this->attributes = $val;
    }

    // To give each block a unique title, passing field_name without field_title will update field title
    if ( array_key_exists( 'field_name', $params ) && ! array_key_exists( 'field_title', $params ) ) {
      $this->field_title = sprintf( '%s - %s', $this->field_title, $params[ 'field_name' ] );
    }

    // Register the block
    add_action( 'pods_blocks_api_init', array( $this, 'register_block' ) );
  }

  // Register this block
  public function register_block() {
    $block = array(
      'namespace' => $this->block_collection,
      'name' => $this->field_name,
      'title' => __( $this->field_title ),
      'description' => __( $this->field_description ),
      'category' => $this->block_collection,
      'icon' => 'admin-site-alt3',
      'keywords' => array( 'Grassroots Volunteering', $this->field_name ),
      'render_type' => 'php',
      'render_callback' => array( $this, 'render_block' ),
    );
    pods_register_block_type( $block, $this->attributes );
  }

  public function render_block( $attributes ) {
    global $post;

    // Check that this is the right post type
    if ( $this->post_type !== $post->post_type ) {
      return sprintf( '<p style="color: red"><strong><em>This block can only be used on GV "%s" post types!</em></strong></p>', $this->post_type );
    }
    
    // Get the pod
    $pod = pods( $this->post_type, $post->ID );
    if ( ! $pod->exists() ) {
      return sprintf( '<p style="color: red"><strong><em>Unable to load pods for this post!</em></strong></p>', $this->post_type );
    }
    
    // Get the field data
    $field_data = $pod->field( $this->field_name );
    if ( null === $field_data ) {
      return sprintf( '<p style="color: red"><strong><em>Unable to load data for field %s!</em></strong></p>', $this->field_name );
    }

    // Display the field
    $class = sprintf( 'gv_block-%s-%s', $this->post_type, $this->field_name );
    $field_heading = sprintf( '<h4>%s</h4>', $this->field_name );
    $display_field_data = $field_data;
    if ( is_array( $field_data ) ) {
      $display_field_data = sprintf( '<code>%s</code>', var_export( $field_data, true ) );
    } elseif ( FALSE === $field_data ) {
      $display_field_data = 'field() returned FALSE';
    } elseif ( '' === $field_data ) {
      $display_field_data = 'field() returned empty string';
    }
    return sprintf( '%s<div class="%s">%s</div>', $field_heading, $class, $display_field_data );
  }
}