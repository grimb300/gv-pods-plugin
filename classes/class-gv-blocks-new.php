<?php

add_action( 'pods_blocks_api_init', 'register_custom_block_types' );

/**
 * Register your custom block type. Rename this function to fit your own project naming and needs.
 */
function register_custom_block_types() {
  $block_0 = [
    'namespace'       => 'my-blocks',
    'name'            => 'my-block-0',
    'title'           => __( 'Block 0' ),
    'description'     => __( 'The description of Block 0.' ),
    'category'        => 'my-block-collection-0',
    'icon'            => 'admin-comments',
    'render_type'     => 'php',
    'render_callback' => 'block_0_render',
  ];
  pods_register_block_type( $block_0, [] );

  $block_1 = [
    'namespace'       => 'my-blocks',
    'name'            => 'my-block-1',
    'title'           => __( 'Block 1' ),
    'description'     => __( 'The description of Block 1.' ),
    'category'        => 'my-block-collection-1',
    'icon'            => 'admin-comments',
    'render_type'     => 'php',
    'render_callback' => 'block_1_render',
  ];
  pods_register_block_type( $block_1, [] );
}

function block_0_render( array $attributes ) {
  return '
    <p>This is block 0</p>
  ';
}

function block_1_render( array $attributes ) {
  return '
    <p>This is block 1</p>
  ';
}

add_action( 'pods_blocks_api_init', 'register_custom_block_collections' );
function register_custom_block_collections() {
  $collection_0 = [
    'namespace' => 'my-block-collection-0',
    'title'     => __( 'Block Collection 0' ),
    'icon'      => 'admin-customizer',
  ];
  pods_register_block_collection( $collection_0 );
  
  $collection_1 = [
    'namespace' => 'my-block-collection-1',
    'title'     => __( 'Block Collection 1' ),
    'icon'      => 'admin-customizer',
  ];
  pods_register_block_collection( $collection_1 );
}