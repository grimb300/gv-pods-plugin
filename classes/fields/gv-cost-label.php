<?php

class PodsField_GV_Cost_Label extends PodsField {
  public static $type = 'gv_cost_label';
  public static $label = 'GV Cost Label';
  public static $prepare = '%s';
  public static $file_path = '';

  public function __construct() {
    self::$label = __( 'GV Cost Label', 'pods' );
  }

  public function options() {

    $options = array(
      // TODO: If I want to go crazy, I could add an option to change the "$" to some other symbol, or allow internationalization
      self::$type . '_default_min_number' => array(
        'label' => __( 'Default Minimum Number of "$"\'s', 'pods' ),
        'description' => __( 'A value of 0 (zero) "$"\'s will be displayed as "FREE"', 'pods' ),
        'number_min' => 0,
        'number_max' => 1,
        'number_html5' => "1", // required to get <input type="number">
        'default' => 0,
        'type' => 'number',
      ),
      self::$type . '_default_max_number' => array(
        'label' => __( 'Default Maximum Number of "$"\'s', 'pods' ),
        'number_min' => 1,
        'number_max' => 10, // Somewhat arbitrary
        'number_html5' => "1", // required to get <input type="number">
        'default' => 3,
        'type' => 'number',
      ),
      self::$type . '_default_number_default' => array(
        'label' => __( 'Default Number of "$"\'s', 'pods' ),
        'number_min' => 0,
        'number_max' => 10, // Still arbitrary
        'number_html5' => "1", // required to get <input type="number">
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

  // Point Pods to the custom input view for this field type
  public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

    // This is a value that pods-address-field uses, not sure what it does yet
    $form_field_type = PodsForm::$field_type;

    // Get the defaults out of the options
    $min_number = intval( pods_v( self::$type . '_default_min_number', $options, 0 ) );
    $max_number = intval( pods_v( self::$type . '_default_max_number', $options, 3 ) );
    $default_number = intval( pods_v( self::$type . '_default_number_default', $options, 0 ) );

    // Get the number out of $value
    // There appears to be a quirk here that I'm not fully understanding.
    // If the stored meta value is an array (associative or otherwise, I think),
    // and that array has only one element, $value is that element. If there are multiple elements, $value is an array.
    // Dealing with this here.
    GVPlugin\gv_debug( sprintf( 'value is %s', is_array( $value ) ? 'an array with [number] = ' . $value[ 'number' ] : 'not an array with value ' . $value ) );
    $number = is_array( $value ) ? pods_v( 'number', $value ) : $value;

    // Get the volunteer_cost_label terms to fill in the $cost_labels array
    $cost_label_terms = get_terms(
      array(
        'taxonomy' => 'volunteer_cost_label',
        'hide_empty' => false,
      )
    );

    // Create the array to be used by the pick field type
    $cost_labels = array();
    for ( $i = $min_number; $i <= $max_number; $i++ ) {
      // If 0, display "FREE", else display that number of '$'s
      $cost_labels[ $i ] = 0 === $i ? 'FREE' : str_repeat( '$', $i );
      // TODO: Add the associated term title to the $cost_labels array elements

      // If this is a post edit screen, add the term name to the display value
      $current_screen = get_current_screen();
      if ( 'post' === $current_screen->base ) {
        // Get any terms that match this index
        $matching_terms = get_terms(
          array(
            'taxonomy' => 'volunteer_cost_label',
            'hide_empty' => false,
            'meta_query' => array(
              array(
                'key' => '_gv_cost_label_number__cost_suggestion',
                'value' => $i,
                'compare' => '=',
              ),
            ),
          )
        );
        // If there is only 1 matching term, add that term's name to the picker display value
        if ( 1 === count( $matching_terms ) ) {
          $cost_labels[ $i ] .= sprintf( ' (%s)', $matching_terms[0]->name );
        }
      }
    }

    $view = GV_PLUGIN_PATH . 'ui/fields/gv-cost-label.php';
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

    $view = GV_PLUGIN_PATH . 'ui/front/gv-cost-label.php';
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