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
    //    -- Indicate that this is a 'Business' or 'Volunteer Opportunity' (added only for is_search() pages?)
    //    -- The short_location
		?>
		<div class="entry-meta">
      <?php if ( is_search() ): ?>
      <h5 class="gv-post-type" style="margin-bottom: 0;"><?php echo 'business' === $post_type ? 'Business' : 'Volunteer Opportunity' ?></h5>
      <?php endif ?>
			<h5 class="gv-short-location" style="font-style: italic;"><?php echo get_post_meta( $post_id, 'short_location', true ); ?></h5>
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

// Hook to add the "details" content to the end of the_content()
function gv_details_content( $content ) {
  global $post;

  $post_id = $post->ID;
  $post_type = get_post_type( $post );

  // If this isn't a business or volunteer opportunity, return the unmodified content
  if ( ! in_array( $post_type, [ 'business', 'vol_opportunity' ] ) ) return $content;

  // Grab the pod for this post
  $pod = pods( $post_type, $post_id );
  if ( $pod->exists() ) {
    // Build the gv-details div one subsection at a time
    $sub_sections = array();

    // Each type has its own sections that can be populated
    // Volunteer opportunities details subsections:
    if ( 'vol_opportunity' === $post_type ) {
      //   Duration
      $duration = $pod->field( 'duration_notes' );
      if ( ! empty( $duration ) ) {
        $sub_sections[ 'duration' ] = array(
          'heading' => 'Duration',
          'text' => $duration,
        );
        // $details[] = '<h5>Duration</h5>';
        // $details[] = sprintf( '<p class="gv-duration">%s</p>', $duration );
      }
      //   Fees
      $fees = $pod->field( 'fees_notes' );
      if ( ! empty( $fees ) ) {
        $sub_sections[ 'fees' ] = array(
          'heading' => 'Fees',
          'text' => $fees,
        );
        // $details[] = '<h5>Fees</h5>';
        // $details[] = sprintf( '<p class="gv-fees">%s</p>', $fees );
      }
      //   Other Ways You Can Help
      $other_ways = $pod->field( 'other_ways_to_help' );
      if ( ! empty( $other_ways ) ) {
        $sub_sections[ 'other_ways' ] = array(
          'heading' => 'Other Ways You Can Help',
          'text' => $other_ways,
        );
        // $details[] = '<h5>Other Ways You Can Help</h5>';
        // $details[] = sprintf( '<p class="gv-other_ways">%s</p>', $other_ways );
      }
      //   Contact
      // $details[] = '<h5>Contact</h5>';
      // $details[] = sprintf( '<p>For more information about volunteering with %s, visit them online:</p>', get_the_title( $post_id ) );-
      $links = array();
      $website = $pod->field( 'organization_url' );
      if ( ! empty( $website ) ) {
        $links[ 'web' ] = $website;
        // $links[] = sprintf( '<a href="%s">%s</a>', $website, gv_get_svg_icon( 'web' ) );
      }
      $facebook = $pod->field( 'facebook_url' );
      if ( ! empty( $facebook ) ) {
        $links[ 'facebook' ] = $facebook;
        // $links[] = sprintf( '<a href="%s">%s</a>', $facebook, gv_get_svg_icon( 'facebook' ) );
      }
      $twitter = $pod->field( 'twitter_username' );
      if ( ! empty( $twitter ) ) {
        $links[ 'twitter' ] = sprintf( 'http://twitter.com/=%s', $twitter );
        // $links[] = sprintf( '<a href="http://twitter.com/=%s">%s</a>', $twitter, gv_get_svg_icon( 'twitter' ) );
      }
      if ( ! empty( $links ) ) {
        $text = sprintf( '<p>For more information about volunteering with %s, visit them online:</p>', get_the_title( $post_id ) );
        foreach ( $links as $type => $link ) {
          $text .= sprintf( '<a href="%s">%s</a>', $link, gv_get_svg_icon( $type ) );
        }
        $sub_sections[ 'contact' ] = array(
          'heading' => 'Contact',
          'text' => $text,
        ); 
        // $details[] = sprintf('<p class="gv-contact-links" style="font-size: 44px;">%s</p>', implode( ' ', $links ) );
      }
      // Volunteer button
      $vol_url = $pod->field( 'volunteer_url' );
      if ( ! empty( $vol_url ) ) {
        $sub_sections[ 'volunteer-link' ] = array(
          'text' => sprintf( '<a href="%s" class="button">Apply Now</a>', $vol_url ),
        );
        // $details[] = sprintf( '<p class="gv-volunteer-link" style="text-align: center;"><a href="%s" class="button">Apply Now</a></p>', $vol_url );
      }
    }
    // The paired business/volunteer opportunity button is a shared subsection
    $pairs = $pod->field( 'business' === $post_type ? 'paired_vol_opps' : 'paired_businesses' );
    if ( ! empty( $pairs ) && is_array( $pairs ) ) {
      $text = '';
      foreach ( $pairs as $pair ) {
        $text .= sprintf( '<a href="%s" class="button">Visit the associated business: %s</a>', $pair[ 'guid' ], $pair[ 'post_title' ] );
      }
      $index = sprintf( 'paired_%s', 'business' === $post_type ? 'vol_opp' : 'business' );
      $sub_sections[ $index ] = array(
        'text' => $text,
      );
    }

    // The default styles
    $styles = array(
      '.gv-details {
          background-color: #f0f0f0;
          padding: 10px;
          border: 1px solid black;
          border-radius: 10px;
      }',
    );
    // The subsection html
    $html = array();

    // Iterate across the subsections, filling in the styles and html
    foreach ( $sub_sections as $index => $section ) {
      if ( ! empty( $section[ 'style' ] ) ) {
        $styles[] = $section[ 'style' ];
      }
      // Assuming that if there is a subsection defined that a div needs to be created
      $html[] = sprintf( '<div class="gv-%s">', $index );
      if ( ! empty( $section[ 'heading' ] ) ) {
        $html[] = sprintf( '<h5 class="gv-%s-heading">%s</h5>', $index, $section[ 'heading' ] );
      }
      if ( ! empty( $section[ 'text' ] ) ) {
        $html[] = sprintf( '<div class="gv-%s-text">%s</div>', $index, $section[ 'text' ] );
      }
    }

    // Return the content + styles + details
    return sprintf(
      '%s <style>%s</style> <div class="gv-details">%s</div>',
      $content, implode( "\n", $styles ), implode( "\n", $html )
    );
  }

  // If we make it this far, just return the content
  return $content;

    // old shit  
    // $styles = '
    // <style>
    //   .gv-details {
    //     background-color: #f0f0f0;
    //     padding: 10px;
    //     border: 1px solid black;
    //     border-radius: 10px;
    //   }
    // </style>
    // ';
    // $details = array( '<div class="gv-details">' );

    // // Old flow
    //   $paired_business = $pod->field( 'paired_businesses' );
    //   // gv_debug( 'Paired business' );
    //   // gv_debug( $paired_business );
    //   if ( ! empty( $paired_business ) && is_array( $paired_business ) ) {
    //     foreach ( $paired_business as $bus ) {
    //       $bus_name = $bus[ 'post_title' ];
    //       $bus_url = $bus[ 'guid' ];
    //       $details[] = sprintf('
    //       <p class="gv-paired_business" style="text-align: center;">
    //         <a href="%s" class="button">Visit the associated business: %s</a>
    //       </p>', $bus_url, $bus_name );
    //     }
    //   }
    //   $details[] = '</div>';

    //   // Return the content + details
    //   return $content . $styles . implode( "\n", $details );
    // }

    // if ( 'business' === $post_type ) {
    //   $hours = $pod->field( 'hours' );
    //   if ( ! empty( $hours ) ) {
    //     $details[] = '<h5>Hours</h5>';
    //     $details[] = sprintf( '<p class="gv-hours">%s</p>', $hours );
    //   }
    //   $contact_details = array();
    //   $phone = $pod->field( 'phone_numbers' );
    //   if ( ! empty( $phone ) ) {
    //     // TODO: Consider making the phone numbers array an array of arrays, less hard coded BS
    //     $phone_details = array();
    //     for ( $i = 0; $i <= 2; $i++ ) {
    //       $num = empty( $phone[ 'number_' . $i ] ) ? '' : $phone[ 'number_' . $i ];
    //       $dsc = empty( $phone[ 'description_' . $i ] ) ? '' : $phone[ 'description_' . $i ];
    //       if ( ! empty( $num ) ) {
    //         $phone_details[] = sprintf( '%s%s', $num, empty( $dsc ) ? '' : " ($dsc)" );
    //       }
    //     }
    //     $contact_details[] = sprintf( '
    //     <div class="gv-business-contact gv-phone-numbers">
    //       <p class="gv-business-contact-icon">%s</p>
    //       <p class="gv-business-contact-data">%s</p>
    //     </div>', gv_get_svg_icon( 'phone' ), implode( '<br>', $phone_details ) );
    //     // $contact_details[] = sprintf( '<a href="%s">%s</a>', $website, gv_get_svg_icon( 'web' ) );
    //   }
    //   $website = $pod->field( 'url' );
    //   if ( ! empty( $website ) ) {
    //     $contact_details[] = sprintf( '
    //     <div class="gv-business-contact gv-website">
    //       <p class="gv-business-contact-icon">%s</p>
    //       <p class="gv-business-contact-data"><a href="%s">Website</a></p>
    //     </div>', gv_get_svg_icon( 'web' ), $website );
    //     // $links[] = sprintf( '<a href="%s">%s</a>', $website, gv_get_svg_icon( 'web' ) );
    //   }
    //   $address = $pod->field( 'address' );
    //   if ( ! empty( $address ) ) {
    //     $contact_details[] = sprintf( '
    //     <div class="gv-business-contact gv-address">
    //       <p class="gv-business-contact-icon">%s</p>
    //       <p class="gv-business-contact-data">%s</p>
    //     </div>', gv_get_svg_icon( 'home' ), $address );
    //     // $links[] = sprintf( '<a href="%s">%s</a>', $facebook, gv_get_svg_icon( 'facebook' ) );
    //   }
    //   if ( ! empty( $contact_details ) ) {
    //     $details[] = '
    //     <style>
    //       .gv-business-contact {
    //         display: flex;
    //         align-items: center;
    //         gap: 1em;
    //       }
    //       .gv-business-contact-icon,
    //       .gv-business-contact-data {
    //         margin: 0;
    //       }
    //       .gv-business-contact-icon {
    //         font-size: 44px;
    //       }
    //     </style>';
    //     $details[] = '<h5>Contact</h5>';
    //     array_push( $details, ...$contact_details );
    //     // $details[] = sprintf('<p class="gv-contact-links" style="font-size: 44px;">%s</p>', implode( "\n", $contact_details ) );
    //   }
    //   $paired_vol_opp = $pod->field( 'paired_vol_opps' );
    //   // gv_debug( 'Paired business' );
    //   // gv_debug( $paired_business );
    //   if ( ! empty( $paired_vol_opp ) && is_array( $paired_vol_opp ) ) {
    //     foreach ( $paired_vol_opp as $vol_opp ) {
    //       $vol_opp_name = $vol_opp[ 'post_title' ];
    //       $vol_opp_url = $vol_opp[ 'guid' ];
    //       $details[] = sprintf('
    //       <p class="gv-paired_vol_opp" style="text-align: center;">
    //         <a href="%s" class="button">Visit the associated volunteer opportunity: %s</a>
    //       </p>', $vol_opp_url, $vol_opp_name );
    //     }
    //   }
    //   $details[] = '</div>';

    //   // Return the content + details
    //   return $content . $styles . implode( "\n", $details );
    // }

    // If we get this far return the unadulterated content
  //   return $content;
  // }

