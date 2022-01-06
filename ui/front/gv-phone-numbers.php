<?php

// TODO: pods-address-field example has a "default format" and a user defined "custom format"
//       It then runs the appropriate format through a preg/str_replace to turn that into html.
//       I might want to do something similar at a later time depending on GV needs.

// Get the max number of phone numbers and make sure it is an integer
if ( empty( $max_phone_numbers ) ) {
  $max_phone_numbers = pods_v( 'gv_phone_numbers_count', $options, '3' );
}
$max_phone_numbers = intval( $max_phone_numbers );

// TODO: Long term I could support multiple format methods (thinking unordered list, grid, table, etc)
?>
<h5>Phone Numbers</h5>
<ul>
  <?php for ( $i = 0; $i < $max_phone_numbers; $i++ ) { ?>
    <?php if ( ! empty( $value[ 'number_' . $i ] ) ) { ?>
      <li>
        <ul>
          <li><?php echo $value[ 'number_' . $i ]; ?></li>
          <?php if ( ! empty( $value[ 'description_' . $i ] ) ) { ?>
            <li><?php echo $value[ 'description_' . $i ]; ?></li>
          <?php } ?>
        </ul>
      </li>
    <?php } ?>
  <?php } ?>
</ul>
