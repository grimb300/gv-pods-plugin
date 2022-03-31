<?php

class PodsField_GV_Duration extends PodsField {
  public static $type = 'gv_duration';
  public static $label = 'GV Duration';
  public static $prepare = '%s';
  public static $file_path = '';

  public function __construct() {
    self::$label = __( 'GV Duration', 'pods' );

    // TODO: Figure out how to update/get so a single number is saved to post_meta instead of [ number, unit, open_ended ]
    // Update/Get post meta filters
    // add_filter( 'pods_meta_update_post_meta', array( $this, 'gv_duration_update_post_meta' ), 10, 2 );
  }

  // public function gv_duration_update_post_meta( $ret_val, $args ) {
  //   GVPlugin\gv_debug( 'pods_meta_update_post_meta called with args' );
  //   GVPlugin\gv_debug( $args );
  // }

  public function options() {

    $options = array(
      self::$type . '_default_min_number' => array(
        'label' => __( 'Minimum Duration Default Value', 'pods' ),
        'description' => __( sprintf( 'Default value range %s to %s', GVPlugin\GV_Duration_Helper::$duration_min, GVPlugin\GV_Duration_Helper::$duration_max ), 'pods' ),
        'number_min' => GVPlugin\GV_Duration_Helper::$duration_min,
        'number_max' => GVPlugin\GV_Duration_Helper::$duration_max,
        'number_html5' => "1", // required to get <input type="number">
        'default' => GVPlugin\GV_Duration_Helper::$duration_min,
        'type' => 'number',
      ),
      self::$type . '_default_min_unit' => array(
        'label' => __( 'Minimum Duration Default Unit', 'pods' ),
        'type' => 'pick',
        'data' => GVPlugin\GV_Duration_Helper::$duration_units,
        'default' => 'days',
      ),
      self::$type . '_default_max_number' => array(
        'label' => __( 'Maximum Duration Default Value', 'pods' ),
        'description' => __( sprintf( 'Default value range %s to %s', GVPlugin\GV_Duration_Helper::$duration_min, GVPlugin\GV_Duration_Helper::$duration_max ), 'pods' ),
        'number_min' => GVPlugin\GV_Duration_Helper::$duration_min,
        'number_max' => GVPlugin\GV_Duration_Helper::$duration_max,
        'number_html5' => "1", // required to get <input type="number">
        'default' => GVPlugin\GV_Duration_Helper::$duration_max,
        'type' => 'number',
      ),
      self::$type . '_default_max_unit' => array(
        'label' => __( 'Maximum Duration Default Unit', 'pods' ),
        'type' => 'pick',
        'data' => GVPlugin\GV_Duration_Helper::$duration_units,
        'default' => 'days',
      ),
      self::$type . '_default_open_ended' => array(
        // 'label' => __( 'Duration Default Open Ended', 'pods' ),
        'type' => 'boolean',
        'boolean_yes_label' => __( 'Duration is Open Ended', 'pods' ),
      ),
    );

    return $options;
  }

  public function schema( $options = null ) {
    // Using INT, 4 bytes
    $schema = 'INT';
    return $schema;
  }

  // Point Pods to the custom input view for this field type
  public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

    // GVPlugin\gv_debug( $name . ': gv_duration input called with options' );
    foreach ( $options as $key => $value ) {
      if ( preg_match( '/^' . self::$type . '/', $key ) ) {
        // GVPlugin\gv_debug( '  ' . $key . ' => ' . $value );
      }
    }

    // This is a value that pods-address-field uses, not sure what it does yet
    $form_field_type = PodsForm::$field_type;

    // Get the defaults out of the options
    // If default is open ended, the max value should be the max and max unit should be days
    // GVPlugin\gv_debug( 'options' );
    // GVPlugin\gv_debug( $options, true );
    // GVPlugin\gv_debug( 'Fallback for default_min_number: ' . GVPlugin\GV_Duration_Helper::$duration_min );
    $default_min_number = pods_v( self::$type . '_default_min_number', $options, GVPlugin\GV_Duration_Helper::$duration_min );
    $default_min_unit = pods_v( self::$type . '_default_min_unit', $options, 'days' );
    $default_open_ended = pods_v( self::$type . '_default_open_ended', $options, '0' );
    $default_max_number = $default_open_ended ? GVPlugin\GV_Duration_Helper::$duration_max : pods_v( self::$type . '_default_max_number', $options, GVPlugin\GV_Duration_Helper::$duration_max );
    $default_max_unit = $default_open_ended ? 'days' : pods_v( self::$type . '_default_max_unit', $options, 'days' );
    // GVPlugin\gv_debug( $name . ': Default value: ' . $default_value . ', Default unit: ' . $default_unit . ", Default open_ended: " . $default_open_ended );

    $view = GV_PLUGIN_PATH . 'ui/fields/gv-duration.php';
    // TODO: pods-address-field example adds a filter here to allow other plugins to change the view

    // Disable REACT DFV for now (is this still necessary?)
    $options[ 'disable_dfv' ] = true;

    pods_view( $view, compact( array_keys( get_defined_vars() ) ) );
  }

  // Point Pods to the custom front end display view for this field type
  // NOTE: This is used by the Pods Field Value block and I suspect magic tags.
  //       I shouldn't care too much about this if I create a custom block for GV.
  public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

    $value_raw = $value;
    // NOTE: This appears to get called for each subfield (number_0/1/2 and description_0/1/2)
    // return '<p>This is the GV Phone Numbers raw value (' . $name . '):</p><code>' . $value_raw . '</code>';

    $view = GV_PLUGIN_PATH . 'ui/front/gv-duration.php';
    // TODO: pods-address-field example adds a a filter here to allow other plugins to change the view
    
    $output = pods_view( $view, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );
    // TODO: pods-address-field example adds a a filter here to allow other plugins to change the output

    return $output;
  }

  // I think this override may be necessary to force one "display" call on the entire field
  // instead of a "display" call on each element of the field (number_0/1/2 and description_0/1/2)
  public function display_list( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
    return call_user_func_array( array( $this, 'display' ), func_get_args() );
  }
}