// return $content . '<h5>This is gv_details_content</h5>';
}
add_filter( 'the_content', 'GVPlugin\gv_details_content', 10, 1 );

// TODO: Find a better place for this
// Helper funciton that returns the text for the SVG icon requested
if ( ! function_exists( 'gv_get_svg_icon' ) ) {
  function gv_get_svg_icon( $icon ) {
    $output = '';
    if ( 'location' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="-8 0 512 512" xmlns="http://www.w3.org/2000/svg"><path d="M336.5 160C322 70.7 287.8 8 248 8s-74 62.7-88.5 152h177zM152 256c0 22.2 1.2 43.5 3.3 64h185.3c2.1-20.5 3.3-41.8 3.3-64s-1.2-43.5-3.3-64H155.3c-2.1 20.5-3.3 41.8-3.3 64zm324.7-96c-28.6-67.9-86.5-120.4-158-141.6 24.4 33.8 41.2 84.7 50 141.6h108zM177.2 18.4C105.8 39.6 47.8 92.1 19.3 160h108c8.7-56.9 25.5-107.8 49.9-141.6zM487.4 192H372.7c2.1 21 3.3 42.5 3.3 64s-1.2 43-3.3 64h114.6c5.5-20.5 8.6-41.8 8.6-64s-3.1-43.5-8.5-64zM120 256c0-21.5 1.2-43 3.3-64H8.6C3.2 212.5 0 233.8 0 256s3.2 43.5 8.6 64h114.6c-2-21-3.2-42.5-3.2-64zm39.5 96c14.5 89.3 48.7 152 88.5 152s74-62.7 88.5-152h-177zm159.3 141.6c71.4-21.2 129.4-73.7 158-141.6h-108c-8.8 56.9-25.6 107.8-50 141.6zM19.3 352c28.6 67.9 86.5 120.4 158 141.6-24.4-33.8-41.2-84.7-50-141.6h-108z"/></svg>';
    }
    if ( 'duration' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 47.001 47.001" xmlns="http://www.w3.org/2000/svg">
                  <path d="M22.432,22.429c0,0.591,0.479,1.067,1.068,1.067s1.068-0.479,1.068-1.067c0.001-1.25,0.445-2.465,1.251-3.421c1.793-2.126,3.137-4.431,3.988-6.851c0.115-0.327,0.065-0.689-0.135-0.972c-0.201-0.283-0.525-0.451-0.872-0.451H18.199c-0.347,0-0.672,0.168-0.873,0.451c-0.2,0.283-0.25,0.645-0.135,0.972c0.853,2.42,2.195,4.725,3.988,6.851C21.986,19.964,22.431,21.18,22.432,22.429z"/>
                  <path d="M24.568,26.71c0-0.59-0.479-1.067-1.068-1.067s-1.068,0.479-1.068,1.067c-0.001,1.542-0.922,3.067-2.593,4.304c-3.574,2.639-6.249,5.506-7.951,8.52c-0.187,0.332-0.184,0.736,0.009,1.062c0.19,0.329,0.542,0.53,0.922,0.53h21.364c0.379,0,0.73-0.201,0.922-0.53c0.191-0.326,0.194-0.73,0.008-1.062c-1.701-3.014-4.377-5.881-7.95-8.52C25.49,29.777,24.569,28.252,24.568,26.71z"/>
                  <path d="M42.192,42.729h-0.639c-0.734-8.313-5.602-14.695-9.386-19.228c3.785-4.532,8.651-10.915,9.388-19.228h0.639c1.18,0,2.136-0.957,2.136-2.137C44.33,0.956,43.374,0,42.194,0H4.807c-1.18,0-2.136,0.957-2.136,2.136c0,1.18,0.956,2.137,2.136,2.137h0.639c0.735,8.314,5.601,14.697,9.386,19.228c-3.784,4.532-8.651,10.914-9.387,19.228H4.807c-1.18,0-2.136,0.955-2.136,2.135c0,1.181,0.956,2.138,2.136,2.138h2.671h32.044h2.672c1.18,0,2.136-0.957,2.136-2.138C44.33,43.684,43.373,42.729,42.192,42.729z M9.728,42.729c0.803-7.511,5.686-13.295,9.335-17.617l0.195-0.231c0.672-0.795,0.672-1.959,0-2.755l-0.194-0.23c-3.648-4.323-8.533-10.107-9.336-17.619h27.544c-0.803,7.512-5.688,13.296-9.336,17.619l-0.193,0.23c-0.672,0.795-0.672,1.959,0,2.755l0.195,0.231c3.648,4.322,8.531,10.106,9.334,17.617H9.728z"/>
                </svg>';
    }
    if ( 'type' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 -64 640 640" xmlns="http://www.w3.org/2000/svg"><path d="M497.941 225.941L286.059 14.059A48 48 0 0 0 252.118 0H48C21.49 0 0 21.49 0 48v204.118a48 48 0 0 0 14.059 33.941l211.882 211.882c18.744 18.745 49.136 18.746 67.882 0l204.118-204.118c18.745-18.745 18.745-49.137 0-67.882zM112 160c-26.51 0-48-21.49-48-48s21.49-48 48-48 48 21.49 48 48-21.49 48-48 48zm513.941 133.823L421.823 497.941c-18.745 18.745-49.137 18.745-67.882 0l-.36-.36L527.64 323.522c16.999-16.999 26.36-39.6 26.36-63.64s-9.362-46.641-26.36-63.64L331.397 0h48.721a48 48 0 0 1 33.941 14.059l211.882 211.882c18.745 18.745 18.745 49.137 0 67.882z"/></svg>';
    }
    if ( 'cost' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 867.801 867.8" xmlns="http://www.w3.org/2000/svg">
                  <path d="M38.3,578.85C7.7,649.25,0.2,719.05,0,770.25c-0.1,44.2,35.8,80,80,80h357.9h349.9c44.199,0,80.1-35.8,80-80c-0.101-51.2-7.7-121-38.2-191.4c-59.3-136.6-166.601-285.5-279-357.4H437.9H317.2C204.8,293.35,97.5,442.25,38.3,578.85zM489.801,575.15c-11.9-14.6-32.301-20.8-50.7-25c-22.8-5.199-43.3-10.399-63.901-21.899c-13.2-7.4-23.6-17.8-30.8-30.9c-7.1-13-10.7-28-10.7-44.399c0-29.301,10.5-53.301,31.3-71.5C377.3,370.65,394.1,363.15,413.1,359.75v-16c0-10.8,8.8-19.5,19.5-19.5h10.6c10.8,0,19.5,8.8,19.5,19.5v16.2c17.601,3.399,33.3,10.3,45,20.1c12.5,10.3,21.601,23.5,27.2,39.1c4.1,11.5-3.4,24-15.6,25.801l-10.301,1.5c-9.3,1.399-18.399-4.101-21.399-13c-2.5-7.301-5.8-13.301-9.9-17.9c-9.3-10.5-24-16.4-40.3-16.4c-17.3,0-33.701,6.8-43.8,18.101c-8.3,9.3-12.3,20-12.3,32.7c0,12.399,3.5,23,10.399,31.199c14.4,17.301,38,22.101,60.8,26.7c13.301,2.7,27.101,5.5,39,10.7c12.7,5.6,23.301,12.6,31.4,20.7c8.1,8.1,14.5,17.899,18.9,29.1c4.3,11.101,6.6,23.3,6.6,36.3c0,28.4-9.2,52.4-27.3,71.5c-14.9,15.601-35.1,26-58.4,30.101v11.8c0,10.8-8.8,19.5-19.5,19.5h-10.6c-10.8,0-19.5-8.8-19.5-19.5v-12.3c-12.8-2.4-25.101-6.601-36.101-12.2c-13-6.7-24.3-17.6-33.699-32.4c-6.301-9.899-10.801-21.3-13.601-34.1c-2.3-10.8,4.9-21.3,15.8-23.2l10.601-1.8c10.1-1.8,19.8,4.5,22.3,14.4c3,11.699,7,20.8,12,27.199c11.2,14.4,29.7,23,49.5,23l0,0c3.4,0,6.7-0.3,10-0.699c14.2-2.101,26.8-8.801,35.7-18.9c9.3-10.6,14-24,14-39.7C499.601,593.85,496.301,583.05,489.801,575.15z"/>
                  <path d="M438.101,17.55h-0.2l0,0l-152.5,0.4c-27.9,0.1-44.5,31.2-29,54.4l68.4,102.6h113.1h0.4h113.1l68.4-102.6c15.5-23.2-1.101-54.3-29-54.4l-152.5-0.4l0,0H438.101z"/>
                </svg>';
    }
    if ( 'facebook' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm3 8h-1.35c-.538 0-.65.221-.65.778v1.222h2l-.209 2h-1.791v7h-3v-7h-2v-2h2v-2.308c0-1.769.931-2.692 3.029-2.692h1.971v3z"/></svg>';
    }
    if ( 'twitter' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm6.066 9.645c.183 4.04-2.83 8.544-8.164 8.544-1.622 0-3.131-.476-4.402-1.291 1.524.18 3.045-.244 4.252-1.189-1.256-.023-2.317-.854-2.684-1.995.451.086.895.061 1.298-.049-1.381-.278-2.335-1.522-2.304-2.853.388.215.83.344 1.301.359-1.279-.855-1.641-2.544-.889-3.835 1.416 1.738 3.533 2.881 5.92 3.001-.419-1.796.944-3.527 2.799-3.527.825 0 1.572.349 2.096.907.654-.128 1.27-.368 1.824-.697-.215.671-.67 1.233-1.263 1.589.581-.07 1.135-.224 1.649-.453-.384.578-.87 1.084-1.433 1.489z"/></svg>';
    }
    if ( 'web' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 0c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm9.567 9.098c-.059-.058-.127-.108-.206-.138-.258-.101-1.35.603-1.515.256-.108-.231-.327.148-.578.008-.121-.067-.459-.52-.611-.465-.312.112.479.974.694 1.087.203-.154.86-.469 1.002-.039.271.812-.745 1.702-1.264 2.171-.775.702-.63-.454-1.159-.86-.277-.213-.274-.667-.555-.824-.125-.071-.7-.732-.694-.821l-.017.167c-.095.072-.297-.27-.319-.325 0 .298.485.772.646 1.011.273.409.42 1.005.756 1.339.179.18.866.923 1.045.908l.921-.437c.649.154-1.531 3.237-1.738 3.619-.171.321.139 1.112.114 1.49-.029.437-.374.579-.7.817-.35.255-.268.752-.562.934-.521.321-.897 1.366-1.639 1.361-.219-.001-1.151.364-1.273.007-.095-.258-.223-.455-.356-.71-.131-.25-.015-.51-.175-.731-.11-.154-.479-.502-.513-.684-.002-.157.118-.632.283-.715.231-.118.044-.462.016-.663-.048-.357-.27-.652-.535-.859-.393-.302-.189-.542-.098-.974 0-.206-.126-.476-.402-.396-.57.166-.396-.445-.812-.417-.299.021-.543.211-.821.295-.349.104-.707-.083-1.053-.126-1.421-.179-1.885-1.804-1.514-2.976.037-.192-.115-.547-.048-.696.159-.352.485-.752.768-1.021.16-.152.365-.113.553-.231.29-.182.294-.558.578-.789.404-.328.956-.321 1.482-.392.281-.037 1.35-.268 1.518-.06 0 .039.193.611-.019.578.438.023 1.061.756 1.476.585.213-.089.135-.744.573-.427.265.19 1.45.275 1.696.07.152-.125.236-.939.053-1.031.117.116-.618.125-.686.099-.122-.044-.235.115-.43.025.117.055-.651-.358-.22-.674-.181.132-.349-.037-.544.109-.135.109.062.181-.13.277-.305.155-.535-.53-.649-.607-.118-.077-1.024-.713-.777-.298l.797.793c-.04.026-.209-.289-.209-.059.053-.136.02.585-.105.35-.056-.09.091-.14.006-.271 0-.085-.23-.169-.275-.228-.126-.157-.462-.502-.644-.585-.05-.024-.771.088-.832.111-.071.099-.131.203-.181.314-.149.055-.29.127-.423.216l-.159.356c-.068.061-.772.294-.776.303.03-.076-.492-.172-.457-.324.038-.167.215-.687.169-.877-.048-.199 1.085.287 1.158-.238.029-.227.047-.492-.316-.531.069.008.702-.249.807-.364.148-.169.486-.447.731-.447.286 0 .225-.417.356-.622.133.053-.071.38.088.512-.01-.104.45.057.494.033.105-.056.691-.023.601-.299-.101-.28.052-.197.183-.255-.02.008.248-.458.363-.456-.104-.089-.398.112-.516.103-.308-.024-.177-.525-.061-.672.09-.116-.246-.258-.25-.036-.006.332-.314.633-.243 1.075.109.666-.743-.161-.816-.115-.283.172-.515-.216-.368-.449.149-.238.51-.226.659-.48.104-.179.227-.389.388-.524.541-.454.689-.091 1.229-.042.526.048.178.125.105.327-.07.192.289.261.413.1.071-.092.232-.326.301-.499.07-.175.578-.2.527-.365 2.72 1.148 4.827 3.465 5.694 6.318zm-11.113-3.779l.068-.087.073-.019c.042-.034.086-.118.151-.104.043.009.146.095.111.148-.037.054-.066-.049-.081.101-.018.169-.188.167-.313.222-.087.037-.175-.018-.09-.104l.088-.108-.007-.049zm.442.245c.046-.045.138-.008.151-.094.014-.084.078-.178-.008-.335-.022-.042.116-.082.051-.137l-.109.032s.155-.668.364-.366l-.089.103c.135.134.172.47.215.687.127.066.324.078.098.192.117-.02-.618.314-.715.178-.072-.083.317-.139.307-.173-.004-.011-.317-.02-.265-.087zm1.43-3.547l-.356.326c-.36.298-1.28.883-1.793.705-.524-.18-1.647.667-1.826.673-.067.003.002-.641.36-.689-.141.021.993-.575 1.185-.805.678-.146 1.381-.227 2.104-.227l.326.017zm-5.086 1.19c.07.082.278.092-.026.288-.183.11-.377.809-.548.809-.51.223-.542-.439-1.109.413-.078.115-.395.158-.644.236.685-.688 1.468-1.279 2.327-1.746zm-5.24 8.793c0-.541.055-1.068.139-1.586l.292.185c.113.135.113.719.169.911.139.482.484.751.748 1.19.155.261.414.923.332 1.197.109-.179 1.081.824 1.259 1.033.418.492.74 1.088.061 1.574-.219.158.334 1.14.049 1.382l-.365.094c-.225.138-.235.397-.166.631-1.562-1.765-2.518-4.076-2.518-6.611zm14.347-5.823c.083-.01-.107.167-.107.167.033.256.222.396.581.527.437.157.038.455-.213.385-.139-.039-.854-.255-.879.025 0 .167-.679.001-.573-.175.073-.119.05-.387.186-.562.193-.255.38-.116.386.032-.001.394.398-.373.619-.399z"/></svg>';
    }
    if ( 'phone' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 22.621l-3.521-6.795c-.008.004-1.974.97-2.064 1.011-2.24 1.086-6.799-7.82-4.609-8.994l2.083-1.026-3.493-6.817-2.106 1.039c-7.202 3.755 4.233 25.982 11.6 22.615.121-.055 2.102-1.029 2.11-1.033z"/></svg>';
    }
    if ( 'home' === $icon ) {
      $output = '<svg width="1em" height="1em" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M21 13v10h-6v-6h-6v6h-6v-10h-3l12-12 12 12h-3zm-1-5.907v-5.093h-3v2.093l3 3z"/></svg>';
    }
    if ( ! empty( $output ) ) {
      return sprintf( '<span class="gp-icon icon-%s">%s</span>', $icon, $output );
    }
    return '';
  }
}
