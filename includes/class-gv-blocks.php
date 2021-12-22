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
    'business' => 'gv-business-blocks-collection',
    'vol_opportunity' => 'gv-vol-opportunity-blocks-collection',
  );

  private $gv_blocks_defs = array(
    array(
      'post_type' => 'business',
      'field_name' => 'business_name',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'location',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'short_location',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'address',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'description',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'hours',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'phone_numbers',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'url',
      'attributes' => array(),
    ),
    array(
      'post_type' => 'business',
      'field_name' => 'paired_vol_opps',
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
  }

  public function register_gv_custom_blocks() {
    // TODO: This is starting out as a brute force
    // Create the collections
    foreach ( $this->gv_blocks_collection_namespace as $post_type => $namespace ) {
      $collection = array(
        'namespace' => $namespace,
        'title' => __( 'GV ' . $post_type . ' Blocks' ),
        'icon' => 'admin-site-alt3',
      );
      pods_register_block_collection( $collection );
    }

    // Create the blocks
    foreach ( $this->gv_blocks_defs as $block_def ) {
      $field_name = $block_def[ 'field_name' ];
      $post_type = $block_def[ 'post_type' ];
      $block = array(
        'namespace' => $this->gv_blocks_namespace[ $post_type ],
        'name' => $field_name,
        'title' => $field_name,
        'description' => __( 'This is a ' . $field_name ),
        'category' => $this->gv_blocks_collection_namespace[ $post_type ],
        'icon' => 'admin-site-alt3',
        'keywords' => array( 'Grassroots Volunteering', $field_name ),
        'render_type' => 'php',
        'render_callback' => array( $this, 'generic_render' ),
      );
      pods_register_block_type( $block, $block_def[ 'attributes' ] );
    }
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