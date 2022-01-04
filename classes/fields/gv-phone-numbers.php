<?php

class PodsField_GV_Phone_Numbers extends PodsField {
  public static $type = 'gv_phone_numbers';
  public static $label = 'GV Phone Numbers';
  public static $prepare = '%s';
  public static $file_path = '';

  public function __construct() {
    self::$label = __( 'GV Phone Numbers', 'pods' );
  }

  public function options() {

    $options = array(
      self::$type . '_count' => array(
        'label' => __( 'Phone Number Max Count', 'pods' ),
        'default' => '3',
        'type' => 'pick',
        'data' => array(
          '1' => __( 'One', 'pods' ),
          '2' => __( 'Two', 'pods' ),
          '3' => __( 'Three', 'pods' ),
        ),
      ),
    );

    return $options;
  }

  public function schema( $options = null ) {
    $schema = 'LONGTEXT';
    return $schema;
  }

  // Point Pods to the custom input view for this field type
  public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

    // This is a value that pods-address-field uses, not sure what it does yet
    $form_field_type = PodsForm::$field_type;

    $view = GV_PLUGIN_PATH . 'ui/fields/gv-phone-numbers.php';
    // TODO: pods-address-field example adds a filter here to allow other plugins to change the view

    // Disable REACT DFV for now (is this still necessary?)
    $options[ 'disable_dfv' ] = true;

    pods_view( $view, compact( array_keys( get_defined_vars() ) ) );
  }
}