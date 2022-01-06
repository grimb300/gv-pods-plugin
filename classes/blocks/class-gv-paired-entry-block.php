<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

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
    if ( FALSE === $field_data ) {
      return sprintf( '<p><em>This %s doesn\'t have a paired %s</em></p>', $this_type, $other_type );
    }
    return sprintf( '<p style="color: red"><strong><em>don\'t know how to display a paired %s yet.</em></strong></p>', $other_type );
  }
}