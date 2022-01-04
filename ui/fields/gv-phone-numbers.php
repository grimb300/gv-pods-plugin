<?php

// Get the max number of phone numbers and make sure it is an integer
if ( empty( $max_phone_numbers ) ) {
  $max_phone_numbers = pods_v( 'gv_phone_numbers_count', $options, '3' );
}
$max_phone_numbers = intval( $max_phone_numbers );

?>
<style>
  .gv_phone_numbers_wrap {
    display: grid;
    grid-template-columns: 20ch 40ch;
    column-gap: 10px;
    row-gap: 10px;
  }
</style>
<div class="gv_phone_numbers_wrap">
  <div class="gv_phone_numbers_number">Phone Number</div>
  <div class="gv_phone_numbers_description">Description</div>
<?php
for ( $i = 0; $i < $max_phone_numbers; $i++ ) {
  ?>
  <div class="gv_phone_numbers_number">
  <?php
  $num_key = 'number_' . $i;
  // echo PodsForm::label( $name . '[' . $key . ']', __( 'Number ' . $i, 'pods' ) );
  echo PodsForm::field( $name . '[' . $num_key . ']', pods_v( $num_key, $value ), 'text', $options );
  ?>
  </div>
  <div class="gv_phone_numbers_description">
  <?php
  $desc_key = 'description_' . $i;
  // echo PodsForm::label( $name . '[' . $key . ']', __( 'Description ' . $i, 'pods' ) );
  echo PodsForm::field( $name . '[' . $desc_key . ']', pods_v( $desc_key, $value ), 'text', $options );
  ?>
  </div>
  <?php
}
?>
</div>
<?php
PodsForm::regex( $form_field_type, $options );
