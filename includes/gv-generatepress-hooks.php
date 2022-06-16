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
}
add_filter( 'generate_blog_columns', 'GVPlugin\extend_blog_columns', 10, 1 );

// Hook to add the business and vol_opp post meta info after the title
// TODO: Figure out how to make this a GP Element
if ( ! function_exists( 'gv_bus_and_vol_post_meta' ) ) {
	function gv_bus_and_vol_post_meta() {
    global $post;

    $post_id = $post->ID;
    $post_type = get_post_type( $post );

    // If this isn't a business or volunteer opportunity, return nothing
    if ( ! in_array( $post_type, [ 'business', 'vol_opportunity' ] ) ) return;

    // Echo the meta info for this bus/vol
    // For now, this is:
    //    -- Indicate that this is a 'Business' or 'Volunteer Opportunity' (Should this be added only for is_search() pages?)
    //    -- The short_location
		?>
		<div class="entry-meta">
      <p class="gv-post-type"><?php echo 'business' === $post_type ? 'Business' : 'Volunteer Opportunity' ?></p>
			<p class="gv-short-location"><?php echo get_post_meta( $post_id, 'short_location', true ); ?></p>
		</div>
		<?php
	}
}
add_action( 'generate_after_entry_title', 'GVPlugin\gv_bus_and_vol_post_meta' );

// Hook to add the business and vol_opp post meta info after the content
// TODO: Figure out how to make this a GP Element
if ( ! function_exists( 'gv_bus_and_vol_footer_meta' ) ) {
	function gv_bus_and_vol_footer_meta() {
    global $post;

    $post_id = $post->ID;
    $post_type = get_post_type( $post );

    // If this isn't a business or volunteer opportunity, return nothing
    if ( ! in_array( $post_type, [ 'business', 'vol_opportunity' ] ) ) return;

    // Generalizing the structure of the post meta info
    $template = '<span class="cat-links">%s<span class="screen-reader-text">%s </span>%s</span> ';
    $before_terms = '';
    $term_separator = ', ';
    $after_terms = '';

    $business_taxonomies = array(
      array(
        'icon' => 'location',
        'label' => 'Business Locations',
        'slug' => 'business_location',
      ),
      array(
        'icon' => 'type',
        'label' => 'Business Types',
        'slug' => 'business_type',
      ),
    );
    $volunteer_taxonomies = array(
      array(
        'icon' => 'location',
        'label' => 'Volunteer Opportunity Locations',
        'slug' => 'volunteer_location',
      ),
      array(
        'icon' => 'type',
        'label' => 'Volunteer Opportunity Types',
        'slug' => 'volunteer_type',
      ),
      array(
        'icon' => 'cost',
        'label' => 'Volunteer Opportunity Cost',
        'slug' => 'volunteer_cost_label',
      ),
      array(
        'icon' => 'duration',
        'label' => 'Volunteer Opportunity Duration',
        'slug' => 'volunteer_duration',
      ),
    );

		?>
		<footer <?php generate_do_attr( 'footer-entry-meta' ); ?>>
      <?php
      if ( 'business' === $post_type ) {
        foreach ( $business_taxonomies as $tax ) {
          echo sprintf(
            $template,
            gv_get_svg_icon( $tax[ 'icon' ] ),
            $tax[ 'label' ],
            get_the_term_list( $post_id, $tax[ 'slug' ], $before_terms, $term_separator, $after_terms )
          );
        }
      }
      if ( 'vol_opportunity' === $post_type ) {
        foreach ( $volunteer_taxonomies as $tax ) {
          echo sprintf(
            $template,
            gv_get_svg_icon( $tax[ 'icon' ] ),
            $tax[ 'label' ],
            get_the_term_list( $post_id, $tax[ 'slug' ], $before_terms, $term_separator, $after_terms )
          );
        }
      }
      ?>
		</footer>
		<?php
	}
}
add_action( 'generate_after_entry_content', 'GVPlugin\gv_bus_and_vol_footer_meta' );

