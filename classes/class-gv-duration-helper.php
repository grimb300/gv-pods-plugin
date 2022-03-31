<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class GV_Duration_Helper {
  // Static methods and properties to be used wherever the durations field is used

  // Min/max for a duration
  public static $duration_min = "0";
  public static $duration_max = "1073741823"; // max from the Rails days
                                              // Keep this in sync with $duration_infinite_in_days

  // Units as <unit> => <display text> pairs
  // Used by the dropdown ('pick') fields
  public static $duration_units = array(
    'days' => 'days',
    'weeks' => 'weeks',
    'months' => 'months',
    'years' => 'years',
    'infinite' => 'open ended',
  );

  // Convert weeks/months/years to days
  private static $duration_day_in_days = 1;
  private static $duration_week_in_days = 7;
  private static $duration_month_in_days = 30;
  private static $duration_year_in_days = 365;
  private static $duration_infinite_in_days = 1073741823; // Keep this in sync with $duration_max
  public static function calculate_duration_in_days( $raw_num = 0, $unit = 'days' ) {
    // Explicitly convert $num into an integer since we're doing math, if it isn't an integer, return $raw_num
    $num = intval( $raw_num );
    if ( ! is_int( $num ) ) return $raw_num;
    // Convert weeks/months/years to days and return the results
    if ( 'weeks' === $unit ) return $num * self::$duration_week_in_days;
    if ( 'months' === $unit ) return $num * self::$duration_month_in_days;
    if ( 'years' === $unit ) return $num * self::$duration_year_in_days;
    if ( 'infinite' === $unit ) return self::$duration_infinite_in_days;
    // If we make it this far $unit is days or something else that we don't know how to handle yet, return the unconverted $num
    return $num;
  }

  // Convert the legacy min_duration/max_duration value into a number/unit pair
  public static function convert_legacy_duration( $raw_num_days ) {
    // Assume input is not an integer
    $num_days = intval( $raw_num_days );
    // Get the unit
    $unit = self::$duration_infinite_in_days === $num_days ? 'infinite' : 'days';
    // Return the converted duration
    // TODO: At some later point, may find it useful to minimize the num/unit pair
    return array(
      'number' => $num_days,
      'unit' => $unit,
    );
  }

  // Check if two durations overlap
  // Used when updating either the term or the post
  // Returns true if any part of durations[0] and durations[1] overlap, false otherwise
  public static function durations_overlap( $durations ) {
    // gv_debug( 'durations_overlap called with' );
    // gv_debug( $durations );
    // Check that the inputs are valid, two associative arrays with 'min' and 'max' elements, both being integers, and min is less than max
    if ( ! is_array( $durations ) || 2 !== count( $durations ) ) return false;
    foreach( $durations as $d ) {
      if ( ! is_array( $d ) || 2 != count( $d ) ) return false;
      foreach( $d as $k => $v ) {
        if ( ! in_array( $k, [ 'min', 'max' ] ) || ! is_int( $v ) ) return false;
      }
      if ( $d[ 'min' ] > $d[ 'max' ] ) return false;
    }

    // Check the overlap
    // Doing this in nested for loops to reduce the chance of cut/paste errors
    foreach( $durations as $idx => $dur ) {
      foreach( $dur as $endpoint => $value ) {
        // The range is the other duration
        $range = 0 === $idx ? $durations[1] : $durations[0];
        // gv_debug( sprintf( 'Checking if durations[%s][%s] (%s) is in the range (%s, %s)', $idx, $endpoint, $value, $range['min'], $range['max'] ) );
        if ( self::value_in_range( $value, $range ) ) return true;
        // gv_debug( 'It was not, moving on' );
      }
    }

    // If we make it this far, there was no overlap, return false
    return false;
  }

  // Helper function returns true if $value is between $range[min] and $range[max]
  private static function value_in_range( $value, $range ) {
    // Check that the inputs are all integers and that the min is less than max
    if ( ! is_int( $value ) || ! is_int( $range['min'] ) || ! is_int( $range['max'] ) ) return false;
    if ( $range['min'] > $range['max'] ) return false;

    // Return the result
    return $value >= $range['min'] && $value <= $range['max'];
  }

}