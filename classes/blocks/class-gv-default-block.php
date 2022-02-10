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
    $display_field_data = $this->format_field_data( $field_data, $attributes );
    if ( is_array( $display_field_data ) ) {
      $display_field_data = sprintf( '<code>%s</code>', var_export( $field_data, true ) );
    } elseif ( FALSE === $display_field_data ) {
      $display_field_data = '<p style="color: red"><strong><em>field() returned FALSE</em></strong></p>';
    // } elseif ( '' === $display_field_data ) {
    //   $display_field_data = '<p style="color: red"><strong><em>field() returned empty string</em></strong></p>';
    }
    return sprintf( '%s<div class="%s">%s</div>', $field_heading, $class, $display_field_data );
  }

  // This function should be overridden by child classes
  protected function format_field_data( $field_data = null ) {
    return $field_data;
  }

  // This function generates an array of general text style fields used by pods_register_block_type()
  // Takes an optional prefix and label so multiple sets of attributes can be added to the same block type
  protected function generate_text_style_fields( $p=null, $l=null ) {
    $prefix = is_null( $p ) ? 'noprefix-' : $p . '-';
    $label = is_null( $l ) ? '' : $l . ' ';
    return array(
      // TODO: Figure out how to extract the included fonts. This is likely theme dependent.
      array(
        'name' => $prefix . 'font_family',
        'label' => $label . 'Font',
        'type' => 'pick',
        'data' => array( 'default' => 'Default' ),
        'default' => 'default',
        'description' => 'Future feature',
      ),
      // TODO: Is it better to make this a text field and let the user decide?
      array(
        'name' => $prefix . 'font_size',
        'label' => $label . 'Size',
        'type' => 'pick',
        'data' => array(
          'default' => 'Default',
          '16px' => 'Extra Small (16px)',
          '18px' => 'Small (18px)',
          '20px' => 'Normal (20px)',
          '24px' => 'Large (24px)',
          '40px' => 'Extra Large (40px)',
          '96px' => 'Huge (96px)',
          '144px' => 'Gigantic (144px)',
        ),
        'default' => 'default',
      ),
      array(
        'name' => $prefix . 'font_appearance',
        'label' => $label . 'Appearance',
        'type' => 'pick',
        'data' => array(
          'default' => 'Default',
          '100' => 'Thin',
          '200' => 'Extra Light',
          '300' => 'Light',
          '400' => 'Regular',
          '500' => 'Medium',
          '600' => 'Semi Bold',
          '700' => 'Bold',
          '800' => 'Black',
          '100-italic' => 'Thin Italic',
          '200-italic' => 'Extra Light Italic',
          '300-italic' => 'Light Italic',
          '400-italic' => 'Regular Italic',
          '500-italic' => 'Medium Italic',
          '600-italic' => 'Semi Bold Italic',
          '700-italic' => 'Bold Italic',
          '800-italic' => 'Black Italic',
        ),
        'default' => 'default',
      ),
      array(
        'name' => $prefix . 'use_text_color',
        'label' => $label . 'Color',
        'type' => 'boolean',
        'boolean_yes_label' => 'Use custom color',
        'boolean_no_label' => 'Use default color',
      ),
      array(
        'name' => $prefix . 'text_color',
        'label' => $label . 'Color',
        'type' => 'color',
      ),
      array(
        'name' => $prefix . 'text_align',
        'label' => $label . 'Text Align',
        'type' => 'pick',
        'data' => array(
          'left' => 'Left',
          'center' => 'Center',
          'right' => 'Right',
        ),
        'default' => 'left',
      ),
      array(
        'name' => $prefix . 'font_case',
        'label' => $label . 'Letter Case',
        'type' => 'pick',
        'data' => array(
          'uppercase' => 'UPPER CASE',
          'lowercase' => 'lower case',
          'capitalize' => 'Capitalize',
        ),
        'default' => 'capitalize',
      ),
    );
  }

  // This function generates an array of styles to be used when generating the formatted field data
  // Takes a required attribute array to extract from and an optional prefix so multiple sets of attributes can be added to the same block type
  // Must use the same prefix defined in generate_text_style_fields( <prefix> )
  protected function generate_text_style_attributes( $a, $p=null ) {
    // Extract the attributes using the provided prefix
    $prefix = is_null( $p ) ? 'noprefix-' : $p . '-';
    // Get the keys that match this prefix
    $matching_keys = array_filter(
      array_keys( $a ),
      function ( $k ) use ( $prefix ) {
        return 1 === preg_match( "/^$prefix/", $k );
      }
    );
    // gv_debug( 'Attributes matching prefix ' . $prefix );
    // gv_debug( $matching_keys );
    // Pull the data out of the attribute array and into non-prefixed variables
    foreach ( $matching_keys as $k ) {
      $varname = preg_replace( "/^$prefix/", '', $k );
      $$varname = is_array( $a[ $k ] ) && isset( $a[ $k ][ 'value' ] ) ? $a[ $k ][ 'value' ] : $a[ $k ];
      // gv_debug( sprintf( '$%s = %s', $varname, $$varname ) );
    }
     
    $styles = array();
    if ( isset( $font_size ) && 'default' !== $font_size ) {
      $styles[] = sprintf( 'font-size:%s', $font_size );
    }
    if ( isset( $font_appearance ) && 'default' !== $font_appearance ) {
      // The appearance attribute contains the font-weight and optionally the font-style
      $font_weight = $font_appearance;
      if ( preg_match( "/^[1-8]00-[a-z]+$/", $font_appearance ) ) {
        [ $font_weight, $font_style ] = explode( '-', $font_appearance );
        $styles[] = sprintf( 'font-style:%s', $font_style );
      }
      $styles[] = sprintf( 'font-weight:%s', $font_weight );
    }
    if ( isset( $text_align ) && 'left' !== $text_align ) {
      $styles[] = sprintf( 'text-align:%s', $text_align );
    }
    if ( isset( $font_case ) && 'capitalize' !== $font_case ) {
      $styles[] = sprintf( 'text-transform:%s', $font_case );
    }
    if ( isset( $use_text_color ) && true === $use_text_color ) {
      $styles[] = sprintf( 'color:%s', $text_color );
    }

    return $styles;
  }
}