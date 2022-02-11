<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

// Generic custom block
class GV_Phone_Numbers_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Customized constructor
  public function __construct( $params = array() ) {
    parent::__construct( $params );

    // Give two sets of text styles, one for the phone number an another for the description
    $this->attributes = array_merge(
      $this->attributes,
      $this->generate_text_style_fields( 'num', 'Phone Number' ),
      $this->generate_text_style_fields( 'dsc', 'Description' )
    );
  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    // If field data is empty, return nothing
    if ( empty( $field_data ) ) return;

    $formatted_data = '
      <style>
        .gv_phone_numbers_wrap {
          display: grid;
          grid-template-columns: 20ch 40ch;
          column-gap: 10px;
          row-gap: 10px;
        }
    ';
    // Get the text styles for the phone number and description
    $num_styles = $this->generate_text_style_attributes( $attributes, 'num' );
    if ( ! empty( $num_styles ) ) {
      $formatted_data .= sprintf( '.gv_phone_numbers_number{%s;}', implode( ';', $num_styles ) );
    }
    $dsc_styles = $this->generate_text_style_attributes( $attributes, 'dsc' );
    if ( ! empty( $dsc_styles ) ) {
      $formatted_data .= sprintf( '.gv_phone_numbers_description{%s;}', implode( ';', $dsc_styles ) );
    }
    $formatted_data .= '
      </style>
      <div class="gv_phone_numbers_wrap">
        <div class="gv_phone_numbers_number">Phone Number</div>
        <div class="gv_phone_numbers_description">Description</div>
    ';
    // TODO: Find out how to pull the max phone numbers info out of the field definition
    $max_phone_numbers = 3;
    for ( $i = 0; $i < $max_phone_numbers; $i++ ) {
      if ( ! empty( $field_data[ 'number_' . $i ] ) ) {
        $formatted_data .= '<div class="gv_phone_numbers_number">';
        $formatted_data .= $field_data[ 'number_' . $i ];
        $formatted_data .= '</div>';
        $formatted_data .= '<div class="gv_phone_numbers_description">';
        $formatted_data .= $field_data[ 'description_' . $i ];
        $formatted_data .= '</div>';
      }
    }
    $formatted_data .= '</div>';

    return $formatted_data;
  }
}