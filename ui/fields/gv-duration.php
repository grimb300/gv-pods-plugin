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
    margin-bottom: 0.5em;
  }
  .gv_duration_fields_label {
    width: 20ch;
  }
  .gv_duration_fields_unit {
    width: 17ch;
  }
</style>
<script>
  // If infinite unit is picked, disable the number input
  // TODO: Check into making the query selectors more automatic
  window.onload = function(){
    const maxUnit = document.querySelector('#pods-form-ui-pods-meta-duration-max-unit');
    const maxNumber = document.querySelector('#pods-form-ui-pods-meta-duration-max-number');
    const disableMaxNumber = () => maxNumber.disabled = maxUnit.value === 'infinite';
    maxUnit.onchange = disableMaxNumber;
    disableMaxNumber();
  }
</script>
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
    // GVPlugin\gv_debug( 'durations value' );
    // GVPlugin\gv_debug( $value );
    // GVPlugin\gv_debug( 'Using pods_v: ' . pods_v( 'min_number', $value ) );
    echo PodsForm::field(
      $name . '[min_number]',
      pods_v( 'min_number', $value ),
      'number',
      array(
        'number_min' => GVPlugin\GV_Duration_Helper::$duration_min,
        'number_max' => GVPlugin\GV_Duration_Helper::$duration_max,
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
        // Filter out the infinite unit for the min duration
        'data' => array_filter( GVPlugin\GV_Duration_Helper::$duration_units, function( $unit ) { return 'infinite' !== $unit; }, ARRAY_FILTER_USE_KEY ),
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
        'number_min' => GVPlugin\GV_Duration_Helper::$duration_min,
        'number_max' => GVPlugin\GV_Duration_Helper::$duration_max,
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
        'data' => GVPlugin\GV_Duration_Helper::$duration_units,
        'default' => $default_max_unit,
      )
    );
    ?>
  </div>
</div>
<div><em>To indicate no maximum duration, choose "open ended"</em></div>
<?php
PodsForm::regex( $form_field_type, $options );
