<?php

class PodsField_GV_Duration extends PodsField {
  public static $type = 'gv_duration';
  public static $label = 'GV Duration';
  public static $prepare = '%s';
  public static $file_path = '';

  public function __construct() {
    self::$label = __( 'GV Duration', 'pods' );
  }

  public function options() {

    $options = array(
      self::$type . '_default_value' => array(
        'label' => __( 'Duration Default Value', 'pods' ),
        'description' => __( sprintf( 'Default value range %s to %s', GV_VOLUNTEER_DURATION_MIN, GV_VOLUNTEER_DURATION_MAX ), 'pods' ),
        'default' => 0,
        'type' => 'number',
      ),
      self::$type . '_default_open_ended' => array(
        'label' => __( 'Duration Default Open Ended', 'pods' ),
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

    // This is a value that pods-address-field uses, not sure what it does yet
    $form_field_type = PodsForm::$field_type;

    // Get the defaults out of the options
    $default_value = pods_v( self::$type . '_default_value', $options, '0' );
    $default_open_ended = pods_v( self::$type . '_default_open_ended', $options, 'true' );

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