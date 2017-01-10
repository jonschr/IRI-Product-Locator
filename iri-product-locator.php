<?php

/*
	Plugin Name: IRI Product Locator
	Plugin URI: http://redblue.us
	Description: A plugin which gets information from IRI and displays it using a shortcode. Credit where it's due: the underlying code for this plugin is by John Macon.
	Version: 0.1
    Author: Jon Schroeder
    Author URI: http://redblue.us

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
*/

/* Prevent direct access to the plugin */
if ( !defined( 'ABSPATH' ) ) {
    die( "Sorry, you are not allowed to access this page directly." );
}

// Plugin directory
define( 'IRI_LOCATOR', dirname( __FILE__ ) );

//* Include our php output files
include_once( 'template/inline-scripts.php' );

//* Include our inline scripts
include_once( 'template/output.php' );

//* Enqueue Scripts and Styles
add_action( 'wp_enqueue_scripts', 'iri_register_styles_scripts' );
function iri_register_styles_scripts() {

    //* Don't add these scripts and styles to the admin side of the site
    if ( is_admin() )
		return;

    //* Basic styles for the plugin; we're only defining baselines here, not colors or anything unique (the theme should do that)
	wp_register_style( 'iri-style', plugin_dir_url( __FILE__ ) . '/css/iri-style.css' );

}

//* Necessary for ajax request handling
add_action( 'wp_ajax_get_product_list', 'iri_ajax_product_list' );
add_action( 'wp_ajax_nopriv_get_product_list', 'iri_ajax_product_list' );
function iri_ajax_product_list () {

    //* To reset the cache while testing
    // set_transient( 'product_list', $product_json, 60 );

    if ( get_transient( 'product_list' ) ) {
        $product_json = get_transient('product_list');
    } else {
        $product_json = file_get_contents( plugin_dir_url( __FILE__ ) . '/locator-service.php?products=1' );
        set_transient( 'product_list', $product_json, 60 * 60 * 24 );
    }

    echo $product_json;
    die();
}

//* Register the shortcode that actually outputs everything
add_shortcode( 'locator', 'locator_main_shortcode' );
function locator_main_shortcode( $atts ) {

    //* Let's go ahead and make sure that our stylesheet is being output
    wp_enqueue_style( 'iri-style' );

    //* Figure out what shortcode attributes we have
	$atts = shortcode_atts( array(
		'google_maps_api_key' => 'GOOGLEAPIKEY',
		'client_id' => '103',
        'brand_id' => 'DEMO',
        'starting_zip' => '76708',
        'map_default_zoom_level' => '10',
        'contact_url' => '/contact',
        'search_radius' => '25',
	), $atts );

    //* Variables shown here for readability only; they'll need to be redefined as needed in individual functions
    $client_id = $atts[ 'client_id' ];
    $brand_id = $atts[ 'brand_id' ];
    $google_maps_api_key = $atts[ 'google_maps_api_key' ];
    $starting_zip = $atts[ 'starting_zip' ];
    $map_default_zoom_level = $atts[ 'map_default_zoom_level' ];
    $contact_url = $atts[ 'contact_url' ];
    $radius = $atts[ 'search_radius' ];

    ob_start();

    //* Anything that needs done before everything (perhaps our theme needs to wrap something?)
    do_action( 'iri_before' );

    //* Set up an action for the form
    do_action( 'iri_do_form_output', $atts );

    //* Set up an action for the locations list
    do_action( 'iri_do_results_output', $atts );

    //* Anything that needs done after everything
    do_action( 'iri_after' );

    return ob_get_clean();

    //* Do our main output function (this is temporary and should be removed before use in prod)
    // iri_output( $atts );
}

//* Set up our default output. We're setting up actions so that components can be easily switched out if needed.
add_action( 'iri_before', 'iri_before_wrapper', 15 );
add_action( 'iri_do_form_output', 'iri_form_output', 15 );
add_action( 'iri_do_results_output', 'iri_map_output', 15 );

//* This action sets up the locations output as a whole
add_action( 'iri_do_results_output', 'iri_locations_output', 20 );

//* This action outputs just the list of locations and nothing else (useful if we don't want a list and need to easily remove it)
add_action( 'iri_do_locations_list', 'iri_locations_list', 15 );

add_action( 'iri_do_results_output', 'iri_inline_scripts', 25 );
add_action( 'iri_after', 'iri_after_wrapper', 15 );
