<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Generic custom block
class GV_Link_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Customized constructor
  public function __construct( $params = array() ) {
    gv_debug( 'Executing the parent constructor from the link block' );
    parent::__construct( $params );

    // Add attribute fields specific to a link block
    $this->attributes[] = array(
      'name' => 'link_text',
      'label' => 'Link Text',
      'type' => 'text',
      'placeholder' => 'placeholder text'
    );
  }

  // Display a link field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // If field data is empty, return nothing
    if ( empty( $field_data ) ) return;

    // Create the link, if no link text provided use the field data
    $formatted_field_data = sprintf(
      '<a href="%s">%s</a>',
      $field_data,
      empty( $attributes[ 'link_text' ] ) ? $field_data : $attributes[ 'link_text' ]
    );

    // Return the formatted field data
    return $formatted_field_data;
  }
}