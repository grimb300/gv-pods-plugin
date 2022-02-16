<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Save_Posts {
  // TODO: Might be able to make this class entirely static functions

  public function __construct() {
    // FIXME: See what kind of refactoring I can do between vol and bus
    //        Get rid of debug messages
    //        Add other vol taxonomies that we need to save based on fields
    add_action( 'save_post_business', array( $this, 'gv_sync_name_field_to_post_title' ), 999, 3 );
    add_action( 'save_post_vol_opportunity', array( $this, 'gv_sync_name_field_to_post_title' ), 999, 3 );
    add_filter( 'update_post_metadata', array( $this, 'gv_check_internal_slug' ), 999, 4 );
    // Right now this is only used to report updates to the internal_slug meta value
    // add_action( 'updated_post_meta', array( $this, 'gv_report_internal_slug' ), 999, 4 );
  }

  public function gv_check_internal_slug( $check, $object_id, $meta_key, $meta_value ) {
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