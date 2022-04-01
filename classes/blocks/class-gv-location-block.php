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

  // Display the field
  protected function format_field_data( $field_data = null, $attributes = array() ) {
    global $post;
    // $pod = pods( $this->post_type, $post->ID );
    // $return_string = $pod->display( $this->field_name );
    // gv_debug( $return_string );
    // return $return_string;
    // wp_enqueue_script( 'googlemaps' );
    // wp_enqueue_script( 'pods-maps' );
    // wp_enqueue_style( 'pods-maps' );

    // gv_debug( 'map field data' );
    // gv_debug( $field_data );

    // TODO: This is a quick and dirty way to display the current business on a map.
    //       Eventually upgrade this to have markers for other businesses and parameterize it better.
    //       Someday it might even use the pods map type. :)
    $lat = $field_data['geo']['lat'];
    $lng = $field_data['geo']['lng'];
    // Really dumb way of adding the rest of the businesses to the map. Create an array of all other businesses.
    // FIXME: Should make this a little smarter at some point to display only the businesses that are visible
    //        or within some reasonable distance from the business on this page.
    // gv_debug( sprintf( 'This post is ID %s', $post->ID ) );
    $other_businesses = pods( 'business', array(
      'limit' => -1,
      'where' => 't.ID NOT IN (' . $post->ID . ')',
    ) );
    // Create a JavaScript array with interesting info
    $js_other_businesses_object = array();
    // If businesses were found
    if ( $other_businesses->total() > 0 ) {
      // Loop over the businesses
      while ( $other_businesses->fetch() ) {
        $location = $other_businesses->field( $this->field_name );
        $name = $other_businesses->field( 'business_name' );
        // gv_debug( sprintf( '%s at lat: %s, lng: %s', $name, $location['geo']['lat'], $location['geo']['lng'] ) );
        // Add the business to the JS object only if the lat and lng are floats
        if ( is_float( $location['geo']['lat'] ) && is_float( $location['geo']['lng'] ) ) {
          $js_other_businesses_object[] = sprintf( '
          { name: "%s", location: { lat: %s, lng: %s } }', $name, $location['geo']['lat'], $location['geo']['lng'] );
        }
      }
    }
    // gv_debug( sprintf( 'Pods found %s matching businesses', $other_businesses->total() ) );
    $gv_settings = get_option( 'gv_settings' );
    $api_key = $gv_settings[ 'google_maps_js_api_key' ];
    $field_data = '
    <style>
    /* Set the size of the div element that contains the map */
    #map {
      height: 400px;
      width: 100%;
    }
    </style>
    <script>
    // Initialize and add the map
    function initMap() {
      // The location of the business
      const business = { lat: ' . $lat . ', lng: ' . $lng . ' };
      // The other businesses (temporary)
      const otherBusinesses = [ ' . implode( ', ', $js_other_businesses_object ) . ' ];
      // The map, centered on the business
      const map = new google.maps.Map(document.getElementById("map"), {
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
    <div id="map"></div>
    <script
      src="https://maps.googleapis.com/maps/api/js?key=' . $api_key . '&callback=initMap&libraries=&v=weekly"
      async
    ></script>
    ';

    return $field_data;
    return '<p style="color: red"><strong><em>don\'t know how to display a location field yet.</em></strong></p>';
  }
}