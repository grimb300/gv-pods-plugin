<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

// Generic custom block
class GV_Textarea_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

  protected $default_html_tag = 'P';
  private $textarea_elements = array();

   /* *******
   * Methods
   * *******/

  // Customized constructor
  public function __construct( $params = array() ) {
    parent::__construct( $params );

    // Elements potentially created by a textarea
    // TODO: There is a lot of options here AND
    //       it looks like the TinyMCE can also add <blockquote>, <code>, <ul>, <ol>, <a>, ...
    //       Unless I can figure out how to group fields together an hide the group, this feels like too much.
    //       Not sure how to handle this right now.
    $this->textarea_elements = array(
      'h1'=> 'Heading 1',
      'h2'=> 'Heading 2',
      'h3'=> 'Heading 3',
      'h4'=> 'Heading 4',
      'h5'=> 'Heading 5',
      'h6'=> 'Heading 6',
      'p'=> 'Paragraph',
      'pre'=> 'Preformatted',
    );

    // Add attribute fields specific to a simple text block
    foreach ( $this->textarea_elements as $element => $label ) {
      $this->attributes = array_merge(
        $this->attributes,
        $this->generate_text_style_fields( $element, $label )
      );
    }
  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // If field data is empty, return nothing
    if ( empty( $field_data ) ) return;

    // Get the styles
    $class_name = sprintf( 'gv_%s_block', $this->field_name );
    $style_element = '';
    foreach ( array_keys( $this->textarea_elements ) as $element ) {
      $styles = $this->generate_text_style_attributes( $attributes, $element );
      if ( ! empty( $styles ) ) {
        $style_element .= sprintf( ' .%s %s {%s;}', $class_name, $element, implode( ';', $styles ) );
      }
    }
    if ( ! empty( $style_element ) ) {
      // Add the style tag around the styles
      $style_element = sprintf( '<style>%s</style>', $style_element );
    }

    // Create the element
    $formatted_field_data = sprintf('%s<div class="%s">%s</div>', $style_element, $class_name, wpautop( $field_data ) );

    // Return the formatted field data
    return $formatted_field_data;
  }
}
