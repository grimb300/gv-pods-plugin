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
        'label' => __( 'Phone Number Count', 'pods' ),
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
}