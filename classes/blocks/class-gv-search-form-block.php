<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

// Generic custom block
class GV_Search_Form_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Customized constructor
  public function __construct( $params = array() ) {
    parent::__construct( $params );

    // Fields specific to search forms
    $search_form_fields = array(
      array(
        'name' => 'label_text',
        'label' => 'Search Form Label',
        'type' => 'text',
        'default' => sprintf( 'Search %s', 'business' === $this->post_type ? 'Businesses' : 'Volunteer Opportunities' ), 
      ),
      array(
        'name' => 'button_text',
        'label' => 'Search Button Text',
        'type' => 'text',
        'default' => 'Search',
      ),
    );

    // Give two sets of text styles, one for the phone number an another for the description
    $this->attributes = array_merge(
      $this->attributes,
      $search_form_fields,
      $this->generate_text_style_fields( 'label_style', 'Search Form Label Style (TBD)' ),
      $this->generate_text_style_fields( 'button_style', 'Search Button Style (TBD)' )
    );
  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // TODO: Eventually create a style element

    // Get the interesting attributes
    $label_text = isset( $attributes[ 'label_text' ] ) ? $attributes[ 'label_text' ] : sprintf( 'Search %s', 'business' === $this->post_type ? 'Businesses' : 'Volunteer Opportunities' );
    $button_text = isset( $attributes[ 'button_text' ] ) ? $attributes[ 'button_text' ] : 'Search';
    // TODO: There might be a way to get the URL of the post type archive out of Pods
    $form_action = esc_url( home_url( 'business' === $this->post_type ? '/businesses/' : '/volunteer_opportunities/' ) );

    // Most of this was borrowed from the default WP search block to make styling easier
    return sprintf(
      '
      <form role="search" method="get" action="%s" class="wp-block-search__button-outside wp-block-search__text-button wp-block-search">
        <label for="wp-block-search__input-1" class="wp-block-search__label">%s</label>
        <div class="wp-block-search__inside-wrapper ">
          <input type="search" id="wp-block-search__input-1" class="wp-block-search__input " name="s" value="" placeholder="" required="">
          <button type="submit" class="wp-block-search__button  ">%s</button>
        </div>
      </form>
      ', $form_action, $label_text, $button_text
    );
  }
}