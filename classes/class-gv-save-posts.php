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
    add_action( 'updated_post_meta', array( $this, 'gv_updated_post_cost_suggestion' ), 999, 4 );
    add_action( 'updated_term_meta', array( $this, 'gv_updated_term_cost_suggestion' ), 999, 4 );
    add_action( 'updated_post_meta', array( $this, 'gv_updated_post_durations' ), 999, 4 );
    add_action( 'updated_term_meta', array( $this, 'gv_updated_term_durations' ), 999, 4 );
  }

  public function gv_updated_post_durations( $meta_id, $post_id, $meta_key, $meta_value ) {
    // Call the more generic function with the update type (post or term)
    $new_min_max = $this->gv_updated_durations( 'post', $meta_id, $post_id, $meta_key, $meta_value );
    // If gv_updated_durations returned null or an unexpected result, do nothing
    if ( ! is_array( $new_min_max ) || ! array_key_exists( 'min', $new_min_max ) || ! array_key_exists( 'max', $new_min_max ) ) return;
    // Otherwise, update the post with the terms matching the new min/max
    // gv_debug( 'New post durations detected, update the terms' );
    $duration_term_ids = get_terms(
      array(
        'taxonomy' => 'volunteer_duration',
        'hide_empty' => false,
        'fields' => 'ids',
      )
    );
    // gv_debug( 'All the duration term IDs' );
    // gv_debug( $duration_term_ids );
    // Filter out the terms that don't overlap the post duration
    $matching_term_ids = array_filter(
      $duration_term_ids,
      function ( $term_id ) use ( $new_min_max ) {
        // Get the min/max of this term
        // FIXME: Hardcoded meta_key, is this worth making more generic
        $term_min = intval( get_term_meta( $term_id, '_gv_duration_min_in_days__duration', true ) );
        $term_max = intval( get_term_meta( $term_id, '_gv_duration_max_in_days__duration', true ) );

        return GV_Duration_Helper::durations_overlap( array(
          $new_min_max, // Should already be an array
          array( 'min' => $term_min, 'max' => $term_max )
        ) );
      }
    );
    // gv_debug( 'The matching duration term IDs' );
    // gv_debug( $matching_term_ids );
    // Update the terms for this post, overwrite previous terms
    wp_set_post_terms( $post_id, $matching_term_ids, 'volunteer_duration', false );
  }

  public function gv_updated_term_durations( $meta_id, $term_id, $meta_key, $meta_value ) {
    // Call the more generic function with the update type (post or term)
    $new_min_max = $this->gv_updated_durations( 'term', $meta_id, $term_id, $meta_key, $meta_value );
    // gv_debug( 'new_min_max returned' );
    // gv_debug( $new_min_max );
    // If gv_updated_durations returned null or an unexpected result, do nothing
    if ( ! is_array( $new_min_max ) || ! array_key_exists( 'min', $new_min_max ) || ! array_key_exists( 'max', $new_min_max ) ) return;
    // Otherwise, iterate across all vol_opp posts and add/remove this term based on the new min/max
    // TODO: This seems like it could be expensive or at the very least not scalable, analyze later
    // gv_debug( 'New term durations detected, update the posts' );
    // Get the post IDs of all published vol_opps
    $post_ids = get_posts( array(
      'post_type' => 'vol_opportunity',
      'fields' => 'ids',
      'numberposts' => -1,
      'post_status' => 'publish',
    ) );
    // gv_debug( 'All published vol_ops' );
    // gv_debug( $post_ids );
    foreach( $post_ids as $post_id ) {
      // Get the min/max of this vol_opp
      // FIXME: Hardcoded meta_key, is this worth making more generic
      $post_min = intval( get_post_meta( $post_id, '_gv_duration_min_in_days__duration', true ) );
      $post_max = intval( get_post_meta( $post_id, '_gv_duration_max_in_days__duration', true ) );
      // Check for overlap
      $durations = array(
        $new_min_max, // Should already be an array
        array( 'min' => $post_min, 'max' => $post_max )
      );
      // FIXME: Might be able to improve perf by adding term only if term doesn't exist
      //        or removing term only if it exists. Use has_term( $term, $taxonomy, $post )
      // gv_debug( 'Checking durations overlap' );
      // gv_debug( $durations );
      if ( 4305 === $post_id ) {
        gv_debug( 'Checking FAME with durations' );
        gv_debug( $durations );
      }
      if ( GV_Duration_Helper::durations_overlap( $durations ) ) {
        if ( 4305 === $post_id ) {
          gv_debug( 'It overlaps' );
        }
          if ( ! has_term( $term_id, 'volunteer_duration', $post_id ) ) {
          gv_debug( sprintf( 'Adding term_id %s to post_id %s because it was not already set', $term_id, $post_id ) );
        }
        // If so, add this term to the post, append to existing term list
        wp_set_post_terms( $post_id, $term_id, 'volunteer_duration', true );
      } else {
        if ( has_term( $term_id, 'volunteer_duration', $post_id ) ) {
          gv_debug( sprintf( 'Removing term_id %s from post_id %s because it was already set', $term_id, $post_id ) );
        }
        if ( 4305 === $post_id ) {
          gv_debug( 'It does not overlap' );
        }
        // Otherwise, remove this term from the post
        wp_remove_object_terms( $post_id, $term_id, 'volunteer_duration' );
      }
    }
  }

  private function gv_updated_durations( $update_type, $meta_id, $object_id, $meta_key, $meta_value ) {
    // Return null if this isn't a duration update
    if ( empty( $meta_value[ 'field_type' ] ) || 'gv_duration' !== $meta_value[ 'field_type' ] ) return null;
    // gv_debug( sprintf( 'Working on a gv_duration %s meta update', $update_type ) );

    // Calculate min/max_in_days
    $min_in_days = GV_Duration_Helper::calculate_duration_in_days( pods_v( 'min_number', $meta_value ), pods_v( 'min_unit', $meta_value ) );
    $max_in_days = GV_Duration_Helper::calculate_duration_in_days( pods_v( 'max_number', $meta_value ), pods_v( 'max_unit', $meta_value ) );
    // gv_debug( 'New range is ' . $min_in_days . ' to ' . $max_in_days . ' days' );

    // Grab the old min/max_in_days
    $meta_key_min = '_gv_duration_min_in_days__' . $meta_key;
    $meta_key_max = '_gv_duration_max_in_days__' . $meta_key;
    $old_min_in_days = intval( get_metadata( $update_type, $object_id, $meta_key_min, true ) );
    $old_max_in_days = intval( get_metadata( $update_type, $object_id, $meta_key_max, true ) );

    // If the old and new values are the same, no change necessary, return null
    if ( $old_min_in_days === $min_in_days && $old_max_in_days === $max_in_days ) return null;

    // Update the post or term meta with hidden values _gv_duration_min_in_days__<$meta_key> and _gv_duration_max_in_days__<$meta_key>
    update_metadata( $update_type, $object_id, $meta_key_min, $min_in_days );
    update_metadata( $update_type, $object_id, $meta_key_max, $max_in_days );

    // Return the new min/max values
    return array( 'min' => $min_in_days, 'max' => $max_in_days );
  }

  public function gv_updated_post_cost_suggestion ( $meta_id, $post_id, $meta_key, $meta_value ) {
    // Call the more generic function with the update type (post or term)
    $new_cost_suggestion = $this->gv_updated_cost_suggestion( 'post', $meta_id, $post_id, $meta_key, $meta_value );

    // If it returned null, do nothing
    if ( null === $new_cost_suggestion ) return;

    // Otherwise, update the terms using the new number
    $matching_term_ids = get_terms(
      array(
        'taxonomy' => 'volunteer_cost_label',
        'hide_empty' => false,
        'fields' => 'ids',
        'meta_query' => array(
          array(
            'key' => '_gv_cost_label_number__cost_suggestion',
            'value' => $new_cost_suggestion,
            'compare' => '=',
          ),
        ),
      )
    );
    wp_set_post_terms( $post_id, $matching_term_ids, 'volunteer_cost_label', false );
  }

  public function gv_updated_term_cost_suggestion ( $meta_id, $term_id, $meta_key, $meta_value ) {
    // Call the more generic function with the update type (post or term)
    $new_cost_suggestion = $this->gv_updated_cost_suggestion( 'term', $meta_id, $term_id, $meta_key, $meta_value );
    
    // If it returned null, do nothing
    if ( null === $new_cost_suggestion ) return;
    gv_debug( sprintf( 'Updated term_id %s to cost_suggestion %s', $term_id, $new_cost_suggestion ) );

    // Otherwise, update the posts using the new number
    // First step, find all posts associated with this term
    $post_ids = get_posts( array(
      'post_type' => 'vol_opportunity',
      'fields' => 'ids',
      'numberposts' => -1,
      'post_status' => 'publish',
      'tax_query' => array(
        array(
          'taxonomy' => 'volunteer_cost_label',
          'field' => 'term_id',
          'terms' => $term_id,
        ),
      ),
    ) );
    // Then remove the term from the matching posts
    if ( count( $post_ids ) > 0 ) {
      gv_debug( sprintf( 'Removing term_id %s from posts %s', $term_id, implode( ', ', $post_ids ) ) );
    }
    foreach ( $post_ids as $post_id ) {
      wp_remove_object_terms( $post_id, $term_id, 'volunteer_cost_label' );
    }
    // Step two, find all posts with the new cost_suggestion
    $post_ids = get_posts( array(
      'post_type' => 'vol_opportunity',
      'fields' => 'ids',
      'numberposts' => -1,
      'post_status' => 'publish',
      'meta_query' => array(
        array(
          'key' => '_gv_cost_label_number__cost_suggestion',
          'value' => $new_cost_suggestion,
          'compare' => '=',
        ),
      ),
    ) );
    // And add the term
    if ( count( $post_ids ) > 0 ) {
      gv_debug( sprintf( 'Adding term_id %s to posts %s', $term_id, implode( ', ', $post_ids ) ) );
    }
    foreach ( $post_ids as $post_id ) {
      wp_set_post_terms( $post_id, $term_id, 'volunteer_cost_label', true );
    }
  }

  private function gv_updated_cost_suggestion( $update_type, $meta_id, $object_id, $meta_key, $meta_value ) {
    // Return unless this is a cost_suggestion update
    if ( empty( $meta_value[ 'field_type' ] ) || 'gv_cost_label' !== $meta_value[ 'field_type' ] ) return null;
    gv_debug( 'gv_updated_cost_suggestion: Working on meta key ' . $meta_key );

    // Get the new value just updated
    $new_cost_suggestion = intval( $meta_value[ 'number' ] );

    // Grab the old hidden number value
    $hidden_meta_key = '_gv_cost_label_number__' . $meta_key;
    $old_number = intval( get_metadata( $update_type, $object_id, $hidden_meta_key, true ) );

    // If old and new values are the same, no change necessary, return null
    if ( $old_number === $new_cost_suggestion ) return null;

    // Update the post or term meta with the hidden value
    update_metadata( $update_type, $object_id, $hidden_meta_key, $new_cost_suggestion );

    // Return the new number
    return $new_cost_suggestion;

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
      // gv_debug( 'Found matching cost suggestion ' . implode( ', ', array_map( function ( $term ) { return $term->name; }, $cost_suggestion_terms ) ) );
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
    // gv_debug(
    //   sprintf(
    //     'Saw set terms:
    //     object_id  - %s
    //     terms      - %s
    //     tt_ids     - %s
    //     taxonomy   - %s
    //     append     - %s
    //     old_tt_ids - %s',
    //     $object_id,
    //     implode( ', ', $terms ),
    //     implode( ', ', $tt_ids ),
    //     $taxonomy,
    //     $append ? 'true' : 'false',
    //     implode( ', ', $old_tt_ids )
    //   )
    // );
    return;
  }

  public function gv_monitor_internal_slug( $check, $object_id, $meta_key, $meta_value ) {
    // Skip any blank internal_slug meta value updates by returning something other than null
    if ( 'internal_slug' === $meta_key && empty( $meta_value ) ) return false;
    return $check;
  }

  public function gv_report_internal_slug( $meta_id, $object_id, $meta_key, $meta_value ) {
    if ( 'internal_slug' === $meta_key ) {
      // gv_debug( 'Updated internal slug to ' . $meta_value );
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

    // TODO: This works for vol_opps despite not removing/adding the action for that post type, consider removing entirely.
    // If a post update is needed...
    if ( $need_post_update ) {
      // ...to avoid an infinite loop, unhook this function...
      $hook = 'save_post_' . $post->post_type;
      // gv_debug( 'Need to update the ' . $post->post_type . ' post, removing the ' . $hook . ' action' );
      remove_action( $hook, array( $this, 'gv_sync_name_field_to_post_title' ), 999 );
      // ...update the post...
      wp_update_post( $postarr );
      // ...then re-hook this function
      add_action( $hook, array( $this, 'gv_sync_name_field_to_post_title' ), 999, 3 );
    }
  }

}