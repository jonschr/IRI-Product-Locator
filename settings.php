<?php

//* Add the options page
add_action( 'init', 'iri_add_options_page' );
function iri_add_options_page() {

    //* Register a page for the options
    if( function_exists('acf_add_options_page') ) {
     
        $option_page = acf_add_options_page(array(
            'page_title'    => 'IRI Locator Settings',
            'menu_title'    => 'Locator Settings',
            'menu_slug'     => 'iri-locator-settings',
            'capability'    => 'edit_posts',
            'redirect'  => false
        ));
    }

    //* Create the actual options
    if( function_exists('acf_add_local_field_group') ) {

        acf_add_local_field_group( array (
            'key' => 'group_58b3376279a57',
            'title' => 'IRI locator options',
            'fields' => array (
                array (
                    'key' => 'field_58b3376abe2fc',
                    'label' => 'Brand ID',
                    'name' => 'brand_id',
                    'type' => 'text',
                    'default_value' => 'FRUS',
                    'placeholder' => 'FRUS',
                    'wrapper' => array (
						'width' => '50',
					),
                ),
                array (
                    'key' => 'field_58b3379dbe2fd',
                    'label' => 'Client ID',
                    'name' => 'client_id',
                    'type' => 'text',
                    'default_value' => '148',
                    'placeholder' => '148',
                    'wrapper' => array (
						'width' => '50',
					),
                ),
                array (
					'key' => 'field_58b3465b9ff7a',
					'label' => 'Allow caching',
					'name' => 'allow_caching',
					'type' => 'true_false',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'default_value' => 0,
					'ui' => 1,
					'ui_on_text' => 'Caching on',
					'ui_off_text' => 'Caching off',
				),
				array (
					'key' => 'field_58b346de22f55',
					'label' => 'Google API Key',
					'name' => 'google_api_key',
					'type' => 'text',
					'instructions' => 'Should be unique for this domain',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '50',
					),
					'default_value' => 'GOOGLEAPIKEY',
					'placeholder' => 'GOOGLEAPIKEY',
				),
				array (
					'key' => 'field_58b3472522f56',
					'label' => 'Contact URL',
					'name' => 'contact_url',
					'type' => 'text',
					'instructions' => 'So we can output a contact link if nothing is found.',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array (
						'width' => '50',
					),
					'default_value' => '/contact',
					'placeholder' => '/contact',
				),
				array (
					'key' => 'field_58b3473922f57',
					'label' => 'Search Radius',
					'name' => 'search_radius',
					'type' => 'text',
					'instructions' => 'How far should we search?',
					'default_value' => 25,
					'placeholder' => 25,
					'append' => 'miles',
					'wrapper' => array (
						'width' => '33',
					),
				),
				array (
					'key' => 'field_58b3473922f58',
					'label' => 'Default ZIP Code',
					'name' => 'default_zip_code',
					'type' => 'text',
					'instructions' => 'Where is the map when the page loads?',
					'default_value' => 75220,
					'placeholder' => 75220,
					'maxlength' => '5',
					'wrapper' => array (
						'width' => '33',
					),
				),
				array (
					'key' => 'field_58b3473922f59',
					'label' => 'Default zoom level',
					'name' => 'default_zoom_level',
					'type' => 'text',
					'instructions' => 'The zoom level of the map',
					'default_value' => 10,
					'placeholder' => 10,
					'maxlength' => '5',
					'wrapper' => array (
						'width' => '33',
					),
				),
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'iri-locator-settings',
                    ),
                ),
            ),
            'menu_order' => 0,
        ));
    }
}