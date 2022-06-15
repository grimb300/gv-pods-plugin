<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Require the default block file
require_once GV_PLUGIN_PATH . 'classes/blocks/class-gv-default-block.php';

// Generic custom block
class GV_Location_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Static helper function
  // Pull lat/lng out of the location data
  // Returns array with keys "lat" and "lng" if successful. Empty array otherwise.
  private static function get_lat_lng( $data ) {
    // Attempt a conversion only if the input has the expected format
    if ( ! empty( $data['geo'] ) && is_array( $data['geo'] ) ) {
      // Convert the values withing the geo array into floats
      $converted_data = array_map(
        function ( $val ) { return floatval( $val ); },
        $data['geo']
      );
      // Filter out the values that aren't floats
      $filtered_data = array_filter(
        $converted_data,
        function ( $val ) { return is_float( $val ); }
      );
      // If filtered array contains expected values, return them.
      if ( array_key_exists( 'lat', $filtered_data ) && array_key_exists( 'lng', $filtered_data ) ) {
        return $filtered_data;
      }
    }
    // If we make it this far, fail
    return array();
  }

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;

    // Get the lat/lng values out of $field_data
    $this_location = self::get_lat_lng( $field_data );

    // Really dumb way of adding the rest of the businesses to the map. Create an array of all other businesses.
    // FIXME: Should make this a little smarter at some point to display only the businesses that are visible
    //        or within some reasonable distance from the business on this page.
    // gv_debug( sprintf( 'This post is ID %s', $post->ID ) );
    $other_businesses = pods( 'business', array(
      'limit' => -1,
      'where' => 't.ID NOT IN (' . $post->ID . ')',
    ) );
    // Create a JavaScript array with interesting info.
    $js_other_businesses_object = array();
    // If businesses were found
    if ( $other_businesses->total() > 0 ) {
      // Loop over the businesses
      while ( $other_businesses->fetch() ) {
        $location = self::get_lat_lng( $other_businesses->field( $this->field_name ) );
        $name = $other_businesses->field( 'business_name' );
        // Add the business to the JS object only if the location is valid
        if ( ! empty( $location ) ) {
          $js_other_businesses_object[] = sprintf( '
          { name: "%s", location: { lat: %s, lng: %s } }', $name, $location['lat'], $location['lng'] );
        }
      }
    }
    // gv_debug( sprintf( 'Pods found %s matching businesses', $other_businesses->total() ) );
    $gv_settings = get_option( 'gv_settings' );
    $api_key = $gv_settings[ 'google_maps_js_api_key' ];
    // Style the map height/width
    $field_data = '
    <style>
    /* Set the size of the div element that contains the map */
    #gv-location-map {
      height: 400px;
      width: 100%;
    }
    </style>
    ';
    // If the location of this buisiness is valid, add the JS controlling the map
    if ( ! empty( $this_location ) ) {
      $field_data .= '
      <script>
      // Initialize and add the map
      function initMap() {
        // The location of the business
        // FIXME: Right now if either lat or lng are null, maps will throw an exception. Maybe this is okay?
        const business = { lat: ' . $this_location['lat'] . ', lng: ' . $this_location['lng'] . ' };
        // The other businesses (temporary)
        const otherBusinesses = [ ' . implode( ', ', $js_other_businesses_object ) . ' ];
        // The map, centered on the business
        const map = new google.maps.Map(document.getElementById("gv-location-map"), {
          zoom: 12,
          center: business,
        });
        // The marker, positioned at the business
        const marker = new google.maps.Marker({
          position: business,
          map: map,
        });
        // Add markers for the other businesses (temporary)
        // FIXME: Need to play with the size, url, shape, etc. of the image
        const otherBusinessMarkerImage = {
          url: "http://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_red.png"
        };
        const otherBusinessMarkers = otherBusinesses.map( ( otherBusiness ) => {
          return new google.maps.Marker({
            position: otherBusiness.location,
            icon: otherBusinessMarkerImage,
            map: map,
          });
        } );
      }
      </script>
      ';
    }
    // The div that will be converted into a map
    $field_data .= '
    <div id="gv-location-map">
      <style>
        #gv-location-map {
          background-color: #b0b0b0;
          display: flex;
          justify-content: center;
          align-items: center;
        }
      </style>
      <h4>Unable to display the map at this time</h4>
    </div>
    ';
    // Finally, the maps JS from Google, if we have a valid location to display
    if ( ! empty( $this_location ) ) {
      $field_data .= '
      <script
        src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&callback=initMap&libraries=&v=weekly"
        async
      ></script>
      ';
    }
    return $field_data;
    return '<p style="color: red"><strong><em>don\'t know how to display a location field yet.</em></strong></p>';
  }

}