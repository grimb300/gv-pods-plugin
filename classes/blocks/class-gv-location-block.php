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
    // global $post;
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
    // Initialize and add teh map
    function initMap() {
      // The location of the business
      const business = { lat: ' . $lat . ', lng: ' . $lng . ' };
      // The map, centered at Uluru
      const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 12,
        center: business,
      });
      // The marker, positioned at the business
      const marker = new google.maps.Marker({
        position: business,
        map: map,
      });
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