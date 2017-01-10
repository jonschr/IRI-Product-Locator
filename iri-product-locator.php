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
        'map_default_zoom_level' => '8',
	), $atts );

    //* Variables shown here for readability only; they'll need to be redefined as needed in individual functions
    $client_id = $atts[ 'client_id' ];
    $brand_id = $atts[ 'brand_id' ];
    $google_maps_api_key = $atts[ 'google_maps_api_key' ];
    $starting_zip = $atts[ 'starting_zip' ];
    $map_default_zoom_level = $atts[ 'map_default_zoom_level' ];

    //* Set up an action for the form
    do_action( 'iri_form_output', $atts );

    //* Set up an action for the locations list
    do_action( 'iri_locations_list_output', $atts );

    //* Set up an action for the map
    do_action( 'iri_locations_list_output', $atts );

    //* Do our main output function (this is temporary and should be removed before use in prod)
    iri_output( $atts );
}

function iri_output( $atts ) {

    $client_id = $atts[ 'client_id' ];
    $brand_id = $atts[ 'brand_id' ];
    $google_maps_api_key = $atts[ 'google_maps_api_key' ];
    $starting_zip = $atts[ 'starting_zip' ];
    $map_default_zoom_level = $atts[ 'map_default_zoom_level' ];

    ?>

            <form id="locator-form" role="form">

                <div class="form-group">
                    <label for="postalCode"><?php _e('Zip Code', 'iri-locator'); ?></label>
                    <span class="error zip required" hidden="">- Please enter a zip code.</span>
                    <span class="error zip invalid" hidden="">- Please enter a 5-digit zip code.</span>
                    <input type="text" class="form-control input-xl" id="postalCode" placeholder="<?php _e('Zip Code', 'iri-locator'); ?>" maxlength="5">
                </div>

                <div class="form-group">
                    <label for="productID"><?php _e('Select Product', 'iri-locator'); ?></label>
                    <select id="productID" style="display:none;">
                        <option value="0">No Products Found</option>
                    </select>
                    <img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/gps.gif" class="loading-products" alt="loading">
                </div>

                <button type="submit" class="block-link search locations"><?php _e('Search', 'iri-locator'); ?></button>
                <img src="<?php echo plugin_dir_url( __FILE__ ); ?>/images/gps.gif" class="loading" alt="loading" hidden="">

            </form>

            <div id="locations" hidden="">
                <h3 id="locationsHeading" hidden=""><?php _e('Find Us at These Locations:', 'iri-locator'); ?></h3>
                <div id="noLocationsFoundText" hidden=""><h2><?php _e('Sadly, no products by the name of', 'iri-locator'); ?> <span id="productNotFoundName">[PRODUCT NAME]</span> <?php _e('were found within 25 miles of ZIP code', 'iri-locator'); ?> <span id="productNotFoundZip">[XXXXX]</span>.</h2>
                    <p><?php _e('But don’t lose hope!', 'iri-locator'); ?></p>
                    <p><?php _e('(1)&nbsp; Request it', 'iri-locator'); ?></p>
                    <p><?php _e('Tell us what you want and we can use this information to let retailers know what products you’re looking for. Don’t worry, though, we’ll never reveal your name to anyone else — pinky promise.', 'iri-locator'); ?></p>
                    <p><?php if (strpos($_SERVER["REQUEST_URI"], '/es/') !== false) : ?>
                            <a href="/es/contact-us"><?php _e('Contact us with your request', 'iri-locator'); ?></a>
                        <?php else : ?>
                            <a href="/contact-us"><?php _e('Contact us with your request', 'iri-locator'); ?></a>
                        <?php endif; ?></p>
                    <p><?php _e('(2) Search again', 'iri-locator'); ?></p>
                    <p><?php _e('Maybe you hit the wrong key. Maybe you just like having Déjà vu. Or maybe you’re considering flying across the country to find our products. (Totally worth it, just saying.)', 'iri-locator'); ?></p></div>
                <div id="geocodeError" hidden=""></div>
                <div id="locationsList"></div>
            </div>

            <p>Map is just below this</p>
            <div id="map-canvas">&nbsp;</div>

        <script src='https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&extension=.js'></script>
        <script>
            var ajaxurl = "<?php echo admin_url( 'admin-ajax.php' ); ?>";

            jQuery( document ).ready(function($) {

                google.maps.event.addDomListener(window, 'load', init);
                var map;
                var markers = [];
                function init() {

                    //jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=' + $('#postalCode').val() + '&key=<?php // echo $google_maps_api_key; ?> ', null, function (zipData) {
                    jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=75215&key=<?php echo $google_maps_api_key; ?>', null, function (zipData) {
                        var p = zipData.results[0].geometry.location;
                        var centerMap = new google.maps.LatLng(p.lat, p.lng);
                        // var MY_MAPTYPE_ID = 'LALA Foods Product Locator';

                        var mapOptions = {
                            center: centerMap,
                            zoom: 8,
                            zoomControl: true,
                            zoomControlOptions: {
                                style: google.maps.ZoomControlStyle.SMALL,
                            },
                            disableDoubleClickZoom: true,
                            mapTypeControl: false,
                            scaleControl: true,
                            scrollwheel: true,
                            panControl: true,
                            streetViewControl: true,
                            draggable : true,
                            overviewMapControl: true,
                            overviewMapControlOptions: {
                                opened: false,
                            },
                            mapTypeId: google.maps.MapTypeId.ROADMAP,
                        }
                        var mapElement = document.getElementById('map-canvas');
                        map = new google.maps.Map(mapElement, mapOptions);
                    });
                }



                $.getJSON( ajaxurl,
                    {
                      action: 'get_product_list',
                    },
                    function( data ) {
                        var items = '';
                        $.each( data, function( key, val ) {
                            /*for(var upcCode in val) {
                             console.log(upcCode);
                             }*/
                            // console.log(key + ' ' + val);
                            $.each( val, function (upcCode, upcName) {
                                //console.log("key: " + upcCode + " value: " + upcName );
                                items += '<option value="' + upcCode + '">' + upcName + '</option>';
                            });

                        });

                        $('#productID').html(items).show().change();

                        // For testing
                        // $('#productID').html( '<option value="1000030001">Maria\'s Fudge 4 oz</option><option value="upcCode2">upcName2</option><option value="upcCode3">upcName3</option>' ).show().change();

                        $('.loading-products').hide();
                });

                $('#productID').on('change', function () {
                   if ($(this).val().indexOf('GROUP') != -1)
                       $(this).addClass('group-selected');
                    else
                       $(this).removeClass('group-selected');
                });


                $('#locator-form').on("submit", function(e) {
                    e.preventDefault();
                    $('.search.locations').hide().next('.loading').removeAttr('hidden');
                    // _gaq.push(['_trackEvent', 'Find a location', 'Click', 'Where to buy']);

                    $('#locationsList').empty();
                    var postalCode = $('#postalCode').val();
                    var upc = $('#productID').val();

                    markers.forEach(function (marker) {
                        marker.setMap(null);
                    });
                    markers = [];

                    $.getJSON( '<?php echo plugin_dir_url( __FILE__ ); ?>/locator-service.php?location=1&brandid=FRUS&upc=' + $('#productID').val() + "&zip=" + $('#postalCode').val(), function( data ) {
                        console.log(data);
                        if(data != "No Stores") {
                            var points = new Array();

                            //console.log(data);
                            $.each( data, function (key, val){
                                if (key == 'STORES') {
                                    $.each(val, function(index, element){
                                        if(index == 'STORE') {
                                            $.each(element, function(store, attr){
                                                $('<p><h4>' + attr['NAME'] + ' ' + attr['DISTANCE'] + ' miles' + '</h4>' + attr['ADDRESS'] + '<br>' + attr['CITY'] + ', ' + attr['STATE'] + '  ' + attr['ZIP'] + '<br>' + attr['PHONE'] + '</p>').appendTo('#locationsList');
                                                var point = [attr['NAME'], attr['ADDRESS'], attr['CITY'] + ', ' + attr['STATE'] + ' ' + attr['ZIP'], attr['DISTANCE'] + ' miles', attr['PHONE'], attr['LATITUDE'], attr['LONGITUDE']];
                                                points.push(point);
                                            });
                                        }
                                    });
                                }
                            });

                            jQuery.getJSON('https://maps.googleapis.com/maps/api/geocode/json?address=' + $('#postalCode').val() + '&key=<?php echo $google_maps_api_key; ?> ', null, function (zipData) {
                                //console.log(zipData);
                                var p = zipData.results[0].geometry.location;
                                //var centerMap = new google.maps.LatLng(p.lat, p.lng);

                                newLocation(p.lat, p.lng);

                                function newLocation(newLat, newLng) {
                                    map.setCenter({
                                        lat: newLat,
                                        lng: newLng
                                    });
                                    //console.log(points);
                                    var locations = points;
                                    for (i = 0; i < locations.length; i++) {
                                        if (locations[i][0] =='undefined'){ storeName ='';} else { storeName = locations[i][0];}
                                        if (locations[i][1] =='undefined'){ storeAddress ='';} else { storeAddress = locations[i][1];}
                                        if (locations[i][2] =='undefined'){ storeCity ='';} else { storeCity = locations[i][2];}
                                        if (locations[i][3] =='undefined'){ storeDistance ='';} else { storeDistance = locations[i][3];}
                                        if (locations[i][4] =='undefined'){ storePhone ='';} else { storePhone = locations[i][4];}
                                        //var newGoogleTest = new google.maps.LatLng(locations[i][5], locations[i][6]);
                                        console.log(locations[i][5]);
                                        marker = new google.maps.Marker({
                                            position: new google.maps.LatLng(locations[i][5], locations[i][6]),
                                            map: map,
                                            title: 'Store Information',
                                            desc: storeName,
                                            addr: storeAddress,
                                            city: storeCity,
                                            distance: storeDistance,
                                            phone: storePhone
                                        });
                                        bindInfoWindow(marker, map, storeName, storeAddress, storeCity, storeDistance, storePhone);
                                        markers.push(marker);
                                    }
                                    function bindInfoWindow(marker, map, storeName, storeAddress, storeCity, storeDistance, storePhone) {
                                        var infoWindowVisible = (function () {
                                            var currentlyVisible = false;
                                            return function (visible) {
                                                if (visible !== undefined) {
                                                    currentlyVisible = visible;
                                                }
                                                return currentlyVisible;
                                            };
                                        }());
                                        iw = new google.maps.InfoWindow();
                                        google.maps.event.addListener(marker, 'click', function() {
                                            if (infoWindowVisible()) {
                                                iw.close();
                                                infoWindowVisible(false);
                                            } else {
                                                var html= "<div class='marker-info'><h4>"+storeName+"</h4><p>"+storeAddress+"<br>"+storeCity+"<br>"+storePhone+"<p><p>"+storeDistance+"<p></div>";
                                                iw = new google.maps.InfoWindow({content:html});
                                                iw.open(map,marker);
                                                infoWindowVisible(true);
                                            }
                                        });
                                        google.maps.event.addListener(iw, 'closeclick', function () {
                                            infoWindowVisible(false);
                                        });
                                    }
                                }
                            });
                            $('#noLocationsFoundText').attr('hidden', '');
                            $('#locations').removeAttr('hidden');
                            $('#locationsHeading').removeAttr('hidden');
                        } else {
                            //Remove markers  on reload
                            //marker.setMap(null);
                            //marker.setVisible(false);
                            $('#productNotFoundName').text($("#productID option:selected").text());
                            $('#productNotFoundZip').text($("#postalCode").val());
                            console.log("No locations found");
                            $('#locations, #noLocationsFoundText').removeAttr('hidden');
                            $('#locationsHeading').attr('hidden', '');
                        }

                        $('.search.locations').show().next('.loading').attr('hidden', '');
                    });
                });
            });
        </script>
        <?php
}
