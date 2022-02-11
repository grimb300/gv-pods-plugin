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

    // Add attribute fields specific to a simple text block
    $block_attributes = array(
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
    );
    $this->attributes = array_merge(
      $this->attributes,
      $block_attributes,
      $this->generate_text_style_fields()
    );
  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // If field data is empty, return nothing
    if ( empty( $field_data ) ) return;

    // Create the element
    $html_tag = empty( $attributes[ 'html_tag' ] ) || 'default' === $attributes[ 'html_tag' ][ 'value' ]
      ? $this->default_html_tag
      : $attributes[ 'html_tag' ][ 'value' ];
    $styles = $this->generate_text_style_attributes( $attributes );
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
