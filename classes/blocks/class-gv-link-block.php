<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

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
    parent::__construct( $params );

    // Add attribute fields specific to a link block
    $link_block_attributes = array(
      array(
        'name' => 'link_text',
        'label' => 'Link Text',
        'type' => 'text',
        'description' => 'Leaving this blank will display the URL',
      ),
      array(
        'name' => 'new_window',
        // 'label' => 'Open Link in New Window',
        'type' => 'boolean',
        'boolean_yes_label' => 'Open link in new tab'
      ),
    );
    $this->attributes = array_merge(
      $this->attributes,
      $link_block_attributes,
      $this->generate_text_style_fields()
    );

  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // If field data is empty, return nothing
    if ( empty( $field_data ) ) return;

    $url = 'twitter_username' === $this->field_name ? sprintf( 'https://twitter.com/%s', $field_data ) : $field_data;
    $link_text = empty( $attributes[ 'link_text' ] ) ? $field_data : $attributes[ 'link_text' ];
    $target = ! empty( $attributes[ 'new_window' ] ) && $attributes[ 'new_window' ] ? ' target="_blank"' : '';

    // Create the link, if no link text provided use the field data
    $link_styles = $this->generate_text_style_attributes( $attributes );
    $style_attribute = empty( $link_styles ) ? '' : sprintf( ' style="%s"', implode( ';', $link_styles ) );
    $formatted_field_data = sprintf( '<div%s><a href="%s"%s>%s</a></div>', $style_attribute, $url, $target, $link_text );

    // Return the formatted field data
    return $formatted_field_data;
  }
}
