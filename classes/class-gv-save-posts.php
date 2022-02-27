<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Save_Posts {
  // TODO: Might be able to make this class entirely static functions

  public function __construct() {
    // FIXME: Add other vol taxonomies that we need to save based on fields
    add_action( 'save_post_business', array( $this, 'gv_sync_name_field_to_post_title' ), 999, 3 );
    add_action( 'save_post_vol_opportunity', array( $this, 'gv_sync_name_field_to_post_title' ), 999, 3 );
    add_filter( 'update_post_metadata', array( $this, 'gv_monitor_internal_slug' ), 999, 4 );
    // Right now this is only used to report updates to the internal_slug meta value
    // add_action( 'updated_post_meta', array( $this, 'gv_report_internal_slug' ), 999, 4 );
    add_action( 'set_object_terms', array( $this, 'gv_monitor_set_terms' ), 999, 6 );
    add_action( 'updated_post_meta', array( $this, 'gv_updated_cost_suggestion' ), 999, 4 );
    add_action( 'updated_post_meta', array( $this, 'gv_updated_durations' ), 999, 4 );
    add_action( 'updated_term_meta', array( $this, 'gv_updated_durations' ), 999, 4 );
  }

  // Duplicating some definitions here only because I don't know for sure where to put it yet
  private static $duration_max = "1073741823"; // max from the Rails days
  private static $duration_week_in_days = 7;
  private static $duration_month_in_days = 30;
  private static $duration_year_in_days = 365;

  private static function calculate_duration_in_days( $num, $unit ) {
    if ( 'weeks' === $unit ) return $num * self::$duration_week_in_days;
    if ( 'months' === $unit ) return $num * self::$duration_month_in_days;
    if ( 'years' === $unit ) return $num * self::$duration_year_in_days;
    return $num;
  }

  public function gv_updated_durations( $meta_id, $object_id, $meta_key, $meta_value ) {
    // Return unless this is a duration update
    if ( empty( $meta_value[ 'field_type' ] ) || 'gv_duration' !== $meta_value[ 'field_type' ] ) return;
    gv_debug( 'Working on a gv_duration update' );
    // Return if the min/max_in_days fields have already been calculated
    if ( isset( $meta_value[ 'min_in_days' ] ) && $meta_value[ 'max_in_days' ] ) return;
    gv_debug( 'Need to calculate min/max_in_days' );
    // Calculate min/max_in_days
    $min_in_days = self::calculate_duration_in_days( $meta_value[ 'min_number' ], $meta_value[ 'min_unit' ] );
    $max_in_days = self::calculate_duration_in_days( $meta_value[ 'max_number' ], $meta_value[ 'max_unit' ] );
    gv_debug( 'New range is ' . $min_in_days . ' to ' . $max_in_days . ' days' );
    // If open_ended is set, 
    return;
    if ( ! in_array( $meta_key, [ 'min_duration', 'max_duration' ] ) ) return;
    gv_debug( 'gv_updated_durations: Working on meta key ' . $meta_key );

    // TODO: I think that I need to create a new field type that allows the user
    //       to enter a duration as a (number, units) pair.
    //       This will make the query more straightforward.
    // The current state of the duration meta fields (post and terms) requires
    // a query of all the terms and then do a comparison manually.
    $duration_terms = get_terms(
      array(
        'taxonomy' => 'volunteer_duration',
        'hide_empty' => false,
      )
    );
    // gv_debug( sprintf( 'Found %s durations', count( $duration_terms ) ) );

    // Since the min/max_durations are updated independently, get the other value through get_post_meta
    $min_duration = 'min_duration' === $meta_key ? $meta_value : get_post_meta( $object_id, 'min_duration', true );
    $max_duration = 'max_duration' === $meta_key ? $meta_value : get_post_meta( $object_id, 'max_duration', true );
    // Doing more cleanup
    // if ( empty( $min_duration ) ) $min_duration = 0;
    // if ( empty( $max_duration ) ) $max_duration = 1073741823;
    gv_debug( sprintf( 'Working with post duration %s to %s days', $min_duration, $max_duration ) );

    // Filter the duration terms using the min/max_duration
    $matching_terms = array_filter(
      $duration_terms,
      function ( $term ) use ( $min_duration, $max_duration ) {
        // Get the term min/max calculated in days
        $term_min = get_term_meta( $term->term_id, 'min', true );
        // Not sure why, but 0 weeks min returns a null value, fix that here
        if ( empty( $term_min ) ) $term_min = 0;
        $term_min_units = get_term_meta( $term->term_id, 'min_units', true );
        // gv_debug( sprintf( 'Term min for %s is %s %s', $term->name, $term_min, $term_min_units ) );
        $term_min_in_days = 'weeks' === $term_min_units ? 7 * $term_min : (
          'months' === $term_min_units ? 30 * $term_min : (
            'years' === $term_min_units ? 365 * $term_min : $term_min
          )
        );
        // More cleanup
        if ( $term_min_in_days > 1073741823 ) $term_min_in_days = 1073741823;

        $term_max = get_term_meta( $term->term_id, 'max', true );
        $term_max_units = get_term_meta( $term->term_id, 'max_units', true );
        $term_max_in_days = 'weeks' === $term_max_units ? 7 * $term_max : (
          'months' === $term_max_units ? 30 * $term_max : (
            'years' === $term_max_units ? 365 * $term_max : $term_max
          )
        );
        // More cleanup
        if ( $term_max_in_days > 1073741823 ) $term_max_in_days = 1073741823;

        // Figure out if the term covers at least part of the post's duration
        // if ( $term_min_in_days >= $min_duration ) gv_debug( sprintf( 'Term min (%s) is greater than the post min (%s)', $term_min_in_days, $min_duration ) );
        // if ( $term_min_in_days <= $max_duration ) gv_debug( sprintf( 'Term min (%s) is less    than the post max (%s)', $term_min_in_days, $max_duration ) );
        // if ( $term_max_in_days >= $min_duration ) gv_debug( sprintf( 'Term max (%s) is greater than the post min (%s)', $term_max_in_days, $min_duration ) );
        // if ( $term_max_in_days <= $max_duration ) gv_debug( sprintf( 'Term max (%s) is less    than the post max (%s)', $term_max_in_days, $max_duration ) );
        return ( ( $term_min_in_days >= $min_duration ) && ( $term_min_in_days <= $max_duration ) ) ||
               ( ( $term_max_in_days >= $min_duration ) && ( $term_max_in_days <= $max_duration ) );
      }
    );

    // If at least one term found, update the post
    if ( count( $matching_terms ) > 0 ) {
      gv_debug( 'Found matching durations ' . implode( ', ', array_map( function ( $term ) { return $term->name; }, $matching_terms ) ) );
      // $matching_term_ids = array_map( function ($t) { return $t->term_id; }, $matching_terms );
      // gv_debug( 'matching term ids: ' . implode( ', ', $matching_term_ids ) );
      // wp_set_post_terms( $object_id, $matching_term_ids, '$taxonomy:string', $append:boolean )
      wp_set_post_terms(
        $object_id,
        array_map( function ( $term ) { return $term->term_id; }, $matching_terms ),
        'volunteer_duration',
        false
      );
      gv_debug( 'Should return here' );
      return;
    }
    
    // If we make it this far, something went wrong
    gv_debug( sprintf(
      'ERROR: Unexpected number of terms matching the cost_suggestion of %s (%s)',
      $meta_value, count( $matching_terms )
    ) );
  }

  public function gv_updated_cost_suggestion( $meta_id, $object_id, $meta_key, $meta_value ) {
    // Return unless this is a cost_suggestion update
    if ( 'cost_suggestion' !== $meta_key ) return;
    gv_debug( 'gv_updated_cost_suggestion: Working on meta key ' . $meta_key );

    // Look up the term that corresponds to this cost suggestion
    $cost_suggestion_terms = get_terms(
      array(
        'taxonomy' => 'volunteer_cost_label',
        'hide_empty' => false,
        'meta_query' => array(
          array(
            'key' => 'cost_suggestion',
            'value' => $meta_value,
            'compare' => '=',
          ),
        ),
      )
    );

    // If one term found, update the post
    if ( 1 === count( $cost_suggestion_terms ) ) {
      gv_debug( 'Found matching cost suggestion ' . implode( ', ', array_map( function ( $term ) { return $term->name; }, $cost_suggestion_terms ) ) );
      wp_set_post_terms(
        $object_id,
        array_map( function ( $term ) { return $term->term_id; }, $cost_suggestion_terms ),
        'volunteer_cost_label',
        false
      );
      return;
    }
    
    // If we make it this far, something went wrong
    gv_debug( sprintf(
      'ERROR: Unexpected number of terms matching the cost_suggestion of %s (%s)',
      $meta_value, count( $cost_suggestion_terms )
    ) );
  }

  public function gv_monitor_set_terms( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    gv_debug(
      sprintf(
        'Saw set terms:
        object_id  - %s
        terms      - %s
        tt_ids     - %s
        taxonomy   - %s
        append     - %s
        old_tt_ids - %s',
        $object_id,
        implode( ', ', $terms ),
        implode( ', ', $tt_ids ),
        $taxonomy,
        $append ? 'true' : 'false',
        implode( ', ', $old_tt_ids )
      )
    );
  }

  public function gv_monitor_internal_slug( $check, $object_id, $meta_key, $meta_value ) {
    // Skip any blank internal_slug meta value updates by returning something other than null
    if ( 'internal_slug' === $meta_key && empty( $meta_value ) ) return false;
    return $check;
  }

  public function gv_report_internal_slug( $meta_id, $object_id, $meta_key, $meta_value ) {
    if ( 'internal_slug' === $meta_key ) {
      gv_debug( 'Updated internal slug to ' . $meta_value );
    }
  }

  public function gv_sync_name_field_to_post_title( $post_id, $post, $update ) {
    global $post_data;
    
    // Pull the important data out of the $_POST array based on the post_type
    $name_field_idx = 'business' === $post->post_type ? 'pods_meta_business_name' : (
      'vol_opportunity' === $post->post_type ? 'pods_meta_volunteer_name' : ''
    );
    $name_meta = empty( $_POST[ $name_field_idx ] ) ? '' : $_POST[ $name_field_idx ];
    $slug_meta = empty( $_POST[ 'pods_meta_internal_slug' ] ) ? '' : $_POST[ 'pods_meta_internal_slug' ];
    
    // If no data to act on, return
    if ( empty( $name_meta ) && empty( $slug_meta ) ) return;

    // Set up the $postarr just in case a post update is needed
    $postarr = array( 'ID' => $post_id );
    $need_post_update = false;

    // Update the post title if it is different than the business name
    if ( $post->post_title !== $name_meta ) {
      $postarr[ 'post_title' ] = $name_meta;
      $need_post_update = true;
    }

    // If the internal slug being is empty, this is the first save for this post
    if ( empty( $slug_meta ) ) {
      // By default, trust the post name (slug) generated during the initial post update
      $new_internal_slug = $post->post_name;
      // If the post title is being updated, update the post name as well
      if ( $need_post_update && isset( $postarr[ 'post_title' ] ) ) {
        $new_internal_slug = wp_unique_post_slug(
          sanitize_title( $name_meta ),
          $post_id,
          $post->post_status,
          $post->post_type,
          $post->post_parent
        );
        $postarr[ 'post_name' ] = $new_internal_slug;
      }
      // Update the internal slug
      update_post_meta( $post_id, 'internal_slug', $new_internal_slug );
    }

    // If a post update is needed...
    if ( $need_post_update ) {
      // ...to avoid an infinite loop, unhook this function...
      remove_action( 'save_post_business', array( $this, 'gv_sync_name_field_to_post_title' ), 999 );
      // ...update the post...
      wp_update_post( $postarr );
      // ...then re-hook this function
      add_action( 'save_post_business', array( $this, 'gv_sync_name_field_to_post_title' ), 999, 3 );
    }
  }

}