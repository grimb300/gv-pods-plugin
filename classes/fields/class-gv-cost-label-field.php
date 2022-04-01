<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Using the pods-address-field plugin as an example
gv_debug( 'class-gv-cost-label-field.php is loaded' );

class GV_Cost_Label_Field extends PodsField {

  /* **********
   * Properties
   * **********/

  // Field type identifier
  public static $type = 'gv_cost_label';
  // Field type label
  public static $label = 'Cost Label';
  // Field type preparation
  // (don't know what this is used for yet)
  public static $prepare = '%s';
  // File path to related files of this field typ
  // (don't know what this is used for yet)
  public static $file_path = '';

  /* *******
   * Methods
   * *******/

  public function __construct() {
    self::$label = __( 'Cost Label', 'pods' );;
  }

  public function options() {

    $options = array(
      self::$type . '_default_value' => array(
        'label' => __( 'Cost Label Default Value', 'pods' ),
        'description' => __( 'Default cost label', 'pods' ),
        'default' => 0,
        'type' => 'number',
      ),
    );

    return $options;
  }

  public function schema( $options = null ) {
    // Using INT, 4 bytes
    $schema = 'INT';
    return $schema;
  }

  public function display( $value = null, $name = null, $options = null, $pod = null, $id = null  ) {

    $value_raw = $value;
    $display_type = pods_v( self::$type . '_display_type', $options );

    // I think this filter is used to allow other plugins to change the PHP file describing how the field is displayed
    // For example, the pods-maps plugin adds the 'Lat/Long' type
    $view = GV_PLUGIN_PATH . 'ui/front/gv-cost-label.php';
    $view = apply_filters( 'pods_ui_field_gv_cost_label_display_view', $view, $display_type, $value, $name, $options, $pod, $id );

    // I think this filter is used to allow other plugins to change the value being displayed
    $output = pods_view( $view, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );
    $output = apply_filters( 'pods_ui_field_gv_cost_label_display_value', $output, $value, $view, $display_type, $name, $options, $pod, $id );

    return $output;
  }

  // According to pods-address-field
  // Change the way the a list of values of the field are displayed with Pods::field
  public function display_list( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
    return call_user_func_array( array( $this, 'display' ), func_get_args() );
  }

  public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

    $form_field_type = PodsForm::$field_type;

    $type = pods_v( self::$type . '_type', $options, 'address' );

    // Text type is handled within the phone numbers field view
    // I think this filter is used to allow other plugins to change the PHP file describing how the field input is displayed
    $view = GV_PLUGIN_PATH . 'ui/fields/gv-cost-label.php';
    $view = apply_filters( 'pods_ui_field_gv_cost_label_input_view', $view, $type, $name, $value, $options, $pod, $id );

    // Disable REACT DFV for now (?)
    $options[ 'disable_dfv' ] = true;

    if ( ! empty( $view ) ) {
      pods_view( $view, compact( array_keys( get_defined_vars() ) ) );
    }

    // I think this action is used to allow other plugins to add a view to the input
    do_action( 'pods_ui_field_gv_cost_label_input_view_extra', $view, $type, $name, $value, $options, $pod, $id );
  }

  public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

    // TODO: Validate each returned value (phone number part) for variable type and content (sanitizing)
    $errors = array();

    // I think this filter is used to allow other plugins to do some of the validation
    $errors = apply_filters( 'pods_ui_field_gv_cost_label_validate', $errors, $value, $type, $name, $options, $fields, $pod, $id, $params );

    if ( empty( array_filter( $value ) ) && 1 === pods_v( 'required', $options ) ) {
      $errors[] = __( 'This field is required.', 'pods' );
    }

    if ( ! empty( $errors ) ) {
      return $errors;
    }

    return true;
  }

  public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

    $type = pods_v( self::$type . '_type', $options );

    // Adds extra value sanitization
    $value = apply_filters( 'pods_ui_field_gv_cost_label_pre_save', $value, $type, $id, $name, $options, $fields, $pod, $params );

    return $value;
  }

  // What does this do?
  public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
    return $value;
  }

  // Convert the value for output display based on the field options
  // I might not need this since I'm not planning to output directly
  public static function format_value_for_output( $value, $options ) {
    // pods-address-field does a lot of manipulation
    
    return $value;
  }
  
  // Convert the field format into HTML for display
  // I might not need this since I'm not planning to output directly
  public static function format_to_html( $format, $value, $options ) {

    $output = '';

    $value = self::format_value_for_output( $value, $options );

    if ( ! empty( $value[ 'gv_cost_label' ] ) ) {
      $phone_numbers = $value[ 'gv_cost_label' ];
    }

    if ( ! empty( $phone_numbers ) ) {
      // Do some slick formatting stuff
      $output = $value;
    }

    return $output;
  }

}