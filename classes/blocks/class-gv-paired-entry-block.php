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

  // Display a location field
  protected function format_field_data( $field_data = null ) {
    $this_type = 'business' === $this->post_type ? 'Business' : 'Volunteer Opportunity';
    $other_type = 'business' === $this->post_type ? 'Volunteer Opportunity' : 'Business';
    if ( FALSE === $field_data || empty( $field_data ) || ! is_array( $field_data ) ) {
      // Return an empty string for now
      return '';
    }
    $formatted_paired_entries = array_map(
      function ( $entry ) {
        return sprintf( '<a href="%s">%s</a>', $entry[ 'guid' ], $entry[ 'post_title' ] );
      },
      $field_data
    );
    return implode( ', ', $formatted_paired_entries );
  }
}