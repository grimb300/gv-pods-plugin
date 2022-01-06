<?php

// This example comes from the Pods docs
// https://docs.pods.io/code/blocks-api/#Example_Block_Pod_Template_Block

/**
 * Example 1: Registering a custom block type.
 */
add_action( 'pods_blocks_api_init', 'register_my_custom_block_type' );
add_action( 'pods_blocks_api_init', 'register_another_custom_block_type' );

/**
 * Register your custom block type. Rename this function to fit your own project naming and needs.
 */
function register_my_custom_block_type() {
  /**
   * This is your block configuration. Customize it to fit your needs.
   */
  $block = [
    // This is unique the name of your project so it won't conflict with other blocks installed (A-Z, a-z and dashes only).
    'namespace'       => 'my-project',
    // The unique name of your block (A-Z, a-z and dashes only).
    'name'            => 'my-custom-block',
    // The block title of your block.
    'title'           => __( 'My Custom Block' ),
    // The text description of your block.
    'description'     => __( 'The description of my custom block.' ),
    // Set your category (collection): common, formatting, layout, widgets, embed, or a custom one you register (A-Z, a-z and dashes only).
    'category'        => 'my-custom-collection',
    // Dashicon name, see https://developer.wordpress.org/resource/dashicons/ for an official list, exclude the "dashicons-" prefix.
    'icon'            => 'admin-comments',
    // Limit to three keywords or phrases.
    'keywords'        => [
      'Project name',
      'keyword',
    ],
    /**
     * Important: The below options will be different depending on what render type you want to use.
     */
    // How you want to render the block output: php or js.
    'render_type'     => 'php',
    // If `render_type` is "php", set your callback below.
    'render_callback' => 'my_custom_block_render',
  ];
  /**
   * This is a list of fields to show in your block options (shown in the "Inspector Controls" area when selecting the block.
   */
  // If you have no fields to set, just use an empty array or don't pass `$fields` into `pods_register_block_type`.
  $fields = [];
  // If you have fields to show, set them and customize the list here. They use the same exact field config form at as normal Pod fields.
  $fields = [
    [
      'name'  => 'my_text_field',
      'label' => __( 'My Text Field' ),
      'type'  => 'text',
    ],
    [
      'name'        => 'my_paragraph_field',
      'label'       => __( 'My Paragraph Field' ),
      'description' => __( 'This is a description for the field.' ),
      'type'        => 'paragraph',
    ],
    [
      'name'    => 'my_number_field',
      'label'   => __( 'My Number Field' ),
      'type'    => 'number',
      'default' => 15,
    ],
    [
      'name'  => 'my_checkbox_field',
      'label' => __( 'My Checkbox Field' ),
      'type'  => 'boolean',
    ],
    [
      'name'    => 'my_select_field',
      'label'   => __( 'My Select Field' ),
      'type'    => 'pick',
      'data'    => [
        'one'   => __( 'Option 1' ),
        'two'   => __( 'Option 2' ),
        'three' => __( 'Option 3' ),
      ],
      'default' => 'two',
    ],
  ];
  pods_register_block_type( $block, $fields );
}

function register_another_custom_block_type() {
  /**
   * This is your block configuration. Customize it to fit your needs.
   */
  $block = [
    // This is unique the name of your project so it won't conflict with other blocks installed (A-Z, a-z and dashes only).
    'namespace'       => 'my-project',
    // The unique name of your block (A-Z, a-z and dashes only).
    'name'            => 'another-custom-block',
    // The block title of your block.
    'title'           => __( 'Another Custom Block' ),
    // The text description of your block.
    'description'     => __( 'The description of another custom block.' ),
    // Set your category (collection): common, formatting, layout, widgets, embed, or a custom one you register (A-Z, a-z and dashes only).
    'category'        => 'my-custom-collection',
    // Dashicon name, see https://developer.wordpress.org/resource/dashicons/ for an official list, exclude the "dashicons-" prefix.
    'icon'            => 'admin-comments',
    // Limit to three keywords or phrases.
    'keywords'        => [
      'Project name',
      'keyword',
    ],
    /**
     * Important: The below options will be different depending on what render type you want to use.
     */
    // How you want to render the block output: php or js.
    'render_type'     => 'php',
    // If `render_type` is "php", set your callback below.
    'render_callback' => 'another_custom_block_render',
  ];
  /**
   * This is a list of fields to show in your block options (shown in the "Inspector Controls" area when selecting the block.
   */
  // If you have no fields to set, just use an empty array or don't pass `$fields` into `pods_register_block_type`.
  $fields = [];
  // If you have fields to show, set them and customize the list here. They use the same exact field config form at as normal Pod fields.
  // $fields = [
  //   [
  //     'name'  => 'my_text_field',
  //     'label' => __( 'My Text Field' ),
  //     'type'  => 'text',
  //   ],
  //   [
  //     'name'        => 'my_paragraph_field',
  //     'label'       => __( 'My Paragraph Field' ),
  //     'description' => __( 'This is a description for the field.' ),
  //     'type'        => 'paragraph',
  //   ],
  //   [
  //     'name'    => 'my_number_field',
  //     'label'   => __( 'My Number Field' ),
  //     'type'    => 'number',
  //     'default' => 15,
  //   ],
  //   [
  //     'name'  => 'my_checkbox_field',
  //     'label' => __( 'My Checkbox Field' ),
  //     'type'  => 'boolean',
  //   ],
  //   [
  //     'name'    => 'my_select_field',
  //     'label'   => __( 'My Select Field' ),
  //     'type'    => 'pick',
  //     'data'    => [
  //       'one'   => __( 'Option 1' ),
  //       'two'   => __( 'Option 2' ),
  //       'three' => __( 'Option 3' ),
  //     ],
  //     'default' => 'two',
  //   ],
  // ];
  pods_register_block_type( $block, $fields );
}
/**
* Render the block HTML if you want to do it with PHP. Rename this function to fit your own project naming and needs.
*
* @param array $attributes List of field attributes.
*
* @return string The content to render.
*/
function my_custom_block_render( array $attributes ) {
  return '
    <p>This is an example of the value for My Text Field: <strong>' . esc_html( $attributes['my_text_field'] ) . '</strong></p>
    <p>This is another example of the value for My Number Field: <strong>' . esc_html( $attributes['my_number_field'] ) . '</strong></p>
  ';
}
function another_custom_block_render( array $attributes ) {
  return '
    <p>This is another custom block</p>
  ';
}
/**
* Example 2: Registering a custom block collection.
*/
add_action( 'pods_blocks_api_init', 'register_my_custom_block_collection' );
/**
* Register your custom block collection. Rename this function to fit your own project naming and needs.
*/
function register_my_custom_block_collection() {
  /**
   * This is your block collection configuration. Customize it to fit your needs.
   */
  $collection = [
    // The unique name of your block collection (A-Z, a-z and dashes only).
    'namespace' => 'my-custom-collection',
    // The block title of your block collection.
    'title'     => __( 'My Custom Collection' ),
    // Dashicon name, see https://developer.wordpress.org/resource/dashicons/ for an official list, exclude the "dashicons-" prefix.
    'icon'      => 'admin-customizer',
  ];
  pods_register_block_collection( $collection );
}