<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

// Generic custom block
class GV_Paired_Entry_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Customized constructor
  public function __construct( $params = array() ) {
    parent::__construct( $params );

    // Add attribute fields specific to a link block
    $this->attributes = array_merge(
      $this->attributes,
      $this->generate_text_style_fields()
    );

  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    $this_type = 'business' === $this->post_type ? 'Business' : 'Volunteer Opportunity';
    $other_type = 'business' === $this->post_type ? 'Volunteer Opportunity' : 'Business';
    if ( FALSE === $field_data || empty( $field_data ) || ! is_array( $field_data ) ) {
      // Return an empty string for now
      return '';
    }
    // Create the link, if no link text provided use the field data
    // TODO: The color attribute doesn't seem to work. I may need to use a <style> element instead
    $styles = $this->generate_text_style_attributes( $attributes );
    $style_attribute = empty( $styles ) ? '' : sprintf( ' style="%s"', implode( ';', $styles ) );
    $formatted_paired_entries = array_map(
      function ( $entry ) use ( $style_attribute ) {
        return sprintf( '<div%s><a href="%s">%s</a></div>', $style_attribute, $entry[ 'guid' ], $entry[ 'post_title' ] );
      },
      $field_data
    );
    return implode( ' ', $formatted_paired_entries );
  }
}