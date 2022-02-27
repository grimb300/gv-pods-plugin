<?php

// GVPlugin\gv_debug( 'name: ' . $name );
// GVPlugin\gv_debug( 'value:' );
// GVPlugin\gv_debug( $value );
// GVPlugin\gv_debug( 'defaults:' );
// GVPlugin\gv_debug( '  min_number: ' . $default_min_number );
// GVPlugin\gv_debug( '  min_unit: ' . $default_min_unit );
// GVPlugin\gv_debug( '  open_ended: ' . $default_open_ended );
// GVPlugin\gv_debug( '  max_number: ' . $default_max_number );
// GVPlugin\gv_debug( '  max_unit: ' . $default_max_unit );

// GVPlugin\gv_debug( 'options: ' );
// GVPlugin\gv_debug( $options );

// Get the max number of phone numbers and make sure it is an integer
if ( empty( $max_phone_numbers ) ) {
  $max_phone_numbers = pods_v( 'gv_phone_numbers_count', $options, '3' );
}
$max_phone_numbers = intval( $max_phone_numbers );

// TODO: Add a little JS to do the right thing when open ended is checked
?>
<style>
  /* Put all fields on one line, space willing */
  .gv_duration_fields {
    display: flex;
    align-items: center;
    gap: 1em;
  }
  .gv_duration_fields_label {
    width: 20ch;
  }
  .gv_duration_fields_open_ended label {
    width: max-content;
  }
</style>
<?php
// Adding a hidden field to make it easier to handle on save
echo PodsForm::field(
  $name . '[field_type]',
  'gv_duration',
  'hidden'
);
?>
<div class="gv_duration_fields">
  <div class="gv_duration_fields_label">Minimum Duration</div>
  <div class="gv_duration_fields_number">
    <?php
    echo PodsForm::field(
      $name . '[min_number]',
      pods_v( 'min_number', $value ),
      'number',
      array(
        'number_min' => PodsField_GV_Duration::$duration_min,
        'number_max' => PodsField_GV_Duration::$duration_max,
        'number_html5' => "1", // required to get <input type="number">
        'default_value' => $default_min_number,
      )
    );
    ?>
  </div>
  <div class="gv_duration_fields_unit">
    <?php
    echo PodsForm::field(
      $name . '[min_unit]',
      pods_v( 'min_unit', $value ),
      'pick',
      array(
        'data' => PodsField_GV_Duration::$duration_units,
        'default' => $default_min_unit,
      )
    );
    ?>
  </div>
</div>
<div class="gv_duration_fields">
  <div class="gv_duration_fields_label">Maximum Duration</div>
  <div class="gv_duration_fields_number">
    <?php
    echo PodsForm::field(
      $name . '[max_number]',
      pods_v( 'max_number', $value ),
      'number',
      array(
        'number_min' => PodsField_GV_Duration::$duration_min,
        'number_max' => PodsField_GV_Duration::$duration_max,
        'number_html5' => "1", // required to get <input type="number">
        'default_value' => $default_max_number,
      )
    );
    ?>
  </div>
  <div class="gv_duration_fields_unit">
    <?php
    echo PodsForm::field(
      $name . '[max_unit]',
      pods_v( 'max_unit', $value ),
      'pick',
      array(
        'data' => PodsField_GV_Duration::$duration_units,
        'default' => $default_max_unit,
      )
    );
    ?>
  </div>
  <div class="gv_duration_fields_open_ended">
    <?php
    echo PodsForm::field(
      $name . '[open_ended]',
      pods_v( 'open_ended', $value ),
      'boolean',
      array(
        'boolean_yes_label' => 'Open Ended',
        // TODO: Why does 'default' work here, but on the number field above I had to use 'default_value'
        'default' => $default_open_ended,
      )
    );
    ?>
  </div>
</div>
<?php
PodsForm::regex( $form_field_type, $options );
