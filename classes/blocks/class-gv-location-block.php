<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Generic custom block
class GV_Location_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Display a location field
  protected function format_field_data( $field_data = null ) {
    return '<p style="color: red"><strong><em>don\'t know how to display a location field yet.</em></strong></p>';
  }
}