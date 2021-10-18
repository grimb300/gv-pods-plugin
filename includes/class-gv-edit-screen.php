<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Edit_Screen {

  /* **********
   * Properties
   * **********/

  /* *******
   * Methods
   * *******/

  // Constructor
  public function __construct() {
    // Modify the title and body placeholders
    add_filter( 'enter_title_here', array( $this, 'business_name_placeholder' ), 10, 2 );
    add_filter( 'write_your_story', array( $this, 'business_description_placeholder' ), 10, 2 );
  }

  // For business CPTs change the default title placeholder to "Business name"
  // TODO: This is only valid if the post title is used for business name
  public function business_name_placeholder( $placeholder, $post ) {
    if ( 'business' === $post->post_type ) {
      return 'Business name';
    }
    return $placeholder;
  }

  // For business CPTs change the default body placeholder to "Business description, start writing or type / to choose a block"
  // TODO: This is only valid using the block editor and if the post body is used for business description
  public function business_description_placeholder( $placeholder, $post ) {
    if ( 'business' === $post->post_type ) {
      return 'Business description, start writing or type / to choose a block';
    }
    return $placeholder;
  }
}