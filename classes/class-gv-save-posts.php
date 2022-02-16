<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Save_Posts {
  // TODO: Might be able to make this class entirely static functions

  public function __construct() {
    // FIXME: See what kind of refactoring I can do between vol and bus
    //        Get rid of debug messages
    //        Add other vol taxonomies that we need to save based on fields
    add_action( 'save_post_business', array( $this, 'gv_save_business' ), 999, 3 );
    add_action( 'save_post_vol_opportunity', array( $this, 'gv_save_vol_opportunity' ), 999, 3 );
    // add_filter( 'pre_wp_unique_post_slug', array( $this, 'gv_pre_unique_post_slug'), 999, 6 );
    add_action( 'updated_post_meta', array( $this, 'gv_report_internal_slug' ), 999, 4 );
    add_filter( 'update_post_metadata', array( $this, 'gv_check_internal_slug' ), 999, 4 );
  }

  public function gv_check_internal_slug( $check, $object_id, $meta_key, $meta_value ) {
    if ( 'internal_slug' === $meta_key ) {
      gv_debug( 'Checking the internal slug update to ' . $meta_value );
    }
    // If this is an update for internal slug and the value is being updated to is blank
    if ( 'internal_slug' === $meta_key && empty( $meta_value ) ) {
      // This is the pods update of the meta value, skip it by returning false
      gv_debug( 'Saw blank update of internal slug' );
      return false;
    }
    return $check;
  }

  public function gv_report_internal_slug( $meta_id, $object_id, $meta_key, $meta_value ) {
    if ( 'internal_slug' === $meta_key ) {
      gv_debug( 'Updated internal slug to ' . $meta_value );
    }
  }

  public function gv_pre_unique_post_slug( $override_slug, $slug, $post_id, $post_status, $post_type, $post_parent ) {
    
    gv_debug( sprintf(
      'In pre_wp_unique_post_slug with override_slug(%s), slug(%s), post_id(%s), post_status(%s)',
      $override_slug,
      $slug,
      $post_id,
      $post_status
    ) );
  }

  public function gv_save_business( $post_id, $post, $update ) {
    global $post_data;
    
    // I expect the business name and internal slug to be in the $_POST array
    if ( empty( $_POST[ 'pods_meta_business_name' ] ) && empty( $_POST[ 'pods_meta_internal_slug' ] ) ) {
      // If they're not there, return
      return;
    }

    // Set up the $postarr just in case an update is needed
    $postarr = array( 'ID' => $post_id );
    $need_post_update = false;

    // gv_debug( sprintf(
    //   'Business saved with business name (%s) and internal slug (%s)',
    //   isset( $_POST[ 'pods_meta_business_name' ] ) ? $_POST[ 'pods_meta_business_name' ] : '---',
    //   isset( $_POST[ 'pods_meta_internal_slug' ] ) ? $_POST[ 'pods_meta_internal_slug' ] : '---'
    // ) );

    // Update the post title if it is different than the business name
    if ( $post->post_title !== $_POST[ 'pods_meta_business_name' ] ) {
      $postarr[ 'post_title' ] = $_POST[ 'pods_meta_business_name' ];
      $need_post_update = true;
    }

    // If the internal slug being is empty, this is the first save for this post
    if ( empty( $_POST[ 'pods_meta_internal_slug' ] ) ) {
      // By default, trust the post name (slug) generated during the initial post update
      $new_internal_slug = $post->post_name;
      // If the post title is being updated, update the post name as well
      if ( $need_post_update && isset( $postarr[ 'post_title' ] ) ) {
        $new_internal_slug = wp_unique_post_slug(
          sanitize_title( $_POST[ 'pods_meta_business_name' ] ),
          $post_id,
          $post->post_status,
          $post->post_type,
          $post->post_parent
        );
        $postarr[ 'post_name' ] = $new_internal_slug;
      }
      // Update the internal slug
      gv_debug( 'Updating internal_slug to ' . $new_internal_slug );
      update_post_meta( $post_id, 'internal_slug', $new_internal_slug );
    }

    // If a post update is needed...
    if ( $need_post_update ) {
      // ...to avoid an infinite loop, unhook this function...
      remove_action( 'save_post_business', array( $this, 'gv_save_business' ), 999 );
      // ...update the post...
      wp_update_post( $postarr );
      // ...then re-hook this function
      add_action( 'save_post_business', array( $this, 'gv_save_business' ), 999, 3 );
    }
  }

  public function gv_save_vol_opportunity( $post_id, $post, $update ) {
    global $post_data;
    
    // I expect the business name and internal slug to be in the $_POST array
    if ( empty( $_POST[ 'pods_meta_volunteer_name' ] ) && empty( $_POST[ 'pods_meta_internal_slug' ] ) ) {
      // If they're not there, return
      return;
    }

    // Set up the $postarr just in case an update is needed
    $postarr = array( 'ID' => $post_id );
    $need_post_update = false;

    // gv_debug( sprintf(
    //   'Business saved with business name (%s) and internal slug (%s)',
    //   isset( $_POST[ 'pods_meta_business_name' ] ) ? $_POST[ 'pods_meta_business_name' ] : '---',
    //   isset( $_POST[ 'pods_meta_internal_slug' ] ) ? $_POST[ 'pods_meta_internal_slug' ] : '---'
    // ) );

    // Update the post title if it is different than the business name
    if ( $post->post_title !== $_POST[ 'pods_meta_volunteer_name' ] ) {
      $postarr[ 'post_title' ] = $_POST[ 'pods_meta_volunteer_name' ];
      $need_post_update = true;
    }

    // If the internal slug being is empty, this is the first save for this post
    if ( empty( $_POST[ 'pods_meta_internal_slug' ] ) ) {
      // By default, trust the post name (slug) generated during the initial post update
      $new_internal_slug = $post->post_name;
      // If the post title is being updated, update the post name as well
      if ( $need_post_update && isset( $postarr[ 'post_title' ] ) ) {
        $new_internal_slug = wp_unique_post_slug(
          sanitize_title( $_POST[ 'pods_meta_volunteer_name' ] ),
          $post_id,
          $post->post_status,
          $post->post_type,
          $post->post_parent
        );
        $postarr[ 'post_name' ] = $new_internal_slug;
      }
      // Update the internal slug
      gv_debug( 'Updating internal_slug to ' . $new_internal_slug );
      update_post_meta( $post_id, 'internal_slug', $new_internal_slug );
    }

    // If a post update is needed...
    if ( $need_post_update ) {
      // ...to avoid an infinite loop, unhook this function...
      remove_action( 'save_post_vol_opportunity', array( $this, 'gv_save_vol_opportunity' ), 999 );
      // ...update the post...
      wp_update_post( $postarr );
      // ...then re-hook this function
      add_action( 'save_post_vol_opportunity', array( $this, 'gv_save_vol_opportunity' ), 999, 3 );
    }
  }
}