<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Hook to extend the column blog layout in GenerateBlocks Premium
// to business and vol_opportunity archive pages
if ( ! function_exists( 'extend_blog_columns' ) ) {
  function extend_blog_columns( $columns ) {
    // Grab the column_layout setting (if I can)
    $generate_blog_settings = wp_parse_args(
			get_option( 'generate_blog_settings', array() ),
			generate_blog_get_defaults()
		);
    $col_layout_is_enabled = ( $generate_blog_settings['column_layout'] ) ? true : false;

    // Check if column layout is eanbled and this is a business or vol_opportunity archive
    if ( $col_layout_is_enabled && is_archive() && in_array( get_post_type(), [ 'business', 'vol_opportunity' ] ) ) {
      // If so, force true      
      return true;
    }
    // Else, return the original value
    return $columns;
  }
  add_filter( 'generate_blog_columns', 'GVPlugin\extend_blog_columns', 10, 1 );
}