// TODO: Find a better place for this
// Helper funciton that returns the text for the SVG icon requested
if ( ! function_exists( 'gv_get_svg_icon' ) ) {
  function gv_get_svg_icon( $icon ) {
    $output = '';
    if ( 'location' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="-8 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M336.5 160C322 70.7 287.8 8 248 8s-74 62.7-88.5 152h177zM152 256c0 22.2 1.2 43.5 3.3 64h185.3c2.1-20.5 3.3-41.8 3.3-64s-1.2-43.5-3.3-64H155.3c-2.1 20.5-3.3 41.8-3.3 64zm324.7-96c-28.6-67.9-86.5-120.4-158-141.6 24.4 33.8 41.2 84.7 50 141.6h108zM177.2 18.4C105.8 39.6 47.8 92.1 19.3 160h108c8.7-56.9 25.5-107.8 49.9-141.6zM487.4 192H372.7c2.1 21 3.3 42.5 3.3 64s-1.2 43-3.3 64h114.6c5.5-20.5 8.6-41.8 8.6-64s-3.1-43.5-8.5-64zM120 256c0-21.5 1.2-43 3.3-64H8.6C3.2 212.5 0 233.8 0 256s3.2 43.5 8.6 64h114.6c-2-21-3.2-42.5-3.2-64zm39.5 96c14.5 89.3 48.7 152 88.5 152s74-62.7 88.5-152h-177zm159.3 141.6c71.4-21.2 129.4-73.7 158-141.6h-108c-8.8 56.9-25.6 107.8-50 141.6zM19.3 352c28.6 67.9 86.5 120.4 158 141.6-24.4-33.8-41.2-84.7-50-141.6h-108z"/></svg>';
    }
    if ( 'duration' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                  <path d="M22.432,22.429c0,0.591,0.479,1.067,1.068,1.067s1.068-0.479,1.068-1.067c0.001-1.25,0.445-2.465,1.251-3.421c1.793-2.126,3.137-4.431,3.988-6.851c0.115-0.327,0.065-0.689-0.135-0.972c-0.201-0.283-0.525-0.451-0.872-0.451H18.199c-0.347,0-0.672,0.168-0.873,0.451c-0.2,0.283-0.25,0.645-0.135,0.972c0.853,2.42,2.195,4.725,3.988,6.851C21.986,19.964,22.431,21.18,22.432,22.429z"/>
                  <path d="M24.568,26.71c0-0.59-0.479-1.067-1.068-1.067s-1.068,0.479-1.068,1.067c-0.001,1.542-0.922,3.067-2.593,4.304c-3.574,2.639-6.249,5.506-7.951,8.52c-0.187,0.332-0.184,0.736,0.009,1.062c0.19,0.329,0.542,0.53,0.922,0.53h21.364c0.379,0,0.73-0.201,0.922-0.53c0.191-0.326,0.194-0.73,0.008-1.062c-1.701-3.014-4.377-5.881-7.95-8.52C25.49,29.777,24.569,28.252,24.568,26.71z"/>
                  <path d="M42.192,42.729h-0.639c-0.734-8.313-5.602-14.695-9.386-19.228c3.785-4.532,8.651-10.915,9.388-19.228h0.639c1.18,0,2.136-0.957,2.136-2.137C44.33,0.956,43.374,0,42.194,0H4.807c-1.18,0-2.136,0.957-2.136,2.136c0,1.18,0.956,2.137,2.136,2.137h0.639c0.735,8.314,5.601,14.697,9.386,19.228c-3.784,4.532-8.651,10.914-9.387,19.228H4.807c-1.18,0-2.136,0.955-2.136,2.135c0,1.181,0.956,2.138,2.136,2.138h2.671h32.044h2.672c1.18,0,2.136-0.957,2.136-2.138C44.33,43.684,43.373,42.729,42.192,42.729z M9.728,42.729c0.803-7.511,5.686-13.295,9.335-17.617l0.195-0.231c0.672-0.795,0.672-1.959,0-2.755l-0.194-0.23c-3.648-4.323-8.533-10.107-9.336-17.619h27.544c-0.803,7.512-5.688,13.296-9.336,17.619l-0.193,0.23c-0.672,0.795-0.672,1.959,0,2.755l0.195,0.231c3.648,4.322,8.531,10.106,9.334,17.617H9.728z"/>
                </svg>';
    }
    if ( 'type' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M497.941 225.941L286.059 14.059A48 48 0 0 0 252.118 0H48C21.49 0 0 21.49 0 48v204.118a48 48 0 0 0 14.059 33.941l211.882 211.882c18.744 18.745 49.136 18.746 67.882 0l204.118-204.118c18.745-18.745 18.745-49.137 0-67.882zM112 160c-26.51 0-48-21.49-48-48s21.49-48 48-48 48 21.49 48 48-21.49 48-48 48zm513.941 133.823L421.823 497.941c-18.745 18.745-49.137 18.745-67.882 0l-.36-.36L527.64 323.522c16.999-16.999 26.36-39.6 26.36-63.64s-9.362-46.641-26.36-63.64L331.397 0h48.721a48 48 0 0 1 33.941 14.059l211.882 211.882c18.745 18.745 18.745 49.137 0 67.882z"/></svg>';
    }
    if ( 'cost' == $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                  <path d="M38.3,578.85C7.7,649.25,0.2,719.05,0,770.25c-0.1,44.2,35.8,80,80,80h357.9h349.9c44.199,0,80.1-35.8,80-80c-0.101-51.2-7.7-121-38.2-191.4c-59.3-136.6-166.601-285.5-279-357.4H437.9H317.2C204.8,293.35,97.5,442.25,38.3,578.85zM489.801,575.15c-11.9-14.6-32.301-20.8-50.7-25c-22.8-5.199-43.3-10.399-63.901-21.899c-13.2-7.4-23.6-17.8-30.8-30.9c-7.1-13-10.7-28-10.7-44.399c0-29.301,10.5-53.301,31.3-71.5C377.3,370.65,394.1,363.15,413.1,359.75v-16c0-10.8,8.8-19.5,19.5-19.5h10.6c10.8,0,19.5,8.8,19.5,19.5v16.2c17.601,3.399,33.3,10.3,45,20.1c12.5,10.3,21.601,23.5,27.2,39.1c4.1,11.5-3.4,24-15.6,25.801l-10.301,1.5c-9.3,1.399-18.399-4.101-21.399-13c-2.5-7.301-5.8-13.301-9.9-17.9c-9.3-10.5-24-16.4-40.3-16.4c-17.3,0-33.701,6.8-43.8,18.101c-8.3,9.3-12.3,20-12.3,32.7c0,12.399,3.5,23,10.399,31.199c14.4,17.301,38,22.101,60.8,26.7c13.301,2.7,27.101,5.5,39,10.7c12.7,5.6,23.301,12.6,31.4,20.7c8.1,8.1,14.5,17.899,18.9,29.1c4.3,11.101,6.6,23.3,6.6,36.3c0,28.4-9.2,52.4-27.3,71.5c-14.9,15.601-35.1,26-58.4,30.101v11.8c0,10.8-8.8,19.5-19.5,19.5h-10.6c-10.8,0-19.5-8.8-19.5-19.5v-12.3c-12.8-2.4-25.101-6.601-36.101-12.2c-13-6.7-24.3-17.6-33.699-32.4c-6.301-9.899-10.801-21.3-13.601-34.1c-2.3-10.8,4.9-21.3,15.8-23.2l10.601-1.8c10.1-1.8,19.8,4.5,22.3,14.4c3,11.699,7,20.8,12,27.199c11.2,14.4,29.7,23,49.5,23l0,0c3.4,0,6.7-0.3,10-0.699c14.2-2.101,26.8-8.801,35.7-18.9c9.3-10.6,14-24,14-39.7C499.601,593.85,496.301,583.05,489.801,575.15z"/>
                  <path d="M438.101,17.55h-0.2l0,0l-152.5,0.4c-27.9,0.1-44.5,31.2-29,54.4l68.4,102.6h113.1h0.4h113.1l68.4-102.6c15.5-23.2-1.101-54.3-29-54.4l-152.5-0.4l0,0H438.101z"/>
                </svg>';
    }
    if ( ! empty( $output ) ) {
      return sprintf( '<span class="gp-icon icon-%s">%s</span>', $icon, $output );
    }
    return '';
  }
}
