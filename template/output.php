<?php

function iri_before_wrapper() {
    echo '<div class="locator-wrap">';
}

//* Outputs the actual form
function iri_form_output( $atts ) {
    $starting_zip = get_field( 'default_zip_code', 'option' );
    ?>
    <form id="locator-form" class="locator-form" role="form">

        <div class="form-group zipcode">
            <!-- <label for="postalCode"><?php // _e('Zip Code', 'iri-locator'); ?></label> -->
            <span class="error zip required" hidden="">- Please enter a zip code.</span>
            <span class="error zip invalid" hidden="">- Please enter a 5-digit zip code.</span>
            <input type="text" class="locator-form-input" id="postalCode" value="<?php echo $starting_zip; ?>" placeholder="<?php _e('Zip Code', 'iri-locator'); ?>" maxlength="5">
        </div>

        <div class="form-group product-selection">
            <!-- <label for="productID"><?php // _e('Select Product', 'iri-locator'); ?></label> -->
            <select id="productID" class="locator-form-input product-select" style="display:none;">
                <option value="0">No Products Found</option>
            </select>
            <img src="<?php echo dirname( plugin_dir_url( __FILE__ ) ); ?>/images/ripple.svg" class="loading-products" alt="loading">
        </div>

        <div class="form-group locator-search">
            <button type="submit" class="button search locations locator-form-input"><?php _e('Search', 'iri-locator'); ?></button>
            <img src="<?php echo dirname( plugin_dir_url( __FILE__ ) ); ?>/images/ripple.svg" class="loading" alt="loading" hidden="">
        </div>

    </form>
    <?php
}


function iri_locations_output( $atts ) {
    $contact_url = $atts[ 'contact_url' ];
    $search_radius = $atts[ 'search_radius' ];
    ?>
    <div id="locations" hidden="">
        <div id="noLocationsFoundText" hidden="">
            <p>Unfortunately, we couldn't find this product within your search area. Please feel free to search again – if you still can't find anything, then please do <a href="<?php echo $contact_url; ?>">contact us</a> directly!</p>
        </div>
        <div id="geocodeError" hidden=""></div>
        <?php do_action( 'iri_do_locations_list' ); ?>
    </div>
    <?php
}

function iri_locations_list() {
    echo '<div id="locationsList" class="locations-list"></div>';
}

function iri_map_output( $atts ) {

    //* We'll need our Google Maps API key here
    $google_maps_api_key = get_field( 'google_api_key', 'option' );

    //* Connect with Google Maps using our api key
    printf( '<script src="https://maps.googleapis.com/maps/api/js?key=%s&extension=.js"></script>', $google_maps_api_key );

    //* Output the actual map canvas
    echo '<div id="map-canvas">&nbsp;</div>';
}

function iri_after_wrapper() {
    echo '</div>'; // .locator-wrap
}
