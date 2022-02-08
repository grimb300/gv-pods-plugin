<?php

namespace GVPlugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly;

// Generic custom block
class GV_Taxonomy_Block extends GV_Default_Block {

  /* **********
   * Properties
   * **********/

   /* *******
   * Methods
   * *******/

  // Display a location field
  protected function format_field_data( $field_data = null ) {
    global $post;

    // TODO: These should be turned into block attributes
    $before_terms = '<ul><li>';
    $term_separator = '</li><li>';
    $after_terms = '</li></ul>';
    $hierarchy_separator = ' > ';
    
    // If this is a hierarchical taxonomy, build the links using get_term_parents_list()
    if ( is_taxonomy_hierarchical( $this->field_name ) ) {
      // Get the terms associated with this post/taxonomy
      $post_terms = get_the_terms( $post->ID, $this->field_name );

      // Filter the terms list to only include the youngest child of each hierarchy
      $filtered_terms = array_filter(
        $post_terms,
        function ( $parent_term ) use ( $post_terms ) {
          // Determine if this term is the parent of another term in the list
          $is_not_a_parent = array_reduce(
            $post_terms,
            function ( $result, $child_term ) use ( $parent_term ) {
              // If any other term in the list has this term as a parent, the final result is FALSE
              return $result && ( $parent_term->term_id !== $child_term->parent );
            },
            TRUE
          );

          return $is_not_a_parent;
        }
      );

      // Use array map to display the term hierarchy
      $hierarchical_terms = array_map(
        function ( $term ) use ( $hierarchy_separator ) {
          // Get the term parent list
          $hierarchy_string = get_term_parents_list(
            $term->term_id,
            $this->field_name,
            array( 'separator' => $hierarchy_separator )
          );
          // I don't like the way get_term_parents_list displays the hierarchy separator
          // Remove the "separator" at the end of the string
          return preg_replace( '/' . $hierarchy_separator . '$/', '', $hierarchy_string );

        },
        // Using the filtered_terms
        $filtered_terms
      );
      return $before_terms . implode( $term_separator, $hierarchical_terms ) . $after_terms;
    }

    // Else, take the easy way out and use get_the_term_list()
    return get_the_term_list( $post->ID, $this->field_name, $before_terms, $term_separator, $after_terms );
  }
}

// Output from pods->field()
// array (
//   0 => array (
//     'term_id' => '744',
//     'name' => 'belize',
//     'slug' => 'belize',
//     'term_group' => '0',
//     'term_taxonomy_id' => '744',
//     'taxonomy' => 'business_location',
//     'description' => '',
//     'parent' => '743',
//     'count' => '2',
//     'object_id' => '2271',
//     'term_order' => '0',
//     'pod_item_id' => '744',
//   ),
//   1 => array (
//     'term_id' => '743',
//     'name' => 'central america',
//     'slug' => 'central-america',
//     'term_group' => '0',
//     'term_taxonomy_id' => '743',
//     'taxonomy' => 'business_location',
//     'description' => '',
//     'parent' => '0',
//     'count' => '22',
//     'object_id' => '2282',
//     'term_order' => '0',
//     'pod_item_id' => '743',
//   ),
//   2 => array (
//     'term_id' => '745',
//     'name' => 'punta gorda',
//     'slug' => 'punta-gorda',
//     'term_group' => '0',
//     'term_taxonomy_id' => '745',
//     'taxonomy' => 'business_location',
//     'description' => '',
//     'parent' => '744',
//     'count' => '1',
//     'object_id' => '2110',
//     'term_order' => '0',
//     'pod_item_id' => '745',
//   ),
// );

// Output from get_the_terms()
// array (
//   0 => WP_Term::__set_state(
//     array(
//       'term_id' => 744,
//       'name' => 'belize',
//       'slug' => 'belize',
//       'term_group' => 0,
//       'term_taxonomy_id' => 744,
//       'taxonomy' => 'business_location',
//       'description' => '',
//       'parent' => 743,
//       'count' => 2,
//       'filter' => 'raw',
//     )
//   ),
//   1 => WP_Term::__set_state(
//     array(
//       'term_id' => 743,
//       'name' => 'central america',
//       'slug' => 'central-america',
//       'term_group' => 0,
//       'term_taxonomy_id' => 743,
//       'taxonomy' => 'business_location',
//       'description' => '',
//       'parent' => 0,
//       'count' => 22,
//       'filter' => 'raw',
//     )
//   ),
//   2 => WP_Term::__set_state(
//     array(
//       'term_id' => 745,
//       'name' => 'punta gorda',
//       'slug' => 'punta-gorda',
//       'term_group' => 0,
//       'term_taxonomy_id' => 745,
//       'taxonomy' => 'business_location',
//       'description' => '',
//       'parent' => 744,
//       'count' => 1,
//       'filter' => 'raw',
//     )
//   ),
// );