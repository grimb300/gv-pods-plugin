<div class="gv_cost_label_fields" style="width:30ch">
  <?php
  // Adding a hidden field to make it easier to handle on save
  echo PodsForm::field(
    $name . '[field_type]',
    'gv_cost_label',
    'hidden'
  );
  // GVPlugin\gv_debug( 'gv_cost_label[number] is ' . $number );
  echo PodsForm::field(
    $name . '[number]',
    $number,
    'pick',
    array(
      'data' => $cost_labels,
      'default' => $default_number,
    )
  );
  ?>
</div>

<?php

PodsForm::regex( $form_field_type, $options );
