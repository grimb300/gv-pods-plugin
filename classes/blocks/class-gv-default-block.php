<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Generic custom block
class GV_Default_Block {

  /* **********
   * Properties
   * **********/

  protected $field_name = 'default_field';
  protected $post_type = 'default_post_type';
  protected $namespace = 'default-block-namespace';
  protected $field_title = 'GV Default Block';
  protected $field_description = 'Default Grassroots Volunteering custom block';
  protected $block_collection = 'gv-default-block-collection';
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
      if ( 'namespace' === $param ) $this->namespace = $val;
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
    // gv_debug( sprintf( 'Registering the %s block into the %s collection', $this->field_name, $this->block_collection ) );
    $block = array(
      'namespace' => $this->namespace,
      // There's a problem when both business and vol_opportunity have the same field name (ex: short_location)
      // Add the post_type to the field_name for the 'name' of the block
      // 'name' => $this->field_name,
      'name' => sprintf( '%s_%s', $this->post_type, $this->field_name ),
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
    $display_field_data = $this->format_field_data( $field_data );
    if ( is_array( $display_field_data ) ) {
      $display_field_data = sprintf( '<code>%s</code>', var_export( $field_data, true ) );
    } elseif ( FALSE === $display_field_data ) {
      $display_field_data = '<p style="color: red"><strong><em>field() returned FALSE</em></strong></p>';
    } elseif ( '' === $display_field_data ) {
      $display_field_data = '<p style="color: red"><strong><em>field() returned empty string</em></strong></p>';
    }
    return sprintf( '%s<div class="%s">%s</div>', $field_heading, $class, $display_field_data );
  }

  // This function should be overridden by child classes
  protected function format_field_data( $field_data = null ) {
    return $field_data;
  }
}