<?php

namespace Roots\Sage\Extras;

use Roots\Sage\Setup;

/**
 * Add <body> classes
 */
function body_class($classes) {
  // Add page slug if it doesn't exist
  if (is_single() || is_page() && !is_front_page()) {
    if (!in_array(basename(get_permalink()), $classes)) {
      $classes[] = basename(get_permalink());
    }
  }

  // Add class if sidebar is active
  if (Setup\display_sidebar()) {
    $classes[] = 'sidebar-primary';
  }

  return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\body_class');

/**
 * Clean up the_excerpt()
 */
function excerpt_more() {
  return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
}
add_filter('excerpt_more', __NAMESPACE__ . '\\excerpt_more');


// add option to hide labels for gravity forms fields
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );


/**
 * Load a template part into a template
 * Same as get_template_part except allows you to pass in parameters
 * Parameters are available in the template file using get_query_var()
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 * @param array $params Any extra params to be passed to the template part.
 */
function get_template_part_extended( $slug, $name = null, $params = array() ) {
  if ( ! empty( $params ) ) {
    foreach ( (array) $params as $key => $param ) {
      set_query_var( $key, $param );
    }
  }
  get_template_part( $slug, $name );
}


// Hook.
add_action( 'rest_api_init', __NAMESPACE__ . '\\wp_rest_allow_all_cors', 15 );
/**
 * Allow all CORS.
 *
 * @since 1.0.0
 */
function wp_rest_allow_all_cors() {
  // Remove the default filter.
  remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
  // Add a Custom filter.
  add_filter( 'rest_pre_serve_request', function( $value ) {
    header( 'Access-Control-Allow-Origin: *' );
    header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
    header( 'Access-Control-Allow-Credentials: true' );
    return $value;
  });
} // End fucntion wp_rest_allow_all_cors().

