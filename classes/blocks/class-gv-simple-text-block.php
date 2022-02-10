<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

// Generic custom block
class GV_Simple_Text_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

  protected $default_html_tag = 'P';

   /* *******
   * Methods
   * *******/

  // Customized constructor
  public function __construct( $params = array() ) {
    parent::__construct( $params );

    // If this is a business_name or volunteer_name, the default HTML tag is 'H1'
    if ( in_array( $this->field_name, [ 'business_name', 'volunteer_name' ] ) ) {
      $this->default_html_tag = 'H1';
    }

    // Add attribute fields specific to a link block
    $link_block_attributes = array(
      array(
        'name' => 'html_tag',
        'label' => 'HTML Tag',
        'type' => 'pick',
        'data' => array(
          'default' => sprintf( 'Default (%s)', $this->default_html_tag ),
          'h1' => 'H1',
          'h2' => 'H2',
          'h3' => 'H3',
          'h4' => 'H4',
          'h5' => 'H5',
          'h6' => 'H6',
          'p' => 'P',
        ),
        'default' => 'default',
      ),
      // TODO: Figure out how to extract the included fonts. This is likely theme dependent.
      array(
        'name' => 'font_family',
        'label' => 'Font',
        'type' => 'pick',
        'data' => array( 'default' => 'Default' ),
        'default' => 'default',
        'description' => 'Future feature',
      ),
      // TODO: Is it better to make this a text field and let the user decide?
      array(
        'name' => 'font_size',
        'label' => 'Size',
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
        'name' => 'font_appearance',
        'label' => 'Appearance',
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
        'name' => 'use_text_color',
        'label' => 'Color',
        'type' => 'boolean',
        'boolean_yes_label' => 'Use custom color',
        'boolean_no_label' => 'Use default color',
      ),
      array(
        'name' => 'text_color',
        'label' => 'Color',
        'type' => 'color',
      ),
      array(
        'name' => 'text_align',
        'label' => 'Text Align',
        'type' => 'pick',
        'data' => array(
          'left' => 'Left',
          'center' => 'Center',
          'right' => 'Right',
        ),
        'default' => 'left',
      ),
      array(
        'name' => 'font_case',
        'label' => 'Letter Case',
        'type' => 'pick',
        'data' => array(
          'uppercase' => 'UPPER CASE',
          'lowercase' => 'lower case',
          'capitalize' => 'Capitalize',
        ),
        'default' => 'capitalize',
      ),
    );
    $this->attributes = array_merge( $this->attributes, $link_block_attributes );
  }

  // Display a link field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // If field data is empty, return nothing
    if ( empty( $field_data ) ) return;

    // gv_debug( 'Inside simple text block format field data with attributes' );
    // gv_debug( $attributes );

    // Create the element
    $html_tag = empty( $attributes[ 'html_tag' ] ) || 'default' === $attributes[ 'html_tag' ][ 'value' ]
      ? $this->default_html_tag
      : $attributes[ 'html_tag' ][ 'value' ];
    $styles = array();
    if ( ! empty( $attributes[ 'font_size' ] ) &&  'default' !== $attributes[ 'font_size' ][ 'value' ] ) {
      $styles[] = sprintf( 'font-size:%s', $attributes[ 'font_size' ][ 'value' ] );
    }
    if ( ! empty( $attributes[ 'font_appearance' ] ) &&  'default' !== $attributes[ 'font_appearance' ][ 'value' ] ) {
      // gv_debug( sprintf( 'Font appearance is %s', $attributes[ 'font_appearance' ][ 'value' ] ) );
      $font_appearance = explode( '-', $attributes[ 'font_appearance' ][ 'value' ] );
      // gv_debug( sprintf( 'Font appearance after explosion is' ) );
      // gv_debug( $font_appearance );
      $styles[] = sprintf( 'font-weight:%s', $font_appearance[ 0 ] );
      if ( 2 === count( $font_appearance ) ) {
        $styles[] = sprintf( 'font-style:%s', $font_appearance[ 1 ] );
      }
      // gv_debug( 'Styles is now ' );
      // gv_debug( $styles );
    }
    if ( ! empty( $attributes[ 'text_align' ] && 'left' !== $attributes[ 'text_align' ][ 'value' ] ) ) {
      $styles[] = sprintf( 'text-align:%s', $attributes[ 'text_align' ][ 'value' ] );
    }
    if ( ! empty( $attributes[ 'font_case' ] ) && ( 'capitalize' !== $attributes[ 'font_case' ][ 'value' ] )) {
      $styles[] = sprintf( 'text-transform:%s', $attributes[ 'font_case' ][ 'value' ] );
    }
    if ( ! empty( $attributes[ 'use_text_color' ] ) && $attributes[ 'use_text_color' ] ) {
      // gv_debug( 'use_text_color' );
      // gv_debug( $attributes[ 'use_text_color' ] );
      $styles[] = sprintf( 'color:%s', $attributes[ 'text_color' ] );
    }
    $formatted_field_data = sprintf(
      '<%s%s>%s</%s>',
      $html_tag,
      empty( $styles ) ? '' : sprintf( ' style="%s"', implode( ';', $styles ) ),
      $field_data,
      $html_tag
    );

    // Return the formatted field data
    return $formatted_field_data;
  }
}
